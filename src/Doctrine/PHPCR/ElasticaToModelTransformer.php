<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Doctrine\PHPCR;

use FOS\ElasticaBundle\Doctrine\AbstractElasticaToModelTransformer;

/**
 * Maps Elastica documents with Doctrine objects.
 * This mapper assumes an exact match between elastica documents ids and doctrine object ids.
 *
 * @template TObject of object
 *
 * @extends AbstractElasticaToModelTransformer<TObject>
 */
class ElasticaToModelTransformer extends AbstractElasticaToModelTransformer
{
    /**
     * Fetch objects for theses identifier values.
     *
     * @param list<string> $identifierValues ids values
     * @param bool         $hydrate          whether or not to hydrate the objects, false returns arrays
     *
     * @return list<TObject|array<string, mixed>>
     */
    protected function findByIdentifiers(array $identifierValues, bool $hydrate): array
    {
        return $this->registry
            ->getManager()
            ->getRepository($this->objectClass)
            // @phpstan-ignore method.notFound (The call is \Doctrine\ODM\PHPCR\DocumentRepository::findMany())
            ->findMany($identifierValues)
            ->toArray()
        ;
    }
}
