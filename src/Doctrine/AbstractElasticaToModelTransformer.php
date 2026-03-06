<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Doctrine;

use Doctrine\Persistence\ManagerRegistry;
use Elastica\Result;
use FOS\ElasticaBundle\HybridResult;
use FOS\ElasticaBundle\Transformer\AbstractElasticaToModelTransformer as BaseTransformer;
use FOS\ElasticaBundle\Transformer\HighlightableModelInterface;

/**
 * Maps Elastica documents with Doctrine objects
 * This mapper assumes an exact match between
 * elastica documents ids and doctrine object ids.
 */
abstract class AbstractElasticaToModelTransformer extends BaseTransformer
{
    /**
     * Optional parameters.
     *
     * @var array<string, mixed>
     */
    protected array $options = [
        'hints' => [],
        'hydrate' => true,
        'identifier' => 'id',
        'ignore_missing' => false,
        'query_builder_method' => 'createQueryBuilder',
    ];

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        protected ManagerRegistry $registry,
        /**
         * Class of the model to map to the elastica documents.
         *
         * @var class-string
         */
        protected string $objectClass,
        array $options = [],
    ) {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Returns the object class that is used for conversion.
     */
    public function getObjectClass(): string
    {
        return $this->objectClass;
    }

    /**
     * Transforms an array of elastica objects into an array of
     * model objects fetched from the doctrine repository.
     *
     * @param Result[] $elasticaObjects of elastica objects
     *
     * @throws \RuntimeException
     */
    public function transform(array $elasticaObjects): array
    {
        $ids = $highlights = [];
        foreach ($elasticaObjects as $elasticaObject) {
            $ids[] = $elasticaObject->getId();
            $highlights[$elasticaObject->getId()] = $elasticaObject->getHighlights();
        }

        $objects = $this->findByIdentifiers($ids, $this->options['hydrate']);
        $objectsCnt = \count($objects);
        $elasticaObjectsCnt = \count($elasticaObjects);
        $propertyAccessor = $this->propertyAccessor;
        $identifier = $this->options['identifier'];
        if (!$this->options['ignore_missing'] && $objectsCnt < $elasticaObjectsCnt) {
            $missingIds = array_diff($ids, array_map(static fn ($object): mixed => $propertyAccessor->getValue($object, $identifier), $objects));

            throw new \RuntimeException(\sprintf('Cannot find corresponding Doctrine objects (%d) for all Elastica results (%d). Missing IDs: %s. IDs: %s', $objectsCnt, $elasticaObjectsCnt, implode(', ', $missingIds), implode(', ', $ids)));
        }

        foreach ($objects as $object) {
            if ($object instanceof HighlightableModelInterface) {
                $id = $propertyAccessor->getValue($object, $identifier);
                $object->setElasticHighlights($highlights[(string) $id]);
            }
        }

        // sort objects in the order of ids
        $idPos = array_flip($ids);
        usort(
            $objects,
            function (object|array $a, object|array $b) use ($idPos, $identifier, $propertyAccessor): int {
                if ($this->options['hydrate']) {
                    return $idPos[(string) $propertyAccessor->getValue(
                        $a,
                        $identifier
                    )] <=> $idPos[(string) $propertyAccessor->getValue($b, $identifier)];
                }

                return $idPos[$a[$identifier]] <=> $idPos[$b[$identifier]];
            }
        );

        return $objects;
    }

    /**
     * @return list<HybridResult<object>>
     */
    public function hybridTransform(array $elasticaObjects): array
    {
        $indexedElasticaResults = [];
        foreach ($elasticaObjects as $elasticaObject) {
            $indexedElasticaResults[(string) $elasticaObject->getId()] = $elasticaObject;
        }

        $objects = $this->transform($elasticaObjects);

        $result = [];
        foreach ($objects as $object) {
            if ($this->options['hydrate']) {
                $id = $this->propertyAccessor->getValue($object, $this->options['identifier']);
            } else {
                $id = $object[$this->options['identifier']];
            }
            $result[] = new HybridResult($indexedElasticaResults[(string) $id], $object);
        }

        return $result;
    }

    public function getIdentifierField(): string
    {
        return $this->options['identifier'];
    }

    /**
     * Fetches objects by these identifier values.
     *
     * @param list<string> $identifierValues ids values
     * @param bool         $hydrate          whether or not to hydrate the objects, false returns arrays
     *
     * @return list<object|array<string, mixed>>
     */
    abstract protected function findByIdentifiers(array $identifierValues, bool $hydrate): array;
}
