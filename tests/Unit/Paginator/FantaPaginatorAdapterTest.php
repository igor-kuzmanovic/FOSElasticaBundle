<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Paginator;

use FOS\ElasticaBundle\Paginator\FantaPaginatorAdapter;
use FOS\ElasticaBundle\Paginator\PaginatorAdapterInterface;
use FOS\ElasticaBundle\Paginator\PartialResultsInterface;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class FantaPaginatorAdapterTest extends TestCase
{
    public function testGetNbResults(): void
    {
        /** @var PaginatorAdapterInterface<mixed>&\PHPUnit\Framework\MockObject\MockObject $mock */
        $mock = $this->mockPaginatorAdapter();
        $mock
            ->expects($this->exactly(1))
            ->method('getTotalHits')
            ->willReturn(123)
        ;
        $adapter = new FantaPaginatorAdapter($mock);
        $this->assertSame(123, $adapter->getNbResults());
    }

    public function testGetAggregations(): void
    {
        /** @var PaginatorAdapterInterface<mixed>&\PHPUnit\Framework\MockObject\MockObject $mock */
        $mock = $this->mockPaginatorAdapter();
        $mock
            ->expects($this->exactly(1))
            ->method('getAggregations')
            ->willReturn([])
        ;
        $adapter = new FantaPaginatorAdapter($mock);
        $this->assertSame([], $adapter->getAggregations());
    }

    public function testGetSuggests(): void
    {
        /** @var PaginatorAdapterInterface<mixed>&\PHPUnit\Framework\MockObject\MockObject $mock */
        $mock = $this->mockPaginatorAdapter();
        $mock
            ->expects($this->exactly(1))
            ->method('getSuggests')
            ->willReturn([])
        ;
        $adapter = new FantaPaginatorAdapter($mock);
        $this->assertSame([], $adapter->getSuggests());
    }

    public function testGetGetSlice(): void
    {
        $results = [];
        $resultsMock = $this->mockPartialResults($results);

        /** @var PaginatorAdapterInterface<mixed>&\PHPUnit\Framework\MockObject\MockObject $mock */
        $mock = $this->mockPaginatorAdapter();
        $mock
            ->expects($this->exactly(1))
            ->method('getResults')
            ->with(1, 10)
            ->willReturn($resultsMock)
        ;
        $adapter = new FantaPaginatorAdapter($mock);
        $this->assertSame($results, $adapter->getSlice(1, 10));
    }

    public function testGetMaxScore(): void
    {
        /** @var PaginatorAdapterInterface<mixed>&\PHPUnit\Framework\MockObject\MockObject $mock */
        $mock = $this->mockPaginatorAdapter();
        $mock
            ->expects($this->exactly(1))
            ->method('getMaxScore')
            ->willReturn(123.0)
        ;
        $adapter = new FantaPaginatorAdapter($mock);
        $this->assertSame(123.0, $adapter->getMaxScore());
    }

    /**
     * @param list<mixed> $results
     *
     * @return PartialResultsInterface<mixed>
     */
    private function mockPartialResults(array $results): PartialResultsInterface
    {
        $mock = $this
            ->getMockBuilder(PartialResultsInterface::class)
            ->getMock()
        ;
        $mock
            ->expects($this->exactly(1))
            ->method('toArray')
            ->willReturn($results)
        ;

        return $mock;
    }

    /**
     * @return PaginatorAdapterInterface<mixed>
     */
    private function mockPaginatorAdapter(): PaginatorAdapterInterface
    {
        return $this
            ->getMockBuilder(PaginatorAdapterInterface::class)
            ->getMock()
        ;
    }
}
