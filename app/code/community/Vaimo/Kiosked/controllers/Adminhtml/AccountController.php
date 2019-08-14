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
class Vaimo_Kiosked_Adminhtml_AccountController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Check that the current user has permission
     *
     * @return mixed
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/kiosked');
    }

    /**
     * Connect the Kiosked account to the site. This action is called when the user is redirected from Kiosked
     *
     * @return mixed
     */
    public function connectAction()
    {
        /* @var $configHelper Vaimo_Kiosked_Helper_Config */
        $configHelper = Mage::helper('kiosked/config');

        if (!$configHelper->getIsActive()) {
            $this->_forward('noRoute');
            return;
        }

        $redirectUrl = $configHelper->getAdminConfigUrl();

        $params = $this->getRequest()->getParams();

        if (!isset($params['kioskedAccountId']) || !isset($params['sign'])) {
            $this->_redirectUrl($redirectUrl);
            return;
        }

        $accountId = $this->getRequest()->getQuery('kioskedAccountId');
        if ($accountId == '0') {
            $this->_redirectUrl($redirectUrl);
            return;
        }

        /* @var $apiHelper Vaimo_Kiosked_Helper_Api */
        $apiHelper = Mage::helper('kiosked/api');

        $sign = $this->getRequest()->getQuery('sign');
        $ipAddress = $this->getRequest()->getClientIp();

        if ($sign == '' || $accountId == '') {
            $this->_redirectUrl($redirectUrl);
            return;
        }

        if (!$apiHelper->verifyAccountConnectSign($sign, $accountId, $ipAddress)) {
            $this->_redirectUrl($redirectUrl);
            return;
        }

        $configHelper->setAccountId($accountId);
        $this->_redirectUrl($redirectUrl);
        return;
    }

    public function autosignupAction()
    {
        /* @var $configHelper Vaimo_Kiosked_Helper_Config */
        $configHelper = Mage::helper('kiosked/config');

        if (!$configHelper->getIsActive()) {
            $this->_forward('noRoute');
            return;
        }

        $redirectUrl = $configHelper->getAdminConfigUrl();

        $params = $this->getRequest()->getParams();

        if (!isset($params['apiKey']) || !isset($params['apiSecret']) || !isset($params['accountId']) ||
            !isset($params['cpaSecret']) || !isset($params['sign'])) {
            $this->_redirectUrl($redirectUrl);
            return;
        }

        /* @var $apiHelper Vaimo_Kiosked_Helper_Api */
        $apiHelper = Mage::helper('kiosked/api');

        /* @var $dataHelper Vaimo_Kiosked_Helper_Data */
        $dataHelper = Mage::helper('kiosked/data');

        $sign = $this->getRequest()->getQuery('sign');
        $tmpSecret = $dataHelper->getTmpScret();
        $apiKey = $this->getRequest()->getQuery('apiKey');
        $apiSecret = $this->getRequest()->getQuery('apiSecret');
        $accountId = $this->getRequest()->getQuery('accountId');
        $cpaDealSignatureSecret = $this->getRequest()->getQuery('cpaSecret');

        if (!$apiHelper->verifyAutoSignupSign($sign, $tmpSecret, $apiKey, $apiSecret, $accountId,
            $cpaDealSignatureSecret)) {
            $this->_redirectUrl($redirectUrl);
            return;
        }

        $configHelper->setAPIKey($apiKey);
        $configHelper->setSecret($apiSecret);
        $configHelper->setAccountId($accountId);
        $configHelper->setCpaDealSignatureSecret($cpaDealSignatureSecret);

        $this->_redirectUrl($redirectUrl);
        return;
    }
}