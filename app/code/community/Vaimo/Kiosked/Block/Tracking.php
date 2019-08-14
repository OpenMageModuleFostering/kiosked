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
class Vaimo_Kiosked_Block_Tracking extends Mage_Core_Block_Abstract
{
    /**
     * @var string
     */
    private $_trackingImageHtml = null;

    /**
     * Check if the referrer cookie is set. If it is set, generate the tracking image html
     *
     * @return Mage_Core_Block_Abstract
     */
    public function _beforeToHtml()
    {
        if (!$this->getTrackingHelper()->isReferrerCookieSet()) {
            return;
        }

        $this->_trackingImageHtml = $this->_prepareTrackingImageHtml();
        return parent::_beforeToHtml();
    }

    /**
     * Prepare the HTML of the tracking image
     *
     * @return string
     */
    private function _prepareTrackingImageHtml()
    {
        $order = $this->_getOrder();

        if ($order == null) {
            return;
        }

        return $this->getTrackingHelper()->getTrackingImageHtml($order);
    }

    /**
     * Return the tracking image html
     *
     * @return null|string
     */
    public function _toHtml()
    {
        if ($this->_trackingImageHtml == null || $this->_trackingImageHtml == '') {
            return;
        }

        return $this->_trackingImageHtml;
    }

    /**
     * The order
     *
     * @var Mage_Sales_Model_Order
     */
    private $_order = null;

    /**
     * Return the order
     *
     * @return Mage_Sales_Model_Order
     */
    private function _getOrder()
    {
        if ($this->_order == null) {
            $orderId = Mage::getSingleton('checkout/session')->getLastOrderId();

            if ($orderId) {
                $this->_order = Mage::getModel('sales/order')->load($orderId);
            }
        }

        return $this->_order;
    }

    /**
     * The tracking helper
     *
     * @var Vaimo_Kiosked_Helper_Tracking
     */
    private $_trackingHelper = null;

    /**
     * Get the tracking helper
     *
     * @return Vaimo_Kiosked_Helper_Tracking
     */
    public function getTrackingHelper()
    {
        if ($this->_trackingHelper == null) {
            $this->_trackingHelper = $this->helper('kiosked/tracking');
        }

        return $this->_trackingHelper;
    }

    /**
     * Set the tracking helper
     *
     * @param $trackingHelper
     */
    public function setTrackingHelper($trackingHelper)
    {
        $this->_trackingHelper = $trackingHelper;
    }
}