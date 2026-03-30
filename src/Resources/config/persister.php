<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use FOS\ElasticaBundle\Persister\InPlacePagerPersister;
use FOS\ElasticaBundle\Persister\Listener\FilterObjectsListener;
use FOS\ElasticaBundle\Persister\ObjectPersister;
use FOS\ElasticaBundle\Persister\ObjectSerializerPersister;
use FOS\ElasticaBundle\Persister\PagerPersisterRegistry;
use FOS\ElasticaBundle\Persister\PersisterRegistry;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('fos_elastica.in_place_pager_persister', InPlacePagerPersister::class)
        ->args([
            service('fos_elastica.persister_registry'),
            service('event_dispatcher'),
        ])
        ->tag('fos_elastica.pager_persister', ['persisterName' => 'in_place'])
    ;

    $services->set('fos_elastica.pager_persister_registry', PagerPersisterRegistry::class)
        ->args([tagged_locator('fos_elastica.pager_persister', 'persisterName')])
    ;

    $services->set('fos_elastica.persister_registry', PersisterRegistry::class)
        ->args([tagged_locator('fos_elastica.persister', 'index')])
    ;

    $services->set('fos_elastica.filter_objects_listener', FilterObjectsListener::class)
        ->args([service('fos_elastica.indexable')])
        ->tag('kernel.event_subscriber')
    ;

    $services->set('fos_elastica.object_persister', ObjectPersister::class)
        ->abstract()
        ->args([
            abstract_arg('index'),
            abstract_arg('model to elastica transformer'),
            abstract_arg('model'),
            abstract_arg('properties mapping'),
            abstract_arg('options'),
        ])
    ;

    $services->set('fos_elastica.object_serializer_persister', ObjectSerializerPersister::class)
        ->abstract()
        ->args([
            abstract_arg('type'),
            abstract_arg('model to elastica transformer'),
            abstract_arg('model'),
            abstract_arg('serializer'),
            abstract_arg('options'),
        ])
    ;
};
