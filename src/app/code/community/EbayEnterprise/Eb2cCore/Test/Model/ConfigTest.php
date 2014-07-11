<?php
/**
 * Test the abstract config model which does the majority of work implementing
 * the config model interface required by the EbayEnterprise_Eb2cCore_Helper_Config
 */
class EbayEnterprise_Eb2cCore_Test_Model_ConfigTest extends EcomDev_PHPUnit_Test_Case
{
	/**
	 * A config model knows about a key.
	 */
	public function testConfigModelHasKey()
	{
		$configModel = new ConfigStub();
		$this->assertTrue($configModel->hasKey('catalog_id'));
		$this->assertFalse($configModel->hasKey('foo_bar_baz'));
	}

	/**
	 * A config model can get the correct path for a known key.
	 */
	public function testConfigModelGetPath()
	{
		$configModel = new ConfigStub();
		$this->assertSame($configModel->getPathForKey('catalog_id'), 'eb2c/core/catalog_id');
	}
}

/**
 * Simple implementation of the config abstract model.
 * Used to test the concrete implementations in the abstract class.
 *
 * @codeCoverageIgnore
 */
class ConfigStub extends EbayEnterprise_Eb2cCore_Model_Config_Abstract
{
	protected $_configPaths = array('catalog_id' => 'eb2c/core/catalog_id');
}
