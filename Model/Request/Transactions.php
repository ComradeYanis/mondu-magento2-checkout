<?php
/**
 * Copyright (c) 2024.
 * wot2304@gmail.com
 * Yanis Yeltsyn
 */

declare(strict_types=1);

namespace Mondu\Mondu\Model\Request;

use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\ClientInterface;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\UrlInterface;
use Magento\Quote\Model\Cart\CartTotalRepository;
use Magento\Quote\Model\Quote;
use Mondu\Mondu\Helper\BuyerParams\BuyerParamsInterface;
use Mondu\Mondu\Helper\OrderHelper;
use Mondu\Mondu\Model\Config\MonduConfigProvider;

class Transactions extends AbstractRequest implements RequestInterface
{
    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var CartTotalRepository
     */
    protected $cartTotalRepository;

    /**
     * @var string
     */
    protected $fallbackEmail;

    /**
     * @var OrderHelper
     */
    protected $orderHelper;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var BuyerParamsInterface
     */
    protected $buyerParams;

    /**
     * @var Resolver
     */
    protected $store;

    /**
     * @param ClientInterface $curl
     * @param SerializerInterface $serializer
     * @param MonduConfigProvider $configProvider
     * @param CartTotalRepository $cartTotalRepository
     * @param CheckoutSession $checkoutSession
     * @param OrderHelper $orderHelper
     * @param UrlInterface $urlBuilder
     * @param BuyerParamsInterface $buyerParams
     * @param Resolver $store
     */
    public function __construct(
        ClientInterface $curl,
        SerializerInterface $serializer,
        MonduConfigProvider $configProvider,
        CartTotalRepository $cartTotalRepository,
        CheckoutSession $checkoutSession,
        OrderHelper $orderHelper,
        UrlInterface $urlBuilder,
        BuyerParamsInterface $buyerParams,
        Resolver $store
    ) {
        parent::__construct(
            $curl,
            $serializer,
            $configProvider
        );
        $this->checkoutSession = $checkoutSession;
        $this->cartTotalRepository = $cartTotalRepository;
        $this->orderHelper = $orderHelper;
        $this->urlBuilder = $urlBuilder;
        $this->buyerParams = $buyerParams;
        $this->store = $store;
    }

    /**
     * Request
     *
     * @param array $_params
     * @return array
     */
    public function request($_params = []): array
    {
        try {
            if ($_params['email']) {
                $this->fallbackEmail = $_params['email'];
            }
            $params = $this->getRequestParams();

            if (in_array($_params['payment_method'], ['direct_debit', 'installment', 'installment_by_invoice'])) {
                $params['payment_method'] = $_params['payment_method'];
            }

            $params = $this->serializer->serialize($params);

            $url = $this->configProvider->getApiUrl('orders');

            $this->curl->addHeader('X-Mondu-User-Agent', $_params['user-agent']);

            $result = $this->sendRequestWithParams('post', $url, $params);
            $data = $this->serializer->unserialize($result);
            $this->checkoutSession->setMonduid($data['order']['uuid'] ?? null);

            if (!isset($data['order']['uuid'])) {
                return [
                    'error' => 1,
                    'body' => $this->serializer->unserialize($result),
                    'message' => __('Error placing an order Please try again later.')
                ];
            } else {
                return [
                    'error' => 0,
                    'body' => $this->serializer->unserialize($result),
                    'message' => __('Success')
                ];
            }
        } catch (Exception $e) {
            return [
                'error' => 1,
                'body' => null,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get Request Params from
     *
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function getRequestParams()
    {
        $quote = $this->checkoutSession->getQuote();
        $quote->collectTotals();

        $quoteTotals = $this->cartTotalRepository->get($quote->getId());

        $discountAmount = $quoteTotals->getDiscountAmount();

        $successUrl = $this->urlBuilder->getUrl('mondu/payment_checkout/success');
        $cancelUrl = $this->urlBuilder->getUrl('mondu/payment_checkout/cancel');
        $declinedUrl = $this->urlBuilder->getUrl('mondu/payment_checkout/decline');

        $locale = $this->store->getLocale();
        $language = $locale ? strstr($locale, '_', true) : 'de';

        $order = [
            'language' => $language,
            'currency' => $quote->getBaseCurrencyCode(),
            'state_flow' => MonduConfigProvider::AUTHORIZATION_STATE_FLOW,
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'declined_url' => $declinedUrl,
            'total_discount_cents' => abs($discountAmount) * 100,
            'buyer' => $this->getBuyerParams($quote),
            'external_reference_id' => uniqid('M2_'),
            'billing_address' => $this->getBillingAddressParams($quote),
            'shipping_address' => $this->getShippingAddressParams($quote)
        ];

        return $this->orderHelper->addLinesOrGrossAmountToOrder($quote, $quoteTotals->getBaseGrandTotal(), $order);
    }

    /**
     * Get Buyer params
     *
     * @param Quote $quote
     * @return array
     */
    private function getBuyerParams(Quote $quote): array
    {
        $params = [];
        if (($billing = $quote->getBillingAddress()) !== null) {
            $params = [
                'is_registered' => (bool) $quote->getCustomer()->getId(),
                'external_reference_id' => $quote->getCustomerId() ? (string) $quote->getCustomerId() : null,
                'email' => $billing->getEmail() ??
                    $quote->getShippingAddress()->getEmail() ??
                    $quote->getCustomerEmail() ??
                    $this->fallbackEmail,
                'company_name' => $billing->getCompany(),
                'first_name' => $billing->getFirstname(),
                'last_name' => $billing->getLastname(),
                'phone' => $billing->getTelephone()
            ];

            $params = $this->buyerParams->getBuyerParams($params, $quote);
        }
        return $params;
    }

    /**
     * Get billing address params
     *
     * @param Quote $quote
     * @return array
     */
    private function getBillingAddressParams(Quote $quote): array
    {
        $params = [];

        if (($billing = $quote->getBillingAddress()) !== null) {
            $address = (array) $billing->getStreet();
            $line1 = (string) array_shift($address);
            if ($billing->getStreetNumber()) {
                $line1 .= ', '. $billing->getStreetNumber();
            }
            $line2 = (string) implode(' ', $address);
            $params = [
                'country_code' => $billing->getCountryId(),
                'city' => $billing->getCity(),
                'zip_code' => $billing->getPostcode(),
                'address_line1' => $line1,
                'address_line2' => $line2,
            ];
            if ($billing->getRegion()) {
                $params['state'] = (string) $billing->getRegion();
            }
        }

        return $params;
    }

    /**
     * Get shipping address params
     *
     * @param Quote $quote
     * @return array
     */
    private function getShippingAddressParams(Quote $quote): array
    {
        $params = [];

        if (($shipping = $quote->getShippingAddress()) !== null) {
            $address = (array) $shipping->getStreet();
            $line1 = (string) array_shift($address);
            if ($shipping->getStreetNumber()) {
                $line1 .= ', '. $shipping->getStreetNumber();
            }
            $line2 = (string) implode(' ', $address);
            $params = [
                'country_code' => $shipping->getCountryId(),
                'city' => $shipping->getCity(),
                'zip_code' => $shipping->getPostcode(),
                'address_line1' => $line1,
                'address_line2' => $line2,
            ];

            if ($shipping->getRegion()) {
                $params['state'] = (string) $shipping->getRegion();
            }
        }

        return $params;
    }
}
