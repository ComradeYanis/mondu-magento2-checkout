<?php
/**
 * Copyright (c) 2024.
 * wot2304@gmail.com
 * Yanis Yeltsyn
 */
declare(strict_types=1);

namespace Mondu\Mondu\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;

class AbstractMonduConfigProvider implements MonduConfigProviderInterface
{
    public const CODE = 'mondu';

    public const MODE_LIVE_CODE = 'live';
    public const MODE_SANDBOX_CODE = 'sandbox';
    public const PAYMENT_TITLE = 'payment/' . self::CODE . '/title';
    public const PAYMENT_DESCRIPTION = 'payment/' . self::CODE . '/description';
    public const PAYMENT_ACTIVE = 'payment/' . self::CODE . '/active';
    public const PAYMENT_ORDER_STATUS = 'payment/' . self::CODE . '/order_status';
    public const PAYMENT_MONDU_IS_SANDBOX = 'payment/mondu/sandbox';
    public const PAYMENT_MONDU_PRODUCTION_URL = 'payment/mondu/production_url';
    public const PAYMENT_MONDU_SANDBOX_URL = 'payment/mondu/sandbox_url';
    public const PAYMENT_MONDU_SDK_URL = 'payment/mondu/sdk_url';
    public const PAYMENT_MONDU_SANDBOX_SDK_URL = 'payment/mondu/sandbox_sdk_url';
    public const PAYMENT_MONDU_DEBUG = 'payment/mondu/debug';
    public const PAYMENT_MONDU_MONDU_KEY = 'payment/mondu/mondu_key';
    public const PAYMENT_MONDU_CRON = 'payment/mondu/cron';
    public const PAYMENT_MONDU_LIVE_WEBHOOK_SECRET = 'payment/mondu/live_webhook_secret';
    public const PAYMENT_MONDU_SANDBOX_WEBHOOK_SECRET = 'payment/mondu/sandbox_webhook_secret';
    public const PAYMENT_MONDU_SEND_LINES = 'payment/mondu/send_lines';
    public const PAYMENT_MONDU_REQUIRE_INVOICE = 'payment/mondu/require_invoice';


    //REVIEW
    public const AUTHORIZATION_STATE_FLOW = 'authorization_flow';

    /**
     * @var string|null
     */
    protected $contextCode = null;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var WriterInterface
     */
    protected $configWriter;
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        WriterInterface      $configWriter,
        UrlInterface         $urlBuilder
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Sets context code ( for multiple stores )
     *
     * @param int|string $code
     * @return void
     */
    public function setContextCode($code)
    {
        $this->contextCode = $code;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig()
    {
        $privacyText =
            __("Information on the processing of your personal data by Mondu GmbH can be found " .
                "<a href='https://www.mondu.ai/de/datenschutzgrundverordnung-kaeufer/' target='_blank'>here.</a>");

        $descriptionConfigMondu = $this->scopeConfig->getValue(self::PAYMENT_DESCRIPTION, ScopeInterface::SCOPE_STORE);

        $descriptionMondu = $descriptionConfigMondu ?
            __($descriptionConfigMondu) . '<br><br>' . $privacyText :
            $privacyText;

        return [
            'payment' => [
                self::CODE => [
                    'sdkUrl' => $this->getSdkUrl(),
                    'monduCheckoutTokenUrl' => $this->urlBuilder->getUrl('mondu/payment_checkout/token'),
                    'description' => $descriptionMondu,
                    'title' => __($this->scopeConfig->getValue(self::PAYMENT_TITLE, ScopeInterface::SCOPE_STORE))
                ]
            ]
        ];
    }

    /**
     * Returns mondu.js url
     *
     * @return string
     */
    public function getSdkUrl(): string
    {
        if ($this->getIsSandbox()) {
            return $this->scopeConfig->getValue(self::PAYMENT_MONDU_SANDBOX_SDK_URL);
        }
        return $this->scopeConfig->getValue(self::PAYMENT_MONDU_SDK_URL);
    }

    public function getIsSandbox(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::PAYMENT_MONDU_IS_SANDBOX,
            ScopeInterface::SCOPE_STORE,
            $this->contextCode
        );
    }

    /**
     * Get mode (sandbox or live)
     *
     * @return string
     */
    public function getMode(): string
    {
        return $this->getIsSandbox() ? self::MODE_SANDBOX_CODE : self::MODE_LIVE_CODE;
    }

    /**
     * Get Debug option
     *
     * @return bool
     */
    public function getDebug()
    {
        return $this->scopeConfig->isSetFlag(self::PAYMENT_MONDU_DEBUG);
    }

    /**
     * {@inheritDoc}
     */
    public function updateOrderStatus(string $status): void
    {
        $this->configWriter->save(self::PAYMENT_ORDER_STATUS, $status);
    }

    /**
     * {@inheritDoc}
     */
    public function isActive(): bool
    {
        return $this->scopeConfig->isSetFlag(self::PAYMENT_ACTIVE, ScopeInterface::SCOPE_STORE, $this->contextCode);
    }
}
