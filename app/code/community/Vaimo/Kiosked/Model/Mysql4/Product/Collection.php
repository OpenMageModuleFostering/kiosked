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
class Vaimo_Kiosked_Model_Mysql4_Product_Collection extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
{
    public static $isEnabledFlat = true;

    public function isEnabledFlat()
    {
        if (Mage::app()->getStore()->isAdmin()) {
            return false;
        }

        return self::$isEnabledFlat;
    }

    public static function setEnabledFlat($value)
    {
        self::$isEnabledFlat = $value;
    }

    /**
     * Inner join the kiosked feed item table with the product collection
     *
     * @param null|int|Mage_Core_Model_Store $storeId
     * @return Vaimo_Kiosked_Model_Mysql4_Product_Collection
     */
    public function addFeedItemFilter($storeId = null)
    {
        $itemTable = $this->getTable('kiosked/feed_item');

        $this->getSelect()->join(
            array('feed_item' => $itemTable),
                'e.entity_id = feed_item.product_id ',
            array()
        );

        return $this;
    }

    /**
     * Left join the kiosked feed item table with the product collection
     *
     * @return Vaimo_Kiosked_Model_Mysql4_Product_Collection
     */
    public function addFeedItemAttribute()
    {
        $itemTable = $this->getTable('kiosked/feed_item');

        $this->getSelect()->joinLeft(
            array('feed_item' => $itemTable),
                'e.entity_id = feed_item.product_id ',
            array('in_feed' => new Zend_Db_Expr('IF(feed_item.item_id IS NOT NULL, 1, 0)'))
        );

        return $this;
    }

    /**
     * Add the in_feed filter to the collection
     *
     * @param $value
     * @return Vaimo_Kiosked_Model_Mysql4_Product_Collection
     */
    public function addInFeedFilter($value)
    {
        if (is_string($value)) {
            $value = (bool)$value;
        }

        if ($value) {
            $this->getSelect()->where('feed_item.item_id IS NOT NULL');
        }
        else {
            $this->getSelect()->where('feed_item.item_id IS NULL');
        }
        return $this;
    }
}