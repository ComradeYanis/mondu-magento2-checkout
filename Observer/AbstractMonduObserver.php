<?php
/**
 * Copyright (c) 2024.
 * wot2304@gmail.com
 * Yanis Yeltsyn
 */

declare(strict_types=1);

namespace Mondu\Mondu\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Mondu\Mondu\Helper\ContextHelper;
use Mondu\Mondu\Helper\PaymentMethod as PaymentMethodHelper;
use Psr\Log\LoggerInterface;
use Throwable;

abstract class AbstractMonduObserver implements ObserverInterface
{
    /**
     * @var string
     */
    protected $name = 'MonduObserver';

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var PaymentMethodHelper
     */
    private $paymentMethodHelper;
    /**
     * @var ContextHelper
     */
    protected $contextHelper;

    /**
     * @param PaymentMethodHelper $paymentMethodHelper
     * @param LoggerInterface $logger
     * @param ContextHelper $contextHelper
     */
    public function __construct(
        PaymentMethodHelper $paymentMethodHelper,
        LoggerInterface     $logger,
        ContextHelper       $contextHelper
    ) {
        $this->paymentMethodHelper = $paymentMethodHelper;
        $this->logger = $logger;
        $this->contextHelper = $contextHelper;
    }

    /**
     * Execute
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $order = $this->getOrderFromObserver($observer);
        $this->contextHelper->setConfigContextForOrder($order);

        if ($this->checkOrderPlacedWithMondu($order) === false) {
            return;
        }

        $this->logger
            ->info("Entered `{$this->name}` observer", ['orderNumber' => $order->getIncrementId()]);
        try {
            $this->executeMondu($observer);
        } catch (Throwable $exception) {
            $this->logger->info(
                "Error on exucution the `{$this->name}` observer. Message: {$exception->getMessage()}",
                ['orderNumber' => $order->getIncrementId()]
            );
        }
    }

    /**
     * Execute to be implemented in the class
     *
     * @param Observer $observer
     * @return void
     */
    abstract public function executeMondu(Observer $observer): void;

    /**
     * Check if order is placed with Mondu
     *
     * @param Order $order
     * @return bool
     */
    private function checkOrderPlacedWithMondu(Order $order): bool
    {
        $payment = $order->getPayment();
        return $this->paymentMethodHelper->isMondu($payment);
    }

    /**
     * Gets order from different observer events
     *
     * @param Observer $observer
     * @return Order|null
     */
    private function getOrderFromObserver(Observer $observer): ?Order
    {
        switch ($this->name) {
            case 'UpdateOrder':
                return $observer->getEvent()->getCreditmemo()->getOrder();
            case 'ShipOrder':
                return $observer->getEvent()->getShipment()->getOrder();
            default:
                return $observer->getEvent()->getOrder();
        }
    }
}
