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
class Vaimo_Kiosked_Model_Feed
{
    /**
     * The name of the referrer query parameter
     */
    const TRACKING_QUERY_PARAM_NAME = 'referrer';

    /**
     * The value of the referrer query parameter
     */
    const TRACKING_QUERY_PARAM_VALUE = 'kiosked';

    const FEED_FORMAT = 'Kiosked';

    const FEED_VERSION = 2;

    const STOCK_STATUS_IN_STOCK = 1;
    const STOCK_STATUS_AVAILABLE_FOR_ORDER = 2;
    const STOCK_STATUS_OUT_OF_STOCK = 3;
    const STOCK_STATUS_PREORDER = 4;
    const STOCK_STATUS_NO_INFO_BUT_AVAILABLE = 5;
    /**
     * @var Vaimo_Kiosked_Helper_Config
     */
    private $_configHelper = null;

    /**
     * @var Vaimo_Kiosked_Helper_Feed
     */
    private $_feedHelper = null;

    /**
     * @var Vaimo_Kiosked_Model_Resource_Product_Collection
     */
    private $_productCollections = array();

    /**
     * Get the config helper
     *
     * @return Vaimo_Kiosked_Helper_Config
     */
    public function getConfigHelper()
    {
        if ($this->_configHelper == null) {
            $this->_configHelper = Mage::helper('kiosked/config');
        }

        return $this->_configHelper;
    }

    /**
     * Set the config helper
     *
     * @param Vaimo_Kiosked_Helper_Config $configHelper
     */
    public function setConfigHelper($configHelper)
    {
        $this->_configHelper = $configHelper;
    }

    /**
     * Get the feed helper
     *
     * @return Vaimo_Kiosked_Helper_Feed
     */
    public function getFeedHelper()
    {
        if ($this->_feedHelper == null) {
            $this->_feedHelper = Mage::helper('kiosked/feed');
        }

        return $this->_feedHelper;
    }

    /**
     * Set feed helper
     *
     * @param Vaimo_Kiosked_Helper_Feed $feedHelper
     */
    public function setFeedHelper($feedHelper)
    {
        $this->_feedHelper = $feedHelper;
    }

    /**
     * Return if the feed should use the flat table or not
     *
     * @return bool
     */
    private function _useFlatTable()
    {
        return $this->_configHelper->useFlatTable();

    }

    /**
     * Log memory information
     */
    private function _logMemory()
    {
        if (Mage::getIsDeveloperMode()) {
            $bt = debug_backtrace();
            $function = $bt[1]['function'];
            $line = $bt[0]['line'];

            $memory = memory_get_peak_usage() / 1024 / 1024;
            Mage::log("Memory allocation in $function at line $line: $memory MB", Zend_Log::DEBUG);
        }
    }

    /**
     * Return product collections to use in the feed
     *
     * @return array|\Vaimo_Kiosked_Model_Resource_Product_Collection
     */
    public function getProductCollections()
    {
        if (count($this->_productCollections)) {
            return $this->_productCollections;
        }

        $this->_logMemory();

        // Get default product collection
        $this->loadProductCollection();

        $stores = $this->_getStores();
        foreach($stores as $store) {
            $this->loadProductCollection($store);
        }

        $this->_logMemory();

        return $this->_productCollections;
    }

    /**
     * Return stores to use in the feed
     *
     * @return array
     */
    private function _getStores()
    {
        $result = array();

        $stores = Mage::app()->getStores();

        foreach($stores as $store) {
            if (!$store->getIsActive()) {
                continue;
            }

            $countryCode = Mage::getStoreConfig('general/country/default', $store->getId());
            if ($countryCode === '') {
                continue;
            }

            $localeCode = Mage::getStoreConfig('general/locale/code', $store->getId());

            $result[] = array(
                'countryCode' => $countryCode,
                'localeCode' => $localeCode,
                'currency' => $this->_getCurrency($store),
                'store' => $store
            );
        }

        return $result;
    }

    /**
     * Return currency of a store
     *
     * @param $store
     * @return Zend_Currency
     */
    private function _getCurrency($store)
    {
        /* @var $app Mage_Core_Model_App */
        $app = Mage::app();

        $currencyCode = $store->getCurrentCurrencyCode();
        $currency = $app->getLocale()->currency($currencyCode);

        return $currency;
    }

    /**
     * Return language ISO from a localeCode
     *
     * @param $localeCode
     * @return mixed
     */
    private function _getLanguageISO($localeCode)
    {
        $parts = explode('_', $localeCode);
        return $parts[0];
    }

    /**
     * Get the product collection joined with the feed items
     *
     * @param null $store
     * @return null|\Vaimo_Kiosked_Model_Resource_Product_Collection
     */
    public function loadProductCollection($store = null)
    {
        if ($store == null) {
            $storeId = 0;
        }
        else {
            $storeId = $store['store']->getId();
        }

        if (!isset($this->_productCollections[$storeId])) {
            if ($storeId == 0) {
                Vaimo_Kiosked_Model_Mysql4_Product_Collection::setEnabledFlat(false);
                $this->_productCollections[$storeId] = array(
                    'collection' => Mage::getResourceModel('kiosked/product_collection')
                        ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
                        ->addAttributeToFilter('visibility', Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
                        ->addAttributeToFilter('type_id', array('in' => array(
                            $this->getFeedHelper()->getProductTypes())))
                        ->addAttributeToSelect('image', 'inner')
                        ->addAttributeToSelect('manufacturer', 'left')
                );
            }
            else {
                Vaimo_Kiosked_Model_Mysql4_Product_Collection::setEnabledFlat($this->_useFlatTable());

                if ($this->_useFlatTable()) {
                    // Set storeId on the flat resource entity model so that the correct table name is used in the
                    // collection select
                    $entity = Mage::getResourceSingleton('catalog/product_flat');
                    $entity->setStoreId($storeId);
                }

                $this->_productCollections[$storeId] = array(
                    'collection' => Mage::getResourceModel('kiosked/product_collection')
                        ->addStoreFilter($storeId)
                        ->addAttributeToSelect('name', 'inner')
                        ->addAttributeToSelect('description', 'inner')
                        ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
                        ->addAttributeToFilter('visibility', Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
                        ->addAttributeToFilter('type_id', array('in' => array(
                            $this->getFeedHelper()->getProductTypes())))
                        ->addFinalPrice()
                );

                if ($this->_useFlatTable()) {
                    $table = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'description')->getBackend()->getTable();
                    $attributeId = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'description')->getAttributeId();

                    $this->_productCollections[$storeId]['collection']
                        ->getSelect()
                        ->join(array(
                            'attributeTable' => $table),
                            'e.entity_id = attributeTable.entity_id',
                            array('description' => 'attributeTable.value')
                        )
                        ->where("attributeTable.attribute_id = ?", $attributeId);
                }

                $this->_productCollections[$storeId]['collection']
                    ->joinTable('cataloginventory/stock_item', 'product_id=entity_id',
                    array(
                        'qty' => 'qty',
                        'is_in_stock' => 'is_in_stock',
                        'manage_stock' => 'manage_stock',
                        'use_config_manage_stock' => 'use_config_manage_stock'
                    )
                );

                $this->_productCollections[$storeId]['language'] = $this->_getLanguageISO($store['localeCode']);
                $this->_productCollections[$storeId]['country'] = $store['countryCode'];
                $this->_productCollections[$storeId]['store'] = $store;
            }

            if (!$this->getConfigHelper()->getOutputEveryProduct()) {
                $this->_productCollections[$storeId]['collection']->addFeedItemFilter();
            }
        }

        return $this->_productCollections[$storeId];
    }

    /**
     * Save all feed items.
     *
     * @param array $productIds
     * @param int|Mage_Core_Model_Store $storeId
     * @return mixed
     */
    public function saveItems($productIds)
    {
        foreach($productIds as $productId) {
            /* @var $item Vaimo_Kiosked_Model_Feed_Item */
            $item = Mage::getModel('kiosked/feed_item');
            $item->setData('product_id', $productId);
            $item->save();
        }
    }

    /**
     * Delete all feed items from a store.
     *
     * @return mixed
     */
    public function deleteItems()
    {
        /* @var $itemCollection Vaimo_Kiosked_Model_Mysql4_Feed_Item_Collection */
        $itemCollection = Mage::getResourceModel('kiosked/feed_item_collection');
        $itemCollection->deleteItems();
    }

    /**
     * Convert the product collection to the feed format and return the feed as json
     *
     * @return string
     */
    public function toFeed()
    {
        $timerStart = microtime(true);

        $productCollections = $this->getProductCollections();

        $feed = array_merge($this->_getFeedHead(), $this->_convertCollectionsToFeed($productCollections));

        $productCollections = null;
        unset($productCollections);

        $timerTotal = microtime(true) - $timerStart;
        Mage::log("Total time for generating feed: $timerTotal seconds");

        return json_encode($feed);
    }

    /**
     * Return the head of the feed
     *
     * @return array
     */
    private function _getFeedHead()
    {
        return array(
            'format' => self::FEED_FORMAT,
            'version' => self::FEED_VERSION
        );
    }

    /**
     * Convert the product collections to the feed format
     *
     * @param $productCollections
     * @return array
     */
    private function _convertCollectionsToFeed($productCollections)
    {
        $this->_internal = $this->_createInternalCollection($productCollections);

        return array(
            'products' => $this->_convertInternalToFeed($this->_internal)
        );
    }

    /**
     * Convert the internal collection to the feed
     *
     * @param $internal
     * @return array
     */
    private function _convertInternalToFeed($internal)
    {
        $result = array();

        $this->_logMemory();

        foreach($internal as $product) {
            if (!isset($product['stores'])) {
                continue;
            }

            foreach($product['stores'] as $country => $store) {
                foreach($store['descriptions'] as $language => $description) {
                    $product['descriptions'][] = array(
                        'name' => $description['name'],
                        'description' => $description['description'],
                        'country' => $country,
                        'language' => $language
                    );
                }

                $product['prices'][] = array(
                    'price' => $store['price'],
                    'country' => $country,
                    'currency' => $store['currency']
                );

                $product['stockStatuses'][] = array(
                    'stockStatus' => $store['stockStatus'],
                    'amount' => $store['amount'],
                    'country' => $country
                );

                $product['links'][] = array(
                    'url' => $store['link'],
                    'country' => $country
                );
            }
            $product['stores'] = null;
            unset($product['stores']);

            $result[] = $product;
        }

        $this->_logMemory();

        $internal = null;
        unset($internal);

        Mage::log("Number of products in the feed: " . count($result), Zend_Log::DEBUG);
        return $result;
    }

    private $_internal = array();

    /**
     * Callback function that are used in _createInternalCollection when iterating the items.
     *
     * @param $args
     */
    public function addProductToInternal($args)
    {
        /* @var $product Mage_Catalog_Model_Product */
        $product = Mage::getModel('catalog/product');
        $product->setData($args['row']);

        $store = $args['store'];
        $storeId = $store !== null ? $store->getId() : 0;
        $country = $args['country'];
        $language = $args['language'];
        $currency = $args['currency'];

        $product->setStoreId($storeId);

        // Default collection
        if ($storeId == 0) {
            $this->_internal[$product->getId()] = array(
                'pricingModel' => $this->_getPricingModel(),
                'productIdFromReseller' => $this->_getProductId($product),
                'allowOtherResellers' => true,
                'manufacturerName' => $this->_getManufacturer($product, $store),
                'lastUpdated' => $this->_getLastUpdated($product),
                'images' => $this->_convertImageToFeed($product)
            );
        }
        else {
            if (!isset($_internal[$product->getId()]['stores'][$country])) {
                $this->_internal[$product->getId()]['stores'][$country] = array_merge(
                    array(
                        'price' => $this->_getPrice($product, $currency),
                        'currency' => $currency->getShortName(),
                        'link' => $this->_addTrackingToProductUrl($product->getProductUrl())),
                    $this->_getStockStatus($product)
                );
            }

            $this->_internal[$product->getId()]['stores'][$country]['descriptions'][$language] = array(
                'name' => strip_tags($product->getName()),
                'description' => strip_tags($product->getDescription())
            );
        }
    }

    /**
     * Create an internal collection of the feed that are used for convenience.
     * This is later transformed to the Kiosked format in _convertInternalToFeed.
     *
     * @param $productCollections
     * @return array
     */
    private function _createInternalCollection($productCollections)
    {
        $this->_logMemory();

        foreach ($productCollections as $productCollection) {
            $store = isset($productCollection['store']['store']) ? $productCollection['store']['store'] : null;
            $country = isset($productCollection['country']) ? $productCollection['country'] : '';
            $language = isset($productCollection['language']) ? $productCollection['language'] : '';
            $currency = isset($productCollection['store']['currency']) ? $productCollection['store']['currency'] : '';

            Mage::getSingleton('core/resource_iterator')->walk($productCollection['collection']->getSelect(),
                array(array($this, 'addProductToInternal')),
                array(
                    'store' => $store,
                    'country' => $country,
                    'language' => $language,
                    'currency' => $currency)
            );

            $this->_logMemory();

            $productCollection = null;
            unset($productCollection);
        }

        $this->_productCollections = null;
        unset($this->_productCollections);

        $this->_logMemory();

        return $this->_internal;
    }

    /**
     * Return if the product is valid to shown in the feed
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    private function _validateProduct($product)
    {
        if ($product->getFinalPrice() == 0) {
            return false;
        }

        return true;
    }

    /**
     * Return a Mage helper
     *
     * @param string $name
     * @return mixed
     */
    private function _getHelper($name)
    {
        return Mage::helper($name);
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    private function _getStockStatus($product)
    {
        if ($product->getData('use_config_manage_stock')) {
            $manageStock = (int) Mage::getStoreConfigFlag(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MANAGE_STOCK);
        } else {
            $manageStock = $product->getData('manage_stock');
        }

        if (!$manageStock) {
            return array(
                'stockStatus' => self::STOCK_STATUS_NO_INFO_BUT_AVAILABLE,
                'amount' => 0
            );
        }

        if ($product->getData('is_in_stock')) {
            $stockStatus['stockStatus'] = self::STOCK_STATUS_IN_STOCK;
        } else {
            $stockStatus['stockStatus'] = self::STOCK_STATUS_OUT_OF_STOCK;
        }
        $stockStatus['amount'] = (int)$product->getQty();

        return $stockStatus;
    }

    /**
     * Return product codes used in the feed
     *
     * @param $product
     * @return array
     */
    private function _getProductCodes($product)
    {
        return array(
            'SKU' => $product->getSku()
        );
    }

    /**
     * Return the manufacturer of a product
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Mage_Core_Model_Store $store
     * @return mixed
     */
    private function _getManufacturer($product, $store)
    {
        $manufacturer = $product->getAttributeText('manufacturer');

        if ($manufacturer == null) {
            if ($store == null) {
                $store = Mage::app()->getStore();
            }

            $manufacturer = $store->getWebsite()->getDefaultGroup()->getName();
        }

        return $manufacturer;
    }

    /**
     * Return the price of a product including the currency code
     *
     * @param Mage_Catalog_Model_Product $product
     * @param $currency
     * @return string
     */
    private function _getPrice($product, $currency)
    {
        $price = $product->getFinalPrice();
        return $currency->toCurrency($price, array(
            'display' => Zend_Currency::NO_SYMBOL,
            'locale' => Mage_Core_Model_Locale::DEFAULT_LOCALE,
            'format' => "#0.00")
        );
    }

    /**
     * Return id of the product
     *
     * @param $product
     * @return int
     */
    private function _getProductId($product)
    {
        return (int)$product->getId();
    }

    /**
     * Convert the product image to the feed format
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    private function _convertImageToFeed($product)
    {
        if ($product->getImage() == null || $product->getImage() == '') {
            return array();
        }

        $result = array();

        /* @var $helper Mage_Catalog_Helper_Image */
        $helper = $this->_getHelper('catalog/image');

        $result[] = (string) $helper->init($product, 'image');

        return $result;
    }

    /**
     * Return the payment model
     *
     * @return string
     */
    private function _getPricingModel()
    {
        return "CPA";
    }

    /**
     * Return the last update date in ISO-8601 of the product
     *
     * @param $product
     * @return string
     */
    private function _getLastUpdated($product)
    {
        /* @var $locale Mage_Core_Model_Locale */
        $locale = Mage::app()->getLocale();
        $date = $locale->date($product->getUpdatedAt());
        return $date->getIso();
    }

    /**
     * Add a tracking query string to the product url
     *
     * @param string $productUrl
     * @return string
     */
    private function _addTrackingToProductUrl($productUrl)
    {
        $uri = Zend_Uri_Http::fromString($productUrl);
        $uri->setQuery(self::TRACKING_QUERY_PARAM_NAME . '=' . self::TRACKING_QUERY_PARAM_VALUE);
        return $uri->getUri();
    }
}