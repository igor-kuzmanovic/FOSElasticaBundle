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

return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('fos_elastica.property_accessor.magicCall', 0)
        ->set('fos_elastica.property_accessor.throwExceptionOnInvalidIndex', 0)
    ;

    $container->services()
        ->set('fos_elastica.client_prototype', \FOS\ElasticaBundle\Elastica\Client::class)
            ->abstract()
            ->args([
                '$config' => abstract_arg('configuration for Ruflin Client'),
                '$forbiddenCodes' => abstract_arg('list of forbidden codes for Client'),
                '$logger' => abstract_arg('logger for Ruflin Client'),
            ])
            ->call('setStopwatch', [service('debug.stopwatch')->nullOnInvalid()])
            ->call('setEventDispatcher', [service('event_dispatcher')->nullOnInvalid()])

        ->set(\FOS\ElasticaBundle\Elastica\NodePool\RoundRobinResurrect::class)
            ->factory([null, 'create'])

        ->set(\FOS\ElasticaBundle\Elastica\NodePool\RoundRobinNoResurrect::class)
            ->factory([null, 'create'])

        ->set('fos_elastica.config_manager', \FOS\ElasticaBundle\Configuration\ConfigManager::class)
            ->args([[]]) // collection of SourceInterface services

        ->alias(\FOS\ElasticaBundle\Configuration\ConfigManager::class, 'fos_elastica.config_manager')

        ->set('fos_elastica.config_manager.index_templates', \FOS\ElasticaBundle\Configuration\ConfigManager::class)
            ->args([[]]) // collection of SourceInterface services

        ->set('fos_elastica.data_collector', \FOS\ElasticaBundle\DataCollector\ElasticaDataCollector::class)
            ->args([service('fos_elastica.logger')])
            ->tag('data_collector', ['template' => '@FOSElastica/Collector/elastica.html.twig', 'id' => 'elastica'])
            ->tag('kernel.reset', ['method' => 'reset'])

        ->set('fos_elastica.paginator.subscriber', \FOS\ElasticaBundle\Subscriber\PaginateElasticaQuerySubscriber::class)
            ->args([service('request_stack')])
            ->tag('kernel.event_subscriber')

        ->set('fos_elastica.logger', \FOS\ElasticaBundle\Logger\ElasticaLogger::class)
            ->args([
                service('logger')->nullOnInvalid(),
                '%kernel.debug%',
            ])
            ->tag('monolog.logger', ['channel' => 'elastica'])
            ->tag('kernel.reset', ['method' => 'reset'])

        ->set('fos_elastica.mapping_builder', \FOS\ElasticaBundle\Index\MappingBuilder::class)
            ->args([service('event_dispatcher')])

        ->set('fos_elastica.property_accessor', \Symfony\Component\PropertyAccess\PropertyAccessor::class)
            ->args([
                '%fos_elastica.property_accessor.magicCall%',
                '%fos_elastica.property_accessor.throwExceptionOnInvalidIndex%',
            ])
    ;
};
