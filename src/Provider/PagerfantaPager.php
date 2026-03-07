<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Provider;

use Pagerfanta\Pagerfanta;

/**
 * @template TObject of object
 *
 * @implements PagerInterface<TObject>
 */
class PagerfantaPager implements PagerInterface
{
    /**
     * @param Pagerfanta<TObject> $pagerfanta
     */
    public function __construct(
        private readonly Pagerfanta $pagerfanta,
    ) {}

    public function getNbResults(): int
    {
        return $this->pagerfanta->getNbResults();
    }

    public function getNbPages(): int
    {
        return $this->pagerfanta->getNbPages();
    }

    public function getCurrentPage(): int
    {
        return $this->pagerfanta->getCurrentPage();
    }

    public function setCurrentPage(int $page): void
    {
        $this->pagerfanta->setCurrentPage($page);
    }

    public function getMaxPerPage(): int
    {
        return $this->pagerfanta->getMaxPerPage();
    }

    public function setMaxPerPage(int $perPage): void
    {
        $this->pagerfanta->setMaxPerPage($perPage);
    }

    /**
     * @return iterable<array-key, TObject>
     */
    public function getCurrentPageResults(): iterable
    {
        return $this->pagerfanta->getCurrentPageResults();
    }

    /**
     * @return Pagerfanta<TObject>
     */
    public function getPagerfanta(): Pagerfanta
    {
        return $this->pagerfanta;
    }
}
