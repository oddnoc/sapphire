<?php

/**
 * Used to locate configuration for a particular named service. 
 * 
 * If it isn't found, return null.
 *
 * @package framework
 * @subpackage injector
 */
class ServiceConfigurationLocator implements Injector_ConfigLocator {
	
	public function locateConfigFor($name) { /* NOOP */ }

	public function reset() { /* NOOP */ }
}