<?php

declare(strict_types=1);

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Finder;

use FOS\ElasticaBundle\Paginator\PaginatorAdapterInterface;
use Pagerfanta\PagerfantaInterface;

/**
 * @template TRaw of array<string, mixed>
 *
 * @phpstan-import-type TQuery from FinderInterface
 * @phpstan-import-type TOptions from FinderInterface
 */
interface PaginatedRawFinderInterface extends RawFinderInterface
{
    /**
     * Searches for query raw results and returns them wrapped in a paginator.
     *
     * @param TQuery   $query
     * @param TOptions $options
     *
     * @return PagerfantaInterface<TRaw>
     */
    public function findRawPaginated(mixed $query, array $options = []): PagerfantaInterface;

    /**
     * Creates a raw paginator adapter for this query.
     *
     * @param TQuery   $query
     * @param TOptions $options
     *
     * @return PaginatorAdapterInterface<TRaw>
     */
    public function createRawPaginatorAdapter(mixed $query, array $options = []): PaginatorAdapterInterface;
}
