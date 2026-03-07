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
use FOS\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface;

/**
 * Partial transformed result set.
 *
 * @template TObject of object
 *
 * @extends AbstractPartialResults<TObject>
 */
class TransformedPartialResults extends AbstractPartialResults
{
    /**
     * @param ElasticaToModelTransformerInterface<TObject> $transformer
     */
    public function __construct(
        ResultSet $resultSet,
        protected ElasticaToModelTransformerInterface $transformer,
    ) {
        parent::__construct($resultSet);
    }

    /**
     * @return list<TObject>
     */
    public function toArray(): array
    {
        return $this->transformer->transform($this->resultSet->getResults());
    }
}
