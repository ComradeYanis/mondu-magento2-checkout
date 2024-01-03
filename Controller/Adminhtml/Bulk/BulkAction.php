<?php
/**
 * Copyright (c) 2024.
 * wot2304@gmail.com
 * Yanis Yeltsyn
 */

declare(strict_types=1);


namespace Mondu\Mondu\Controller\Adminhtml\Bulk;

use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
use Mondu\Mondu\Helper\BulkActions;
use Mondu\Mondu\Helper\Logger\Logger;

abstract class BulkAction extends Action
{
    use BulkActionHelpers;

    public const ADMIN_RESOURCE = 'Magento_Sales::sales_order';

    /**
     * @var BulkActions
     */
    private $bulkActions;

    /**
     * @var OrderCollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var Logger
     */
    private $monduFileLogger;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @param Action\Context $context
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param BulkActions $bulkActions
     * @param Logger $monduFileLogger
     * @param Filter $filter
     */
    public function __construct(
        Action\Context $context,
        OrderCollectionFactory $orderCollectionFactory,
        BulkActions $bulkActions,
        Logger $monduFileLogger,
        Filter $filter
    ) {
        $this->bulkActions = $bulkActions;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->monduFileLogger = $monduFileLogger;
        $this->filter = $filter;
        parent::__construct($context);
    }
    /**
     * Execute
     *
     * @throws LocalizedException
     */
    abstract public function execute();
}
