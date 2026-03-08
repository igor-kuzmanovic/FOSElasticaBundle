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
 * Interface Index config interface.
 *
 * @author Dmitry Balabka <dmitry.balabka@intexsys.lv>
 *
 * @phpstan-type TMapping = array<string, mixed>
 * @phpstan-type TSettings = array<string, mixed>
 * @phpstan-type TDynamicDateFormats = list<non-empty-string>
 * @phpstan-type TDynamic = true|'runtime'
 * @phpstan-type TConfig = array{
 *     elasticsearch_name?: string,
 *     name: string,
 *     settings?: TSettings,
 *     use_alias?: bool,
 *     config: TElasticConfig,
 *     mapping: TMapping,
 *     model?: class-string<object>,
 *     index_patterns?: list<non-empty-string>,
 * }
 * @phpstan-type TElasticConfig = array{
 *     date_detection?: bool,
 *     dynamic_date_formats?: TDynamicDateFormats,
 *     analyzer?: string,
 *     numeric_detection?: bool,
 *     dynamic?: TDynamic
 * }
 */
interface IndexConfigInterface
{
    public function getElasticSearchName(): string;

    /**
     * @return class-string<object>|null
     */
    public function getModel(): ?string;

    public function getName(): string;

    /**
     * @return TSettings
     */
    public function getSettings(): array;

    public function getDateDetection(): ?bool;

    /**
     * @return ?TDynamicDateFormats
     */
    public function getDynamicDateFormats(): ?array;

    public function getAnalyzer(): ?string;

    /**
     * @return TMapping
     */
    public function getMapping(): array;

    public function getNumericDetection(): ?bool;

    /**
     * @return ?TDynamic
     */
    public function getDynamic(): string|bool|null;
}
