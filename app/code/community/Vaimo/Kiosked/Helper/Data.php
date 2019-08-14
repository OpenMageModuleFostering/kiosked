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
class Vaimo_Kiosked_Helper_Data extends Mage_Core_Helper_Abstract
{
    const KREF_NAME = 'kref';

    public function setTmpSecret()
    {
        $tmpSecret = MD5(microtime());
        Mage::getSingleton('core/session')->setTmpSecret($tmpSecret);
        return $tmpSecret;
    }

    public function getTmpScret()
    {
        return Mage::getSingleton('core/session')->getTmpSecret();
    }

    public function getModuleVersion()
    {
        return (string) Mage::getConfig()->getNode()->modules->Vaimo_Kiosked->version;
    }

    public function getKrefName()
    {
        return self::KREF_NAME;
    }

    public function getKrefValue()
    {
        return 'KMPv' . $this->getModuleVersion();
    }

    public function getKrefParam()
    {
        return array($this->getKrefName() => $this->getKrefValue());
    }


    /**
     * Add kref query parameter to a url
     *
     * @param Zend_Uri_Http $url
     * @return Zend_Uri_Http
     */
    public function addKrefParam($url)
    {
        if (!$url instanceof Zend_Uri_Http) {
            return $url;
        }

        $query = array_merge(
            $url->getQueryAsArray(),
            $this->getKrefParam()
        );

        $url->setQuery($query);

        return $url;
    }
}