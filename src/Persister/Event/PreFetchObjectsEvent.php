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

final class PreFetchObjectsEvent extends Event implements PersistEvent
{
    /**
     * @param PagerInterface<object>           $pager
     * @param ObjectPersisterInterface<object> $objectPersister
     * @param array<string, mixed>             $options
     */
    public function __construct(
        private PagerInterface $pager,
        private ObjectPersisterInterface $objectPersister,
        private array $options,
    ) {}

    /**
     * @return PagerInterface<object>
     */
    public function getPager(): PagerInterface
    {
        return $this->pager;
    }

    /**
     * @param PagerInterface<object> $pager
     */
    public function setPager(PagerInterface $pager): void
    {
        $this->pager = $pager;
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    /**
     * @return ObjectPersisterInterface<object>
     */
    public function getObjectPersister(): ObjectPersisterInterface
    {
        return $this->objectPersister;
    }

    /**
     * @param ObjectPersisterInterface<object> $objectPersister
     */
    public function setObjectPersister(ObjectPersisterInterface $objectPersister): void
    {
        $this->objectPersister = $objectPersister;
    }
}
