<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Persister;

use Elastica\Document;
use Elastica\Exception\BulkException;
use Elastica\Index;
use FOS\ElasticaBundle\Transformer\ModelToElasticaTransformerInterface;
use Psr\Log\LoggerInterface;

/**
 * Inserts, replaces and deletes single documents in an elastica type
 * Accepts domain model objects and converts them to elastica documents.
 *
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 *
 * @template T of object
 *
 * @implements ObjectPersisterInterface<T>
 *
 * @phpstan-type TOptions = array<string, mixed>
 *
 * @phpstan-import-type TFields from ModelToElasticaTransformerInterface
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/docs-bulk.html for TOptions description
 */
class ObjectPersister implements ObjectPersisterInterface
{
    protected ?LoggerInterface $logger = null;

    /**
     * @param ModelToElasticaTransformerInterface<T> $transformer
     * @param class-string<T>                        $objectClass
     * @param TFields                                $fields
     * @param TOptions                               $options
     */
    public function __construct(
        protected Index $index,
        protected ModelToElasticaTransformerInterface $transformer,
        protected string $objectClass,
        protected array $fields,
        private readonly array $options = [],
    ) {}

    /**
     * @template TObject of object
     *
     * @param TObject $object
     */
    public function handlesObject(object $object): bool
    {
        return $object instanceof $this->objectClass;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @param T $object
     */
    public function insertOne(object $object): void
    {
        $this->insertMany([$object]);
    }

    /**
     * @param T $object
     */
    public function replaceOne(object $object): void
    {
        $this->replaceMany([$object]);
    }

    /**
     * @param T $object
     */
    public function deleteOne(object $object): void
    {
        $this->deleteMany([$object]);
    }

    public function deleteById(string $id, string|bool $routing = false): void
    {
        $this->deleteManyByIdentifiers([$id], $routing);
    }

    /**
     * @param list<object> $objects
     */
    public function insertMany(array $objects): void
    {
        $documents = [];
        foreach ($objects as $object) {
            $documents[] = $this->transformToElasticaDocument($object);
        }
        try {
            $this->index->addDocuments($documents, $this->options);
        } catch (BulkException $e) {
            $this->log($e);
        }
    }

    /**
     * @param list<object> $objects
     */
    public function replaceMany(array $objects): void
    {
        $documents = [];
        foreach ($objects as $object) {
            $document = $this->transformToElasticaDocument($object);
            $document->setDocAsUpsert(true);
            $documents[] = $document;
        }

        try {
            $this->index->updateDocuments($documents, $this->options);
        } catch (BulkException $e) {
            $this->log($e);
        }
    }

    /**
     * @param list<object> $objects
     */
    public function deleteMany(array $objects): void
    {
        $documents = [];
        foreach ($objects as $object) {
            $documents[] = $this->transformToElasticaDocument($object);
        }
        try {
            $this->index->deleteDocuments($documents);
        } catch (BulkException $e) {
            $this->log($e);
        }
    }

    /**
     * @param list<string> $identifiers
     */
    public function deleteManyByIdentifiers(array $identifiers, string|bool $routing = false): void
    {
        try {
            $this->index->getClient()->deleteIds($identifiers, $this->index->getName(), $routing);
        } catch (BulkException $e) {
            $this->log($e);
        }
    }

    /**
     * Transforms an object to an elastica document.
     */
    public function transformToElasticaDocument(object $object): Document
    {
        return $this->transformer->transform($object, $this->fields);
    }

    /**
     * Log exception if logger defined for persister belonging to the current listener, otherwise re-throw.
     *
     * @throws BulkException
     */
    private function log(BulkException $e): void
    {
        if (!$this->logger) {
            throw $e;
        }

        $this->logger->error($e);
    }
}
