<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Paginator;

use Pagerfanta\Adapter\AdapterInterface;

/**
 * @template TResult
 *
 * @implements AdapterInterface<TResult>
 */
class FantaPaginatorAdapter implements AdapterInterface
{
    /**
     * @param PaginatorAdapterInterface<TResult> $adapter
     */
    public function __construct(
        private readonly PaginatorAdapterInterface $adapter,
    ) {}

    /**
     * Returns the number of results.
     */
    public function getNbResults(): int
    {
        return $this->adapter->getTotalHits();
    }

    /**
     * Returns Aggregations.
     *
     * @return array<string, mixed>
     *
     * @api
     */
    public function getAggregations(): array
    {
        return $this->adapter->getAggregations();
    }

    /**
     * Returns Suggestions.
     *
     * @return array<string, mixed>
     *
     * @api
     */
    public function getSuggests(): array
    {
        return $this->adapter->getSuggests();
    }

    /**
     * Returns a slice of the results.
     *
     * @param int $offset The offset
     * @param int $length The length
     *
     * @return iterable<array-key, TResult>
     */
    public function getSlice(int $offset, int $length): iterable
    {
        return $this->adapter->getResults($offset, $length)->toArray();
    }

    public function getMaxScore(): float
    {
        return $this->adapter->getMaxScore();
    }
}
