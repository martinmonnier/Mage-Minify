<?php
/** 
 * Compori Minify Extension based upon Minify Project from http://code.google.com/p/minify/
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category    Compori
 * @package     Compori_Minify
 * @author 		Martin Nemitz <martin.nemitz@compori.com>
 * @copyright 	Martin Nemitz 
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * General Helper Class
 *
 * @category    Compori
 * @package     Compori_Minify
 * @author 		Martin Nemitz <martin.nemitz@compori.com>
 */
final class Compori_Minify_Model_Cache_Observer {
	
	/**
     * Cleans cache
     * 
     * @param Varien_Event_Observer $observer
     * @return Compori_Minify_Model_Cache_Observer
     */
	public function onCleanAfter ($observer) {
		Mage::helper('minify')->cleanDirectories();
		return $this;
	}	
}