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
 * Default Action Controller Class
 *
 * @category    Compori
 * @package     Compori_Minify
 * @author 		Martin Nemitz <martin.nemitz@compori.com>
 */
class Compori_Minify_IndexController extends Mage_Core_Controller_Front_Action {
    
    /**
     * Index action
     */
    public function indexAction() { 	
		// try to disable output compression, that will be done by minify
    	@ini_set('zlib.output_compression', '0'); 
		
		$response = $this->getResponse();
		
		// for now only grouped files are accepted
    	if (!isset($_GET['g'])) {
    		// bad request!
			$response->setHttpResponseCode(400); 
			return;
		}
		
		// load options
	    $options = Mage::helper('minify')
    			->init()
    			->getOptions();
    	// parse groups
    	if(isset($_GET['g'])) {
    		$groups = null;
    		
    		$names = explode(',', $_GET['g']);
    		foreach($names as $name) {
    			$data = Mage::helper('minify')->loadGroupData($name);
    			
    			// valid information?
    			if( is_array($data) ) {
    				if ($groups === null) {
    					$groups = array();
    				}
    				$groups[$name] = $data;
    			}    			
    		}
    		
    		// for now only grouped files are accepted
    		if ($groups === null) {
    			// bad request!
    			$response->setHttpResponseCode(400); 
    			return;
    		}
    		
    		// setup groups
    		$options['minApp']['groups'] = $groups;
    	}
    	
    	$options['quiet'] 		= true;    	
    	$options['groupsOnly'] 	= true;
    	
    	// clear headers, do not use Pragma that is set by PHP session management!
		$response->clearAllHeaders()->clearRawHeaders()->setRawHeader('Pragma:');
		
    	// generate content
    	$content = Minify::serve('MinApp', $options);
		
    	// set response header
    	$response->setHttpResponseCode($content['statusCode']);
    	$headers = $content['headers'];
    	foreach ($headers as $name => $val) {
        	$response->setRawHeader($name . ': ' . $val);
		}
		
		// ... and content
		$response->setBody($content['content']);
    }
}
