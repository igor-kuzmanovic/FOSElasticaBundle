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

namespace FOS\ElasticaBundle\Tests\Unit\Index;

use FOS\ElasticaBundle\Configuration\ConfigManager;
use FOS\ElasticaBundle\Configuration\IndexConfig;
use FOS\ElasticaBundle\Elastica\Index;
use FOS\ElasticaBundle\Event\PostIndexResetEvent;
use FOS\ElasticaBundle\Event\PreIndexResetEvent;
use FOS\ElasticaBundle\Index\AliasProcessor;
use FOS\ElasticaBundle\Index\IndexManager;
use FOS\ElasticaBundle\Index\MappingBuilder;
use FOS\ElasticaBundle\Index\Resetter;
use FOS\ElasticaBundle\Index\ResetterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
final class ResetterTest extends TestCase
{
    private Resetter $resetter;

    /**
     * @var AliasProcessor&MockObject
     */
    private MockObject $aliasProcessor;
    /**
     * @var ConfigManager&MockObject
     */
    private MockObject $configManager;
    /**
     * @var EventDispatcherInterface&MockObject
     */
    private MockObject $dispatcher;
    /**
     * @var IndexManager&MockObject
     */
    private MockObject $indexManager;
    /**
     * @var MappingBuilder&MockObject
     */
    private MockObject $mappingBuilder;

    protected function setUp(): void
    {
        $this->aliasProcessor = $this->createMock(AliasProcessor::class);
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->indexManager = $this->createMock(IndexManager::class);
        $this->mappingBuilder = $this->createMock(MappingBuilder::class);

        $this->resetter = new Resetter(
            $this->configManager,
            $this->indexManager,
            $this->aliasProcessor,
            $this->mappingBuilder,
            $this->dispatcher
        );
    }

    public function testResetAllIndexes(): void
    {
        $indexName = 'index1';
        $indexConfig = new IndexConfig([
            'name' => $indexName,
            'config' => [],
            'mapping' => [],
        ]);
        $mapping = ['map' => 'ping'];
        $index = $this->mockIndex($indexName, $indexConfig, $mapping);

        $this->configManager->expects($this->once())
            ->method('getIndexNames')
            ->willReturn([$indexName])
        ;

        $this->dispatcherExpects([
            [$this->isInstanceOf(PreIndexResetEvent::class)],
            [$this->isInstanceOf(PostIndexResetEvent::class)],
        ]);

        $index->expects($this->once())
            ->method('create')
            ->with($mapping, ['recreate' => true])
        ;

        $this->resetter->resetAllIndexes();
    }

    public function testResetIndex(): void
    {
        $indexConfig = new IndexConfig([
            'name' => 'index1',
            'config' => [],
            'mapping' => [],
        ]);
        $mapping = ['map' => 'ping'];
        $index = $this->mockIndex('index1', $indexConfig, $mapping);

        $this->dispatcherExpects([
            [$this->isInstanceOf(PreIndexResetEvent::class)],
            [$this->isInstanceOf(PostIndexResetEvent::class)],
        ]);

        $index->expects($this->once())
            ->method('create')
            ->with($mapping, ['recreate' => true])
        ;

        $this->resetter->resetIndex('index1');
    }

    public function testResetIndexWithDifferentNameAndAlias(): void
    {
        $indexConfig = new IndexConfig([
            'name' => 'index1',
            'elasticSearchName' => 'notIndex1',
            'use_alias' => true,
            'config' => [],
            'mapping' => [],
        ]);
        $mapping = ['map' => 'ping'];
        $index = $this->mockIndex('index1', $indexConfig, $mapping);
        $this->dispatcherExpects([
            [$this->isInstanceOf(PreIndexResetEvent::class)],
            [$this->isInstanceOf(PostIndexResetEvent::class)],
        ]);

        $this->aliasProcessor->expects($this->once())
            ->method('switchIndexAlias')
            ->with($indexConfig, $index, false)
        ;

        $index->expects($this->once())
            ->method('create')
            ->with($mapping, ['recreate' => true])
        ;

        $this->resetter->resetIndex('index1');
    }

    public function testFailureWhenMissingIndexDoesntDispatch(): void
    {
        $this->configManager->expects($this->once())
            ->method('getIndexConfiguration')
            ->with('nonExistant')
            ->willThrowException(new \InvalidArgumentException())
        ;

        $this->indexManager->expects($this->never())
            ->method('getIndex')
        ;

        $this->expectException(\InvalidArgumentException::class);
        $this->resetter->resetIndex('nonExistant');
    }

    public function testPostPopulateWithoutAlias(): void
    {
        $this->mockIndex('index', new IndexConfig([
            'name' => 'index',
            'config' => [],
            'mapping' => [],
        ]));

        $this->indexManager->expects($this->never())
            ->method('getIndex')
        ;
        $this->aliasProcessor->expects($this->never())
            ->method('switchIndexAlias')
        ;

        $this->resetter->switchIndexAlias('index');
    }

    public function testPostPopulate(): void
    {
        $indexConfig = new IndexConfig([
            'name' => 'index1',
            'use_alias' => true,
            'config' => [],
            'mapping' => [],
        ]);
        $index = $this->mockIndex('index', $indexConfig);

        $this->aliasProcessor->expects($this->once())
            ->method('switchIndexAlias')
            ->with($indexConfig, $index)
        ;

        $this->resetter->switchIndexAlias('index');
    }

    public function testResetterImplementsResetterInterface(): void
    {
        $this->assertInstanceOf(ResetterInterface::class, $this->resetter);
    }

    /**
     * @param array<int, array<int, mixed>> $events
     */
    private function dispatcherExpects(array $events): void
    {
        $matcher = $this->exactly(\count($events));
        $this->dispatcher->expects($matcher)
            ->method('dispatch')
            ->willReturnCallback(function (...$args) use ($matcher, $events): object {
                $expectedArgs = $events[$matcher->numberOfInvocations() - 1];

                foreach ($expectedArgs as $index => $expectedArg) {
                    $actualArg = $args[$index] ?? null;

                    if ($expectedArg instanceof \PHPUnit\Framework\Constraint\Constraint) {
                        $this->assertThat($actualArg, $expectedArg);

                        continue;
                    }

                    $this->assertEquals($expectedArg, $actualArg);
                }

                return $args[0] ?? null;
            })
        ;
    }

    /**
     * @param array<string, mixed> $mapping
     */
    private function mockIndex(string $indexName, IndexConfig $config, array $mapping = []): Index&MockObject
    {
        $this->configManager->expects($this->atLeast(1))
            ->method('getIndexConfiguration')
            ->with($indexName)
            ->willReturn($config)
        ;
        $index = $this->createMock(Index::class);
        $this->indexManager
            ->method('getIndex')
            ->with($indexName)
            ->willReturn($index)
        ;
        $this->mappingBuilder
            ->method('buildIndexMapping')
            ->with($config)
            ->willReturn($mapping)
        ;

        return $index;
    }
}
