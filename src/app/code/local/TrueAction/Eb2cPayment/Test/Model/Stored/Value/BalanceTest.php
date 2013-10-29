<?php
class TrueAction_Eb2cPayment_Test_Model_Stored_Value_BalanceTest extends EcomDev_PHPUnit_Test_Case
{
	protected $_balance;

	/**
	 * setUp method
	 */
	public function setUp()
	{
		parent::setUp();
		$this->_balance = Mage::getModel('eb2cpayment/stored_value_balance');
	}

	public function providerGetBalance()
	{
		return array(
			array('4111111111111111', '1234')
		);
	}

	/**
	 * testing getBalance method
	 *
	 * @test
	 * @dataProvider providerGetBalance
	 * @loadFixture loadConfig.yaml
	 */
	public function testGetBalance($pan, $pin)
	{
		$this->assertNotNull(
			$this->_balance->getBalance($pan, $pin)
		);
	}

	/**
	 * testing when getBalance API call throw an exception
	 *
	 * @test
	 * @dataProvider providerGetBalance
	 * @loadFixture loadConfig.yaml
	 */
	public function testGetBalanceWithException($pan, $pin)
	{
		$apiModelMock = $this->getModelMockBuilder('eb2ccore/api')
			->setMethods(array('setUri', 'request'))
			->getMock();

		$apiModelMock->expects($this->any())
			->method('setUri')
			->will($this->returnSelf());
		$apiModelMock->expects($this->any())
			->method('request')
			->will($this->throwException(new Zend_Http_Client_Exception));

		$this->replaceByMock('model', 'eb2ccore/api', $apiModelMock);

		$this->assertSame(
			'',
			trim($this->_balance->getBalance($pan, $pin))
		);
	}

	public function providerParseResponse()
	{
		return array(
			array(file_get_contents(__DIR__ . '/BalanceTest/fixtures/StoredValueBalanceReply.xml', true))
		);
	}

	/**
	 * testing parseResponse method
	 *
	 * @test
	 * @dataProvider providerParseResponse
	 * @loadFixture loadConfig.yaml
	 */
	public function testParseResponse($storeValueBalanceReply)
	{
		$this->assertSame(
			array('paymentAccountUniqueId' => '4111111ak4idq1111', 'responseCode' => 'Success', 'balanceAmount' => 50.00),
			$this->_balance->parseResponse($storeValueBalanceReply)
		);
	}
}
