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

use FOS\ElasticaBundle\Persister\AsyncPagerPersister;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('fos_elastica.async_pager_persister', AsyncPagerPersister::class)
        ->args([
            service('fos_elastica.pager_persister_registry'),
            service('fos_elastica.pager_provider_registry'),
            service('fos_elastica.messenger.bus'),
        ])
        ->tag('fos_elastica.pager_persister', ['persisterName' => 'async'])
    ;
};
