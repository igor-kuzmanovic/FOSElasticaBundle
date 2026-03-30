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

use FOS\ElasticaBundle\Configuration\Source\ContainerSource;
use FOS\ElasticaBundle\Configuration\Source\TemplateContainerSource;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('fos_elastica.config_source.container', ContainerSource::class)
        ->args([[]])
        ->tag('fos_elastica.config_source')
    ;

    $services->set('fos_elastica.config_source.template_container', TemplateContainerSource::class)
        ->args([[]])
        ->tag('fos_elastica.config_source', ['source' => 'index_template'])
    ;
};
