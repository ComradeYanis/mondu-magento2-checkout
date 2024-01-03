<?php
/**
 * Copyright (c) 2024.
 * wot2304@gmail.com
 * Yanis Yeltsyn
 */

declare(strict_types=1);

namespace Mondu\Mondu\Observer\Adminhtml;

use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Mondu\Mondu\Helper\ContextHelper;
use Mondu\Mondu\Helper\InvoiceOrderHelper;
use Mondu\Mondu\Helper\Log;
use Mondu\Mondu\Helper\PaymentMethod;
use Mondu\Mondu\Observer\AbstractMonduObserver;
use Psr\Log\LoggerInterface;

class ShipOrder extends AbstractMonduObserver
{
    /**
     * @var string
     */
    protected $name = 'ShipOrder';

    /**
     * @var Log
     */
    protected $monduLogger;

    /**
     * @var InvoiceOrderHelper
     */
    private $invoiceOrderHelper;

    /**
     * @param PaymentMethod $paymentMethodHelper
     * @param LoggerInterface $logger
     * @param ContextHelper $contextHelper
     * @param Log $monduLogger
     * @param InvoiceOrderHelper $invoiceOrderHelper
     */
    public function __construct(
        PaymentMethod      $paymentMethodHelper,
        LoggerInterface    $logger,
        ContextHelper      $contextHelper,
        Log                $monduLogger,
        InvoiceOrderHelper $invoiceOrderHelper
    ) {
        parent::__construct(
            $paymentMethodHelper,
            $logger,
            $contextHelper
        );
        $this->monduLogger = $monduLogger;
        $this->invoiceOrderHelper = $invoiceOrderHelper;
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
        $shipment = $observer->getEvent()->getShipment();
        $order = $shipment->getOrder();

        $monduLog = $this->monduLogger->getLogCollection($order->getData('mondu_reference_id'));

        if ($monduLog->getSkipShipObserver()) {
            $this->logger
                ->info(
                    'Already invoiced using invoice orders action, skipping',
                    ['orderNumber' => $order->getIncrementId()]
                );
            return;
        }

        $monduId = $order->getData('mondu_reference_id');
        $this->monduLogger->syncOrder($monduId);

        if (!$this->monduLogger->canShipOrder($monduId)) {
            throw new LocalizedException(
                __('Can\'t ship order: Mondu order state must be confirmed or partially_shipped')
            );
        }

        $this->invoiceOrderHelper->handleInvoiceOrder($order, $shipment, $monduLog);
    }
}
