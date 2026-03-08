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
    $container->extension('framework', [
        'test' => true,
        'secret' => 'secret',
    ]);

    $container->services()
        ->set('logger', \Psr\Log\NullLogger::class)

        ->set(\FOS\ElasticaBundle\Test\ClientLocator::class)
            ->public()
            ->args([
                tagged_iterator('fos_elastica.client'),
            ])
    ;
};
