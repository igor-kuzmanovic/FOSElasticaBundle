<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Event;

use Elastica\Document;
use FOS\ElasticaBundle\Transformer\ModelToElasticaAutoTransformer;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @template TObject of object
 *
 * @phpstan-import-type TFields from ModelToElasticaAutoTransformer
 */
abstract class AbstractTransformEvent extends Event
{
    /**
     * @param TFields $fields
     * @param TObject $object
     */
    public function __construct(
        protected Document $document,
        private readonly array $fields,
        private readonly object $object,
    ) {}

    public function getDocument(): Document
    {
        return $this->document;
    }

    /**
     * @return TFields
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @return TObject
     */
    public function getObject(): object
    {
        return $this->object;
    }
}
