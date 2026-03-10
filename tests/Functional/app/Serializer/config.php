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

    $doctrineBundleVersion = class_exists(\Composer\InstalledVersions::class)
        ? \Composer\InstalledVersions::getVersion('doctrine/doctrine-bundle')
        : null;
    $isDoctrineBundle3 = null !== $doctrineBundleVersion && version_compare($doctrineBundleVersion, '3.0.0', '>=');

    $doctrineOrmConfig = [
        'auto_mapping' => false,
    ];

    if (!$isDoctrineBundle3) {
        $doctrineOrmConfig['auto_generate_proxy_classes'] = false;
        $doctrineOrmConfig['controller_resolver'] = [
            'auto_mapping' => false,
        ];
    }

    if (!$isDoctrineBundle3 && \PHP_VERSION_ID >= 80400 && \Symfony\Component\HttpKernel\Kernel::VERSION_ID >= 80000) {
        $doctrineOrmConfig['enable_native_lazy_objects'] = true;
    }

    $container->extension('doctrine', [
        'dbal' => [
            'path' => '%kernel.cache_dir%/db.sqlite',
            'charset' => 'UTF8',
        ],
        'orm' => $doctrineOrmConfig,
    ]);

    $container->services()
        ->set('indexableService', \FOS\ElasticaBundle\Tests\Functional\app\ORM\IndexableService::class)
    ;

    $container->extension('jms_serializer', [
        'metadata' => [
            'auto_detection' => true,
            'directories' => [
                'type_obj' => [
                    'namespace_prefix' => 'FOS\ElasticaBundle\Tests\Functional',
                    'path' => '%kernel.project_dir%/Serializer',
                ],
            ],
        ],
    ]);

    $container->extension('fos_elastica', [
        'clients' => [
            'default' => [
                'hosts' => [
                    'http://%fos_elastica.host%:%fos_elastica.port%',
                ],
            ],
        ],
        'serializer' => [
            'serializer' => 'jms_serializer',
        ],
        'indexes' => [
            'index' => [
                'index_name' => 'foselastica_ser_test_%kernel.environment%',
                'persistence' => [
                    'driver' => 'orm',
                    'model' => \FOS\ElasticaBundle\Tests\Functional\TypeObj::class,
                ],
                'serializer' => [
                    'groups' => [
                        'search',
                        'Default',
                    ],
                    'version' => 1.1,
                ],
                'properties' => [
                    'coll' => null,
                    'field1' => null,
                ],
            ],
            'index_serialize_null_disabled' => [
                'persistence' => [
                    'driver' => 'orm',
                    'model' => \FOS\ElasticaBundle\Tests\Functional\TypeObj::class,
                ],
                'serializer' => [
                    'serialize_null' => false,
                ],
                'properties' => [
                    'field1' => null,
                ],
            ],
            'index_serialize_null_enabled' => [
                'persistence' => [
                    'driver' => 'orm',
                    'model' => \FOS\ElasticaBundle\Tests\Functional\TypeObj::class,
                ],
                'serializer' => [
                    'serialize_null' => true,
                ],
                'properties' => [
                    'field1' => null,
                ],
            ],
            'index_unmapped' => [
                'persistence' => [
                    'driver' => 'orm',
                    'model' => \FOS\ElasticaBundle\Tests\Functional\TypeObj::class,
                ],
                'serializer' => [
                    'groups' => [
                        'search',
                        'Default',
                    ],
                    'version' => 1.1,
                ],
            ],
        ],
    ]);
};
