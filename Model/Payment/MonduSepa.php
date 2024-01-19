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
use Mondu\Mondu\Model\Config\MonduSepaConfigProvider;

class MonduSepa extends AbstractMethod
{

    /**
     * @var string
     */
    protected $_code = 'mondusepa';

    /**
     * Authorize
     *
     * @param InfoInterface $payment
     * @param float $amount
     * @return $this|MonduSepa
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
            MonduSepaConfigProvider::PAYMENT_MONDU_ALLOW_SPECIFIC,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if ($allowSpecific === true) {
            $availableCountries = $this->_scopeConfig->getValue(
                MonduSepaConfigProvider::PAYMENT_MONDU_SPECIFIC_COUNTRY,
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
