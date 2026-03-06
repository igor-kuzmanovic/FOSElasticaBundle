<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle;

use FOS\ElasticaBundle\Finder\FinderInterface;
use FOS\ElasticaBundle\Finder\PaginatedFinderInterface;
use FOS\ElasticaBundle\Paginator\PaginatorAdapterInterface;
use Pagerfanta\Pagerfanta;

/**
 * @author Richard Miller <info@limethinking.co.uk>
 *
 * Basic repository to be extended to hold custom queries to be run
 * in the finder
 *
 * @phpstan-import-type TQuery from FinderInterface
 * @phpstan-import-type TOptions from FinderInterface
 */
class Repository
{
    public function __construct(
        protected PaginatedFinderInterface $finder,
    ) {}

    /**
     * @param TQuery   $query
     * @param TOptions $options
     *
     * @return array<object>
     */
    public function find($query, ?int $limit = null, array $options = []): array
    {
        return $this->finder->find($query, $limit, $options);
    }

    /**
     * @param TQuery   $query
     * @param TOptions $options
     *
     * @return list<HybridResult<object>>
     */
    public function findHybrid($query, ?int $limit = null, array $options = []): array
    {
        return $this->finder->findHybrid($query, $limit, $options);
    }

    /**
     * @param TQuery   $query
     * @param TOptions $options
     *
     * @return Pagerfanta<object>
     */
    public function findPaginated($query, array $options = []): Pagerfanta
    {
        return $this->finder->findPaginated($query, $options);
    }

    /**
     * @param TQuery   $query
     * @param TOptions $options
     *
     * @return PaginatorAdapterInterface<object>
     */
    public function createPaginatorAdapter($query, array $options = []): PaginatorAdapterInterface
    {
        return $this->finder->createPaginatorAdapter($query, $options);
    }

    /**
     * @param TQuery   $query
     * @param TOptions $options
     *
     * @return PaginatorAdapterInterface<HybridResult<object>>
     */
    public function createHybridPaginatorAdapter($query, array $options = []): PaginatorAdapterInterface
    {
        return $this->finder->createHybridPaginatorAdapter($query, $options);
    }
}
