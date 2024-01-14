<?php
/**
 * Copyright (c) 2024.
 * wot2304@gmail.com
 * Yanis Yeltsyn
 */

declare(strict_types=1);

namespace Mondu\Mondu\Plugin;

use Magento\Payment\Helper\Data;
use Mondu\Mondu\Model\Config\GlobalConfigProvider;
use Mondu\Mondu\Model\PaymentMethodList;

class DataPlugin
{
    /**
     * @var PaymentMethodList
     */
    private $paymentMethodList;

    /**
     * @var GlobalConfigProvider
     */
    private $configProvider;

    /**
     * @param PaymentMethodList $paymentMethodList
     * @param GlobalConfigProvider $configProvider
     */
    public function __construct(
        PaymentMethodList $paymentMethodList,
        GlobalConfigProvider $configProvider
    ) {
        $this->paymentMethodList = $paymentMethodList;
        $this->configProvider = $configProvider;
    }

    /**
     * Filters Mondu payment methods
     *
     * @param Data $subject
     * @param array $result
     * @return array
     */
    public function afterGetPaymentMethods(Data $subject, $result)
    {
        if ($this->configProvider->getIsAnyConfigProviderActive() === false) {
            return $result;
        }

        return $this->paymentMethodList->filterMonduPaymentMethods($result);
    }
}
