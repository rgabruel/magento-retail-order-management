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

class EbayEnterprise_Catalog_Helper_Pim
{
    const DEFAULT_OPERATION_TYPE = 'Change';
    const NEW_PRODUCT_OPERATION_TYPE = 'Add';
    const MAX_SKU_LENGTH         = 15;
    const STRING_LIMIT           = 4000;
    // Config XML path for gift wrapping availability flag
    const XML_PATH_ALLOW_GIFT_WRAPPING = 'sales/gift_options/wrapping_allow_items';
    /**
     * return a cdata node from a given string value.
     * @param  string                              $attrValue
     * @param  string                              $attribute
     * @param  Mage_Catalog_Model_Product          $product
     * @param  DOMDocument             $doc
     * @return DOMNode|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getValueAsDefault($attrValue, $attribute, Mage_Catalog_Model_Product $product, DOMDocument $doc)
    {
        if ($attrValue) {
            $frag = $doc->createDocumentFragment();
            $frag->appendChild($doc->createElement('Value'))
                ->appendChild($this->passString($attrValue, $attribute, $product, $doc));
            return $frag;
        }
        return null;
    }
    /**
     * call self::createStringNode passing it string truncate to on self::STRING_LIMIT and pass the given DOMDocument
     * which will either return DOMNode object or a null value.
     * @param  string                              $attrValue
     * @param  string                              $attribute
     * @param  Mage_Catalog_Model_Product          $product
     * @param  DOMDocument         $doc
     * @return DOMNode|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passString($attrValue, $attribute, Mage_Catalog_Model_Product $product, DOMDocument $doc)
    {
        return $this->createStringNode(substr($attrValue, 0, self::STRING_LIMIT), $doc);
    }
    /**
     * De-normalized a given sku by calling EbayEnterprise_Catalog_Helper_Data::denormalizeSku method and then calling
     * the self::createStringNode method given the de-normalize sku and the given DOMDocument object in which
     * will return a DOMNode object
     * @param  string                              $attrValue
     * @param  string                              $attribute
     * @param  Mage_Catalog_Model_Product          $product
     * @param  DOMDocument             $doc
     * @throws EbayEnterprise_Catalog_Model_Pim_Product_Validation_Exception
     * @return DOMNode|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passSKU($attrValue, $attribute, Mage_Catalog_Model_Product $product, DOMDocument $doc)
    {
        $catalogId = Mage::helper('eb2ccore')->getConfigModel($product->getStoreId())->catalogId;
        $sku       = Mage::helper('ebayenterprise_catalog')->denormalizeSku($attrValue, $catalogId);
        if (strlen($sku) > self::MAX_SKU_LENGTH) {
            throw new EbayEnterprise_Catalog_Model_Pim_Product_Validation_Exception(
                sprintf('%s SKU \'%s\' Exceeds max length.', __FUNCTION__, $sku)
            );
        }
        return $this->createStringNode($sku, $doc);
    }
    /**
     * Pass the string IF it has a value.
     * which will either return DOMNode object or a null value.
     * @param  string                              $attrValue
     * @param  string                              $attribute
     * @param  Mage_Catalog_Model_Product          $product
     * @param  DOMDocument         $doc
     * @return DOMNode|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passStringIf($attrValue, $attribute, Mage_Catalog_Model_Product $product, DOMDocument $doc)
    {
        if (!empty($attrValue)) {
            return $this->passString($attrValue, $attribute, $product, $doc);
        }
        return null;
    }
    /**
     * round the attrValue to two decimal point by calling the method Mage_Core_Model_Store::roundPrice given the attrValue
     * which will return a rounded attrValue, than pass this attrValue to the method self::createTextNode as first parameter
     * and the given DOMDocument object as second parameter which will return a DOMNode object
     * @param  string                              $attrValue
     * @param  string                              $attribute
     * @param  Mage_Catalog_Model_Product          $product
     * @param  DOMDocument         $doc
     * @return DOMNode|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passPrice($attrValue, $attribute, Mage_Catalog_Model_Product $product, DOMDocument $doc)
    {
        return $this->createTextNode(Mage::getModel('core/store')->roundPrice($attrValue), $doc);
    }
    /**
     * Call the Self::createDecimal method given the attrValue which will return a decimal value if the attriValue is numeric
     * otherwise will return null if it null pass it to self::createTextNode will also return node but if return an actual
     * decimal value a DOMNode object will be returned
     * @param  string                              $attrValue
     * @param  string                              $attribute
     * @param  Mage_Catalog_Model_Product          $product
     * @param  DOMDocument         $doc
     * @return DOMNode|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passDecimal($attrValue, $attribute, Mage_Catalog_Model_Product $product, DOMDocument $doc)
    {
        return $this->createTextNode($this->createDecimal($attrValue), $doc);
    }
    /**
     * the self::createDateTime method is called given the attrValue if it return a valid date time value then the method
     * self::createTextNode will return a DOMNode object when invoked with the attrValue and DOMDocument object, however
     * if self::createDateTime method return null than the self::createTextNode will return null
     * @param  string                              $attrValue
     * @param  string                              $attribute
     * @param  Mage_Catalog_Model_Product          $product
     * @param  DOMDocument         $doc
     * @return DOMNode|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passDate($attrValue, $attribute, Mage_Catalog_Model_Product $product, DOMDocument $doc)
    {
        return $this->createTextNode($this->createDateTime($attrValue), $doc);
    }
    /**
     * the method self::createInteger will be called given an attrValue it will return an integer value if the attrValue string
     * is numeric other null. when it return an integer value this value is then pass to the method self::createTextNode method
     * along with the given DOMDocument object in which will return a DOMNode object, but if a null is given to the method
     * self:createTextNode it will return null
     * @param  string                              $attrValue
     * @param  string                              $attribute
     * @param  Mage_Catalog_Model_Product          $product
     * @param  DOMDocument         $doc
     * @return DOMNode|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passInteger($attrValue, $attribute, Mage_Catalog_Model_Product $product, DOMDocument $doc)
    {
        return $this->createTextNode($this->createInteger($attrValue), $doc);
    }
    /**
     * The Yes/No selector control passes 1 or 0 to represent Yes or No respectively.
     * the method self::createBool will be invoked in this method given attrValue if the attribute value is the value
     * '1' the return value will be 'true' otherwise the return value will be 'false', this value will then passed to
     * the method self::createTextNode then return a DOMNode object given the attrValue and DOMDocument object
     * @param  string                              $attrValue
     * @param  string                              $attribute
     * @param  Mage_Catalog_Model_Product          $product
     * @param  DOMDocument         $doc
     * @return DOMNode|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passYesNoToBool($attrValue, $attribute, Mage_Catalog_Model_Product $product, DOMDocument $doc)
    {
        return $this->createTextNode($this->createBool($attrValue), $doc);
    }
    /**
     * return a DOMAttr object containing the client id value
     * @param  string                              $attrValue
     * @param  string                              $attribute
     * @param  Mage_Catalog_Model_Product          $product
     * @param  DOMDocument         $doc
     * @return DOMAttr
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passGsiClientId($attrValue, $attribute, Mage_Catalog_Model_Product $product, DOMDocument $doc)
    {
        $domAttribute = $this->_getDomAttr($doc, $attribute);
        $domAttribute->value = Mage::helper('ebayenterprise_catalog/feed')->getClientId();
        return $domAttribute;
    }
    /**
     * return DOMAttr object containing the operation type for the product.
     * Any products created after the last export run should use the new product
     * operation type ("Add"). All other products being exported should use
     * the default operation type ("Change").
     * @param  string $attrValue
     * @param  string $attribute
     * @param  Mage_Catalog_Model_Product $product
     * @param  DOMDocument $doc
     * @return DOMAttr
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passOperationType($attrValue, $attribute, Mage_Catalog_Model_Product $product, DOMDocument $doc)
    {
        $domAttribute = $this->_getDomAttr($doc, $attribute);
        $lastRunTime = Mage::helper('ebayenterprise_catalog')->getConfigModel()->pimExportFeedCutoffDate;
        $domAttribute->value = strtotime($product->getCreatedAt()) > strtotime($lastRunTime) ?
            static::NEW_PRODUCT_OPERATION_TYPE :
            static::DEFAULT_OPERATION_TYPE;
        return $domAttribute;
    }
    /**
     * return a DOMAttr object containing catalog id value
     * @param  string                              $attrValue
     * @param  string                              $attribute
     * @param  Mage_Catalog_Model_Product          $product
     * @param  DOMDocument         $doc
     * @return DOMAttr
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passCatalogId($attrValue, $attribute, Mage_Catalog_Model_Product $product, DOMDocument $doc)
    {
        $domAttribute = $this->_getDomAttr($doc, $attribute);
        $domAttribute->value = Mage::helper('ebayenterprise_catalog/feed')->getCatalogId();
        return $domAttribute;
    }
    /**
     * return a DOMAttr object containing store id value
     * @param  string                              $attrValue
     * @param  string                              $attribute
     * @param  Mage_Catalog_Model_Product          $product
     * @param  DOMDocument         $doc
     * @return DOMAttr
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passStoreId($attrValue, $attribute, Mage_Catalog_Model_Product $product, DOMDocument $doc)
    {
        $domAttribute = $this->_getDomAttr($doc, $attribute);
        // get the rom store id based on the product's store view.
        $domAttribute->value = Mage::helper('ebayenterprise_catalog/feed')->getStoreId($product->getStoreId());
        return $domAttribute;
    }
    /**
     * given a DOMDocument and attribute name normalize the attribute create a DONAttr
     * @param DOMDocument $doc
     * @param string $nodeAttribute
     * @return DOMAttr
     */
    protected function _getDomAttr(DOMDocument $doc, $nodeAttribute)
    {
        return $doc->createAttribute(implode('_', array_filter(explode('_', $nodeAttribute))));
    }

    /**
     * given a value if it is null return null otherwise a DOMNode
     *
     * @param string $value
     * @param DOMDocument $doc
     * @return DOMNode | null
     */
    public function createStringNode($value, DOMDocument $doc)
    {
        return is_null($value) ? null : $doc->createCDataSection($value);
    }

    /**
     * given a value if it is null return null otherwise a DOMNode
     * @param string $value
     * @param DOMDocument $doc
     * @return DOMNode | null
     */
    public function createTextNode($value, DOMDocument $doc)
    {
        return is_null($value) ? null : $doc->createTextNode($value);
    }
    /**
     * given a string representing date time if the string is not is not empty return and integer date time
     * @param string $value
     * @return string | null
     */
    public function createDateTime($value)
    {
        return !empty($value)? date('c', strtotime($value)) : null;
    }
    /**
     * given a string representing integer value if the string is a numeric value cast it as integer otherwise return null
     * @param string $value
     * @return int | null
     */
    public function createInteger($value)
    {
        return is_numeric($value)? (int) $value : null;
    }
    /**
     * given a string representing decimal value if the string is a numeric value cast it as float otherwise return null
     * @param string $value
     * @return int | null
     */
    public function createDecimal($value)
    {
        return is_numeric($value)? (float) $value : null;
    }
    /**
     * given a string if it is '1' return 'true' otherwise 'false'
     * @param string $value
     * @return string
     */
    public function createBool($value)
    {
        return ($value === '1')? 'true' : 'false';
    }
    /**
     * For a given product, look for a configurable product using that product.
     * If a product is used by multiple configurable products, only the first
     * configurable product will be returned.
     * @param  Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Model_Product|null Parent configurable product or null of no product is found
     */
    protected function _getParentConfigurableProduct(Mage_Catalog_Model_Product $product)
    {
        $parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')
            ->getParentIdsByChild($product->getId());
        return isset($parentIds[0]) ? Mage::getModel('catalog/product')->load($parentIds[0]) : null;
    }
    /**
     * Get the product to use as the source for style data. The selection of the
     * source product will follow these rules:
     * - Products used by a configurable product that exists will use the
     *   configurable product.
     * - Products used by a configurable product that does not exist, will have
     *   no source - return null. This scenario is almost guaranteed to never
     *   occur by the DB schema and the way the parent product lookup is
     *   implemented. As it is still technically possible, however, for the
     *   product to have not been loaded (customization on the load events or
     *   similar), failing to cover the scenario would cause a catastrophic
     *   failure of the export and there is minimal logic to cover the scenario,
     *   handling is included.
     * - All other products will get the data from itself.
     * @param  Mage_Catalog_Model_Product $product Product being exported
     * @return Mage_Catalog_Model_Product|null Product to use as source for style data, null of no such product exists
     */
    protected function _getStyleSourceProduct(Mage_Catalog_Model_Product $product)
    {
        // only simple products can be used by configurable products so only look
        // for the relationships if dealing with a simple product
        if ($product->getTypeId() === Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
            $parentProduct = $this->_getParentConfigurableProduct($product);
            // if a parent product was found, it should be the style source
            if ($parentProduct) {
                // if the product doesn't exist (no id), there should be no style source
                return $parentProduct->getId() ? $parentProduct : null;
            }
        }
        return $product;
    }
    /**
     * if $product is configurable return the result of passSKU
     * @param  string                     $attrValue
     * @param  string                     $attribute
     * @param  Mage_Catalog_Model_Product $product
     * @param  DOMDocument                $doc
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passStyleId($attrValue, $attribute, Mage_Catalog_Model_Product $product, DOMDocument $doc)
    {
        $sourceProduct = $this->_getStyleSourceProduct($product);
        return $sourceProduct ? $this->passSKU($sourceProduct->getSku(), 'sku', $sourceProduct, $doc) : null;
    }
    /**
     * if $product is a giftcard, return fragment with the child nodes
     * of the GiftCard Element
     * @param  string                     $attrValue
     * @param  string                     $attribute
     * @param  Mage_Catalog_Model_Product $product
     * @param  DOMDocument                $doc
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passGiftCard($attrValue, $attribute, Mage_Catalog_Model_Product $product, DOMDocument $doc)
    {
        if ($product->getTypeId() !== Enterprise_GiftCard_Model_Catalog_Product_Type_Giftcard::TYPE_GIFTCARD) {
            return null;
        }
        $cfg = Mage::helper('ebayenterprise_catalog')->getConfigModel();
        $allowMessage = $product->getUseConfigAllowMessage() ?
            $cfg->getConfigData(Enterprise_GiftCard_Model_Giftcard::XML_PATH_ALLOW_MESSAGE) :
            $product->getAllowMessage();
        $MessageMaxLength = $allowMessage ?
            (int) $cfg->getConfigData(Enterprise_GiftCard_Model_Giftcard::XML_PATH_MESSAGE_MAX_LENGTH) :
            0;
        $isDigital = $product->getGiftCardType() === Enterprise_GiftCard_Model_Giftcard::TYPE_VIRTUAL ? 'true' : 'false';
        $namespaceUri = $doc->documentElement->namespaceURI;
        $frag = $doc->createDocumentFragment();
        $frag->appendChild($doc->createElement('Digital', $isDigital, $namespaceUri));
        $frag->appendChild($doc->createElement('MessageMaxLength', $MessageMaxLength, $namespaceUri));
        $frag->appendChild($doc->createElement('CardFacingDisplayName', (string) $product->getName(), $namespaceUri));
        return $frag;
    }
    /**
     * return a fragment containing a product link element for each
     * linked product.
     * @param  string                     $attrValue
     * @param  string                     $attribute
     * @param  Mage_Catalog_Model_Product $product
     * @param  DOMDocument                $doc
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passProductLinks($attrValue, $attribute, Mage_Catalog_Model_Product $product, DOMDocument $doc)
    {
        $frag = $doc->createDocumentFragment();
        $products = $product->getRelatedProducts();
        $index = 0;
        foreach ($products as $rProduct) {
            $index++;
            $this->_addProductLink($frag, 'ES_Accessory', $index, $this->passSKU($rProduct->getSku(), '', $rProduct, $doc));
        }
        $products = $product->getUpSellProducts();
        $index = 0;
        foreach ($products as $rProduct) {
            $index++;
            $this->_addProductLink($frag, 'ES_UpSelling', $index, $this->passSKU($rProduct->getSku(), '', $rProduct, $doc));
        }
        $products = $product->getCrossSellProducts();
        $index = 0;
        foreach ($products as $rProduct) {
            $index++;
            $this->_addProductLink($frag, 'ES_CrossSelling', $index, $this->passSKU($rProduct->getSku(), '', $rProduct, $doc));
        }
        return $frag->hasChildNodes() ? $frag : null;
    }

    /**
     * build out a product link subtree
     *
     * @param DOMDocumentFragment $frag
     * @param string $type
     * @param int $position
     * @param DOMNode $value
     * @return DOMDocumentFragment
     */
    protected function _addProductLink(DOMDocumentFragment $frag, $type, $position, DOMNode $value)
    {
        $frag->appendChild($frag->ownerDocument->createElement('ProductLink'))
            ->addAttributes(array(
                'link_type' => $type,
                'operation_type' => 'Add',
                'position' => $position,
            ))
            ->createChild('LinkToUniqueID')
            ->appendChild($value);
        return $frag;
    }
    /**
     * return a fragment containing the nodes for the product's category links
     * or null if there are none.
     * @param  string                     $attrValue
     * @param  string                     $attribute
     * @param  Mage_Catalog_Model_Product $product
     * @param  DOMDocument                $doc
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passCategoryLinks($attrValue, $attribute, Mage_Catalog_Model_Product $product, DOMDocument $doc)
    {
        $frag = $doc->createDocumentFragment();
        $categories = $product->getCategoryCollection();
        $all = Mage::getResourceModel('catalog/category_collection')
            ->addAttributeToSelect('name');
        foreach ($categories as $category) {
            $pathArr = explode('/', $category->getPath());
            array_walk($pathArr, function (&$val) use ($all) {
                $part = $all->getItemById((int) $val);
                $val = $part ? $part->getName() : null;
            });
            $catString = implode('-', array_filter($pathArr));
            if ($catString) {
                $frag->appendChild($doc->createElement('CategoryLink'))
                ->addAttributes(array('import_mode' => 'Replace'))
                ->addChild('Name', $catString);
            }
        }
        return $frag->hasChildNodes() ? $frag : null;
    }
    /**
     * return Y/N when the value evaluates to true/false respectively.
     * @param  string                     $attrValue
     * @param  string                     $attribute
     * @param  Mage_Catalog_Model_Product $product
     * @param  DOMDocument                $doc
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passGiftWrap($attrValue, $attribute, Mage_Catalog_Model_Product $product, DOMDocument $doc)
    {
        // when the attribute value is not set, fall back to the configured gift wrap avail flag
        if (is_null($attrValue)) {
            $attrValue = Mage::getStoreConfigFlag(self::XML_PATH_ALLOW_GIFT_WRAPPING, $product->getStoreId());
        }
        return $this->createStringNode($attrValue ? 'Y' : 'N', $doc);
    }
    /**
     * Pass string as an ISO Country code otherwise null.
     * @param  string                              $attrValue
     * @param  string                              $attribute
     * @param  Mage_Catalog_Model_Product          $product
     * @param  DOMDocument         $doc
     * @return DOMNode|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passIsoCountryCode($attrValue, $attribute, Mage_Catalog_Model_Product $product, DOMDocument $doc)
    {
        return Mage::helper('ebayenterprise_catalog')->isValidIsoCountryCode($attrValue) ?
            $this->passString($attrValue, $attribute, $product, $doc): null;
    }
}
