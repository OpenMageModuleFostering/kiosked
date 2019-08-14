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
class Vaimo_Kiosked_Block_Adminhtml_Product_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * @var Vaimo_Kiosked_Helper_Feed
     */
    private $_feedHelper;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('kioskedProductGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('product_filter');

        $this->_feedHelper = Mage::helper('kiosked/feed');
    }

    /**
     * Prepare the collection
     *
     * @return \Vaimo_Kiosked_Block_Adminhtml_Product_Grid
     */
    protected function _prepareCollection()
    {
        $this->setDefaultFilter(array('in_feed' => 1));

        /* @var $collection Vaimo_Kiosked_Model_Resource_Product_Collection */
        $collection = Mage::getResourceModel('kiosked/product_collection')
            ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
            ->addAttributeToFilter('visibility', Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
            ->addAttributeToFilter('type_id', array('in' =>
                $this->_feedHelper->getProductTypes()))
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('attribute_set_id')
            ->addFeedItemAttribute();

        $this->setCollection($collection);

        parent::_prepareCollection();
        return $this;
    }

    /**
     * Add column filter to collection
     *
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @return Vaimo_Kiosked_Block_Adminhtml_Product_Grid
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_feed') {
            $filter = $column->getFilter();
            $value = $filter->getValue();

            if ($value !== '') {
                $this->getCollection()->addInFeedFilter($value);
            }
        }
        else {
            return parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * Prepare columns
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('in_feed',
            array(
                'header' => Mage::helper('kiosked')->__('In Current Feed'),
                'align' => 'left',
                'width' => '80px',
                'index' => 'in_feed',
                'type' => 'options',
                'options' => array(
                    0 => Mage::helper('kiosked')->__('No'),
                    1 => Mage::helper('kiosked')->__('Yes'),
                ),
            ));

        $this->addColumn('entity_id',
            array(
                'header'=> Mage::helper('kiosked')->__('ID'),
                'width' => '50px',
                'type'  => 'number',
                'index' => 'entity_id',
            ));
        $this->addColumn('name',
            array(
                'header'=> Mage::helper('kiosked')->__('Name'),
                'index' => 'name',
            ));

        $this->addColumn('type',
            array(
                'header'=> Mage::helper('catalog')->__('Type'),
                'width' => '60px',
                'index' => 'type_id',
                'type'  => 'options',
                'options' => Mage::getSingleton('catalog/product_type')->getOptionArray(),
            ));

        $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
            ->load()
            ->toOptionHash();

        $this->addColumn('set_name',
            array(
                'header'=> Mage::helper('kiosked')->__('Attrib. Set Name'),
                'width' => '100px',
                'index' => 'attribute_set_id',
                'type'  => 'options',
                'options' => $sets,
            ));

        $this->addColumn('sku',
            array(
                'header'=> Mage::helper('kiosked')->__('SKU'),
                'width' => '80px',
                'index' => 'sku',
            ));

        $this->addColumn('visibility',
            array(
                'header'=> Mage::helper('catalog')->__('Visibility'),
                'width' => '70px',
                'index' => 'visibility',
                'type'  => 'options',
                'options' => Mage::getModel('catalog/product_visibility')->getOptionArray(),
            ));

        return parent::_prepareColumns();
    }

    /**
     * Prepare massaction
     *
     * @return Vaimo_Kiosked_Block_Adminhtml_Product_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('item_id');

        $this->getMassactionBlock()->addItem('publish', array(
            'label'         => Mage::helper('kiosked')->__('Publish all selected'),
            'url'           => $this->getUrl('*/*/massPublish',
                array('store' => $this->getRequest()->getParam('store'))),
            'selected'      => true,
        ));

        return $this;
    }

    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    /**
     * Get row url
     *
     * @param $row
     * @return null
     */
    public function getRowUrl($row)
    {
        return null;
    }
}