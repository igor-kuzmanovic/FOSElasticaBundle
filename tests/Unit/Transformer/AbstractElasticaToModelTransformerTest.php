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

namespace FOS\ElasticaBundle\Tests\Unit\Transformer;

use FOS\ElasticaBundle\Tests\Unit\UnitTestHelper;
use FOS\ElasticaBundle\Transformer\AbstractElasticaToModelTransformer;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @internal
 */
final class AbstractElasticaToModelTransformerTest extends UnitTestHelper
{
    public function testSetPropertyAccessor(): void
    {
        $propertyAccessor = $this->mockPropertyAccesor();
        $transformer = $this->mockAbstractElasticaToModelTransformer();
        $transformer->setPropertyAccessor($propertyAccessor);
        $this->assertSame($propertyAccessor, $this->getProtectedProperty($transformer, 'propertyAccessor'));
    }

    /**
     * @return AbstractElasticaToModelTransformer<object>
     */
    protected function mockAbstractElasticaToModelTransformer(): AbstractElasticaToModelTransformer
    {
        return $this
            ->getMockBuilder(AbstractElasticaToModelTransformer::class)
            ->onlyMethods(['transform', 'hybridTransform', 'getObjectClass', 'getIdentifierField'])
            ->getMock()
        ;
    }

    protected function mockPropertyAccesor(): PropertyAccessorInterface
    {
        return $this->createMock(PropertyAccessorInterface::class);
    }
}
