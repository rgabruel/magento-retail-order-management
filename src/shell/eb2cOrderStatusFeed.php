<?php
require_once('abstract.php');
/**
 * Drives the Eb2cOrder Status Feed processor from a command line
 */
class TrueAction_Eb2cOrder_Shell_Status_Feed extends Mage_Shell_Abstract
{
	/**
	 * Log message and (if debug mode and stdout is a tty) echo it (Code review will tell me if echo is OK to do.)
	 *
	 * @param string $message to log
	 * @param log level
	 */
	private function _log($message, $level)
	{
		$message = '[' . __CLASS__ . '] ' . basename(__FILE__) . ': ' . $message;
		Mage::log($message, $level);
		if( $level === Zend_Log::DEBUG && posix_isatty(STDOUT)) {
			echo $message . "\n";
		}
	}

	/**
	 * Return some help text
	 *
	 * @return string
	 */
	public function usageHelp()
	{
		$scriptName = basename(__FILE__);
		return <<<USAGE
Usage: php -f $scriptName -- [options]
	help	This help

USAGE;
	}

	/**
	 * Instantiate and call the _processFeeds Method
	 *
	 */
	public function run()
	{
		$this->_log( 'Script started', Zend_log::DEBUG );
		$filesProcessed = Mage::getModel('eb2corder/status_feed')->processFeeds();
		$this->_log( "Script finished, $filesProcessed file(s) processed.", Zend_log::DEBUG );
	}
}
$feedProcessor = new TrueAction_Eb2cOrder_Shell_Status_Feed();
$feedProcessor->run();