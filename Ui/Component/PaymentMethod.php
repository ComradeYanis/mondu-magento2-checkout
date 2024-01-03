<?php
/**
 * Copyright (c) 2024.
 * wot2304@gmail.com
 * Yanis Yeltsyn
 */

declare(strict_types=1);

namespace Mondu\Mondu\Ui\Component;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class PaymentMethod extends Column
{
    /**
     * @var \Mondu\Mondu\Helper\PaymentMethod
     */
    private $paymentMethodHelper;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Mondu\Mondu\Helper\PaymentMethod $paymentMethodHelper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface                   $context,
        UiComponentFactory                 $uiComponentFactory,
        \Mondu\Mondu\Helper\PaymentMethod $paymentMethodHelper,
        array                              $components = [],
        array                              $data = []
    ) {
        parent::__construct(
            $context,
            $uiComponentFactory,
            $components,
            $data
        );
        $this->paymentMethodHelper = $paymentMethodHelper;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$this->getData('name')] = $this->paymentMethodHelper->getLabel($item['payment_method']);
            }
        }

        return $dataSource;
    }
}
