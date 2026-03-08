<?php

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
class IndexableTest extends TestCase
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
     * @return list<array{0: mixed, 1: bool}>
     */
    public static function provideIsIndexableCallbacks(): array
    {
        return [
            ['isIndexable', false],
            [[new IndexableDecider(), 'isIndexable'], true],
            [new IndexableDecider(), true],
            [static fn (Entity $entity) => $entity->maybeIndex(), true],
            ['entity.maybeIndex()', true],
            ['!object.isIndexable() && entity.property == "abc"', true],
            ['entity.property != "abc"', false],
            ['["array", "values"]', true],
            ['[]', false],
        ];
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
     * @return list<array{0: mixed}>
     */
    public static function provideInvalidIsIndexableCallbacks(): array
    {
        return [
            ['nonexistentEntityMethod'],
            [[new IndexableDecider(), 'internalMethod']],
            [42],
            ['entity.getIsIndexable() && nonexistentEntityFunction()'],
        ];
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
