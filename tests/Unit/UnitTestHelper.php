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

namespace FOS\ElasticaBundle\Tests\Unit;

use Elastica\ResultSet;
use Elastica\SearchableInterface;
use FOS\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class UnitTestHelper extends TestCase
{
    /**
     * Gets a protected property on a given object via reflection.
     *
     * @param object $object   instance in which protected value is being modified
     * @param string $property property on instance being modified
     */
    protected function getProtectedProperty(object $object, string $property): mixed
    {
        $reflection = new \ReflectionClass($object);
        $reflectionProperty = $reflection->getProperty($property);

        return $reflectionProperty->getValue($object);
    }

    /**
     * @return ElasticaToModelTransformerInterface<object>&\PHPUnit\Framework\MockObject\MockObject
     */
    protected function mockElasticaToModelTransformer(): ElasticaToModelTransformerInterface
    {
        return $this->createMock(ElasticaToModelTransformerInterface::class)
        ;
    }

    /**
     * @return SearchableInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    protected function mockSearchable(): SearchableInterface
    {
        return $this->createMock(SearchableInterface::class)
        ;
    }

    /**
     * @return ResultSet&\PHPUnit\Framework\MockObject\MockObject
     */
    protected function mockResultSet(): ResultSet
    {
        return $this->createMock(ResultSet::class)
        ;
    }
}
