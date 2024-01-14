<?php
/**
 * Copyright (c) 2024.
 * wot2304@gmail.com
 * Yanis Yeltsyn
 */

declare(strict_types=1);

namespace Mondu\Mondu\Helper;

use Exception;
use Mondu\Mondu\Model\Config\MonduConfigProvider;

class HeadersHelper
{
    /**
     * @var ModuleHelper
     */
    private $moduleHelper;

    /**
     * @var MonduConfigProvider
     */
    private $configProvider;

    /**
     * @param ModuleHelper $moduleHelper
     * @param MonduConfigProvider $configProvider
     */
    public function __construct(
        ModuleHelper $moduleHelper,
        MonduConfigProvider $configProvider
    ) {
        $this->moduleHelper = $moduleHelper;
        $this->configProvider = $configProvider;
    }

    /**
     * Gets headers Mondu needs for requests
     *
     * @return array
     */
    public function getHeaders(): array
    {
        $apiToken = $this->configProvider->getApiKey();
        return [
            'Content-Type' => 'application/json',
            'Api-Token' => $apiToken,
            'x-mondu-trace-id' => $this->getUUIDV4(),
            'x-mondu-parent-span-id' => $this->getUUIDV4(),
            'x-plugin-version' => $this->getPluginVersion(),
            'x-plugin-name' => $this->getPluginName()
        ];
    }

    /**
     * Generates UUIDV4
     *
     * @return string
     * @throws Exception
     */
    private function getUUIDV4(): string
    {
        $data = random_bytes(16);
        //phpcs:ignore
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // Set version to 0100
        //phpcs:ignore
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // Set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Gets Mondu Plugin Verson
     *
     * @return string
     */
    private function getPluginVersion(): string
    {
        return $this->moduleHelper->getModuleVersion();
    }

    /**
     * Gets Mondu Plugin name
     *
     * @return string
     */
    private function getPluginName(): string
    {
        return $this->moduleHelper->getModuleNameForApi();
    }
}
