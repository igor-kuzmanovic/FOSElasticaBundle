<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Transformer;

use Elastica\Document;
use FOS\ElasticaBundle\Event\PostTransformEvent;
use FOS\ElasticaBundle\Event\PreTransformEvent;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Maps Elastica documents with Doctrine objects
 * This mapper assumes an exact match between
 * elastica documents ids and doctrine object ids.
 *
 * @template T of object
 *
 * @implements ModelToElasticaTransformerInterface<T>
 *
 * @phpstan-import-type TFields from ModelToElasticaTransformerInterface
 *
 * @phpstan-type TOptions = array{identifier: string, index: string}
 */
class ModelToElasticaAutoTransformer implements ModelToElasticaTransformerInterface
{
    /**
     * Optional parameters.
     *
     * @var TOptions
     */
    protected array $options = [
        'identifier' => 'id',
        'index' => '',
    ];

    /**
     * PropertyAccessor instance.
     */
    protected ?PropertyAccessorInterface $propertyAccessor = null;

    /**
     * Instanciates a new Mapper.
     *
     * @param array<string, mixed> $options
     */
    public function __construct(
        array $options = [],
        protected ?EventDispatcherInterface $dispatcher = null,
    ) {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Set the PropertyAccessor.
     */
    public function setPropertyAccessor(PropertyAccessorInterface $propertyAccessor): void
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * Transforms an object into an elastica object having the required keys.
     *
     * @param T       $object
     * @param TFields $fields
     */
    public function transform(object $object, array $fields): Document
    {
        $identifier = $this->propertyAccessor->getValue($object, $this->options['identifier']);

        return $this->transformObjectToDocument($object, $fields, (string) $identifier);
    }

    /**
     * transform a nested document or an object property into an array of ElasticaDocument.
     *
     * @param array<T>|\Traversable<T>|\ArrayAccess<mixed, mixed>|null $objects the object to convert
     * @param TFields                                                  $fields  the keys we want to have in the returned array
     *
     * @return array<mixed>
     */
    protected function transformNested($objects, array $fields): ?array
    {
        if (is_iterable($objects)) {
            $documents = [];
            foreach ($objects as $object) {
                $document = $this->transformObjectToDocument($object, $fields);
                $documents[] = $document->getData();
            }

            return $documents;
        }

        if (null !== $objects) {
            $document = $this->transformObjectToDocument($objects, $fields);

            return $document->getData();
        }

        return null;
    }

    /**
     * TODO Breaking change.
     *
     * @return mixed|list<mixed>
     */
    protected function normalizeValue(mixed $value): mixed
    {
        $normalizeValue = static function (&$v): void {
            if ($v instanceof \DateTimeInterface) {
                $v = $v->format('c');
            } elseif ($v instanceof \DateInterval) {
                $v = $v->format('P%yY%mM%dDT%hH%iM%sS');
            } elseif ($v instanceof \BackedEnum) {
                $v = $v->value;
            } elseif (!\is_scalar($v) && null !== $v) {
                $v = (string) $v;
            }
        };

        if (is_iterable($value)) {
            $value = \is_array($value) ? $value : iterator_to_array($value, false);
            array_walk_recursive($value, $normalizeValue);
        } else {
            $normalizeValue($value);
        }

        return $value;
    }

    /**
     * Transforms the given object to an elastica document.
     *
     * @param T       $object
     * @param TFields $fields
     */
    protected function transformObjectToDocument(object $object, array $fields, string $identifier = ''): Document
    {
        $document = new Document($identifier, [], $this->options['index']);

        if ($this->dispatcher) {
            $this->dispatcher->dispatch($event = new PreTransformEvent($document, $fields, $object));

            $document = $event->getDocument();
        }

        foreach ($fields as $key => $mapping) {
            $path = $mapping['property_path'] ?? $key;
            if (false === $path) {
                continue;
            }
            $value = $this->propertyAccessor->getValue($object, $path);

            if (isset($mapping['properties'], $mapping['type'])
                && $mapping['properties']
                && \in_array($mapping['type'], ['nested', 'object'], true)
            ) {
                /* $value is a nested document or object. Transform $value into
                 * an array of documents, respective the mapped properties.
                 */
                $document->set($key, $this->transformNested($value, $mapping['properties']));

                continue;
            }

            if ('attachment' === ($mapping['type'] ?? null)) {
                // $value is an attachment. Add it to the document.
                if ($value instanceof \SplFileInfo) {
                    $document->addFile($key, $value->getPathName());
                } else {
                    $document->addFileContent($key, $value);
                }

                continue;
            }

            $document->set($key, $this->normalizeValue($value));
        }

        if ($this->dispatcher) {
            $this->dispatcher->dispatch($event = new PostTransformEvent($document, $fields, $object));

            $document = $event->getDocument();
        }

        return $document;
    }
}
