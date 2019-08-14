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
class Vaimo_Kiosked_Adminhtml_FeedController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('kiosked/feed_item');
    }

    /**
     * Index action for the feed admin section
     */
    public function indexAction()
    {
        $this->_title($this->__('Manage Kiosked Product Feed'));

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Product grid for AJAX request
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Publish product feed
     */
    public function massPublishAction()
    {
        $productIds = $this->getRequest()->getParam('item_id');

        /* @var $feed Vaimo_Kiosked_Model_Feed */
        $feed = Mage::getModel('kiosked/feed');

        $feed->deleteItems();

        try {
            $feed->saveItems($productIds);

            $this->_getSession()->addSuccess(
                Mage::helper('kiosked')->__("All the selected products are now published."));
        } catch (Exception $e) {
            $this->_getSession()->addError(
                Mage::helper('kiosked')->__("Unable to publish the selected products. ") . $e->getMessage());
        }

        Mage::app()->cleanCache(array(Vaimo_Kiosked_Block_Feed::CACHE_TAG));

        $this->_redirect('*/*/index');
    }
}