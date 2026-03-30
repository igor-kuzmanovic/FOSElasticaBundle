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

use FOS\ElasticaBundle\Transformer\ModelToElasticaAutoTransformer;
use FOS\ElasticaBundle\Transformer\ModelToElasticaIdentifierTransformer;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('fos_elastica.model_to_elastica_transformer', ModelToElasticaAutoTransformer::class)
        ->abstract()
        ->args([
            [],
            service('event_dispatcher'),
        ])
        ->call('setPropertyAccessor', [service('fos_elastica.property_accessor')])
    ;

    $services->set('fos_elastica.model_to_elastica_identifier_transformer', ModelToElasticaIdentifierTransformer::class)
        ->abstract()
        ->args([[]])
        ->call('setPropertyAccessor', [service('fos_elastica.property_accessor')])
    ;
};
