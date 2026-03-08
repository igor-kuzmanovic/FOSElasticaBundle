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

namespace FOS\ElasticaBundle\Tests\Unit\Doctrine;

use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ODM\PHPCR\DocumentRepository;
use Doctrine\ODM\PHPCR\Query\Builder\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use FOS\ElasticaBundle\Doctrine\PHPCRPagerProvider;
use FOS\ElasticaBundle\Doctrine\RegisterListenersService;
use FOS\ElasticaBundle\Provider\PagerfantaPager;
use FOS\ElasticaBundle\Provider\PagerInterface;
use FOS\ElasticaBundle\Provider\PagerProviderInterface;
use FOS\ElasticaBundle\Tests\Unit\Mocks\DoctrinePHPCRCustomRepositoryMock;
use Pagerfanta\Doctrine\PHPCRODM\QueryAdapter;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class PHPCRPagerProviderTest extends TestCase
{
    protected function setUp(): void
    {
        if (!class_exists(DocumentManager::class)) {
            $this->markTestSkipped('Doctrine PHPCR is not present');
        }
    }

    public function testShouldImplementPagerProviderInterface(): void
    {
        $rc = new \ReflectionClass(PHPCRPagerProvider::class);

        $this->assertTrue($rc->implementsInterface(PagerProviderInterface::class));
    }

    public function testCouldBeConstructedWithExpectedArguments(): void
    {
        $doctrine = $this->createDoctrineMock();
        $objectClass = \stdClass::class;
        $baseConfig = [];

        new PHPCRPagerProvider($doctrine, $this->createRegisterListenersServiceMock(), $objectClass, $baseConfig);
    }

    public function testShouldReturnPagerfanataPagerWithDoctrineODMMongoDBAdapter(): void
    {
        $objectClass = \stdClass::class;
        $baseConfig = ['query_builder_method' => 'createQueryBuilder'];

        $expectedBuilder = $this->createStub(QueryBuilder::class);

        $repository = $this->createMock(DocumentRepository::class);
        $repository
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($expectedBuilder)
        ;

        $manager = $this->createMock(DocumentManager::class);
        $manager
            ->expects($this->once())
            ->method('getRepository')
            ->with($objectClass)
            ->willReturn($repository)
        ;

        $doctrine = $this->createDoctrineMock();
        $doctrine
            ->expects($this->once())
            ->method('getManagerForClass')
            ->with($objectClass)
            ->willReturn($manager)
        ;

        $provider = new PHPCRPagerProvider($doctrine, $this->createRegisterListenersServiceMock(), $objectClass, $baseConfig);

        $pager = $provider->provide();

        $this->assertInstanceOf(PagerfantaPager::class, $pager);

        $adapter = $pager->getPagerfanta()->getAdapter();
        $this->assertInstanceOf(QueryAdapter::class, $adapter);
    }

    public function testShouldAllowCallCustomRepositoryMethod(): void
    {
        $objectClass = \stdClass::class;
        $baseConfig = ['query_builder_method' => 'createQueryBuilder'];

        $repository = $this->createMock(DoctrinePHPCRCustomRepositoryMock::class);
        $repository
            ->expects($this->once())
            ->method('createCustomQueryBuilder')
            ->willReturn($this->createStub(QueryBuilder::class))
        ;

        $manager = $this->createMock(DocumentManager::class);
        $manager
            ->expects($this->once())
            ->method('getRepository')
            ->willReturn($repository)
        ;

        $doctrine = $this->createDoctrineMock();
        $doctrine
            ->expects($this->once())
            ->method('getManagerForClass')
            ->willReturn($manager)
        ;

        $provider = new PHPCRPagerProvider($doctrine, $this->createRegisterListenersServiceMock(), $objectClass, $baseConfig);

        $pager = $provider->provide(['query_builder_method' => 'createCustomQueryBuilder']);

        $this->assertInstanceOf(PagerfantaPager::class, $pager);
    }

    public function testShouldCallRegisterListenersService(): void
    {
        $objectClass = \stdClass::class;
        $baseConfig = ['query_builder_method' => 'createQueryBuilder'];

        $queryBuilder = $this->createStub(QueryBuilder::class);

        $repository = $this->createMock(DocumentRepository::class);
        $repository
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder)
        ;

        $manager = $this->createMock(DocumentManager::class);
        $manager
            ->expects($this->once())
            ->method('getRepository')
            ->willReturn($repository)
        ;

        $doctrine = $this->createDoctrineMock();
        $doctrine
            ->expects($this->once())
            ->method('getManagerForClass')
            ->willReturn($manager)
        ;

        $registerListenersMock = $this->createRegisterListenersServiceMock();
        $registerListenersMock
            ->expects($this->once())
            ->method('register')
            ->with($this->identicalTo($manager), $this->isInstanceOf(PagerInterface::class), $baseConfig)
        ;

        $provider = new PHPCRPagerProvider($doctrine, $registerListenersMock, $objectClass, $baseConfig);

        $provider->provide();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|ManagerRegistry
     */
    private function createDoctrineMock(): ManagerRegistry
    {
        return $this->createMock(ManagerRegistry::class);
    }

    /**
     * @return RegisterListenersService|\PHPUnit\Framework\MockObject\MockObject
     */
    private function createRegisterListenersServiceMock(): RegisterListenersService
    {
        return $this->createMock(RegisterListenersService::class);
    }
}
