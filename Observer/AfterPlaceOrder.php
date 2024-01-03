<?php
/**
 * Copyright (c) 2024.
 * wot2304@gmail.com
 * Yanis Yeltsyn
 */

declare(strict_types=1);

namespace Mondu\Mondu\Observer;

use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order;
use Mondu\Mondu\Helper\ContextHelper;
use Mondu\Mondu\Helper\Log;
use Mondu\Mondu\Helper\PaymentMethod;
use Psr\Log\LoggerInterface;

class AfterPlaceOrder extends AbstractMonduObserver
{
    /**
     * @var Log
     */
    protected $monduLogger;

    /**
     * @param PaymentMethod $paymentMethodHelper
     * @param LoggerInterface $logger
     * @param ContextHelper $contextHelper
     * @param Log $monduLogger
     */
    public function __construct(
        PaymentMethod   $paymentMethodHelper,
        LoggerInterface $logger,
        ContextHelper   $contextHelper,
        Log             $monduLogger
    ) {
        parent::__construct(
            $paymentMethodHelper,
            $logger,
            $contextHelper
        );
        $this->monduLogger = $monduLogger;
    }

    /**
     * Execute
     *
     * @param Observer $observer
     * @return void
     */
    public function executeMondu(Observer $observer): void
    {
        $order = $observer->getEvent()->getOrder();
        $monduUuid = $order->getMonduReferenceId();
        $orderData = $this->monduLogger->getTransactionByOrderUid($monduUuid);
        $order->setState(Order::STATE_PROCESSING);
        $order->setStatus(Order::STATE_PROCESSING);

        if (isset($orderData['mondu_state']) && $orderData['mondu_state'] === 'pending') {
            $order->addStatusHistoryComment(
                __('Mondu: Order Status changed to Payment Review because it needs manual confirmation')
            );
            $order->setState(Order::STATE_PAYMENT_REVIEW);
            $order->setStatus(Order::STATE_PAYMENT_REVIEW);
        }
        $order->save();
    }
}
