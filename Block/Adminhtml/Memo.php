<?php
/**
 * Copyright (c) 2024.
 * wot2304@gmail.com
 * Yanis Yeltsyn
 */

declare(strict_types=1);

namespace Mondu\Mondu\Block\Adminhtml;

use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Psr\Log\LoggerInterface;

class Memo extends Template
{
    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * @var Registry $coreRegistry
     */
    private $coreRegistry;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param LoggerInterface $logger
     * @param SerializerInterface $serializer
     */
    public function __construct(
        Context $context,
        Registry $registry,
        LoggerInterface $logger,
        SerializerInterface $serializer
    ) {
        $this->coreRegistry = $registry;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * GetCreditMemo
     *
     * @return mixed|null
     * @todo get the data throw customer checkout session
     * @deprecated
     */
    public function getCreditMemo()
    {
        return $this->coreRegistry->registry('current_creditmemo');
    }

    /**
     * Render
     *
     * @return string
     */
    public function render()
    {
        return $this->getOrderMonduId();
    }

    /**
     * GetOrder
     *
     * @return mixed
     */
    public function getOrder()
    {
        return $this->getCreditMemo()->getOrder();
    }

    /**
     * GetOrderMonduId
     *
     * @return string
     */
    public function getOrderMonduId()
    {
        $memo = $this->getCreditMemo();
        $order = $memo->getOrder();

        return $order->getMonduReferenceId();
    }

    /**
     * Invoice collection for specific order
     *
     * @return mixed
     */
    public function invoices()
    {
        $invoiceCollection = $this->getOrder()->getInvoiceCollection();
        return $invoiceCollection;
    }

    /**
     * GetInvoiceMappings
     *
     * @return array|mixed
     * @todo ???
     */
    public function getInvoiceMappings()
    {
        $monduId = $this->getOrderMonduId();
        $log = $this->logger->getTransactionByOrderUid($monduId);

        if (!$log) {
            return [];
        }

        return $log['addons'] ? (json_decode($log['addons'], true) ?? []) : [];
    }
}
