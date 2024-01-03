<?php
/**
 * Copyright (c) 2024.
 * wot2304@gmail.com
 * Yanis Yeltsyn
 */

declare(strict_types=1);

namespace Mondu\Mondu\Observer\Adminhtml\Config;

use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Mondu\Mondu\Helper\PaymentMethod;
use Mondu\Mondu\Model\Request\Factory as RequestFactory;
use Mondu\Mondu\Model\Ui\ConfigProvider;

/**
* @todo Add logger
 */
class Save implements ObserverInterface
{
    /**
     * @var RequestFactory
     */
    private $requestFactory;

    /**
     * @var ConfigProvider
     */
    private $monduConfig;
    /**
     * @var PaymentMethod
     */
    private $paymentMethod;

    /**
     * @var string[]
     */
    private $subscriptions = [
        'order/confirmed',
        'order/declined',
        'order/pending'
    ];

    /**
     * @param RequestFactory $requestFactory
     * @param ConfigProvider $monduConfig
     * @param PaymentMethod $paymentMethod
     */
    public function __construct(
        RequestFactory $requestFactory,
        ConfigProvider $monduConfig,
        PaymentMethod $paymentMethod
    ) {
        $this->requestFactory = $requestFactory;
        $this->monduConfig = $monduConfig;
        $this->paymentMethod = $paymentMethod;
    }

    /**
     * Execute
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        if ($this->monduConfig->isActive()) {
            if ($this->monduConfig->getApiKey()) {
                try {
                    $this->monduConfig->updateNewOrderStatus();
                    $this->paymentMethod->resetAllowedCache();

                    $this->requestFactory->create(RequestFactory::WEBHOOKS_KEYS_REQUEST_METHOD)
                       ->process()
                       ->checkSuccess()
                       ->update();

                    $this->requestFactory
                       ->create(RequestFactory::WEBHOOKS_REQUEST_METHOD)
                       ->setTopic('order/confirmed')
                       ->process();

                    $this->requestFactory
                       ->create(RequestFactory::WEBHOOKS_REQUEST_METHOD)
                       ->setTopic('order/pending')
                       ->process();

                    $this->requestFactory
                       ->create(RequestFactory::WEBHOOKS_REQUEST_METHOD)
                       ->setTopic('order/declined')
                       ->process();

                    $this->monduConfig->clearConfigurationCache();
                } catch (Exception $e) {
                    throw new LocalizedException(__($e->getMessage()));
                }
            } else {
                throw new LocalizedException(__('Cant enable Mondu payments API key is missing'));
            }
        }
    }
}
