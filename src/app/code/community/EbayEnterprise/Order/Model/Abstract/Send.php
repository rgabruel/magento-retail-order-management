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

use eBayEnterprise\RetailOrderManagement\Payload\IPayload;
use eBayEnterprise\RetailOrderManagement\Api\IBidirectionalApi;
use eBayEnterprise\RetailOrderManagement\Api\Exception\NetworkError;
use eBayEnterprise\RetailOrderManagement\Api\Exception\UnsupportedHttpAction;
use eBayEnterprise\RetailOrderManagement\Api\Exception\UnsupportedOperation;

abstract class EbayEnterprise_Order_Model_Abstract_Send implements EbayEnterprise_Order_Model_Abstract_ISend
{
    const REQUEST_PAYLOAD_NAME = '';

    /** @var IPayload */
    protected $_request;
    /** @var EbayEnterprise_Order_Helper_Data */
    protected $_orderHelper;
    /** @var EbayEnterprise_Eb2cCore_Model_Config_Registry */
    protected $_orderCfg;
    /** @var EbayEnterprise_Eb2cCore_Helper_Data */
    protected $_coreHelper;
    /** @var EbayEnterprise_MageLog_Helper_Data */
    protected $_logger;
    /** @var EbayEnterprise_MageLog_Helper_Context */
    protected $_logContext;
    /** @var IBidirectionalApi */
    protected $_api;

    /**
     * @param array $initParams Must have these keys:
     *                          - 'api' => IBidirectionalApi
     *                          - 'request' => IPayload
     */
    public function __construct(array $initParams)
    {
        list($this->_api, $this->_request, $this->_orderHelper, $this->_orderCfg, $this->_coreHelper, $this->_logger, $this->_logContext) = $this->_checkTypes(
            $initParams['api'],
            $initParams['request'],
            $this->_nullCoalesce($initParams, 'order_helper', Mage::helper('ebayenterprise_order')),
            $this->_nullCoalesce($initParams, 'order_cfg', Mage::helper('ebayenterprise_order')->getConfigModel()),
            $this->_nullCoalesce($initParams, 'core_helper', Mage::helper('eb2ccore')),
            $this->_nullCoalesce($initParams, 'logger', Mage::helper('ebayenterprise_magelog')),
            $this->_nullCoalesce($initParams, 'log_context', Mage::helper('ebayenterprise_magelog/context'))
        );
    }

    /**
     * Type hinting for self::__construct $initParams
     *
     * @param  IBidirectionalApi
     * @param  IPayload
     * @param  EbayEnterprise_Order_Helper_Data
     * @param  EbayEnterprise_Eb2cCore_Model_Config_Registry
     * @param  EbayEnterprise_Eb2cCore_Helper_Data
     * @param  EbayEnterprise_MageLog_Helper_Data
     * @param  EbayEnterprise_MageLog_Helper_Context
     * @return array
     */
    protected function _checkTypes(
        IBidirectionalApi $api,
        IPayload $request,
        EbayEnterprise_Order_Helper_Data $orderHelper,
        EbayEnterprise_Eb2cCore_Model_Config_Registry $orderCfg,
        EbayEnterprise_Eb2cCore_Helper_Data $coreHelper,
        EbayEnterprise_MageLog_Helper_Data $logger,
        EbayEnterprise_MageLog_Helper_Context $logContext
    ) {
        return [$api, $request, $orderHelper, $orderCfg, $coreHelper, $logger, $logContext];
    }

    /**
     * Return the value at field in array if it exists. Otherwise, use the default value.
     * @param  array
     * @param  string $field Valid array key
     * @param  mixed
     * @return mixed
     */
    protected function _nullCoalesce(array $arr, $field, $default)
    {
        return isset($arr[$field]) ? $arr[$field] : $default;
    }

    /**
     * Return the concrete payload name.
     *
     * @return string
     */
    protected function _getPayloadName()
    {
        return static::REQUEST_PAYLOAD_NAME;
    }

    /**
     * @see EbayEnterprise_Order_Model_Abstract_ISend::send()
     */
    public function send()
    {
        return $this->_sendRequest();
    }

    /**
     * Sending the payload request and returning the response.
     *
     * @return IPayload | null
     */
    protected function _sendRequest()
    {
        $response = null;
        try {
            $response = $this->_api
                ->setRequestBody($this->_request)
                ->send()
                ->getResponseBody();
        } catch (Exception $e) {
            $this->_processException($e);
        }
        return $response;
    }

    /**
     * Determine the type of exception and logged it accordingly.
     *
     * @param  Exception
     * @return self
     */
    protected function _processException(Exception $e)
    {
        if ($e instanceof NetworkError) {
            $logMessage = "Caught a network error sending {$this->_getPayloadName()}. Please check the ROM API Configuration.";
            $this->_logger->warning($logMessage, $this->_getLogContext($e));
        } elseif ($e instanceof UnsupportedOperation || $e instanceof UnsupportedHttpAction) {
            $logMessage = "{$this->_getPayloadName()} request could not be sent. Please check your configuration.";
            $this->_logger->critical($logMessage, $this->_getLogContext($e));
        } else {
            $logMessage = "Encountered a fatal error attempting to send {$this->_getPayloadName()} request.";
            $this->_logger->warning($logMessage, $this->_getLogContext($e));
        }

        return $this;
    }

    /**
     * Get the log meta data.
     *
     * @param  Exception
     * @return array
     */
    protected function _getLogContext(Exception $e)
    {
        return $this->_logContext->getMetaData(__CLASS__, [], $e);
    }
}
