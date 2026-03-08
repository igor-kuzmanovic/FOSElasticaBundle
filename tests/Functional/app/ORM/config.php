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

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container): void {
    $container->import(__DIR__.'/./../config/config.php');

    $container->extension('doctrine', [
        'dbal' => [
            'path' => '%kernel.cache_dir%/db.sqlite',
            'charset' => 'UTF8',
        ],
        'orm' => [
            'auto_generate_proxy_classes' => false,
            'auto_mapping' => false,
        ],
    ]);

    $container->services()
        ->alias('test_alias.fos_elastica.indexable', 'fos_elastica.indexable')
            ->public()

        ->set('indexable_service', \FOS\ElasticaBundle\Tests\Functional\app\ORM\IndexableService::class)

        ->alias('test_alias.fos_elastica.manager.orm', 'fos_elastica.manager.orm')
            ->public()
    ;

    $container->extension('fos_elastica', [
        'clients' => [
            'default' => [
                'hosts' => [
                    'http://%fos_elastica.host%:%fos_elastica.port%',
                ],
            ],
        ],
        'indexes' => [
            'fos_elastica_orm_test' => [
                'properties' => [
                    'field1' => null,
                ],
            ],
            'index' => [
                'index_name' => 'foselastica_orm_test_%kernel.environment%',
                'indexable_callback' => 'object.isIndexable() && !object.isntIndexable()',
                'persistence' => [
                    'driver' => 'orm',
                    'model' => \FOS\ElasticaBundle\Tests\Functional\TypeObj::class,
                    'listener' => null,
                    'provider' => [
                        'debug_logging' => true,
                    ],
                ],
                'properties' => [
                    'field1' => null,
                    'coll' => null,
                ],
            ],
            'second_index' => [
                'index_name' => 'foselastica_orm_test_second_%kernel.environment%',
                'indexable_callback' => 'object.isIndexable() && !object.isntIndexable()',
                'persistence' => [
                    'driver' => 'orm',
                    'model' => \FOS\ElasticaBundle\Tests\Functional\TypeObj::class,
                    'listener' => null,
                ],
                'properties' => [
                    'field1' => null,
                    'coll' => null,
                ],
            ],
            'third_index' => [
                'index_name' => 'foselastica_orm_test_third_%kernel.environment%',
                'indexable_callback' => [
                    service('indexable_service'),
                    'isIndexable',
                ],
                'persistence' => [
                    'driver' => 'orm',
                    'model' => \FOS\ElasticaBundle\Tests\Functional\TypeObj::class,
                    'listener' => null,
                ],
                'properties' => [
                    'field1' => null,
                ],
            ],
            'fourth_index' => [
                'index_name' => 'foselastica_orm_test_fourth_%kernel.environment%',
                'indexable_callback' => 'isntIndexable',
                'persistence' => [
                    'driver' => 'orm',
                    'model' => \FOS\ElasticaBundle\Tests\Functional\TypeObj::class,
                    'finder' => null,
                    'provider' => null,
                    'listener' => null,
                ],
                'properties' => [
                    'field1' => null,
                ],
            ],
            'fifth_index' => [
                'index_name' => 'foselastica_orm_test_fifth_%kernel.environment%',
                'indexable_callback' => [
                    \FOS\ElasticaBundle\Tests\Functional\app\ORM\IndexableService::class,
                    'isntIndexable',
                ],
                'persistence' => [
                    'driver' => 'orm',
                    'model' => \FOS\ElasticaBundle\Tests\Functional\TypeObj::class,
                    'finder' => null,
                    'provider' => null,
                    'listener' => null,
                ],
                'properties' => [
                    'field1' => null,
                ],
            ],
            'property_paths_index' => [
                'index_name' => 'foselastica_orm_test_%kernel.environment%',
                'persistence' => [
                    'driver' => 'orm',
                    'model' => \FOS\ElasticaBundle\Tests\Functional\TypeObj::class,
                    'provider' => null,
                ],
                'properties' => [
                    'field1' => [
                        'property_path' => 'field2',
                    ],
                    'something' => [
                        'property_path' => 'coll',
                    ],
                    'dynamic' => [
                        'property_path' => false,
                    ],
                ],
            ],
            'with_repository_index' => [
                'index_name' => 'foselastica_orm_test_%kernel.environment%',
                'persistence' => [
                    'driver' => 'orm',
                    'model' => \FOS\ElasticaBundle\Tests\Functional\TypeObject::class,
                    'repository' => \FOS\ElasticaBundle\Tests\Functional\TypeObjectRepository::class,
                    'finder' => null,
                    'provider' => null,
                ],
                'properties' => [
                    'field1' => null,
                    'coll' => null,
                ],
            ],
        ],
    ]);
};
