<?php
/**
 * Debug_Bar class file
 *
 * @package Indabug
 */

namespace Wpseed\Indabug;

use DebugBar\DebugBar;
use DebugBar\DataCollector\PhpInfoCollector;
use DebugBar\DataCollector\MessagesCollector;
use DebugBar\DataCollector\MemoryCollector;
use DebugBar\DataCollector\ExceptionsCollector;

/**
 * Class Debug_Bar
 *
 * @package Wpseed\Indabug
 */
class Debug_Bar extends DebugBar {

	/**
	 * Debug_Bar constructor.
	 *
	 * @throws \DebugBar\DebugBarException DebugBar Exception.
	 */
	public function __construct() {
		$this->addCollector( new PhpInfoCollector() );
		$this->addCollector( new MessagesCollector() );
		$this->addCollector( new MemoryCollector() );
		$this->addCollector( new ExceptionsCollector() );
	}
}
