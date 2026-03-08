<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Doctrine;

use FOS\ElasticaBundle\Doctrine\ConditionalUpdate;

class ConditionalUpdateEntity implements ConditionalUpdate
{
    public mixed $identifier = null;

    public function __construct(private readonly int $id, private bool $shouldBeUpdated = true) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function shouldBeUpdated(): bool
    {
        return $this->shouldBeUpdated;
    }

    public function setShouldBeUpdated(bool $shouldBeUpdated): void
    {
        $this->shouldBeUpdated = $shouldBeUpdated;
    }
}
