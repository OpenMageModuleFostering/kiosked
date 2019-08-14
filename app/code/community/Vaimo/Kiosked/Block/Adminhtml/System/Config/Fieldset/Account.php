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
class Vaimo_Kiosked_Block_Adminhtml_System_Config_Fieldset_Account
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Render the account fieldset
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);

        /* @var $configHelper Vaimo_Kiosked_Helper_Config */
        $configHelper = Mage::helper('kiosked/config');

        if (!$configHelper->getIsActive()) {
            return sprintf('<tr><td colspan="2">%s</td></tr>',
                $this->__('Enable the module to be able to connect your Kiosked account'));
        }

        /* @var $apiHelper Vaimo_Kiosked_Helper_Api */
        $apiHelper = Mage::helper('kiosked/api');

        /* @var $feedHelper Vaimo_Kiosked_Helper_Feed */
        $feedHelper = Mage::helper('kiosked/feed');

        $ipAddress = $this->getRequest()->getClientIp();

        $message = $this->__('The Kiosked account is now connected to this site. ');
        $message .= sprintf('<a href="%s">%s</a>', $apiHelper->getAccountConnectUrl($ipAddress),
            $this->__('Connect a different account'));

        if ($configHelper->getAPIKey() == null || $configHelper->getAPIKey() == ''
            || $configHelper->getSecret() == null || $configHelper->getSecret() == '') {
            $message = sprintf('<a href="%s">%s</a>', $apiHelper->getAutoSignupUrl(),
                $this->__('Sign up for an account at Kiosked'));
        } else if ($configHelper->getAccountId() == null || $configHelper->getAccountId() == '') {
            $message = sprintf('<a href="%s">%s</a>', $apiHelper->getAccountConnectUrl($ipAddress),
                $this->__('Connect your Kiosked account to this site'));
        }

        $message .= '<br />';

        $feedUrl = $feedHelper->getFeedUrl();
        $message .= sprintf($this->__('The product feed url: <a href="%s">%s</a>'),
            $feedUrl, $feedUrl);

        return sprintf('<tr><td colspan="2">%s</td></tr>', $this->__($message));
    }
}