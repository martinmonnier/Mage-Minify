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
 * Abstract base block for javascript, css external and internal 
 *
 * @category    Compori
 * @package     Compori_Minify
 * @author 		Martin Nemitz <martin.nemitz@compori.com>
 */
abstract class Compori_Minify_Block_Abstract extends Mage_Core_Block_Template {

	/**
	 * Inline type
	 *
	 * @var bool
	 */
    protected $_inline;
	
    /**
     * (non-PHPdoc)
     * @see Mage_Core_Block_Template::_construct()
     */
	protected function _construct() {
		parent::_construct();
		$this->_inline = false;
		$this->_data['items'] = array();
	}	
	
	/**
	 * Returns whether Minify should be used or not.
	 * 
	 * @return bool
	 */
	abstract public function useMinify();
	
	/**
	 * Returns whether output should be placed in html or external
	 * 
	 * @return bool 
	 */
	public function isInline() {
		return $this->_inline;
	}
	
	/**
	 * Sets the output as inline
	 * 
	 * @param bool $inline
	 * @return Compori_Minify_Block_Abstract
	 */
	public function setInline( $inline = true) {
		$this->_inline = $inline;
		return $this;
	}
	
    /**
     * Add Item
     * 
     * @param string $type 
     * @param string $name
     * @param string $params
     * @param string $if
     * @param string $cond
     * 
     * @return Compori_Minify_Block_Abstract
     */
    protected function _addItem($type, $name, $params=null, $if=null, $cond=null) {

        // add item
        $this->_data['items'][$type.'/'.$name] = array(
            'type'   => $type,
            'name'   => $name,
            'params' => $params,
            'if'     => $if,
            'cond'   => $cond,
		);
        return $this;
    }

    /**
     * Remove Item
     *
     * @param string $type
     * @param string $name
     * @return Compori_Minify_Block_Abstract
     */
    protected function _removeItem($type, $name) {
        unset($this->_data['items'][$type.'/'.$name]);
        return $this;
    }
    
    /**
     * Sorting items 
     * 
     * @return array sorted items by if and type
     */
    protected function _prepareItems() {
        
    	// separate items by types
        $lines  = array();
        foreach ($this->_data['items'] as $item) {
            if (!is_null($item['cond']) && !$this->getData($item['cond']) || !isset($item['name'])) {
                continue;
            }
            $if     = !empty($item['if']) ? $item['if'] : '';
            $params = !empty($item['params']) ? $item['params'] : '';
            $lines[$if][$item['type']][$params][$item['name']] = $item['name'];
        }
        
        return $lines;
    }
    
    /**
     * Creates Array with output data
     * 
     * @return array 
     */
    public function getElements() {
    	$lines 		= $this->_prepareItems();
    	$elements 	= array();
    	foreach ($lines as $if => $typeItems) {
    		
    		$element = array();
    		
    		// remove empty items
    		if (empty($typeItems)) {
                continue;
            }
            
            // set if
            if (!empty($if)) {
            	$element['if'] = $if;
            }
            
            // get types items
            $resultItems = array();
            $minifyItems = array();
            
            foreach($typeItems as $type => $paramItems) {
            	
            	foreach($paramItems as $params => $items) {
            		
            		// create additional param
            		$params = trim($params);
            		$params = $params ? ' ' . $params : '';
            		
            		
            		foreach($items as $name) {
            			
            			$fullName = $type.'/'.$name;
            			
            			// prepare item
	            		$resultItem = array( 'params' =>  $params );
	            		
	            		if (!$this->useMinify() && $this->isInline()) {
	            			
	            			// not minify and internal -> simply load content 
	            			$content = file_get_contents($this->_data['items'][$fullName]['path']);
	            			$resultItem['content'] = $content;
	            			$resultItems[] = $resultItem;	
	            			
	            		} elseif (!$this->useMinify() && !$this->isInline()) {
	            			
	            			// not minify and external -> simply add url 
	            			$resultItem['url'] = $this->_data['items'][$fullName]['url'];
	            			$resultItems[] = $resultItem;	
	            		} elseif ($this->useMinify() ) {
	            			
	            			// minify -> store path with param data
	            			if(!isset($minifyItems[$params])) {
	            				$minifyItems[$params] = array();
	            			} 
	            			$minifyItems[$params][] = $this->_data['items'][$fullName]['path'];
	            		} 
            		}
            	}
            }
            
            if ( $this->useMinify() ) {
            	
            	/* @var $helper Compori_Minify_Helper_Data */
            	$helper = Mage::helper('minify');
            	
            	foreach($minifyItems as $params => $files ) {
            		$group = $this->_createHash($files);
					$helper->saveGroupData($group, $files);					
					$resultItem['params'] = $params;
					
					// serve inline? or external
					if ($this->isInline()) {
						$resultItem['content'] = Mage::helper('minify')->getContent($group);
					}else{
						$resultItem['url'] = Mage::helper('minify')->getUrl($group);
					}
					$resultItems[] = $resultItem;
            	}
            }
            
            $element['items'] = $resultItems;            
            $elements[] = $element;
    	}

    	return $elements;
    }

    /**
     * Create a Hash value for a set of values
     * 
     * @param array $values flat array of strings
     * @return string hash value
     */
    protected function _createHash($values) {
    	return sha1(implode(',', $values)); 
    }

    /**
     * Get HEAD HTML with CSS/JS/RSS definitions
     * (actually it also renders other elements, TODO: fix it up or rename this method)
     *
     * @return string
     */
    public function getCssJsHtml() {

    	// get items sorted by type and if condi
		$lines = $this->_getSortedItems();
		
        // prepare HTML
        $shouldMergeJs = Mage::getStoreConfigFlag('dev/js/merge_files');
        $shouldMergeCss = Mage::getStoreConfigFlag('dev/css/merge_css_files');
        
        $html   = '';
        foreach ($lines as $if => $items) {
        	
            if (empty($items)) {
                continue;
            }
            
            if (!empty($if)) {
                $html .= '<!--[if '.$if.']>'."\n";
            }

            // static and skin css
            $html .= $this->_prepareStaticAndSkinElements(
            	'<link rel="stylesheet" type="text/css" href="%s"%s />' . "\n",
                empty($items['js_css']) ? array() : $items['js_css'],
                empty($items['skin_css']) ? array() : $items['skin_css'],
                $shouldMergeCss ? array(Mage::getDesign(), 'getMergedCssUrl') : null
            );

            // static and skin javascripts
            $html .= $this->_prepareStaticAndSkinElements(
            	'<script type="text/javascript" src="%s"%s></script>' . "\n",
                empty($items['js']) ? array() : $items['js'],
                empty($items['skin_js']) ? array() : $items['skin_js'],
                $shouldMergeJs ? array(Mage::getDesign(), 'getMergedJsUrl') : null
            );

                   if (!empty($if)) {
                $html .= '<![endif]-->'."\n";
            }
        }
        return $html;
    }

    /**
     * Merge static and skin files of the same format into 1 set of HEAD directives or even into 1 directive
     *
     * Will attempt to merge into 1 directive, if merging callback is provided. In this case it will generate
     * filenames, rather than render urls.
     * The merger callback is responsible for checking whether files exist, merging them and giving result URL
     *
     * @param string $format - HTML element format for sprintf('<element src="%s"%s />', $src, $params)
     * @param array $staticItems - array of relative names of static items to be grabbed from js/ folder
     * @param array $skinItems - array of relative names of skin items to be found in skins according to design config
     * @param callback $mergeCallback
     * @return string
     */
    protected function &_prepareStaticAndSkinElements($format, array $staticItems, array $skinItems, $mergeCallback = null)
    {
        $designPackage = Mage::getDesign();
        $baseJsUrl = Mage::getBaseUrl('js');
        $items = array();
        if ($mergeCallback && !is_callable($mergeCallback)) {
            $mergeCallback = null;
        }

        // get static files from the js folder, no need in lookups
        foreach ($staticItems as $params => $rows) {
            foreach ($rows as $name) {
                $items[$params][] = $mergeCallback ? Mage::getBaseDir() . DS . 'js' . DS . $name : $baseJsUrl . $name;
            }
        }

        // lookup each file basing on current theme configuration
        foreach ($skinItems as $params => $rows) {
            foreach ($rows as $name) {
                $items[$params][] = $mergeCallback ? $designPackage->getFilename($name, array('_type' => 'skin'))
                    : $designPackage->getSkinUrl($name, array());
            }
        }

        $html = '';
        foreach ($items as $params => $rows) {
            // attempt to merge
            $mergedUrl = false;
            if ($mergeCallback) {
                $mergedUrl = call_user_func($mergeCallback, $rows);
            }
            // render elements
            $params = trim($params);
            $params = $params ? ' ' . $params : '';
            if ($mergedUrl) {
                $html .= sprintf($format, $mergedUrl, $params);
            } else {
                foreach ($rows as $src) {
                    $html .= sprintf($format, $src, $params);
                }
            }
        }
        return $html;
    }
}
