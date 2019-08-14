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
class Vaimo_Kiosked_Helper_Api extends Mage_Core_Helper_Abstract
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
     * Return the API user signature that is required by the API.
     *
     * @param $message
     * @return string
     */
    public function getUserSignature($message)
    {
        if (!is_string($message)) {
            $message = json_encode($message);
        }

        return md5($message . $this->_configHelper->getSecret());
    }

    /**
     * Verify that the signature that is returned from Kiosked when connecting an account is correct.
     *
     * @param $sign
     * @param $accountId
     * @param $ipAddress
     * @return bool
     */
    public function verifyAccountConnectSign($sign, $accountId, $ipAddress)
    {
        $generated = md5($this->_configHelper->getSecret() . $this->_configHelper->getAPIKey() .
            $accountId . $ipAddress);

        return $sign == $generated;
    }

    /**
     * Verify that the signature that is returned from Kiosked after automatic signup is correct.
     *
     * @param $sign
     * @param $tmpSecret
     * @param $apiKey
     * @param $secret
     * @param $accountId
     * @param $cpaDealSignatureSecret
     * @return bool
     */
    public function verifyAutoSignupSign($sign, $tmpSecret, $apiKey, $secret, $accountId, $cpaDealSignatureSecret)
    {
        // TODO: $cpaDealSignatureSecret ???
        $generated = md5($tmpSecret . $apiKey . $secret . $accountId . $cpaDealSignatureSecret);

        return $sign == $generated;
    }

    /**
     * Return the signature for the account connect url
     *
     * @param string $redirect
     * @param string $ipAddress
     * @return string
     */
    protected function _getAccountConnectSign($redirect, $ipAddress)
    {
        return md5($this->_configHelper->getSecret() . $this->_configHelper->getAPIKey() . $redirect . $ipAddress);
    }

    /**
     * Return the API Url
     *
     * @param string $ipAddress
     * @return string
     */
    public function getAccountConnectUrl($ipAddress)
    {
        $redirect = $this->_getAccountConnectRedirectUrl();

        $message = array(
            'apiKey' => $this->_configHelper->getAPIKey(),
            'redirect' => $redirect
        );

        $apiUrl = Zend_Uri_Http::fromString($this->_configHelper->getAccountConnectUrl());
        $apiUrl->setPath($apiUrl->getPath() . 'connect');
        $apiUrl->setQuery(
                array_merge(
                    $message,
                    array('sign' => $this->_getAccountConnectSign($redirect, $ipAddress))
                )
        );

        $apiUrl = $this->_dataHelper->addKrefParam($apiUrl);

        return $apiUrl;
    }

    /**
     * Return the API url
     *
     * @param string $moduleName
     * @param string $methodName
     * @return mixed|Zend_Uri_Http
     */
    public function getAPIUrl($moduleName = '', $methodName = '')
    {
        $url = Zend_Uri_Http::fromString($this->_configHelper->getAPIUrl());

        if ($url->getPath()) {
            $path = $url->getPath();
        }
        else {
            $path = '/';
        }

        if ($moduleName !== '') {
            $path .= $moduleName . '/';
        }

        if ($methodName !== '') {
            $path .= $methodName . '/';
        }

        $url->setPath($path);

        $url = $this->_dataHelper->addKrefParam($url);

        return $url;
    }

    /**
     * Return the automatic signup url
     *
     * @return string
     */
    public function getAutoSignupUrl()
    {
        $url = Zend_Uri_Http::fromString($this->_configHelper->getAutoSignupUrl());
        $url->setQuery(
            array(
                'redirectUrl' => $this->_getAutoSignupRedirectUrl(),
                'tmpSecret' => $this->_dataHelper->setTmpSecret()
            )
        );

        $url = $this->_dataHelper->addKrefParam($url);

        return $url;
    }

    /**
     * Return the account connect redirect url that is sent to Kiosked when the use tries to connect the account
     * to the site.
     *
     * @return string
     */
    protected function _getAccountConnectRedirectUrl()
    {
        return Mage::helper("adminhtml")->getUrl('kiosked/adminhtml_account/connect');
    }

    /**
     * Return the account connect redirect url that is sent to Kiosked when the use tries to connect the account
     * to the site.
     *
     * @return string
     */
    protected function _getAutoSignupRedirectUrl()
    {
        return Mage::helper("adminhtml")->getUrl('kiosked/adminhtml_account/autosignup');
    }
}