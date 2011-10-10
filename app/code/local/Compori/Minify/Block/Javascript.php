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
 * Javascript inline and external block 
 *
 * @category    Compori
 * @package     Compori_Minify
 * @author 		Martin Nemitz <martin.nemitz@compori.com>
 */
class Compori_Minify_Block_Javascript extends Compori_Minify_Block_Abstract {

	/**
	 * (non-PHPdoc)
	 * @see Compori_Minify_Block_Abstract::_construct()
	 */
    protected function _construct() {
    	parent::_construct();
    	$this->setTemplate('minify/javascript.phtml');
    }

    /**
     * (non-PHPdoc)
     * @see Compori_Minify_Block_Abstract::_addItem()
     */
 	public function addItem($type, $name, $params=null, $if=null, $cond=null) {
    	if( $type != 'js' && $type != 'skin_js' ) {
    		return $this;
    	}
        return parent::_addItem($type, $name, $params, $if, $cond);
 	}  
 	
 	/**
 	 * (non-PHPdoc)
 	 * @see Compori_Minify_Block_Abstract::useMinify()
 	 */
 	public function useMinify() {
		return (bool)Mage::getStoreConfigFlag('minify/js/enable');
 	}
 	
    /**
     * (non-PHPdoc)
     * @see Compori_Minify_Block_Abstract::_prepareItems()
     */
    protected function _prepareItems() {
    	
    	// fetch path or url information
    	$designPackage = Mage::getDesign();
        $baseJsUrl = Mage::getBaseUrl('js');
        
        // inline or minify enabel fetch path information
        $fetchPath 	= $this->isInline() || $this->useMinify();
         
        $items = $this->_data['items'];
        foreach ($items as $key => $item) {
        	
            $type 	= $item['type'];
        	$name	= $item['name'];
        	
        	if($fetchPath) {
        		
        		// need path
        		if ($type == 'js') {
	        		$this->_data['items'][$key]['path'] = Mage::getBaseDir() . DS . 'js' . DS . $name;
        		}elseif ($type == 'skin_js') {
        			$this->_data['items'][$key]['path'] = $designPackage->getFilename($name, array('_type' => 'skin'));
        		}
        		
        	} else {
        		
	        	// url
        		if ($type == 'js') {
	        		$this->_data['items'][$key]['url'] = $baseJsUrl . $name;
        		}elseif ($type == 'skin_js') {
        			$this->_data['items'][$key]['url'] = $designPackage->getSkinUrl($name, array());
        		}
        	}
        }
        
        // return prepared item
        return parent::_prepareItems();
    }
 	

    /**
     * Remove Item
     *
     * @param string $type
     * @param string $name
     * @return Compori_Minify_Block_Javascript
     */
    public function removeItem($type, $name) {
    	if( $type != 'js' && $type != 'skin_js' ) {
    		return $this;
    	}
        parent::_removeItem($type, $name);        
        return $this;
    }
 	
    /**
     * Add JavaScript file
     *
     * @param string $name
     * @param string $params
     * @return Compori_Minify_Block_Javascript
     */
    public function addJs($name, $params = '') {
        return $this->_addItem('js', $name, $params);
    }

    /**
     * Add JavaScript file for Internet Explorer only 
     *
     * @param string $name
     * @param string $params
     * @return Compori_Minify_Block_Javascript
     */
    public function addJsIe($name, $params = '') {
        return $this->_addItem('js', $name, $params);
    }
}