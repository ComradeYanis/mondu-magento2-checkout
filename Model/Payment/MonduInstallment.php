<?php
/**
 * Copyright (c) 2024.
 * wot2304@gmail.com
 * Yanis Yeltsyn
 */

declare(strict_types=1);

namespace Mondu\Mondu\Model\Payment;

use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Store\Model\ScopeInterface;
use Mondu\Mondu\Model\Config\MonduInstallmentConfigProvider;

class MonduInstallment extends AbstractMethod
{
    public const PAYMENT_METHOD_MONDU_CODE = 'monduinstallment';

    /**
     * @var string
     */
    protected $_code = 'monduinstallment';

    /**
     * Authorize
     *
     * @param InfoInterface $payment
     * @param float $amount
     * @return $this|MonduInstallment
     */
    public function authorize(InfoInterface $payment, $amount)
    {
        return $this;
    }

    /**
     * SetCode
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->_code = $code;
        return $this;
    }

    /**
     * CanUseForCountry
     *
     * @param string $country
     * @return bool
     */
    public function canUseForCountry($country)
    {
        $storeId = $this->getStore();

        $allowSpecific = $this->_scopeConfig->isSetFlag(
            MonduInstallmentConfigProvider::PAYMENT_MONDU_ALLOW_SPECIFIC,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if ($allowSpecific === true) {
            $availableCountries = $this->_scopeConfig->getValue(
                MonduInstallmentConfigProvider::PAYMENT_MONDU_SPECIFIC_COUNTRY,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
            if (str_contains($availableCountries, $country) !== false) {
                return false;
            }
        }
        return true;
    }
}
