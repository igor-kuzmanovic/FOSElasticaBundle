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

use Elastica\Result;

/**
 * Raw partial results transforms to a simple array.
 *
 * @template TRaw of array<string, mixed>
 *
 * @extends AbstractPartialResults<TRaw>
 */
class RawPartialResults extends AbstractPartialResults
{
    /**
     * @return list<TRaw>
     */
    public function toArray(): array
    {
        return array_map(static fn (Result $result): array => $result->getSource(), $this->resultSet->getResults());
    }
}
