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

/**
 * @template TObject of object
 *
 * @extends AbstractTransformEvent<TObject>
 */
final class PreTransformEvent extends AbstractTransformEvent
{
    /**
     * @return PreTransformEvent<TObject>
     */
    public function setDocument(Document $document): self
    {
        $this->document = $document;

        return $this;
    }
}
