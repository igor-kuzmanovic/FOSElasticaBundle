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

namespace FOS\ElasticaBundle\Tests\Unit\Paginator;

use Elastica\Query;
use Elastica\ResultSet;
use FOS\ElasticaBundle\Paginator\RawPaginatorAdapter;
use FOS\ElasticaBundle\Tests\Unit\UnitTestHelper;

/**
 * @internal
 */
final class RawPaginatorAdapterTest extends UnitTestHelper
{
    public function testGetTotalHits(): void
    {
        $adapter = $this->createAdapterWithCount(123);
        $this->assertSame(123, $adapter->getTotalHits());

        $adapter = $this->createAdapterWithCount(123, 100);
        $this->assertSame(100, $adapter->getTotalHits());
    }

    public function testGetTotalHitsGenuineTotal(): void
    {
        $adapter = $this->createAdapterWithCount(123);
        $this->assertSame(123, $adapter->getTotalHits(true));

        $adapter = $this->createAdapterWithCount(123, 100);
        $this->assertSame(123, $adapter->getTotalHits(true));
    }

    public function testGetAggregations(): void
    {
        $value = [];
        $adapter = $this->createAdapterWithSearch('getAggregations', $value);
        $this->assertSame($value, $adapter->getAggregations());
    }

    public function testGetSuggests(): void
    {
        $value = [];
        $adapter = $this->createAdapterWithSearch('getSuggests', $value);
        $this->assertSame($value, $adapter->getSuggests());
    }

    public function testGetMaxScore(): void
    {
        $value = 1.0;
        $adapter = $this->createAdapterWithSearch('getMaxScore', $value);
        $this->assertSame($value, $adapter->getMaxScore());
    }

    public function testGetQuery(): void
    {
        $this->mockResultSet();

        $query = new Query();
        $options = [];
        $searchable = $this->mockSearchable();

        $adapter = new RawPaginatorAdapter($searchable, $query, $options);
        $this->assertSame($query, $adapter->getQuery());
    }

    protected function mockResultSet(): ResultSet
    {
        $methods = ['getTotalHits', 'getAggregations', 'getSuggests', 'getMaxScore'];

        return $this
            ->getMockBuilder(ResultSet::class)
            ->disableOriginalConstructor()
            ->onlyMethods($methods)
            ->getMock()
        ;
    }

    private function createAdapterWithSearch(string $methodName, mixed $value): RawPaginatorAdapter
    {
        $resultSet = $this->mockResultSet();
        $resultSet
            ->expects($this->exactly(1))
            ->method($methodName)
            ->willReturn($value)
        ;

        $query = new Query();
        $options = [];
        $searchable = $this->mockSearchable();
        $searchable
            ->expects($this->exactly(1))
            ->method('search')
            ->with($query)
            ->willReturn($resultSet)
        ;

        return new RawPaginatorAdapter($searchable, $query, $options);
    }

    private function createAdapterWithCount(int $totalHits, ?int $querySize = null): RawPaginatorAdapter
    {
        $query = new Query();
        if ($querySize) {
            $query->setParam('size', $querySize);
        }
        $options = [];
        $searchable = $this->mockSearchable();
        $searchable
            ->expects($this->exactly(1))
            ->method('count')
            ->willReturn($totalHits)
        ;

        return new RawPaginatorAdapter($searchable, $query, $options);
    }
}
