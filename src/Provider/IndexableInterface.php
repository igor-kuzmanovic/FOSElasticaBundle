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

/**
 * @template TObject of object
 */
interface IndexableInterface
{
    /**
     * Checks if an object passed should be indexable or not.
     *
     * @param TObject $object
     */
    public function isObjectIndexable(string $indexName, object $object): bool;
}
