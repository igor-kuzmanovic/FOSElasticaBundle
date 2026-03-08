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

namespace FOS\ElasticaBundle\Tests\Unit\Persister;

use FOS\ElasticaBundle\Message\AsyncPersistPage;
use FOS\ElasticaBundle\Persister\AsyncPagerPersister;
use FOS\ElasticaBundle\Persister\PagerPersisterInterface;
use FOS\ElasticaBundle\Persister\PagerPersisterRegistry;
use FOS\ElasticaBundle\Provider\PagerInterface;
use FOS\ElasticaBundle\Provider\PagerProviderRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @internal
 */
final class AsyncPagerPersisterTest extends TestCase
{
    public function testShouldImplementPagerPersisterInterface(): void
    {
        $reflectionClass = new \ReflectionClass(AsyncPagerPersister::class);
        $this->assertTrue($reflectionClass->implementsInterface(PagerPersisterInterface::class));
    }

    public function testInsertDispatchAsyncPersistPageObject(): void
    {
        $pagerPersisterRegistry = new PagerPersisterRegistry($this->createStub(ServiceLocator::class));
        $pagerProviderRegistry = $this->createStub(PagerProviderRegistry::class);
        $messageBus = $this->createMock(MessageBusInterface::class);
        $sut = new AsyncPagerPersister($pagerPersisterRegistry, $pagerProviderRegistry, $messageBus);

        $messageBus->expects($this->once())->method('dispatch')->willReturnCallback(
            function (object $message): Envelope {
                $this->assertInstanceOf(AsyncPersistPage::class, $message);

                // @phpstan-ignore argument.type (test verifies dispatch mechanics; empty options are valid at runtime and completed later by persister defaults)
                return new Envelope(new AsyncPersistPage(0, []));
            }
        );

        $pager = $this->createStub(PagerInterface::class);
        $sut->insert($pager);
    }
}
