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
class Compori_Minify_Helper_Data extends Mage_Core_Helper_Data {

	/**
	 * Library Path
	 * 
	 * @var string
	 */
	protected $_libraryPath;
	
	/**
	 * Construct
	 */
	public function __construct() {
		
		// include minify library
		$this->_libraryPath = Mage::getBaseDir('lib') . DS . 'minify-2.4.1b' . DS . 'min' . DS . 'lib'; 	
		set_include_path($this->_libraryPath . PS . get_include_path());
		if (!class_exists('Minify', false)) {
			require 'Minify.php';
		}
	}
	
	/**
	 * Creates directory structure needed by this extension
	 * 
	 * @return Compori_Minify_Helper_Data
	 */
	public function prepareDirectories() {
		
		// base minify 
		$minifyDirectory = Mage::getBaseDir('var') . DS . 'minify';
		if (!is_dir($minifyDirectory)) {
        	mkdir($minifyDirectory, 0777 , true);
		}
		
		// caching directory
		$cacheDirectory = $minifyDirectory . DS . 'cache';
		if (!is_dir($cacheDirectory)) {
        	mkdir($cacheDirectory, 0777 , true);
		}
		
		// groups directory
		$groupDirectory = $minifyDirectory . DS . 'groups';
		if (!is_dir($groupDirectory)) {
        	mkdir($groupDirectory, 0777 , true);
		}	

		return $this;
	}
	
	/**
	 * Removes all directories and files
	 * 
	 * @return Compori_Minify_Helper_Data
	 */
	public function cleanDirectories() {
		$minifyDirectory = Mage::getBaseDir('var') . DS . 'minify';
		if (is_dir($minifyDirectory)) {
			Varien_Io_File::rmdirRecursive($minifyDirectory);
		}
	}
	
	/**
	 * Load data from a file in groups directory
	 * 
	 * @param string $filename
	 * @return array $data
	 */
	public function loadGroupData($filename) {
		
		// ensure no directory information is given		
		$filename = basename($filename);
		
		// create full path
		$path = Mage::getBaseDir('var') . DS . 'minify'. DS . 'groups' . DS . $filename;
		if(!is_file($path)) {
			return null;
		}
		
		// read content
		return unserialize(file_get_contents($path));
	}
	
	/**
	 * Saves data to file in the groups directory
	 * 
	 * @param string $filename
	 * @param array $data
	 */
	public function saveGroupData($filename, $data) {
		
		// prepare directory structure
		$this->prepareDirectories();
		
		// ensure no directory information is given		
		$filename = basename($filename);
		
		// write group data 
		$path = Mage::getBaseDir('var') . DS . 'minify'. DS . 'groups' . DS . $filename;
		$result = file_put_contents($path, serialize($data));
		
		// success?
		return $result !== false;
	}
	
	/**
	 * Initialize Minify Facility
	 * 
	 * @return Compori_Minify_Helper_Data
	 */
	public function init() {
		
// ini_set('zlib.output_compression', '0'); ->> gehört in den Controller!!!
		// setup mtime adjuments
		Minify::$uploaderHoursBehind = Mage::getStoreConfig('minify/general/uploader_hours_behind');
		
		// setup caching
		$cacheDirectory = Mage::getBaseDir('var') . DS . 'minify' . DS . 'cache';
		if (!is_dir($cacheDirectory)) {
        	mkdir($cacheDirectory, 0777 , true);
		}
		$cacheFileLocking = (bool)Mage::getStoreConfig('minify/cache/file_locking'); 
		Minify::setCache( $cacheDirectory, $cacheFileLocking);

		// setup document root
		if (0 === stripos(PHP_OS, 'win')) {
    		Minify::setDocRoot(); // IIS may need help
		}

		// logger enabled?
		if ( Mage::getStoreConfig('minify/debug/log') ) {
    		require_once 'Minify/Logger.php';
			require_once 'FirePHP.php';
        	Minify_Logger::setLogger(FirePHP::getInstance(true));
		}
		
		return $this;
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function getOptions() {
		
		$options = array(
			'debug'				=> false,
			'maxAge'			=> 1800,
			'bubbleCssImports' 	=> false,
			'minApp'			=> array(
				'groupsOnly' 		=> false,
				'maxFiles' 			=> 10
			)
		);

		// setup debug mode
		if ( (isset($_GET['debug']) && Mage::getStoreConfig('minify/debug/allow')) 
			|| Mage::getStoreConfig('minify/debug/enable' )) {
			$options['debug'] 	= true;
		}

		// setup max age if revision number is supplied
		if (preg_match('/&ts=\\d/', $_SERVER['QUERY_STRING'])) {
    		$options['maxAge'] 	= 31536000;
		}
		return $options;
	}
	
	/**
	 * Retrieves the full url to a file group via minify
	 * 
	 * @param string $group Group name
	 * @return string Url 
	 */
	public function getUrl( $group ) {
		
		// determine the max modification timestamp
		$files = $this->loadGroupData( $group );
		$lastModificationTimestamp = 0;
		foreach($files as $file) {
			$lastModificationTimestamp = max($lastModificationTimestamp, filemtime($file));
		}		
		$lastModificationTimestamp = $lastModificationTimestamp - mktime(0, 0, 0, 1, 1, 2005);
		
		// create url
//		return Mage::getUrl('minify', array('_query' => 
//			array( 'g' => $group, 'ts' =>  $lastModificationTimestamp ) ) ); 

		// create url
		$model 		= Mage::getModel('core/url');
		$url = $model->getUrl('minify', array('_query' => array( 'g' => $group, 'ts' =>  $lastModificationTimestamp ) ) );
		if(Mage::app()->getStore()->isCurrentlySecure() && (strpos($url, 'http://')===0)){
			$url = 'https'.substr($url, 4);
		}		
		return $url;
	}
	
	/**
	 * Creates the whole content of a group 
	 * 
	 * @param string $group
	 * @return string
	 */
	public function getContent( $group, $options = array()) {
		$sources = $this->loadGroupData( $group );
		return Minify::combine($sources, $options );
	}
}