<?php
/**
 * Copyright (c) 2024.
 * wot2304@gmail.com
 * Yanis Yeltsyn
 */

declare(strict_types=1);

namespace Mondu\Mondu\Helper;

use Exception;
use Magento\Framework\App\CacheInterface;
use Mondu\Mondu\Model\Request\Factory;

class PaymentMethod
{
    public const PAYMENT_MONDU_CACHE = 'mondu_payment_methods';
    public const PAYMENT_MONDU = 'mondu';
    public const PAYMENT_MONDUSEPA = 'mondusepa';
    public const PAYMENT_MONDUINSTALLMENT = 'monduinstallment';
    public const PAYMENT_MONDUINSTALLMENTBYINVOICE = 'monduinstallmentbyinvoice';
    public const PAYMENTS = [
        self::PAYMENT_MONDU,
        self::PAYMENT_MONDUSEPA,
        self::PAYMENT_MONDUINSTALLMENT,
        self::PAYMENT_MONDUINSTALLMENTBYINVOICE
    ];
    public const LABEL_MONDU = 'Rechnungskauf';
    public const LABEL_MONDUSEPA = 'SEPA Direct Debit';
    public const LABEL_MONDUINSTALLMENT = 'Installment';
    public const LABEL_MONDUINSTALLMENTBYINVOICE = 'Installment By Invoice';

    public const LABELS = [
        self::PAYMENT_MONDU => self::LABEL_MONDU,
        self::PAYMENT_MONDUSEPA => self::PAYMENT_MONDUSEPA,
        self::PAYMENT_MONDUINSTALLMENT => self::LABEL_MONDUINSTALLMENT,
        self::PAYMENT_MONDUINSTALLMENTBYINVOICE => self::PAYMENT_MONDUINSTALLMENTBYINVOICE
    ];

    public const MAPPING = [
        'invoice' => self::PAYMENT_MONDU,
        'direct_debit' => self::PAYMENT_MONDUSEPA,
        'installment' => self::PAYMENT_MONDUINSTALLMENT,
        'installment_by_invoice' => self::PAYMENT_MONDUINSTALLMENTBYINVOICE
    ];
    /**
     * @var Factory
     */
    private $requestFactory;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @param Factory $requestFactory
     * @param CacheInterface $cache
     */
    public function __construct(
        Factory $requestFactory,
        CacheInterface $cache
    ) {
        $this->requestFactory = $requestFactory;
        $this->cache = $cache;
    }

    /**
     * GetPayments
     *
     * @return string[]
     */
    public function getPayments()
    {
        return self::PAYMENTS;
    }

    /**
     * GetAllowed
     *
     * @param float|int|null $storeId
     * @return array
     */
    public function getAllowed($storeId = null)
    {
        $result = [];
        try {
            if ($cacheResult = $this->cache->load(self::PAYMENT_MONDU_CACHE . "_{$storeId}")) {
                return json_decode($cacheResult, true);
            }
            $paymentMethods = $this->requestFactory->create(Factory::PAYMENT_METHODS)->process();
            foreach ($paymentMethods as $value) {
                $result[] = self::MAPPING[$value['identifier']] ?? '';
            }
        } catch (Exception $e) {
            //TODO: add logs
        }
        $this->cache->save(json_encode($result), self::PAYMENT_MONDU_CACHE . "_{$storeId}", [], 3600);
        return $result;
    }

    /**
     * ResetAllowedCache
     *
     * @return void
     */
    public function resetAllowedCache()
    {
        $this->cache->remove(self::PAYMENT_MONDU_CACHE);
    }

    /**
     * IsMondu
     *
     * @param mixed $method
     * @return bool
     */
    public function isMondu($method): bool
    {
        $code = $method->getCode() ?? $method->getMethod();

        return in_array($code, self::PAYMENTS);
    }

    /**
     * GetCode
     *
     * @param mixed $method
     * @return mixed
     */
    public function getCode($method)
    {
        return $method->getCode() ?? $method->getMethod();
    }

    /**
     * GetLabel
     *
     * @param string $code
     * @return string|null
     */
    public function getLabel($code)
    {
        return self::LABELS[$code] ?? null;
    }
}
