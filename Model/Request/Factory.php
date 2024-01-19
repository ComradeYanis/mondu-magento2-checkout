<?php
/**
 * Copyright (c) 2024.
 * wot2304@gmail.com
 * Yanis Yeltsyn
 */

declare(strict_types=1);

namespace Mondu\Mondu\Model\Request;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Mondu\Mondu\Helper\HeadersHelper;
use Mondu\Mondu\Helper\Logger\Logger;
use Mondu\Mondu\Helper\ModuleHelper;
use Mondu\Mondu\Model\Request\Webhooks\Keys;

class Factory
{
    public const TRANSACTIONS_REQUEST_METHOD = 'CREATE_ORDER';
    public const TRANSACTION_CONFIRM_METHOD = 'GET_ORDER';
    public const SHIP_ORDER = 'CREATE_INVOICE';
    public const CANCEL = 'CANCEL_ORDER';
    public const MEMO = 'CREATE_CREDIT_NOTE';
    public const WEBHOOKS_KEYS_REQUEST_METHOD = 'GET_WEBHOOK_KEY';
    public const WEBHOOKS_REQUEST_METHOD = 'CREATE_WEBHOOK';
    public const ADJUST_ORDER = 'ADJUST_ORDER_2';
    public const EDIT_ORDER = 'ADJUST_ORDER';
    public const PAYMENT_METHODS = 'GET_PAYMENT_METHODS';
    public const ORDER_INVOICES = 'GET_ORDER_INVOICES';
    public const ERROR_EVENTS = 'CREATE_PLUGIN_EVENTS';
    public const CONFIRM_ORDER = 'CONFIRM_ORDER';

    /**
     * @var Logger
     */
    private $monduFileLogger;
    /**
     * @var HeadersHelper
     */
    private $headersHelper;

    /**
     * @var ModuleHelper
     */
    private $moduleHelper;

    /**
     * @var string[]
     */
    private $invokableClasses = [
        self::TRANSACTIONS_REQUEST_METHOD => Transactions::class,
        self::TRANSACTION_CONFIRM_METHOD => Confirm::class,
        self::SHIP_ORDER => Ship::class,
        self::CANCEL => Cancel::class,
        self::MEMO => Memo::class,
        self::WEBHOOKS_KEYS_REQUEST_METHOD => Keys::class,
        self::WEBHOOKS_REQUEST_METHOD => Webhooks::class,
        self::ADJUST_ORDER => Adjust::class,
        self::EDIT_ORDER => Edit::class,
        self::PAYMENT_METHODS => PaymentMethods::class,
        self::ORDER_INVOICES => OrderInvoices::class,
        self::ERROR_EVENTS => ErrorEvents::class,
        self::CONFIRM_ORDER => ConfirmOrder::class
    ];

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Logger $monduFileLogger
     * @param HeadersHelper $headersHelper
     * @param ModuleHelper $moduleHelper
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Logger $monduFileLogger,
        HeadersHelper $headersHelper,
        ModuleHelper $moduleHelper
    ) {
        $this->objectManager = $objectManager;
        $this->monduFileLogger = $monduFileLogger;
        $this->headersHelper = $headersHelper;
        $this->moduleHelper = $moduleHelper;
    }

    /**
     * Create class using object manager
     *
     * @param mixed $method
     * @return AbstractRequest
     * @throws LocalizedException
     */
    public function create($method)
    {
        $className = !empty($this->invokableClasses[$method])
            ? $this->invokableClasses[$method]
            : null;

        if ($className === null) {
            throw new LocalizedException(
                __('%1 method is not supported.')
            );
        }

        $this->monduFileLogger->info('Sending a request to mondu api, action: '. $method);
        $model = $this->objectManager->create($className);
        $model->setCommonHeaders($this->headersHelper->getHeaders())
            ->setEnvironmentInformation($this->moduleHelper->getEnvironmentInformation())
            ->setRequestOrigin($method);

        if ($method !== self::ERROR_EVENTS) {
            $model->setErrorEventsHandler($this->create(self::ERROR_EVENTS));
        }

        return $model;
    }
}
