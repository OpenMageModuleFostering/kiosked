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
 * @package     Vaimo_
 * @copyright   Copyright (c) 2009-2012 Vaimo AB
 */

class Vaimo_Kiosked_Helper_Log extends Mage_Core_Helper_Abstract
{
    const LOG_FILENAME = 'vaimo_kiosked.log';

    public function debug($msg)
    {
        Mage::log($msg, Zend_Log::DEBUG, self::LOG_FILENAME, true);
    }

    public function info($msg, $logExtended = true)
    {
        $msgExtended = '';

        if ($logExtended) {
            /* @var $session Mage_Core_Model_Session */
            $session = Mage::getSingleton('core/session');

            if ($session != null && $session->getSessionId()) {
                $msgExtended = "Session {$session->getEncryptedSessionId()}. ";
            }

            /* @var $customerSession Mage_Customer_Model_Session */
            $customerSession = Mage::getSingleton('customer/session');

            if ($customerSession != null) {
                $customer = $customerSession->getCustomer();

                if ($customer != null && $customer->getId() && $customerSession->getSessionId()) {
                    $msgExtended = "Session {$customerSession->getEncryptedSessionId()}. Customer {$customer->getId()}. ";
                }
            }
        }

        if ($msgExtended !== '') {
            $msg = $msgExtended . $msg;
        }

        Mage::log($msg, Zend_Log::INFO, self::LOG_FILENAME, true);
    }
}
