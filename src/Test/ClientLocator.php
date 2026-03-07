<?php

declare(strict_types=1);

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Test;

use FOS\ElasticaBundle\Elastica\Client;

class ClientLocator
{
    /**
     * @param list<Client> $clients
     */
    public function __construct(
        // @phpstan-ignore property.onlyWritten (Used only in tests to forbid container to remove clients.)
        private readonly array $clients,
    ) {}
}
