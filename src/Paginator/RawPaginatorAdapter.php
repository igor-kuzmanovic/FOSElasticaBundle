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

/**
 * Allows pagination of Elastica\Query. Does not map results.
 *
 * @template TRaw of array<string, mixed>
 *
 * @extends AbstractPaginatorAdapter<TRaw>
 */
class RawPaginatorAdapter extends AbstractPaginatorAdapter
{
    public function getResults(int $offset, int $itemCountPerPage): PartialResultsInterface
    {
        /** @var RawPartialResults<TRaw> $results */
        $results = new RawPartialResults($this->getElasticaResults($offset, $itemCountPerPage));

        return $results;
    }
}
