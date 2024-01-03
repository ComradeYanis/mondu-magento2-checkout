<?php
/**
 * Copyright (c) 2024.
 * wot2304@gmail.com
 * Yanis Yeltsyn
 */

declare(strict_types=1);


namespace Mondu\Mondu\Controller\Payment\Checkout;

class Decline extends AbstractPaymentController
{
    /**
     * @inheritDoc
     */
    public function execute()
    {
        return $this->redirectWithErrorMessage('Mondu: Order has been declined');
    }
}
