<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use FOS\ElasticaBundle\Persister\Event\PersistEvent;
use FOS\ElasticaBundle\Persister\Event\PostInsertObjectsEvent;
use FOS\ElasticaBundle\Persister\Event\PreFetchObjectsEvent;
use FOS\ElasticaBundle\Persister\Event\PreInsertObjectsEvent;
use FOS\ElasticaBundle\Provider\PagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RegisterListenersService
{
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
    ) {}

    /**
     * @param PagerInterface<object> $pager
     * @param array<string, mixed>   $options
     */
    public function register(ObjectManager $manager, PagerInterface $pager, array $options): void
    {
        $options = array_replace([
            'clear_object_manager' => true,
            'debug_logging' => false,
            'sleep' => 0,
        ], $options);

        if ($options['clear_object_manager']) {
            $this->addListener($pager, PostInsertObjectsEvent::class, static function () use ($manager): void {
                $manager->clear();
            });
        }

        if ($options['sleep']) {
            $this->addListener($pager, PostInsertObjectsEvent::class, static function () use ($options): void {
                usleep($options['sleep']);
            });
        }

        if (
            false === $options['debug_logging']
            && $manager instanceof EntityManagerInterface
        ) {
            $configuration = $manager->getConnection()->getConfiguration();
            if (method_exists($configuration, 'getSQLLogger') && method_exists($configuration, 'setSQLLogger')) {
                $logger = $configuration->getSQLLogger();

                $this->addListener($pager, PreFetchObjectsEvent::class, static function () use ($configuration): void {
                    $configuration->setSQLLogger(null);
                });

                $this->addListener($pager, PreInsertObjectsEvent::class, static function () use ($configuration, $logger): void {
                    $configuration->setSQLLogger($logger);
                });
            }
        }
    }

    /**
     * @param PagerInterface<object> $pager
     */
    private function addListener(PagerInterface $pager, string $eventName, \Closure $callable): void
    {
        $this->dispatcher->addListener($eventName, static function (PersistEvent $event) use ($pager, $callable): void {
            if ($event->getPager() !== $pager) {
                return;
            }

            \call_user_func_array($callable, \func_get_args());
        });
    }
}
