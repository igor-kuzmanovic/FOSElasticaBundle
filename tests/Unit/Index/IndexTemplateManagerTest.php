<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Index;

use Elastica\IndexTemplate;
use FOS\ElasticaBundle\Index\IndexTemplateManager;
use PHPUnit\Framework\TestCase;

/**
 * @author Dmitry Balabka <dmitry.balabka@intexsys.lv>
 *
 * @internal
 */
class IndexTemplateManagerTest extends TestCase
{
    /**
     * Test get index template.
     *
     * @param array<string, IndexTemplate>  $templates
     * @param class-string<\Throwable>|null $expectedException
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideTestGetIndexTemplate')]
    public function testGetIndexTemplate(array $templates, string $name, ?IndexTemplate $expectedTemplate, ?string $expectedException = null): void
    {
        if (null !== $expectedException) {
            $this->expectException($expectedException);
        }
        $templateManager = new IndexTemplateManager($templates);
        $this->assertSame($expectedTemplate, $templateManager->getIndexTemplate($name));
    }

    /**
     * @return array<string, array{
     *     templates: array<string, IndexTemplate>,
     *     name: string,
     *     expectedTemplate: IndexTemplate|null,
     *     expectedException?: class-string<\Throwable>
     * }>
     */
    public static function provideTestGetIndexTemplate(): array
    {
        return [
            'empty templates' => [
                'templates' => [],
                'name' => 'any template',
                'expectedTemplate' => null,
                'expectedException' => \InvalidArgumentException::class,
            ],
            'expected template found' => [
                'templates' => [
                    'first template' => $firstTemplate = static::createStub(IndexTemplate::class),
                    'second template' => $secondTemplate = static::createStub(IndexTemplate::class),
                ],
                'name' => 'second template',
                'expectedTemplate' => $secondTemplate,
            ],
            'expected template not found' => [
                'templates' => [
                    'first template' => $firstTemplate = static::createStub(IndexTemplate::class),
                    'second template' => $secondTemplate = static::createStub(IndexTemplate::class),
                ],
                'name' => 'some template',
                'expectedTemplate' => null,
                'expectedException' => \InvalidArgumentException::class,
            ],
        ];
    }
}
