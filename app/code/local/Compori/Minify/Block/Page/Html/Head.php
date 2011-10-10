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
 * Overloads the head block class to support transparent mode
 *
 * @category    Compori
 * @package     Compori_Minify
 * @author 		Martin Nemitz <martin.nemitz@compori.com>
 */
class Compori_Minify_Block_Page_Html_Head extends Mage_Page_Block_Html_Head {
	
	/**
	 * Use the transparent Mode?
	 * 
	 * @var bool
	 */
	protected $_isTransparent;
	
	/**
	 * Css Block object
	 * 
	 * @var Compori_Minify_Block_Stylesheet
	 */
	protected $_cssBlock;
	
	/**
	 * Javascript Block object
	 * 
	 * @var Compori_Minify_Block_Javascript
	 */
	protected $_jsBlock;
	
	/**
	 * (non-PHPdoc)
	 * @see Mage_Page_Block_Html_Head::_construct()
	 */
	protected function _construct() {
		parent::_construct();
		
		$this->_isTransparent = Mage::getStoreConfig('minify/general/transparent_mode');
		if($this->_isTransparent) {
			$this->_data['items'] = array();
		}
		$this->_cssBlock = null;
		$this->_jsBlock = null;
	}
	
	protected function _prepareLayout() {
		if($this->_isTransparent) {
			$this->_cssBlock = $this->getLayout()->createBlock('minify/stylesheet', 'head.minify.css.transparent');
			$this->_jsBlock = $this->getLayout()->createBlock('minify/javascript', 'head.minify.js.transparent');
			$this->append($this->_cssBlock);
			$this->append($this->_jsBlock);
		}
	}
	
	/**
	 * Returns the css block
	 * 
	 * @return Compori_Minify_Block_Stylesheet
	 */
	protected function _getCssBlock() {
		return $this->_cssBlock;
	} 
	
	/**
	 * Returns the javascript block
	 * 
	 * @return Compori_Minify_Block_Javascript
	 */
	protected function _getJsBlock() {
		return $this->_jsBlock;
	} 
	
	/**
	 * (non-PHPdoc)
	 * @see Mage_Page_Block_Html_Head::addCss()
	 */
    public function addCss($name, $params = "") {
    	if (!$this->_isTransparent) {
    		return parent::addCss($name, $params);
    	}
        $this->_getCssBlock()->addCss($name, $params);
        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see Mage_Page_Block_Html_Head::addJs()
     */
    public function addJs($name, $params = "") {
    	if (!$this->_isTransparent) {
    		return parent::addJs($name, $params);
    	}
        $this->_getJsBlock()->addJs($name, $params);
    	return $this;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Mage_Page_Block_Html_Head::addCssIe()
	 */
    public function addCssIe($name, $params = "") {
    	if (!$this->_isTransparent) {
    		return parent::addCssIe($name, $params);
    	}
        $this->_getCssBlock()->addCssIe($name, $params);
    	return $this;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Mage_Page_Block_Html_Head::addJsIe()
	 */
    public function addJsIe($name, $params = "") {
    	if (!$this->_isTransparent) {
    		return parent::addJsIe($name, $params);
    	}
        $this->_getJsBlock()->addJsIe($name, $params);
    	return $this;
	}

    /**
     * Add HEAD Item
     *
     * Allowed types:
     *  - js
     *  - js_css
     *  - skin_js
     *  - skin_css
     *  - rss
     *
     * @param string $type
     * @param string $name
     * @param string $params
     * @param string $if
     * @param string $cond
     * @return Mage_Page_Block_Html_Head
     */
    public function addItem($type, $name, $params=null, $if=null, $cond=null) {
    	if (!$this->_isTransparent) {
    		return parent::addItem($type, $name, $params, $if, $cond);
    	}
    	if ( ($type == 'js_css') || ($type == 'skin_css') ) {
    		$this->_getCssBlock()->addItem($type, $name, $params, $if, $cond);
    	}elseif(($type == 'js') || ($type == 'skin_js')) {
    		$this->_getJsBlock()->addItem($type, $name, $params, $if, $cond);
    	}else{
    		parent::addItem($type, $name, $params, $if, $cond);
    	}
    	return $this;
    }

    /**
     * Remove Item from HEAD entity
     *
     * @param string $type
     * @param string $name
     * @return Mage_Page_Block_Html_Head
     */
    public function removeItem($type, $name) {
    	if (!$this->_isTransparent) {
    		return parent::removeItem($type, $name);
    	}
        
    	if ( ($type == 'js_css') || ($type == 'skin_css') ) {
    		$this->_getCssBlock()->removeItem($type, $name);
    	}elseif(($type == 'js') || ($type == 'skin_js')) {
    		$this->_getJsBlock()->removeItem($type, $name);
    	}else{
    		parent::removeItem($type, $name);
    	}
    	return $this;
    }
}