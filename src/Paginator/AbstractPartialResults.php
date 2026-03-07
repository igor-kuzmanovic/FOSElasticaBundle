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

use Elastica\ResultSet;

/**
 * Base partial results implementation.
 *
 * @template TResult
 *
 * @implements PartialResultsInterface<TResult>
 */
abstract class AbstractPartialResults implements PartialResultsInterface
{
    public function __construct(
        protected ResultSet $resultSet,
    ) {}

    public function getTotalHits(): int
    {
        return $this->resultSet->getTotalHits();
    }

    /**
     * @return array<string, mixed>
     */
    public function getAggregations(): array
    {
        return $this->resultSet->getAggregations();
    }

    /**
     * @return array<string, mixed>
     */
    public function getSuggests(): array
    {
        return $this->resultSet->getSuggests();
    }
}
