<?php
abstract class EbayEnterprise_Eb2cCore_Model_Config_Abstract implements EbayEnterprise_Eb2cCore_Model_Config_Interface
{
	/**
	 * Associative array of configKey => configPath
	 * @var array
	 */
	protected $_configPaths;

	/**
	 * Determines if this config model knows about the given key
	 * @param string $configKey
	 * @return bool
	 */
	public function hasKey($configKey)
	{
		return isset($this->_configPaths[$configKey]);
	}

	/**
	 * Get the config path for the given known key
	 * @param string $configKey
	 * @return string
	 */
	public function getPathForKey($configKey)
	{
		return $this->_configPaths[$configKey];
	}
}
