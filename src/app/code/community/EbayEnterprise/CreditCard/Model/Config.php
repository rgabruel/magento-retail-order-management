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

class EbayEnterprise_CreditCard_Model_Config
	extends EbayEnterprise_Eb2cCore_Model_Config_Abstract
{
	protected $_configPaths = array(
		'api_operation' => 'ebayenterprise_creditcard/api/operation',
		'api_service' => 'ebayenterprise_creditcard/api/service',
		'tender_type_ae' => 'ebayenterprise_creditcard/tender_types/ae',
		'tender_type_di' => 'ebayenterprise_creditcard/tender_types/di',
		'tender_type_mc' => 'ebayenterprise_creditcard/tender_types/mc',
		'tender_type_vi' => 'ebayenterprise_creditcard/tender_types/vi',
	);
}