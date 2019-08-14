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
class Vaimo_Kiosked_Helper_Config extends Mage_Core_Helper_Abstract
{
    /**
     * Return if the module is enabled or disabled
     *
     * @return bool
     */
    public function getIsActive()
    {
        return Mage::getStoreConfigFlag('kiosked/general_settings/active');
    }

    /**
     * Return if the feed should contain every product
     *
     * @return bool
     */
    public function getOutputEveryProduct()
    {
        return Mage::getStoreConfigFlag('kiosked/general_settings/output_every_product');
    }

    /**
     * Return the API key set in the configuration
     *
     * @return string
     */
    public function getAPIKey()
    {
        return Mage::getStoreConfig('kiosked/account_settings/api_key');
    }

    /**
     * Return if the module should use the flat product table
     * @return bool
     */
    public function useFlatTable()
    {
        return Mage::getStoreConfigFlag('kiosked/advanced_settings/use_flat_table');
    }

    /**
     * Set the API key
     *
     * @param string $apiKey
     */
    public function setAPIKey($apiKey)
    {
        Mage::getConfig()->saveConfig('kiosked/account_settings/api_key', $apiKey);
        Mage::getConfig()->saveCache();
    }

    /**
     * Return the secret set in the configuration
     *
     * @return string
     */
    public function getSecret()
    {
        return Mage::helper('core')->decrypt(Mage::getStoreConfig('kiosked/account_settings/secret'));
    }

    /**
     * Set the secret
     *
     * @param string $secret
     */
    public function setSecret($secret)
    {
        Mage::getConfig()->saveConfig('kiosked/account_settings/secret', $secret);
        Mage::getConfig()->saveCache();
    }

    /**
     * Return the Kiosked account id
     *
     * @return int
     */
    public function getAccountId()
    {
        return intval(Mage::getStoreConfig('kiosked/account_settings/account_id'));
    }

    /**
     * Set the Kiosked account id
     *
     * @param string|int $accountId
     */
    public function setAccountId($accountId)
    {
        Mage::getConfig()->saveConfig('kiosked/account_settings/account_id', $accountId);
        Mage::getConfig()->saveCache();
    }

    /**
     * Return the cpa deal signature secret set in the configuration
     *
     * @return string
     */
    public function getCpaDealSignatureSecret()
    {
        return Mage::helper('core')->decrypt(Mage::getStoreConfig('kiosked/general_settings/cpa_deal_signature_secret'));
    }

    /**
     * Set the cpa deal signature secret
     *
     * @param string $secret
     */
    public function setCpaDealSignatureSecret($secret)
    {
        $encrypted = Mage::helper('core')->encrypt($secret);

        Mage::getConfig()->saveConfig('kiosked/general_settings/cpa_deal_signature_secret', $encrypted);
        Mage::getConfig()->saveCache();
    }

    /**
     * Return the API url. Always returns the url with an ending slash.
     *
     * @return string
     */
    public function getAPIUrl()
    {
        $apiUrl = Mage::getStoreConfig('kiosked/general_settings/api_url');

        if (substr($apiUrl, -1) !== '/') {
            $apiUrl .= '/';
        }

        return $apiUrl;
    }

    /**
     * Return the Widgets url.
     *
     * @return string
     */
    public function getWidgetsUrl()
    {
        $widgetsUrl = Mage::getStoreConfig('kiosked/general_settings/widgets_url');

        if (substr($widgetsUrl, -1) !== '/') {
            $widgetsUrl .= '/';
        }

        return $widgetsUrl;
    }

    /**
     * Return the account connect url.
     *
     * @return string
     */
    public function getAccountConnectUrl()
    {
        $accountConnectUrl = Mage::getStoreConfig('kiosked/general_settings/account_connect_url');

        if (substr($accountConnectUrl, -1) !== '/') {
            $accountConnectUrl .= '/';
        }

        return $accountConnectUrl;
    }

    /**
     * Return the admin configuration url for Kiosked
     *
     * @return string
     */
    public function getAdminConfigUrl()
    {
        return Mage::helper("adminhtml")->getUrl('adminhtml/system_config/edit', array('section' => 'kiosked'));
    }


    /**
     * Return the lifetime of the tracking cookie in seconds
     *
     * @return int
     */
    public function getTrackingCookieLifetimeSeconds()
    {
        $days = intval(Mage::getStoreConfig('kiosked/general_settings/cookie_lifetime_days'));
        return $days * 3600 * 24;
    }

    /**
     * Return the automatic signup url
     *
     * @return string
     */
    public function getAutoSignupUrl()
    {
        return "{$this->getAPIUrl()}setup/setup-merchant";
    }
}