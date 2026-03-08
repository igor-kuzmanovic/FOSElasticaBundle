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
 * Raw partial results transforms to a simple array.
 *
 * @extends AbstractPartialResults<array<string, mixed>>
 */
class RawPartialResults extends AbstractPartialResults
{
    /**
     * @return array<string, mixed>[]
     */
    public function toArray(): array
    {
        /** @var list<array<string, mixed>> $results */
        $results = [];
        foreach ($this->resultSet->getResults() as $result) {
            $results[] = $result->getSource();
        }

        return $results;
    }
}
