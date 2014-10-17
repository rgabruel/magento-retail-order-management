<?php
/**
 * Copyright (c) 2013-2014 eBay Enterprise, Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright   Copyright (c) 2013-2014 eBay Enterprise, Inc. (http://www.ebayenterprise.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once 'abstract.php';

/**
 * Rom Category Shell
 */
class EbayEnterprise_Rom_Shell_Add_Categories extends Mage_Shell_Abstract
{
	private $_categoryObject;
	private $_attributeSetId;
	private $_defaultParentCategoryId;
	private $_storeRootCategoryId;
	private $_defaultStoreId;

    /** @var EbayEnterprise_MageLog_Helper_Data $_log */
    protected $_log;

    /**
	 * Instantiate the catalog/category
	 */
	public function _construct()
	{
		parent::_construct();
        $this->_log = Mage::helper('ebayenterprise_magelog');
		$this->_categoryObject = Mage::getModel('catalog/category');
		$this->_attributeSetId = $this->_getCategoryAttributeSetId();
		$this->_defaultParentCategoryId = $this->_getDefaultParentCategoryId();
		$this->_storeRootCategoryId = $this->_getStoreRootCategoryId();
		$this->_defaultStoreId = $this->_getDefaultStoreId();
	}

	/**
	 * getting default store id
	 * @return int, the default store id
	 */
	private function _getDefaultStoreId()
	{
		$allStores = Mage::app()->getStores();
		foreach (array_keys($allStores) as $storeId) {
			return Mage::app()->getStore($storeId)->getId();
		}
		return Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID;
	}

	/**
	 * getting category attribute set id.
	 * @return int, the category attribute set id
	 */
	protected function _getCategoryAttributeSetId()
	{
		return (int) Mage::getSingleton('eav/config')
			->getAttribute(Mage_Catalog_Model_Category::ENTITY, 'attribute_set_id')
			->getEntityType()
			->getDefaultAttributeSetId();
	}

	/**
	 * load category by name
	 * @param string $categoryName, the category name to filter the category table
	 * @return Mage_Catalog_Model_Category
	 */
	protected function _loadCategoryByName($categoryName)
	{
		return Mage::getModel('catalog/category')
			->getCollection()
			->addAttributeToSelect('*')
			->addAttributeToFilter('name', array('eq' => $categoryName))
			->load()
			->getFirstItem();
	}

	/**
	 * get parent default category id
	 * @return int, default parent category id
	 */
	protected function _getDefaultParentCategoryId()
	{
		return Mage::getModel('catalog/category')->getCollection()
			->addAttributeToSelect('*')
			->addAttributeToFilter('parent_id', array('eq' => 0))
			->load()
			->getFirstItem()
			->getId();
	}

	/**
	 * get store root category id
	 * @return int, store root category id
	 */
	protected function _getStoreRootCategoryId()
	{
		return Mage::app()->getWebsite(true)->getDefaultStore()->getRootCategoryId();
	}

	/**
	 * add category to magento, check if already exist and return the category id
	 * @param string $categoryName, the category to either add or get category id from magento
	 * @param string $path, delimited string of the category depth path
	 * @return int, the category id
	 */
	protected function _addCategory($categoryName, $path)
	{
		$categoryId = 0;
		if (trim($categoryName) !== '') {
			// let's check if category already exists
			$this->_categoryObject = $this->_loadCategoryByName($categoryName);
			$categoryId = $this->_categoryObject->getId();
			if (!$categoryId) {
				// category doesn't currently exists let's add it.
				try {
					$this->_categoryObject->addData(array(
						'attribute_set_id' => $this->_attributeSetId,
						'store_id' => $this->_defaultStoreId,
						'name' => $categoryName,
						'path' => $path, // parent relationship..
						'description' => $categoryName,
						'is_active' => 1,
						'is_anchor' => 0, //for layered navigation
						'page_layout' => 'default',
						'url_key' => Mage::helper('catalog/product_url')->format($categoryName), // URL to access this category
						'image' => null,
						'thumbnail' => null,
					))->save();
					$categoryId = $this->_categoryObject->getId();
				} catch (Exception $e) {
                    $this->_log->logErr('[%s] Error adding categories: %s', array(__CLASS__, $e->getMessage()));
				}
			}
		}

		return $categoryId;
	}

	/**
	 * The 'main' of a Mage Shell Script
	 * @see usageHelp
	 */
	public function run()
	{
		if( !count($this->_args) ) {
			echo $this->usageHelp();
			return 0;
		}
		$errors = 0;
		if (isset($this->_args['categories'])) {
			$categories = explode('-', $this->_args['categories']);
			$path = sprintf('%s/%s', $this->_defaultParentCategoryId, $this->_storeRootCategoryId);
			foreach ($categories as $category) {
				if (!is_numeric($category)) {
					$path .= '/' . $this->_addCategory(ucwords($category), $path);
				}
			}
		}

		echo "\nScript Ended\n";
		return $errors;
	}

	/**
	 * Return some help text
	 *
	 * @return string
	 */
	public function usageHelp()
	{
		$scriptName = basename(__FILE__);
		$msg = <<<USAGE

Usage: php -f $scriptName -- [options]
  -categories     category set. Example: "Men-Watches"
  help       This help

Adding categories to Magento stores:

USAGE;
		return $msg . " Done!!!\n";
	}
}

$shell = new EbayEnterprise_Rom_Shell_Add_Categories();
exit($shell->run());