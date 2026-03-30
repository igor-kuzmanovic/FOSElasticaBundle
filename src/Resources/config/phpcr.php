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

use FOS\ElasticaBundle\Doctrine\Listener;
use FOS\ElasticaBundle\Doctrine\PHPCR\ElasticaToModelTransformer;
use FOS\ElasticaBundle\Doctrine\PHPCRPagerProvider;
use FOS\ElasticaBundle\Doctrine\RegisterListenersService;
use FOS\ElasticaBundle\Doctrine\RepositoryManager;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('fos_elastica.pager_provider.prototype.phpcr', PHPCRPagerProvider::class)
        ->abstract()
        ->args([
            service('doctrine_phpcr'),
            service('fos_elastica.doctrine.register_listeners'),
            abstract_arg('model'),
            [],
        ])
    ;

    $services->set('fos_elastica.doctrine.register_listeners', RegisterListenersService::class)
        ->args([service('event_dispatcher')])
    ;

    $services->set('fos_elastica.listener.prototype.phpcr', Listener::class)
        ->abstract()
        ->args([
            abstract_arg('object persister'),
            service('fos_elastica.indexable'),
            [],
            null,
        ])
    ;

    $services->set('fos_elastica.elastica_to_model_transformer.prototype.phpcr', ElasticaToModelTransformer::class)
        ->abstract()
        ->args([
            service('doctrine_phpcr'),
            abstract_arg('model'),
            [],
        ])
        ->call('setPropertyAccessor', [service('fos_elastica.property_accessor')])
    ;

    $services->set('fos_elastica.manager.phpcr', RepositoryManager::class)
        ->args([
            service('doctrine_phpcr'),
            service('fos_elastica.repository_manager'),
        ])
    ;
};
