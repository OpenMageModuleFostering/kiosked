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
class Vaimo_Kiosked_Model_Api_Product extends Vaimo_Kiosked_Model_Api_Abstract
{
    protected function _getModuleName()
    {
        return 'product';
    }

    protected function _getModuleMessage()
    {
        return array(
            'kioskedAccountId' => $this->_configHelper->getAccountId(),
            'timestamp' => time()
        );
    }

    /**
     * Call the Kiosked API. Check that the accountId is set.
     *
     * @param string $methodName
     * @param array $message
     * @return mixed|null
     * @throws Vaimo_Kiosked_Exception
     */
    protected function _callService($methodName, $message = array())
    {
        if ($this->_configHelper->getAccountId() == null || $this->_configHelper->getAccountId() == '') {
            throw new Vaimo_Kiosked_Exception('The account id must be set in the configuration');
        }

        return parent::_callService($methodName, $message);
    }

    /**
     * Return all countries
     *
     * @return mixed|null
     */
    public function getCountries()
    {
        return $this->_callService('get-countries');
    }
}