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
class Vaimo_Kiosked_Model_Observer
{
    /**
     * Event that is fired on controller_front_init_before
     *
     * @param $observer
     */
    public function onControllerFrontInitBefore($observer)
    {
        $event = $observer->getEvent();

        /* @var $front Mage_Core_Controller_Varien_Front */
        $front = $event->getFront();

        $query = $front->getRequest()->getQuery();

        /* @var $trackingHelper Vaimo_Kiosked_Helper_Tracking */
        $trackingHelper = Mage::helper('kiosked/tracking');

        if ($trackingHelper->isQueryReferred($query)) {
            $trackingHelper->setReferrerCookie();
        }
    }

    /**
     * Event that is fired on admin_system_config_changed_section_kiosked.
     * Clean the feed block cache.
     *
     * @param $observer
     */
    public function onAdminSystemConfigChangedSectionKiosked($observer)
    {
        Mage::app()->cleanCache(array(Vaimo_Kiosked_Block_Feed::CACHE_TAG));
    }
}