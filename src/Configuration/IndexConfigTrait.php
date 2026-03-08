<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Configuration;

/**
 * Index configuration trait class.
 *
 * @author Dmitry Balabka <dmitry.balabka@intexsys.lv>
 *
 * @phpstan-import-type TMapping from IndexConfigInterface
 * @phpstan-import-type TSettings from IndexConfigInterface
 * @phpstan-import-type TConfig from IndexConfigInterface
 * @phpstan-import-type TElasticConfig from IndexConfigInterface
 */
trait IndexConfigTrait
{
    /**
     * The name of the index for ElasticSearch.
     */
    private string $elasticSearchName;

    /**
     * The model of the index.
     *
     * @var class-string<object>|null
     */
    private ?string $model = null;

    /**
     * The internal name of the index. May not be the same as the name used in ElasticSearch,
     * especially if aliases are enabled.
     */
    private string $name;

    /**
     * An array of settings sent to ElasticSearch when creating the index.
     *
     * @var TSettings
     */
    private array $settings;

    /**
     * @var TElasticConfig
     */
    private array $config;

    /**
     * @var TMapping
     */
    private array $mapping;

    public function getElasticSearchName(): string
    {
        return $this->elasticSearchName;
    }

    /**
     * @return class-string<object>|null
     */
    public function getModel(): ?string
    {
        return $this->model;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    public function getDateDetection(): ?bool
    {
        return $this->config['date_detection'] ?? null;
    }

    public function getDynamicDateFormats(): ?array
    {
        return $this->config['dynamic_date_formats'] ?? null;
    }

    public function getAnalyzer(): ?string
    {
        return $this->config['analyzer'] ?? null;
    }

    public function getMapping(): array
    {
        return $this->mapping;
    }

    public function getNumericDetection(): ?bool
    {
        return $this->config['numeric_detection'] ?? null;
    }

    public function getDynamic(): string|bool|null
    {
        return $this->config['dynamic'] ?? null;
    }
}
