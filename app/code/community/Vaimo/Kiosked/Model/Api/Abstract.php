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
abstract class Vaimo_Kiosked_Model_Api_Abstract
{
    /**
     * The API helper
     *
     * @var null|Vaimo_Kiosked_Helper_Api
     */
    protected $_apiHelper = null;

    /**
     * The config helper
     *
     * @var null|Vaimo_Kiosked_Helper_Config
     */
    protected $_configHelper = null;

    /**
     * The HTTP Client
     *
     * @var null|Zend_Http_Client
     */
    private $_httpClient = null;

    public function __construct($apiHelper = null, $configHelper = null, $httpClient = null)
    {
        $this->_apiHelper = $apiHelper;
        if ($this->_apiHelper == null) {
            $this->_apiHelper = Mage::helper('kiosked/api');
        }

        $this->_configHelper = $configHelper;
        if ($this->_configHelper == null) {
            $this->_configHelper = Mage::helper('kiosked/config');
        }

        $this->_httpClient = $httpClient;
        if ($this->_httpClient == null) {
            $this->_httpClient = new Zend_Http_Client();
        }
    }

    /**
     * Set the HTTP client
     *
     * @param $httpClient
     */
    public function setHttpClient($httpClient)
    {
        $this->_httpClient = $httpClient;
    }

    /**
     * Set the API helper
     *
     * @param $apiHelper
     */
    public function setAPIHelper($apiHelper)
    {
        $this->_apiHelper = $apiHelper;
    }

    /**
     * Set the config helper
     *
     * @param $configHelper
     */
    public function setConfigHelper($configHelper)
    {
        $this->_configHelper = $configHelper;
    }

    /**
     * Return the base message. This contains values that always should be included when making calls
     * to the Kiosked API.
     *
     * @return array
     */
    private function _getBaseMessage()
    {
        return array(
            'apiKey' => $this->_configHelper->getAPIKey()
        );
    }

    /**
     * Call the Kiosked API.
     *
     * @param string $methodName
     * @param array $message
     * @return mixed|null
     */
    protected function _callService($methodName, $message = array())
    {
        if ($methodName == null || $methodName == '') {
            return null;
        }

        if (!is_array($message)) {
            return null;
        }

        $message = array_merge($this->_getBaseMessage(), $message);
        $message = array_merge($this->_getModuleMessage(), $message);
        $message = json_encode($message);

        $url = $this->_apiHelper->getAPIUrl($this->_getModuleName(), $methodName);

        $this->_httpClient->setUri($url);
        $this->_httpClient->setMethod(Varien_Http_Client::POST);

        $this->_httpClient->setParameterPost('apiUserSignature', $this->_apiHelper->getUserSignature($message));
        $this->_httpClient->setParameterPost('message', $message);

        try {
            $response = $this->_httpClient->request('POST');

            if ($response->isSuccessful()) {
                return $this->_prepareResponse($response->getBody());
            }
        } catch(Zend_Http_Client_Exception $e) {
            Mage::log($e->getMessage(), Zend_Log::ERR);
            return null;
        }

        return null;
    }

    /**
     * Prepare the response before returning it to the calling method
     *
     * @param $response
     * @return mixed
     */
    protected function _prepareResponse($response)
    {
        return json_decode($response);
    }

    /**
     * Return the module message. This contains values that always should be included when making calls
     * to the Kiosked API for the specific module.
     *
     * @return array
     */
    protected function _getModuleMessage()
    {
        return array();
    }

    /**
     * Return the module name. This is used when creating the API url.
     *
     * @abstract
     */
    abstract protected function _getModuleName();
}