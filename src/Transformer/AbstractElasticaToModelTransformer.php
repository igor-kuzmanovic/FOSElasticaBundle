<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Transformer;

use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @template TObject of object
 *
 * @implements ElasticaToModelTransformerInterface<TObject>
 */
abstract class AbstractElasticaToModelTransformer implements ElasticaToModelTransformerInterface
{
    /**
     * PropertyAccessor instance.
     */
    protected ?PropertyAccessorInterface $propertyAccessor = null;

    /**
     * Set the PropertyAccessor instance.
     */
    public function setPropertyAccessor(PropertyAccessorInterface $propertyAccessor): void
    {
        $this->propertyAccessor = $propertyAccessor;
    }
}
