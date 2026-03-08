<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Doctrine;

use Doctrine\Persistence\ManagerRegistry;
use Elastica\Result;
use FOS\ElasticaBundle\Doctrine\AbstractElasticaToModelTransformer;
use FOS\ElasticaBundle\Doctrine\ORM\ElasticaToModelTransformer;
use FOS\ElasticaBundle\HybridResult;
use FOS\ElasticaBundle\Transformer\HighlightableModelInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @internal
 */
class AbstractElasticaToModelTransformerTest extends TestCase
{
    /**
     * @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $registry;

    /**
     * @var string
     */
    protected $objectClass = 'stdClass';

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
    }

    /**
     * Tests if ignore_missing option is properly handled in transformHybrid() method.
     */
    public function testIgnoreMissingOptionDuringTransformHybrid(): void
    {
        $transformer = $this->getMockBuilder(ElasticaToModelTransformer::class)
            ->onlyMethods(['findByIdentifiers'])
            ->setConstructorArgs([$this->registry, $this->objectClass, ['ignore_missing' => true]])
            ->getMock()
        ;

        $transformer->setPropertyAccessor(PropertyAccess::createPropertyAccessor());

        $firstOrmResult = new \stdClass();
        $firstOrmResult->id = 1;
        $secondOrmResult = new \stdClass();
        $secondOrmResult->id = 3;
        $transformer->expects($this->once())
            ->method('findByIdentifiers')
            ->with([1, 2, 3])
            ->willReturn([$firstOrmResult, $secondOrmResult])
        ;

        $firstElasticaResult = new Result(['_id' => 1]);
        $secondElasticaResult = new Result(['_id' => 2]);
        $thirdElasticaResult = new Result(['_id' => 3]);

        $hybridResults = $transformer->hybridTransform([$firstElasticaResult, $secondElasticaResult, $thirdElasticaResult]);

        $this->assertCount(2, $hybridResults);
        $this->assertSame($firstOrmResult, $hybridResults[0]->getTransformed());
        $this->assertSame($firstElasticaResult, $hybridResults[0]->getResult());
        $this->assertSame($secondOrmResult, $hybridResults[1]->getTransformed());
        $this->assertSame($thirdElasticaResult, $hybridResults[1]->getResult());
    }

    public function testObjectClassCanBeSet(): void
    {
        $transformer = $this->createMockTransformer();
        $this->assertSame(Foo::class, $transformer->getObjectClass());
    }

    /**
     * @param list<Result> $elasticaResults
     * @param list<Foo>    $doctrineObjects
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('resultsWithMatchingObjects')]
    public function testObjectsAreTransformedByFindingThemByTheirIdentifiers(array $elasticaResults, array $doctrineObjects): void
    {
        $transformer = $this->createMockTransformer();

        $transformer
            ->expects($this->once())
            ->method('findByIdentifiers')
            ->with($this->equalTo([1, 2, 3]), $this->isType('boolean'))
            ->willReturn($doctrineObjects)
        ;

        $transformedObjects = $transformer->transform($elasticaResults);

        $this->assertSame($doctrineObjects, $transformedObjects);
    }

    /**
     * @param list<Result> $elasticaResults
     * @param list<Foo>    $doctrineObjects
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('resultsWithMatchingObjects')]
    public function testAnExceptionIsThrownWhenTheNumberOfFoundObjectsIsLessThanTheNumberOfResults(
        array $elasticaResults,
        array $doctrineObjects,
    ): void {
        $transformer = $this->createMockTransformer();

        $transformer
            ->expects($this->once())
            ->method('findByIdentifiers')
            ->with($this->equalTo([1, 2, 3]), $this->isType('boolean'))
            ->willReturn([])
        ;

        $this->expectExceptionMessage(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot find corresponding Doctrine objects (0) for all Elastica results (3). Missing IDs: 1, 2, 3. IDs: 1, 2, 3');

        $transformer->transform($elasticaResults);
    }

    /**
     * @param list<Result> $elasticaResults
     * @param list<Foo>    $doctrineObjects
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('resultsWithMatchingObjects')]
    public function testAnExceptionIsNotThrownWhenTheNumberOfFoundObjectsIsLessThanTheNumberOfResultsIfOptionSet(
        array $elasticaResults,
        array $doctrineObjects,
    ): void {
        $transformer = $this->createMockTransformer(['ignore_missing' => true]);

        $transformer
            ->expects($this->once())
            ->method('findByIdentifiers')
            ->with($this->equalTo([1, 2, 3]), $this->isType('boolean'))
            ->willReturn([])
        ;

        $results = $transformer->transform($elasticaResults);

        $this->assertSame([], $results);
    }

    /**
     * @param list<Result> $elasticaResults
     * @param list<Foo>    $doctrineObjects
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('resultsWithMatchingObjects')]
    public function testHighlightsAreSetOnTransformedObjects(array $elasticaResults, array $doctrineObjects): void
    {
        $transformer = $this->createMockTransformer();

        $transformer
            ->expects($this->once())
            ->method('findByIdentifiers')
            ->with($this->equalTo([1, 2, 3]), $this->isType('boolean'))
            ->willReturn($doctrineObjects)
        ;

        $results = $transformer->transform($elasticaResults);

        foreach ($results as $result) {
            $this->assertIsArray($result->highlights);
            $this->assertNotEmpty($result->highlights);
        }
    }

    /**
     * @param list<Result> $elasticaResults
     * @param list<Foo>    $doctrineObjects
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('resultsWithMatchingObjects')]
    public function testResultsAreSortedByIdentifier(array $elasticaResults, array $doctrineObjects): void
    {
        rsort($doctrineObjects);

        $transformer = $this->createMockTransformer();

        $transformer
            ->expects($this->once())
            ->method('findByIdentifiers')
            ->with($this->equalTo([1, 2, 3]), $this->isType('boolean'))
            ->willReturn($doctrineObjects)
        ;

        $results = $transformer->transform($elasticaResults);

        $this->assertSame($doctrineObjects[2], $results[0]);
        $this->assertSame($doctrineObjects[1], $results[1]);
        $this->assertSame($doctrineObjects[0], $results[2]);
    }

    /**
     * @param list<Result> $elasticaResults
     * @param list<Foo>    $doctrineObjects
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('resultsWithMatchingObjects')]
    public function testHybridTransformReturnsDecoratedResults(array $elasticaResults, array $doctrineObjects): void
    {
        $transformer = $this->createMockTransformer();

        $transformer
            ->expects($this->once())
            ->method('findByIdentifiers')
            ->with($this->equalTo([1, 2, 3]), $this->isType('boolean'))
            ->willReturn($doctrineObjects)
        ;

        $results = $transformer->hybridTransform($elasticaResults);

        $this->assertNotEmpty($results);

        foreach ($results as $key => $result) {
            // @phpstan-ignore method.alreadyNarrowedType (Test validates hybrid result type)
            $this->assertInstanceOf(HybridResult::class, $result);
            $this->assertSame($elasticaResults[$key], $result->getResult());
            $this->assertSame($doctrineObjects[$key], $result->getTransformed());
        }
    }

    /**
     * @return array<array{list<Result>, list<Foo>}>
     */
    public static function resultsWithMatchingObjects(): array
    {
        $elasticaResults = $doctrineObjects = [];
        for ($i = 1; $i < 4; ++$i) {
            $elasticaResults[] = new Result(['_id' => $i, 'highlight' => ['foo']]);
            $doctrineObjects[] = new Foo($i);
        }

        return [
            [$elasticaResults, $doctrineObjects],
        ];
    }

    public function testIdentifierFieldDefaultsToId(): void
    {
        $transformer = $this->createMockTransformer();
        $this->assertSame('id', $transformer->getIdentifierField());
    }

    /**
     * @return PropertyAccessorInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private function createMockPropertyAccessor(): PropertyAccessorInterface
    {
        $callback = static fn ($object, $identifier) => $object->{$identifier};

        $propertyAccessor = $this->createMock(PropertyAccessorInterface::class);
        $propertyAccessor
            ->expects($this->any())
            ->method('getValue')
            ->with($this->isType('object'), $this->isType('string'))
            ->willReturnCallback($callback)
        ;

        return $propertyAccessor;
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return AbstractElasticaToModelTransformer<Foo>&\PHPUnit\Framework\MockObject\MockObject
     */
    private function createMockTransformer(array $options = []): AbstractElasticaToModelTransformer
    {
        $objectClass = Foo::class;
        $propertyAccessor = $this->createMockPropertyAccessor();

        $transformer = $this->getMockBuilder(AbstractElasticaToModelTransformer::class)
            ->setConstructorArgs([$this->registry, $objectClass, $options])
            ->onlyMethods(['findByIdentifiers'])
            ->getMock();

        $transformer->setPropertyAccessor($propertyAccessor);

        return $transformer;
    }
}

class Foo implements HighlightableModelInterface
{
    public mixed $id;
    /**
     * @var list<array<mixed>>|null
     */
    public ?array $highlights = null;

    public function __construct(mixed $id)
    {
        $this->id = $id;
    }

    public function getId(): mixed
    {
        return $this->id;
    }

    /**
     * @param list<array<mixed>> $highlights
     */
    public function setElasticHighlights(array $highlights): void
    {
        $this->highlights = $highlights;
    }
}
