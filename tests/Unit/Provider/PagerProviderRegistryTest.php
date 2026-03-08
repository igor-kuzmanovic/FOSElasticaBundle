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

namespace FOS\ElasticaBundle\Tests\Unit\Provider;

use FOS\ElasticaBundle\Provider\PagerProviderInterface;
use FOS\ElasticaBundle\Provider\PagerProviderRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * @internal
 */
final class PagerProviderRegistryTest extends TestCase
{
    public function testGetProviders(): void
    {
        $service = $this->createStub(PagerProviderInterface::class);

        $providers = new ServiceLocator([
            'index' => static fn (): \PHPUnit\Framework\MockObject\Stub => $service,
        ]);

        $registry = new PagerProviderRegistry($providers);
        $this->assertSame(['index' => $service], $registry->getProviders());
    }

    public function testGetProviderValid(): void
    {
        $service = $this->createStub(PagerProviderInterface::class);

        $providers = new ServiceLocator([
            'index' => static fn (): \PHPUnit\Framework\MockObject\Stub => $service,
        ]);

        $registry = new PagerProviderRegistry($providers);
        $this->assertSame($service, $registry->getProvider('index'));
    }

    public function testGetProviderInvalid(): void
    {
        $registry = new PagerProviderRegistry(new ServiceLocator([]));
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No provider was registered for index "index".');
        $registry->getProvider('index');
    }
}
