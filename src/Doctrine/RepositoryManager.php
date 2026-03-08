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

use Doctrine\Persistence\ManagerRegistry;
use FOS\ElasticaBundle\Finder\FinderInterface;
use FOS\ElasticaBundle\Manager\RepositoryManagerInterface;
use FOS\ElasticaBundle\Repository;

/**
 * @author Richard Miller <info@limethinking.co.uk>
 *
 * Allows retrieval of basic or custom repository for mapped Doctrine entities/documents
 */
class RepositoryManager implements RepositoryManagerInterface
{
    /** @var array<class-string<object>, string> */
    protected array $entities = [];

    /** @var array<string, Repository<object>> */
    protected array $repositories = [];

    public function __construct(
        protected ManagerRegistry $managerRegistry,
        private readonly RepositoryManagerInterface $repositoryManager,
    ) {}

    /**
     * @param FinderInterface<object> $finder
     */
    public function addIndex(string $indexName, FinderInterface $finder, ?string $repositoryName = null): void
    {
        throw new \LogicException(__METHOD__.' should not be called. Call addIndex on the main repository manager');
    }

    /**
     * @param class-string<object> $entityName
     */
    public function addEntity(string $entityName, string $indexName): void
    {
        $this->entities[$entityName] = $indexName;
    }

    /**
     * Return repository for entity.
     *
     * Returns custom repository if one specified otherwise returns a basic repository.
     *
     * @param class-string<object> $entityName
     *
     * @return Repository<object>
     */
    public function getRepository(string $entityName): Repository
    {
        $realEntityName = $entityName;
        if (str_contains($entityName, ':')) {
            [$namespaceAlias, $simpleClassName] = explode(':', $entityName);
            // @link https://github.com/doctrine/persistence/pull/204
            if (method_exists($this->managerRegistry, 'getAliasNamespace')) {
                $realEntityName = $this->managerRegistry->getAliasNamespace($namespaceAlias).'\\'.$simpleClassName;
            } else {
                $realEntityName = $simpleClassName.'::class';
            }
        }

        if (isset($this->entities[$realEntityName])) {
            $realEntityName = $this->entities[$realEntityName];
        }

        return $this->repositoryManager->getRepository($realEntityName);
    }

    public function hasRepository(string $indexName): bool
    {
        return $this->repositoryManager->hasRepository($indexName);
    }
}
