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
class Vaimo_Kiosked_Block_Feed extends Mage_Core_Block_Abstract
{
    /**
     * Cache tag
     */
    const CACHE_TAG = 'kiosked_feed';

    public function getCacheKeyInfo()
    {
        return array(
            self::CACHE_TAG,
            Mage::app()->getStore()->getId()
        );
    }

    public function _construct()
    {
        $this->addData(array(
            'cache_lifetime' => 604800, // One week
            'cache_tags'     => array(Mage_Catalog_Model_Product::CACHE_TAG, self::CACHE_TAG)
        ));
    }

    /**
     * Generate feed json
     *
     * @return string
     */
    public function _toHtml()
    {
        /* @var $feed Vaimo_Kiosked_Model_Feed */
        $feed = Mage::getModel('kiosked/feed');

        return $feed->toFeed();
    }
}