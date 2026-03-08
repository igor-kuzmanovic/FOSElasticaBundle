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

namespace FOS\ElasticaBundle\Tests\Unit\Provider;

use FOS\ElasticaBundle\Provider\Indexable;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class IndexableTest extends TestCase
{
    public function testIndexableUnknown(): void
    {
        $indexable = new Indexable([]);
        $index = $indexable->isObjectIndexable('index', new Entity());

        $this->assertTrue($index);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideIsIndexableCallbacks')]
    public function testValidIndexableCallbacks(mixed $callback, bool $return): void
    {
        $indexable = new Indexable([
            'index' => $callback,
        ]);
        $index = $indexable->isObjectIndexable('index', new Entity());

        $this->assertSame($return, $index);
    }

    /**
     * @return \Iterator<int<0, max>, array{mixed, bool}>
     */
    public static function provideIsIndexableCallbacks(): \Iterator
    {
        yield ['isIndexable', false];
        yield [[new IndexableDecider(), 'isIndexable'], true];
        yield [new IndexableDecider(), true];
        yield [static fn (Entity $entity): bool => $entity->maybeIndex(), true];
        yield ['entity.maybeIndex()', true];
        yield ['!object.isIndexable() && entity.property == "abc"', true];
        yield ['entity.property != "abc"', false];
        yield ['["array", "values"]', true];
        yield ['[]', false];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideInvalidIsIndexableCallbacks')]
    public function testInvalidIsIndexableCallbacks(mixed $callback): void
    {
        $indexable = new Indexable([
            'index' => $callback,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $indexable->isObjectIndexable('index', new Entity());
    }

    /**
     * @return \Iterator<int<0, max>, array{mixed}>
     */
    public static function provideInvalidIsIndexableCallbacks(): \Iterator
    {
        yield ['nonexistentEntityMethod'];
        yield [[new IndexableDecider(), 'internalMethod']];
        yield [42];
        yield ['entity.getIsIndexable() && nonexistentEntityFunction()'];
    }
}

class Entity
{
    public string $property = 'abc';

    public function isIndexable(): bool
    {
        return false;
    }

    public function maybeIndex(): bool
    {
        return true;
    }
}

class IndexableDecider
{
    public function __invoke(object $object): bool
    {
        return true;
    }

    public function isIndexable(Entity $entity): bool
    {
        return !$entity->isIndexable();
    }

    protected function internalMethod(): void {}
}
