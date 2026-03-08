<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Finder;

use FOS\ElasticaBundle\HybridResult;
use FOS\ElasticaBundle\Paginator\PaginatorAdapterInterface;
use Pagerfanta\Pagerfanta;

/**
 * @phpstan-import-type TQuery from FinderInterface
 * @phpstan-import-type TOptions from FinderInterface
 *
 * @template TObject of object
 *
 * @extends FinderInterface<TObject>
 *
 * @method Pagerfanta<HybridResult<TObject>> findHybridPaginated(TQuery $query, array<string, mixed> $options = [])            Searches for query hybrid results.
 * @method list<HybridResult<TObject>>       findHybrid(TQuery $query, ?int $limit = null, array<string, mixed> $options = [])
 */
interface PaginatedFinderInterface extends FinderInterface
{
    /**
     * Searches for query results and returns them wrapped in a paginator.
     *
     * @param TQuery   $query   Can be a string, an array or an \Elastica\Query object
     * @param TOptions $options
     *
     * @return Pagerfanta<TObject> paginated results
     */
    public function findPaginated(mixed $query, array $options = []): Pagerfanta;

    /**
     * Creates a paginator adapter for this query.
     *
     * @param TQuery   $query
     * @param TOptions $options
     *
     * @return PaginatorAdapterInterface<TObject>
     */
    public function createPaginatorAdapter(mixed $query, array $options = []): PaginatorAdapterInterface;

    /**
     * Creates a hybrid paginator adapter for this query.
     *
     * @param TQuery   $query
     * @param TOptions $options
     *
     * @return PaginatorAdapterInterface<HybridResult<TObject>>
     */
    public function createHybridPaginatorAdapter(mixed $query, array $options = []): PaginatorAdapterInterface;

    /**
     * Creates a raw paginator adapter for this query.
     *
     * @param TQuery   $query
     * @param TOptions $options
     *
     * @return PaginatorAdapterInterface<array<string, mixed>>
     */
    public function createRawPaginatorAdapter(mixed $query, array $options = []): PaginatorAdapterInterface;
}
