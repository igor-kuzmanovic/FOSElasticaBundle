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

namespace FOS\ElasticaBundle\Tests\Unit\Doctrine\ORM;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use FOS\ElasticaBundle\Doctrine\ORM\ElasticaToModelTransformer;
use FOS\ElasticaBundle\Tests\Unit\Mocks\DoctrineORMCustomRepositoryMock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ElasticaToModelTransformerTest extends TestCase
{
    public const OBJECT_CLASS = \stdClass::class;

    /**
     * @var ManagerRegistry&MockObject
     */
    private MockObject $registry;

    /**
     * @var DoctrineORMCustomRepositoryMock&MockObject
     */
    private MockObject $repository;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $manager = $this->createMock(ObjectManager::class);

        $this->registry
            ->method('getManagerForClass')
            ->with(self::OBJECT_CLASS)
            ->willReturn($manager)
        ;

        $this->repository = $this
            ->getMockBuilder(DoctrineORMCustomRepositoryMock::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'createQueryBuilder',
                'customQueryBuilderCreator',
                'find',
                'findAll',
                'findBy',
                'findOneBy',
                'getClassName',
            ])->getMock()
        ;

        $manager
            ->method('getRepository')
            ->with(self::OBJECT_CLASS)
            ->willReturn($this->repository)
        ;
    }

    /**
     * Tests that the Transformer uses the query_builder_method configuration option
     * allowing configuration of createQueryBuilder call.
     */
    public function testTransformUsesQueryBuilderMethodConfiguration(): void
    {
        $qb = $this->createStub(QueryBuilder::class);

        $this->repository->expects($this->once())
            ->method('customQueryBuilderCreator')
            ->with(ElasticaToModelTransformer::ENTITY_ALIAS)
            ->willReturn($qb)
        ;
        $this->repository->expects($this->never())
            ->method('createQueryBuilder')
        ;

        $transformer = new ElasticaToModelTransformer($this->registry, self::OBJECT_CLASS, [
            'query_builder_method' => 'customQueryBuilderCreator',
        ]);

        $class = new \ReflectionClass(ElasticaToModelTransformer::class);
        $method = $class->getMethod('getEntityQueryBuilder');

        $method->invokeArgs($transformer, []);
    }

    /**
     * Tests that the Transformer uses the query_builder_method configuration option
     * allowing configuration of createQueryBuilder call.
     */
    public function testTransformUsesDefaultQueryBuilderMethodConfiguration(): void
    {
        $qb = $this->createStub(QueryBuilder::class);

        $this->repository->expects($this->never())
            ->method('customQueryBuilderCreator')
        ;
        $this->repository->expects($this->once())
            ->method('createQueryBuilder')
            ->with(ElasticaToModelTransformer::ENTITY_ALIAS)
            ->willReturn($qb)
        ;

        $transformer = new ElasticaToModelTransformer($this->registry, self::OBJECT_CLASS);

        $class = new \ReflectionClass(ElasticaToModelTransformer::class);
        $method = $class->getMethod('getEntityQueryBuilder');

        $method->invokeArgs($transformer, []);
    }

    /**
     * Checks that the 'hints' parameter is used on the created query.
     */
    public function testUsesHintsConfigurationIfGiven(): void
    {
        $query = $this->getMockBuilder(Query::class)
            ->onlyMethods(['setHint', 'execute', 'setHydrationMode'])
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $query->method('setHydrationMode')->willReturnSelf();
        $query->method('execute')->willReturn([]);
        $query->expects($this->once())  //  check if the hint is set
            ->method('setHint')
            ->with('customHintName', 'Custom\Hint\Class')
            ->willReturnSelf()
        ;

        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('getQuery')->willReturn($query);
        $qb->method('expr')->willReturn($this->createStub(Expr::class));
        $qb->method('andWhere')->willReturnSelf();

        $this->repository->expects($this->once())
            ->method('createQueryBuilder')
            ->with(ElasticaToModelTransformer::ENTITY_ALIAS)
            ->willReturn($qb)
        ;

        $transformer = new ElasticaToModelTransformer($this->registry, self::OBJECT_CLASS, [
            'hints' => [
                ['name' => 'customHintName', 'value' => 'Custom\Hint\Class'],
            ],
        ]);

        $class = new \ReflectionClass(ElasticaToModelTransformer::class);
        $method = $class->getMethod('findByIdentifiers');

        $method->invokeArgs($transformer, [[1, 2, 3], /* $hydrate */ true]);
    }
}
