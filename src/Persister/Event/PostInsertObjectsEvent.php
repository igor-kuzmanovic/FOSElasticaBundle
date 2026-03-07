<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Persister\Event;

use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use FOS\ElasticaBundle\Provider\PagerInterface;
use Symfony\Contracts\EventDispatcher\Event;

final class PostInsertObjectsEvent extends Event implements PersistEvent
{
    /**
     * @param PagerInterface<object>           $pager
     * @param ObjectPersisterInterface<object> $objectPersister
     * @param list<object>                     $objects
     * @param array<string, mixed>             $options
     */
    public function __construct(
        private readonly PagerInterface $pager,
        private readonly ObjectPersisterInterface $objectPersister,
        private readonly array $objects,
        private readonly array $options,
        private readonly int $filteredObjectCount = 0,
    ) {}

    /**
     * @return PagerInterface<object>
     */
    public function getPager(): PagerInterface
    {
        return $this->pager;
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @return ObjectPersisterInterface<object>
     */
    public function getObjectPersister(): ObjectPersisterInterface
    {
        return $this->objectPersister;
    }

    /**
     * @return list<object>
     */
    public function getObjects(): array
    {
        return $this->objects;
    }

    public function getFilteredObjectCount(): int
    {
        return $this->filteredObjectCount;
    }
}
