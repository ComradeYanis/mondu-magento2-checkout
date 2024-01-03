<?php
/**
 * Copyright (c) 2024.
 * wot2304@gmail.com
 * Yanis Yeltsyn
 */

declare(strict_types=1);

namespace Mondu\Mondu\Observer;

use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\QuoteFactory;
use Mondu\Mondu\Helper\ContextHelper;
use Mondu\Mondu\Helper\Log;
use Mondu\Mondu\Helper\MonduTransactionItem;
use Mondu\Mondu\Helper\OrderHelper;
use Mondu\Mondu\Helper\PaymentMethod;
use Mondu\Mondu\Model\Request\Factory as RequestFactory;
use Psr\Log\LoggerInterface;

class CreateOrder extends AbstractMonduObserver
{
    /**
     * @var string
     */
    protected $name = 'CreateOrder';

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var RequestFactory
     */
    private $requestFactory;

    /**
     * @var Log
     */
    private $monduLogger;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var PaymentMethod
     */
    private $paymentMethodHelper;

    /**
     * @var OrderHelper
     */
    private $orderHelper;

    /**
     * @var MonduTransactionItem
     */
    private $monduTransactionItem;

    /**
     * @param PaymentMethod $paymentMethodHelper
     * @param LoggerInterface $logger
     * @param ContextHelper $contextHelper
     * @param CheckoutSession $checkoutSession
     * @param RequestFactory $requestFactory
     * @param Log $monduLogger
     * @param QuoteFactory $quoteFactory
     * @param OrderHelper $orderHelper
     * @param MonduTransactionItem $monduTransactionItem
     */
    public function __construct(
        PaymentMethod        $paymentMethodHelper,
        LoggerInterface      $logger,
        ContextHelper        $contextHelper,
        CheckoutSession      $checkoutSession,
        RequestFactory       $requestFactory,
        Log                  $monduLogger,
        QuoteFactory         $quoteFactory,
        OrderHelper          $orderHelper,
        MonduTransactionItem $monduTransactionItem
    ) {
        parent::__construct(
            $paymentMethodHelper,
            $logger,
            $contextHelper
        );
        $this->checkoutSession = $checkoutSession;
        $this->requestFactory = $requestFactory;
        $this->quoteFactory = $quoteFactory;
        $this->orderHelper = $orderHelper;
        $this->monduLogger = $monduLogger;
        $this->paymentMethodHelper = $paymentMethodHelper;
        $this->monduTransactionItem = $monduTransactionItem;
    }

    /**
     * Execute
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function executeMondu(Observer $observer): void
    {
        $orderUid = $this->checkoutSession->getMonduid();
        $order = $observer->getEvent()->getOrder();
        $payment = $order->getPayment();
        $createMonduDatabaseRecord = true;

        $isEditOrder = $order->getRelationParentRealId() || $order->getRelationParentId();
        $isMondu = $this->paymentMethodHelper->isMondu($payment);

        if ($isEditOrder && !$isMondu) {
            //checks if order with Mondu payment method was changed to other payment method and cancels Mondu order.
            $this->orderHelper->handlePaymentMethodChange($order);
        }

        if (!$isMondu) {
            $this->logger->info('Not a Mondu order, skipping', ['orderNumber' => $order->getIncrementId()]);
            return;
        }

        if ($isEditOrder) {
            $this->logger
                ->info(
                    'Order has parent id, adjusting order in Mondu. ',
                    ['orderNumber' => $order->getIncrementId()]
                );
            $this->orderHelper->handleOrderAdjustment($order);
            $orderUid = $order->getMonduReferenceId();
            $createMonduDatabaseRecord = false;
        }

        try {
            $this->logger
                ->info('Validating order status in Mondu. ', ['orderNumber' => $order->getIncrementId()]);

            $orderData = $this->requestFactory->create(RequestFactory::TRANSACTION_CONFIRM_METHOD)
                ->setValidate(true)
                ->process(['orderUid' => $orderUid]);

            $orderData = $orderData['order'];
            $authorizationData = $this->confirmAuthorizedOrder($orderData, $order->getIncrementId());
            $orderData['state'] = $authorizationData['state'];

            $order->setData('mondu_reference_id', $orderUid);
            $order->addStatusHistoryComment(__('Mondu: order id %1', $orderData['uuid']));
            $order->save();
            $this->logger->info('Saved the order in Magento ', ['orderNumber' => $order->getIncrementId()]);

            if ($createMonduDatabaseRecord) {
                $this->monduLogger
                    ->logTransaction($order, $orderData, null, $this->paymentMethodHelper->getCode($payment));
            } else {
                $transactionId = $this->monduLogger->updateLogMonduData($orderUid, null, null, null, $order->getId());

                $this->monduTransactionItem->deleteRecords($transactionId);
                $this->monduTransactionItem->createTransactionItemsForOrder($transactionId, $order);
            }
        } catch (Exception $e) {
            $this->logger->info('Error in CreateOrder observer', ['orderNumber' => $order->getIncrementId()]);
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * Confirm Authorized Order
     *
     * @param array $orderData
     * @param string $orderNumber
     */
    protected function confirmAuthorizedOrder($orderData, $orderNumber)
    {
        if ($orderData['state'] === 'authorized') {
            $authorizationData = $this->requestFactory->create(RequestFactory::CONFIRM_ORDER)
                ->process(['orderUid' => $orderData['uuid'], 'referenceId' => $orderNumber]);
            return $authorizationData['order'];
        }
        return $orderData;
    }
}
