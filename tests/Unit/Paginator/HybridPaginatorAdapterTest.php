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
use FOS\ElasticaBundle\Paginator\HybridPaginatorAdapter;
use FOS\ElasticaBundle\Tests\Unit\UnitTestHelper;

/**
 * @internal
 */
final class HybridPaginatorAdapterTest extends UnitTestHelper
{
    public function testGetResults(): void
    {
        $searchable = $this->mockSearchable();
        $query = new Query();
        $transformer = $this->mockElasticaToModelTransformer();

        $adapter = $this->mockHybridPaginatorAdapter([$searchable, $query, [], $transformer]);
        $adapter->getResults(0, 0);
    }

    /**
     * @param array<int, mixed> $args
     *
     * @return HybridPaginatorAdapter<object>
     */
    protected function mockHybridPaginatorAdapter(array $args): HybridPaginatorAdapter
    {
        $mock = $this
            ->getMockBuilder(HybridPaginatorAdapter::class)
            ->setConstructorArgs($args)
            ->onlyMethods(['getElasticaResults'])
            ->getMock()
        ;

        $resultSet = $this->mockResultSet();

        $mock
            ->expects($this->exactly(1))
            ->method('getElasticaResults')
            ->willReturn($resultSet)
        ;

        return $mock;
    }
}
