<?php
/**
 * Copyright (c) 2024.
 * wot2304@gmail.com
 * Yanis Yeltsyn
 */

declare(strict_types=1);


namespace Mondu\Mondu\Helper;

use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Module\ModuleListInterface;

class ModuleHelper
{
    public const MODULE_NAME = 'Mondu_Mondu';

    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @param ModuleListInterface $moduleList
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(
        ModuleListInterface $moduleList,
        ProductMetadataInterface $productMetadata
    ) {
        $this->moduleList = $moduleList;
        $this->productMetadata = $productMetadata;
    }

    /**
     * GetEnvironmentInformation
     *
     * @return array
     */
    public function getEnvironmentInformation(): array
    {
        return [
            'plugin' => $this->getModuleNameForApi(),
            'version' => $this->getModuleVersion(),
            'language_version' => 'PHP '. phpversion(),
            'shop_version' => $this->productMetadata->getVersion(),
        ];
    }

    /**
     * GetModuleVersion
     *
     * @return string
     */
    public function getModuleVersion(): string
    {
        return $this->moduleList->getOne(self::MODULE_NAME)['setup_version'];
    }

    /**
     * GetModuleNameForApi
     *
     * @return string
     */
    public function getModuleNameForApi(): string
    {
        return 'magento2';
    }
}
