<?php
/**
 * Copyright (c) 2009-2012 Vaimo AB
 *
 * Vaimo reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Vaimo, except as provided by licence. A licence
 * under Vaimo's rights in the Program may be available directly from
 * Vaimo.
 *
 * Disclaimer:
 * THIS NOTICE MAY NOT BE REMOVED FROM THE PROGRAM BY ANY USER THEREOF.
 * THE PROGRAM IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Vaimo
 * @package     Vaimo_Kiosked
 * @copyright   Copyright (c) 2009-2012 Vaimo AB
 */
class Vaimo_Kiosked_Helper_Tracking extends Mage_Core_Helper_Abstract
{
    /**
     * @var Vaimo_Kiosked_Helper_Config
     */
    private $_configHelper = null;

    /**
     * @var Vaimo_Kiosked_Helper_Data
     */
    private $_dataHelper = null;

    public function __construct($configHelper = null, $dataHelper = null)
    {
        $this->_configHelper = $configHelper;

        if ($this->_configHelper == null) {
            $this->_configHelper = Mage::helper('kiosked/config');
        }

        $this->_dataHelper = $dataHelper;

        if ($this->_dataHelper == null) {
            $this->_dataHelper = Mage::helper('kiosked/data');
        }
    }

    /**
     * Return if the query contains referrer information
     *
     * @param array $query
     * @return bool
     */
    public function isQueryReferred($query)
    {
        if (isset($query[Vaimo_Kiosked_Model_Feed::TRACKING_QUERY_PARAM_NAME]) &&
            $query[Vaimo_Kiosked_Model_Feed::TRACKING_QUERY_PARAM_NAME] ==
                Vaimo_Kiosked_Model_Feed::TRACKING_QUERY_PARAM_VALUE) {
            return true;
        }

        return false;
    }

    /**
     * Set referrer cookie
     */
    public function setReferrerCookie()
    {
        /* @var $cookie Mage_Core_Model_Cookie */
        $cookie = Mage::app()->getCookie();

        /* @var $configHelper Vaimo_Kiosked_Helper_Config */
        $configHelper = Mage::helper('kiosked/config');

        /* @var $logHelper Vaimo_Kiosked_Helper_Log */
        $logHelper = Mage::helper('kiosked/log');

        $logHelper->info("Set referrer cookie.");

        $cookie->set(Vaimo_Kiosked_Model_Feed::TRACKING_QUERY_PARAM_NAME,
            Vaimo_Kiosked_Model_Feed::TRACKING_QUERY_PARAM_VALUE, $configHelper->getTrackingCookieLifetimeSeconds());
    }

    /**
     * Return if the referrer cookie it set
     *
     * @return bool
     */
    public function isReferrerCookieSet()
    {
        /* @var $cookie Mage_Core_Model_Cookie */
        $cookie = Mage::app()->getCookie();

        $value = $cookie->get(Vaimo_Kiosked_Model_Feed::TRACKING_QUERY_PARAM_NAME);

        return $value && $value == Vaimo_Kiosked_Model_Feed::TRACKING_QUERY_PARAM_VALUE;
    }

    /**
     * Return the src of the tracking image
     *
     * @param string $accountId
     * @param string|int $orderId
     * @param string|float $amount
     * @param string $currency
     * @param string $sign
     * @return string
     */
    private function _getTrackingImageSrc($accountId, $orderId, $amount, $currency, $sign)
    {
        $url = Zend_Uri_Http::fromString($this->_configHelper->getWidgetsUrl());

        $url->setPath('/an/sales/sale');

        $url->setQuery(
            array(
                'accountId' => $accountId,
                'orderId' => $orderId,
                'amount' => $amount,
                'currency' => $currency,
                'sign' => $sign
            )
        );

        $url = $this->_dataHelper->addKrefParam($url);

        return $url;
    }

    /**
     * Return the complete HTML of the tracking image
     *
     * @param Mage_Sales_Model_Order $order
     * @return string
     */
    public function getTrackingImageHtml($order)
    {
        // amount of the total purchase in euro (everything in the shopping cart)
        $amount = $order->getGrandTotal();
        $currency = $order->getStore()->getCurrentCurrencyCode();
        // orderId is a unique ID for the order in your system
        $orderId = $order->getIncrementId();
        // apiKey is your Kiosked API key
        $accountId = $this->_configHelper->getAccountId();
        // cpaDealSignatureSecret a shared secret between you and Kiosked. Don't make this available for your end users
        $secret = $this->_configHelper->getCpaDealSignatureSecret();

        $signature = $this->_createSignature($accountId, $orderId, $amount, $currency, $secret);
        $trackingImageSrc = $this->_getTrackingImageSrc($accountId, $orderId, $amount, $currency, $signature);

        $trackingImageHtml = sprintf('<img src="%s" />', $trackingImageSrc);

        /* @var $logHelper Vaimo_Kiosked_Helper_Log */
        $logHelper = Mage::helper('kiosked/log');

        $logHelper->info("Get tracking image html: $trackingImageSrc");

        return $trackingImageHtml;
    }

    /**
     * Create a signature according to instructions from Kiosked
     *
     * @param string $accountId
     * @param string|int $orderId
     * @param string|float $amount
     * @param string $currency
     * @param string $secret
     * @return string
     */
    private function _createSignature($accountId, $orderId, $amount, $currency, $secret)
    {
        return md5($accountId . $orderId . $amount . $currency . $secret);
    }

}