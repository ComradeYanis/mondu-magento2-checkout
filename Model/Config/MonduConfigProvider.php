<?php
/**
 * Copyright (c) 2024.
 * wot2304@gmail.com
 * Yanis Yeltsyn
 */

declare(strict_types=1);

namespace Mondu\Mondu\Model\Config;

use Magento\Config\Model\ResourceModel\Config as ResourceConfig;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;

class MonduConfigProvider extends AbstractMonduConfigProvider
{

    /**
     * @var ResourceConfig
     */
    private $resourceConfig;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var TypeListInterface
     */
    private $cacheTypeList;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param WriterInterface $configWriter
     * @param UrlInterface $urlBuilder
     * @param ResourceConfig $resourceConfig
     * @param EncryptorInterface $encryptor
     * @param TypeListInterface $cacheTypeList
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        WriterInterface      $configWriter,
        UrlInterface         $urlBuilder,
        ResourceConfig       $resourceConfig,
        EncryptorInterface   $encryptor,
        TypeListInterface    $cacheTypeList
    ) {
        parent::__construct(
            $scopeConfig,
            $configWriter,
            $urlBuilder
        );
        $this->resourceConfig = $resourceConfig;
        $this->encryptor = $encryptor;
        $this->cacheTypeList = $cacheTypeList;
    }

    /**
     * Returns mondu api url
     *
     * @param string|null $path
     * @return string
     *
     */
    public function getApiUrl($path = null): string
    {
        if ($this->getIsSandbox()) {
            return $this->scopeConfig->getValue(self::PAYMENT_MONDU_SANDBOX_URL) . ($path ? '/' . $path : '');
        }
        return $this->scopeConfig->getValue(self::PAYMENT_MONDU_PRODUCTION_URL) . ($path ? '/' . $path : '');
    }

    /**
     * Get Webhook url
     *
     * @return string
     *
     * ???
     */
    public function getWebhookUrl(): string
    {
        $test = $this->urlBuilder->getBaseUrl() . 'mondu/webhooks/index';
        return $this->urlBuilder->getUrl('mondu/webhooks/index');
    }

    /**
     * Get api key
     *
     * @return mixed
     *
     * TODO: should be separate for prod and sandox
     */
    public function getApiKey()
    {
        return $this->scopeConfig->getValue(self::PAYMENT_MONDU_MONDU_KEY, ScopeInterface::SCOPE_STORE, $this->contextCode);
    }

    /**
     * Is Cron enabled
     *
     * @return bool
     */
    public function isCronEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::PAYMENT_MONDU_CRON);
    }

    /**
     * Get invoice url for order
     *
     * @param string $orderUid
     * @param string $invoiceId
     * @return string
     */
    public function getPdfUrl($orderUid, $invoiceId)
    {
        $test = $this->urlBuilder->getBaseUrl() . "mondu/index/invoice?id={$orderUid}&r={$invoiceId}";
        return $this->urlBuilder->getUrl('mondu/index/invoice', [
            'id' => $orderUid,
            'r' => $invoiceId
        ]);
    }

    /**
     * Updates webhook secret
     *
     * @param string $webhookSecret
     * @param string $storeId
     * @return $this
     */
    public function updateWebhookSecret($webhookSecret = ""): self
    {
        $this->resourceConfig->saveConfig(
            $this->getWebhookSecretConfigPath(),
            $this->encryptor->encrypt($webhookSecret)
        );

        return $this;
    }

    public function getWebhookSecretConfigPath(): string
    {
        $path = self::PAYMENT_MONDU_LIVE_WEBHOOK_SECRET;
        if ($this->getIsSandbox()) {
            $path = self::PAYMENT_MONDU_SANDBOX_WEBHOOK_SECRET;
        }
        return $path;
    }

    /**
     * Change new order status
     *
     * @return void
     */
    public function updateNewOrderStatus()
    {
        $status = $this->getNewOrderStatus();

        $this->configWriter->save('payment/mondusepa/order_status', $status);
        $this->configWriter->save('payment/monduinstallment/order_status', $status);
        $this->configWriter->save('payment/monduinstallmentbyinvoice/order_status', $status);
    }

    /**
     * Get new order status
     *
     * @return mixed
     */
    public function getNewOrderStatus()
    {
        return $this->scopeConfig->getValue(self::PAYMENT_ORDER_STATUS);
    }

    /**
     * Get webhook secret
     *
     * @return string
     */
    public function getWebhookSecret()
    {
        $val = $this->scopeConfig->getValue(
            $this->getWebhookSecretConfigPath()
        );
        return $this->encryptor->decrypt($val);
    }

    /**
     * @deprecated use isSendLines()
     */
    public function sendLines()
    {
        return $this->isSendLines();
    }

    /**
     * Get send lines (if false Mondu plugin will not send order line information to api)
     *
     * @return bool
     */
    public function isSendLines(): bool
    {
        return $this->scopeConfig->isSetFlag(self::PAYMENT_MONDU_SEND_LINES);
    }

    /**
     * Get require invoice (if false Mondu plugin won't require invoice for shipping)
     *
     * @return bool
     */
    public function isInvoiceRequiredForShipping(): bool
    {
        return $this->scopeConfig->isSetFlag(self::PAYMENT_MONDU_REQUIRE_INVOICE);
    }

    /**
     * Clears configuration cache
     *
     * @return void
     */
    public function clearConfigurationCache()
    {
        $this->cacheTypeList->cleanType('config');
    }
}
