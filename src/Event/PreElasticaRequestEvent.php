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

use Symfony\Contracts\EventDispatcher\Event;

class PreElasticaRequestEvent extends Event
{
    /**
     * @param array<string, mixed>|string $data
     * @param array<string, mixed>        $query
     */
    public function __construct(
        private readonly string $path,
        private readonly string $method,
        private readonly array|string $data,
        private readonly array $query,
        private readonly string $contentType = 'application/json',
    ) {}

    public function getPath(): string
    {
        return $this->path;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return array<string, mixed>|string
     */
    public function getData(): array|string
    {
        return $this->data;
    }

    /**
     * @return array<string, mixed>
     */
    public function getQuery(): array
    {
        return $this->query;
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }
}
