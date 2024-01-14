<?php
/**
 * Copyright (c) 2024.
 * wot2304@gmail.com
 * Yanis Yeltsyn
 */

declare(strict_types=1);

namespace Mondu\Mondu\Model\Config;

use Magento\Checkout\Model\ConfigProviderInterface;

class GlobalConfigProvider implements ConfigProviderInterface
{
    /**
     * @var MonduConfigProviderInterface[]|null
     */
    private $configProviders;

    public function __construct(
        array $configProviders = []
    ) {
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $result = [];
        /** @var MonduConfigProviderInterface $configProvider */
        foreach ($this->configProviders as $configProvider) {
            array_merge($result, $configProvider->getConfig());
        }
        return $result;
    }

    public function getIsAnyConfigProviderActive(): bool
    {
        foreach ($this->configProviders as $configProvider) {
            if ($configProvider->isActive()) {
                return true;
            }
        }
        return false;
    }

    public function updateConfigProviderOrderStatus(string $orderStatus): void
    {
        foreach ($this->configProviders as $configProvider) {
            $configProvider->updateOrderStatus($orderStatus);
        }
    }
}
