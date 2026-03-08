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

namespace FOS\ElasticaBundle\Tests\Unit\Configuration\Source;

use FOS\ElasticaBundle\Configuration\IndexTemplateConfig;
use FOS\ElasticaBundle\Configuration\Source\TemplateContainerSource;
use PHPUnit\Framework\TestCase;

/**
 * @author Dmitry Balabka <dmitry.balabka@intexsys.lv>
 *
 * @internal
 */
final class TemplateContainerSourceTest extends TestCase
{
    public function testGetEmptyConfiguration(): void
    {
        $containerSource = new TemplateContainerSource([]);
        $indexes = $containerSource->getConfiguration();
        $this->assertSame([], $indexes);
    }

    public function testGetConfiguration(): void
    {
        $containerSource = new TemplateContainerSource(
            [
                [
                    'name' => 'some_index_template',
                    'mapping' => [
                        'some_field' => [],
                    ],
                    'config' => [
                        'date_detection' => false,
                    ],
                    'elasticsearch_name' => 'some_search_name',
                    'settings' => [
                        'some_setting' => 'setting_value',
                    ],
                    'index_patterns' => ['some_index_config_*'],
                ],
            ]
        );
        $indexes = $containerSource->getConfiguration();
        $this->assertInstanceOf(IndexTemplateConfig::class, $indexes['some_index_template']);
        $templateConfig = $indexes['some_index_template'];
        $this->assertSame('some_index_template', $templateConfig->getName());
        $this->assertSame(['some_index_config_*'], $templateConfig->getIndexPatterns());
        $this->assertSame(
            [
                'some_setting' => 'setting_value',
            ],
            $templateConfig->getSettings()
        );
        $this->assertSame('some_search_name', $templateConfig->getElasticSearchName());
        $this->assertSame('some_index_template', $templateConfig->getName());
        $this->assertSame(['some_field' => []], $templateConfig->getMapping());
        $this->assertFalse($templateConfig->getDateDetection());
    }
}
