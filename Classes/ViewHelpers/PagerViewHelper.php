<?php

/***************************************************************
*  Copyright notice
*
*  (c) 2010 Nils Blattner <nb@cabag.ch>, cab AG
*  			
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * This class is a pager view helper for the Fluid templating engine.
 * 
 * @see Tx_ExtbasePager_Utility_Pager for utility
 *
 * @package TYPO3
 * @subpackage extbase_pager
 * @author Nils Blattner <nb@cabag.ch>
 */
class Tx_ExtbasePager_ViewHelpers_PagerViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {
	/**
	 * @var tslib_cObj
	 */
	protected $contentObject;

	/**
	 * @var array The global TypoScript.
	 */
	protected $globalTypoScriptSetup;

	/**
	 * @var array The pager TypoScript.
	 */
	protected $typoScriptSetup;

	/**
	 * @var array The order of the segments.
	 */
	protected $order;

	/**
	 * @var string This will hold a '.' for old school TS and will be empty for ExtBase TS.
	 */
	protected $typoScriptSeparator = '';
	
	/**
	 * @var string The calling extension name.
	 */
	protected $extensionName;
	
	/**
	 * @var string The calling plugin name.
	 */
	protected $pluginName;

	/**
	 * Constructor. Used to create an instance of tslib_cObj used by the render() method.
	 *
	 * @param tslib_cObj $contentObject injector for tslib_cObj (optional)
	 * @param array $typoScriptSetup global TypoScript setup (optional)
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function __construct($contentObject = NULL, array $typoScriptSetup = NULL) {
		$this->contentObject = $contentObject !== NULL ? $contentObject : t3lib_div::makeInstance('tslib_cObj');
		if ($typoScriptSetup !== NULL) {
			$this->globalTypoScriptSetup = &$typoScriptSetup;
		}
		if (TYPO3_MODE === 'BE') {
			// this is a hacky work around to enable this view helper for backend mode
			$GLOBALS['TSFE']->cObjectDepthCounter = 100;
		}
	}
	
	/**
	 * Renders the pager with the given typoscript setup.
	 *
	 * @param mixed $typoscript The TypoScript setup path of the TypoScript object to render or the typoscript object itself (as an array).
	 * @param int $page The current page. Note: This can be left out, but only if GPnamespace is set via typoscript! Or page 1 will be shown always!
	 * @param int $lastPage The last page. If this is not set, the active page will also be the last!
	 * @param string $localLang The path to the locallang file in the form of EXT:ext_key/.../locallang.xml. If empty the included locallang is taken. If empty string is given, the locallang of the active extension is taken.
	 * @param string $llPrefix Prefix to be used for the locallang keys. The keys are first/last/previous/next/current/more. 'pager.' by default.
	 * @return string The rendered pager
	 * @author Nils Blattner <nb@cabag.ch>
	 */
	 public function render($typoscript = array(), $page = -1, $lastPage = -1, $localLang = 'EXT:extbase_pager/Resources/Private/Language/locallang.xml', $llPrefix = 'pager.') {
		$request = $this->controllerContext->getRequest();
		$this->extensionName = $request->getControllerExtensionName();
		$this->pluginName = $request->getPluginName();
		
		// uri builder
		$this->uriBuilder = $this->controllerContext->getUriBuilder();
		
		$this->request = $request;
		
		$this->resolveTypoScript($typoscript)
			->injectDefaultValuesToSettings();
		
		$content = '';
		
		$pArray = $this->resolvePage($page, $lastPage);
		$page = $pArray['page'];
		$lastPage = $pArray['lastPage'];
		
		$sArray = $this->resolveStartEnd($page, $lastPage);
		$start = $sArray['start'];
		$end = $sArray['end'];
		
		$this->order = $this->getOrder();
		
		// translation prefix
		$this->locallangPrefix = empty($localLang) ? $llPrefix : 'LLL:' . $localLang . ':' . $llPrefix;
		
		// fields that will be accessible through typoscript
		$fields = array(
			'activePage' => $page,
			'lastPage' => $lastPage,
			'page' => 0,
			'title' => '',
		);
		
		if ($this->typoScriptSetup['includeLastPage']) {
			$this->linkArguments[$this->namespace][$this->typoScriptSetup['lastPageKey']] = $lastPage;
		}
		
		// link to first page
		$ts = $this->typoScriptSetup['first' . $this->typoScriptSeparator];
		if (empty($ts['doNotDisplay']) && ($page > 1 || !empty($ts['showIfFirstPage'])) && ($lastPage > 1 || !empty($ts['showIfOnePage']))) {
			$ts['class'] = empty($ts['class']) ? 'first' : $ts['class'];
			$this->order['first'] = $this->renderItem($ts, 1, $page, $lastPage, $fields, $this->translate($this->locallangPrefix . 'first'));
		}
		
		// link to previous page
		$ts = $this->typoScriptSetup['previous' . $this->typoScriptSeparator];
		if (empty($ts['doNotDisplay']) && ($page > 1)) {
			$ts['class'] = empty($ts['class']) ? 'previous' : $ts['class'];
			$this->order['previous'] = $this->renderItem($ts, $page - 1, $page, $lastPage, $fields, $this->translate($this->locallangPrefix . 'previous'));
		}
		
		// statics
		$itemClass = empty($this->typoScriptSetup['item' . $this->typoScriptSeparator]['class']) ? 'item' : $this->typoScriptSetup['item' . $this->typoScriptSeparator]['class'];
		$activeClass = empty($this->typoScriptSetup['active' . $this->typoScriptSeparator]['class']) ? 'active' : $this->typoScriptSetup['active' . $this->typoScriptSeparator]['class'];
		$separator = empty($this->typoScriptSetup['item' . $this->typoScriptSeparator]['separator']) ? '' : $this->typoScriptSetup['item' . $this->typoScriptSeparator]['separator'];
		
		if ($this->typoScriptSetup['includeLastPage']) {
			$this->linkArguments[$this->namespace][$this->typoScriptSetup['lastPageKey']] = $lastPage;
		}
		
		// go through each page number
		for ($p = $start; $p <= $end; $p++) {
			$linkIt = true;
			$class = $itemClass;
			if ($page == $p) {
				$key = 'active';
				$class .= ' ' . $activeClass;
			} else {
				$key = 'item';
			}
			
			$ts = $conf[$key . $this->typoScriptSeparator];
			$ts = empty($ts) ? array() : $ts;
			
			$ts['class'] = $class;
			
			$this->linkArguments[$this->namespace][$this->typoScriptSetup['pageKey']] = $p;
			
			if (empty($ts['doNotDisplay'])) {
				$value = $this->renderItem($ts, $p, $page, $lastPage, $fields, $p);
				
				// append/prepend the segment
				if ($this->typoScriptSetup['reverse']) {
					$this->order['items'] = $value . "\n" . ($p != $start ? $separator : '') . $this->order['items'];
				} else {
					$this->order['items'] .= ($p != $start ? $separator : '') . $value . "\n";
				}
			}
		}
		
		// text before/after the page links
		$ts = $this->typoScriptSetup['more' . $this->typoScriptSeparator];
		if (empty($ts['doNotDisplay'])) {
			$ts['class'] = empty($ts['class']) ? 'more' : $ts['class'];
			$ts['doNotLinkIt'] = true;
			$value = $this->renderItem($ts, $page, $page, $lastPage, $fields, $this->translate($this->locallangPrefix . 'more'));
			
			if ($start > 1) {
				if ($this->typoScriptSetup['reverse']) {
					$this->order['items'] .= $value . "\n";
				} else {
					$this->order['items'] = $value . "\n" . $order['items'];
				}
			}
			if ($end < $lastPage) {
				if ($this->typoScriptSetup['reverse']) {
					$this->order['items'] = $value . "\n" . $order['items'];
				} else {
					$this->order['items'] .= $value . "\n";
				}
			}
		}
		
		// link to next page
		$ts = $this->typoScriptSetup['next' . $this->typoScriptSeparator];
		if (empty($ts['doNotDisplay']) && ($page < $lastPage)) {
			$ts['class'] = empty($ts['class']) ? 'next' : $ts['class'];
			$this->order['next'] = $this->renderItem($ts, $page + 1, $page, $lastPage, $fields, $this->translate($this->locallangPrefix . 'next'));
		}
		
		// link to last page
		$ts = $this->typoScriptSetup['last' . $this->typoScriptSeparator];
		if (empty($ts['doNotDisplay']) && ($page < $lastPage || !empty($ts['showIfLastPage'])) && ($lastPage > 1 || !empty($ts['showIfOnePage']))) {
			$ts['class'] = empty($ts['class']) ? 'last' : $ts['class'];
			$this->order['last'] = $this->renderItem($ts, $lastPage, $page, $lastPage, $fields, $this->translate($this->locallangPrefix . 'last'));
		}
		
		// jumpto field
		$ts = $this->typoScriptSetup['jumpto' . $this->typoScriptSeparator];
		if (empty($ts['doNotDisplay'])) {
			$this->order['jumpto'] = $this->renderJumpTo($ts, $page, $lastPage, $fields);
		}
		
		// info about the current page/last page
		$ts = $this->typoScriptSetup['current' . $this->typoScriptSeparator];
		if (empty($ts['doNotDisplay'])) {
			$ts['class'] = empty($ts['class']) ? 'current' : $ts['class'];
			$ts['doNotLinkIt'] = true;
			$title = $this->translate($llPrefix . 'current');
			
			$order['current'] = $this->renderItem($ts, $page, $page, $lastPage, $fields, $title, $title . ': ' . $page . '/' . $lastPage);
		}
		
		// reassemble the content by order
		$content = "\n" . implode("\n", $this->order) . "\n";
		
		if (!$this->typoScriptSetup['noUlWrap']) {
			$content = '<ul' . $this->typoScriptSetup['class'] . '>' . $content . '</ul>';
		}
		
		if (!empty($this->typoScriptSetup['stdWrap' . $this->typoScriptSeparator])) {
			$content = $this->contentObject->stdWrap($content, $this->typoScriptSetup['stdWrap' . $this->typoScriptSeparator]);
		}
		if (!empty($this->typoScriptSetup['stdWrap'])) {
			$content = $this->contentObject->wrap($content, $this->typoScriptSetup['stdWrap']);
		}
		return $content;
	}
	
	/**
	 * Finds the TypoScript to use and stores it in the field typoScriptSetup.
	 * 
	 * @param mixed $typoscript Either 
	 * @throws Tx_Fluid_Core_ViewHelper_Exception
	 */
	public function resolveTypoScript($typoscript) {
		// get the typoscript configuration either from an object path or from the array given
		$conf = array();
		
		$this->typoScriptSeparator = '.';
		if (is_string($typoscript)) {
			if (!is_array($this->globalTypoScriptSetup) || !count($this->globalTypoScriptSetup)) {
				// this class is not part of the open API
				$configurationManager = Tx_Extbase_Dispatcher::getConfigurationManager();
				
				if (method_exists($configurationManager, 'loadTypoScriptSetup')) {
					// extbase pre 1.3.0
					$this->globalTypoScriptSetup = &$configurationManager->loadTypoScriptSetup();
				} else {
					// API change at extbase 1.3.0
					$this->globalTypoScriptSetup = &$configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
				}
			}
			// code taken from CObjectViewHelper
			$pathSegments = t3lib_div::trimExplode('.', $typoscript, true);
			$conf = &$this->globalTypoScriptSetup;
			foreach ($pathSegments as $segment) {
				if (!array_key_exists($segment . $this->typoScriptSeparator, $conf)) {
					throw new Tx_Fluid_Core_ViewHelper_Exception('TypoScript object path "' . htmlspecialchars($typoscript) . '" does not exist' , 1253191023);
				}
				$conf = &$conf[$segment . $this->typoScriptSeparator];
			}
		} else if (is_array($typoscript)) {
			// $typoscript is supposed to be the config directly
			$conf = &$typoscript;
			$this->typoScriptSeparator = '';
		} else {
			$type = get_class($typoscript);
			$type = $type ? $type : gettype($typoscript);
			throw new Tx_Fluid_Core_ViewHelper_Exception('Property typoscript must be of type array or string, ' . $type . ' given.' , 1253191023);
		}
		
		$this->typoScriptSetup = &$conf;
		
		return $this;
	}
	
	/**
	 * Make sure the default values are set in the typoscript.
	 * 
	 * @return void
	 */
	public function injectDefaultValuesToSettings() {
		$this->typoScriptSetup['style'] = !empty($this->typoScriptSetup['style']) ? strtolower($this->typoScriptSetup['style']) : false;
		$this->typoScriptSetup['maxBefore'] = !empty($this->typoScriptSetup['maxBefore']) ? intval($this->typoScriptSetup['maxBefore']) : 0;
		$this->typoScriptSetup['maxAfter'] = !empty($this->typoScriptSetup['maxAfter']) ? intval($this->typoScriptSetup['maxAfter']) : 0;
		$this->typoScriptSetup['max'] = !empty($this->typoScriptSetup['max']) ? intval($this->typoScriptSetup['max']) : 5;
		$this->typoScriptSetup['noUlWrap'] = !empty($this->typoScriptSetup['noUlWrap']) ? true : false;
		$this->typoScriptSetup['favourStart'] = empty($this->typoScriptSetup['favourStart']) ? false : true;
		$this->typoScriptSetup['includeLastPage'] = empty($this->typoScriptSetup['includeLastPage']) ? false : true;
		$this->typoScriptSetup['pageKey'] = !empty($this->typoScriptSetup['pageKey']) ? $this->typoScriptSetup['pageKey'] : 'page';
		$this->typoScriptSetup['lastPageKey'] = !empty($this->typoScriptSetup['lastPageKey']) ? $this->typoScriptSetup['lastPageKey'] : 'lastPage';
		$this->typoScriptSetup['reverse'] = empty($this->typoScriptSetup['reverse']) ? false : true;
		
		if (isset($this->typoScriptSetup['class'])) {
			$this->typoScriptSetup['class'] = !empty($this->typoScriptSetup['class']) ? ' class="' . $this->typoScriptSetup['class'] . '"' : '';
		} else {
			$this->typoScriptSetup['class'] = ' class="pager"';
		}
		
		if ($this->typoScriptSetup['maxBefore']) {
			if ($this->typoScriptSetup['maxAfter']) {
				$this->typoScriptSetup['style'] = 'static';
				$this->typoScriptSetup['max'] = $this->typoScriptSetup['maxAfter'] + $this->typoScriptSetup['maxBefore'] + 1;
			} else if ($this->typoScriptSetup['style'] == 'growing') {
				$this->typoScriptSetup['style'] = 'static';
			}
		}
		
		return $this;
	}
	
	/**
	 * Returns the order that the parts should be displayed in.
	 * 
	 * @return array Array with the keys representing (future) content of each section.
	 */
	public function getOrder() {
		// manage the default order
		$defaultOrder = array(
			'current' => '',
			'first' => '',
			'previous' => '',
			'items' => '',
			'next' => '',
			'last' => '',
			'jumpto' => ''
		);
		$order = array();
		
		if (!empty($this->typoScriptSetup['order'])) {
			// there is a order defined by typoscript
			$tOrder = t3lib_div::trimExplode(',', $this->typoScriptSetup['order'], true);
			// add each part by its order -> array will be in the given order, any keywords that are wrong will be omitted
			foreach ($tOrder as $part) {
				$order[$part] = '';
			}
			// overwrites the existing keys at the same position and adds any missing at the end
			foreach ($defaultOrder as $key => $val) {
				$order[$key] = '';
			}
		} else {
			// no order definition -> take default order
			$order = $defaultOrder;
		}
		
		if ($this->typoScriptSetup['reverse']) {
			// reverse the array but preserve the keys!
			$order = array_reverse($order, true);
		}
		
		return $order;
	}
	
	/**
	 * Resolves and fixes page and last page variables.
	 * 
	 * @param int $page The page number to display.
	 * @param int $lastPage The page number of the last page.
	 * @return array Array containing the page and lastPage (as keys).
	 */
	public function resolvePage($page, $lastPage) {
		if (!empty($this->typoScriptSetup['GPnamespace'])) {
			$this->namespace = $this->typoScriptSetup['GPnamespace'];
		} else {
			$this->namespace = 'tx_' . strtolower($this->extensionName) . '_' . strtolower($this->pluginName);
		}
		
		// get the proper arguments
		$this->linkArguments = Tx_ExtbasePager_Utility_Pager::getAllGPArguments();
		unset($this->linkArguments['L']);
		unset($this->linkArguments['cHash']);
		
		// make sure that the page and lastPage are valid or get them from the request if possible
		$page = intval($page);
		if ($page < 1) {
			$page = intval($this->linkArguments[$this->namespace][$this->typoScriptSetup['pageKey']]);
		}
		$page = $page < 1 ? 1 : $page;
		$lastPage = intval($lastPage);
		if ($lastPage < 1) {
			$lastPage = intval($this->linkArguments[$this->namespace][$this->typoScriptSetup['lastPageKey']]);
		}
		if (!$this->typoScriptSetup['includeLastPage']) {
			unset($this->linkArguments[$this->namespace][$this->typoScriptSetup['lastPageKey']]);
		}
		$lastPage = $lastPage > $page ? $lastPage : $page;
		
		return array('page' => $page, 'lastPage' => $lastPage);
	}
	
	/**
	 * Resolves and fixes start and end of the pages to be displayed.
	 * 
	 * @param int $page The page number to display.
	 * @param int $lastPage The page number of the last page.
	 * @return array Array containing the start and end (as keys).
	 */
	public function resolveStartEnd($page, $lastPage) {
		if ($this->typoScriptSetup['style'] == 'auto') {
			return $this->resolveStartEndForAuto($page, $lastPage);
		} else if ($this->typoScriptSetup['style'] == 'growing') {
			// TODO
			return $this->resolveStartEndForGrowing($page, $lastPage);
		} else {
			return $this->resolveStartEndForStatic($page, $lastPage);
		}
	}
	
	/**
	 * Returns the start and end variables for an variable pager.
	 * 
	 * @param int $page The start and end number to display.
	 * @param int $lastPage The page number of the last page.
	 * @return array Array containing the start and end (as keys).
	 */
	public function resolveStartEndForAuto($page, $lastPage) {
		$start = 1;
		$end = $lastPage;
		
		if ($this->typoScriptSetup['maxBefore']) {
			// $this->typoScriptSetup['maxAfter'] cannot be set
			if ($page > $this->typoScriptSetup['maxBefore']) {
				// active page is 'far' away from the start (page 1)
				$start = $page - $this->typoScriptSetup['maxBefore'];
			}
			$end = $end > ($start + $this->typoScriptSetup['max']) ? $start + $this->typoScriptSetup['max'] : $end;
		} else if ($this->typoScriptSetup['maxAfter']) {
			// $this->typoScriptSetup['maxBefore'] cannot be set
			if ($page < $end - $this->typoScriptSetup['maxAfter']) {
				// active page is 'far' away from the end
				$end = $page + $this->typoScriptSetup['maxAfter'];
			}
			$start = $start < ($end - $this->typoScriptSetup['max']) ? $end - $this->typoScriptSetup['max'] : $start;
		} else {
			// neither $this->typoScriptSetup['maxBefore'] nor $this->typoScriptSetup['maxAfter'] is set
			$side = floor(($this->typoScriptSetup['max'] - 1) / 2);
			if ($start < $page - $side) {
				// enough space towards first page
				$start = $end > ($page + $side) ? $page - $side : $end - $this->typoScriptSetup['max'];
				$start = $start < 1 ? 1 : $start;
			}
			$end = $end > ($start + $this->typoScriptSetup['max']) ? $start + $this->typoScriptSetup['max'] : $end;
		}
		
		return array('start' => $start, 'end' => $end);
	}
	
	/**
	 * @param int $page The start and end number to display for a static pager.
	 * 
	 * @param int $page The page number to display.
	 * @param int $lastPage The page number of the last page.
	 * @return array Array containing the start and end (as keys).
	 */
	public function resolveStartEndForStatic($page, $lastPage) {
		$start = 1;
		$end = $lastPage;
		
		if (!$this->typoScriptSetup['maxBefore']) {
			$this->typoScriptSetup['maxBefore'] = $this->typoScriptSetup['maxAfter'] ? $this->typoScriptSetup['max'] - 1 - $this->typoScriptSetup['maxAfter'] : floor(($this->typoScriptSetup['max'] - 1) / 2);
		}
		$this->typoScriptSetup['maxAfter'] = $this->typoScriptSetup['maxAfter'] ? $this->typoScriptSetup['maxAfter'] : $this->typoScriptSetup['max'] - 1 - $this->typoScriptSetup['maxBefore'];
		
		if ($this->typoScriptSetup['favourStart'] && ($this->typoScriptSetup['max'] % 2) == 0) {
			$this->typoScriptSetup['maxAfter']--;
			$this->typoScriptSetup['maxBefore']++;
		}
		
		$start = $page > $this->typoScriptSetup['maxBefore'] ? $page - $this->typoScriptSetup['maxBefore'] : $start;
		$end = $page + $this->typoScriptSetup['maxAfter'] > $end ? $end : $page + $this->typoScriptSetup['maxAfter'];
		
		return array('start' => $start, 'end' => $end);
	}
	
	/**
	 * Dongle method at the moment.
	 * TODO: implement google like growing pager.
	 * 
	 * @param int $page The page number to display.
	 * @param int $lastPage The page number of the last page.
	 * @return array Array containing the start and end (as keys).
	 */
	public function resolveStartEndForGrowing($page, $lastPage) {
		return array('start' => 0, 'end' => 0);
	}
	
	/**
	 * Renders a pager segment.
	 *
	 * @param array $ts The typoscript for the segment.
	 * @param int $linkPage The page number to link to.
	 * @param int $page The current page number.
	 * @param int $lastPage The last page number.
	 * @param array $fields The fields that will be available in typoscript stdWraps ({field:...}).
	 * @param string $title The title of the link and also the default text of the segment if that is not statet explicitly.
	 * @param string $defaultValue The default text of the segment (optional).
	 * @return string The rendered segment.
	 */
	public function renderItem($ts = array(), $linkPage, $page, $lastPage, array $fields = array(), $title, $defaultValue = NULL) {
		if (!is_array($ts)) {
			$ts = array();
		}
		$fields['page'] = $linkPage;
		$fields['title'] = $title;
		$class = ' class="' . $ts['class'] . '"';
		
		$value = $this->confStdWrap($ts, 'value', '', NULL, 'page', $fields, ($defaultValue === NULL ? $title : $defaultValue));
		
		$this->linkArguments[$this->namespace][$this->typoScriptSetup['pageKey']] = $fields['page'];
		if ($linkPage !== $page || empty($ts['doNotLinkIt'])) {
			// generate the uri to the page
			$uri = $this->uriBuilder
				->reset()
				->setArguments($this->linkArguments)
				->build();
			
			$value = '<a href="' . $uri . '" title="' . $title . '">' . $value . '</a>';
		}
		
		if (empty($ts['noLiWrap'])) {
			$value = '<li' . $class . '>' . $value . '</li>';
		}
		if (!empty($ts['outerWrap' . $this->typoScriptSeparator])) {
			$value = $this->confStdWrap($ts, 'outerWrap', $value, NULL, 'page', $fields, $value);
		}
		
		return $value;
	}
	
	/**
	 * Renders a jump to form.
	 * 
	 * @param array $ts The typoscript for the jumpTo segment.
	 * @param int $page The current page number.
	 * @param int $lastPage The last page number.
	 * @param array $fields The fields that will be available in typoscript stdWraps ({field:...}).
	 * @return string The rendered jump to form.
	 */
	public function renderJumpTo($ts = array(), $page, $lastPage, array $fields = array()) {
		if (!is_array($ts)) {
			$ts = array();
		}
		$fields['page'] = $page;
		$fields['title'] = $fields['page'];
		$class = empty($ts['class']) ? 'jumpto' : $ts['class'];
		// add pagerJumptoJS class so the javascript hooks onto the input field
		$class = ' class="' . $class . ' pagerJumptoJS"';
		$title = $this->translate($this->locallangPrefix . 'jumptoTitle');
		
		$value = $this->confStdWrap($ts, 'value', '', NULL, 'page', $fields, $fields['page']);
		
		// generate the uri to itself
		$uri = $this->uriBuilder
			->reset()
			->build();
		
		$value = '<input type="text" name="' . $this->namespace . '[' . $this->typoScriptSetup['pageKey'] . ']" value="' . $value . '" onkeyup="pagerJSkeyup(event, this, 1, ' . $lastPage . ')" title="' . $title . '" />' . "\n";
		
		if ($this->typoScriptSetup['includeLastPage']) {
			$value .= '<input type="hidden" name="' . $this->namespace . '[' . $this->typoScriptSetup['lastPageKey'] . ']" value="' . $lastPage . '" />';
		}
		
		// page/lastpage should not be in arguments array for the calculations that follow
		unset($this->linkArguments[$this->namespace][$this->typoScriptSetup['pageKey']]);
		unset($this->linkArguments[$this->namespace][$this->typoScriptSetup['lastPageKey']]);
		
		$argString = http_build_query($this->linkArguments, NULL, '&');
		
		// urldecode as it will be encoded by the browser
		$argString = urldecode($argString);
		
		$flatArguments = explode('&', $argString);
		
		foreach ($flatArguments as $argument) {
			list($n, $v) = explode('=', $argument);
			if (!empty($n)) {
				$value .= '<input type="hidden" name="' . $n . '" value="' . $v . '" />';
			}
		}
		
		$value = '<fieldset class="defaultForm">' . "\n" . $value . "\n" . '</fieldset>';
		
		// javascript include
		$value = '<script type="text/javascript" src="typo3conf/ext/extbase_pager/Resources/Public/JavaScript/pager.js"></script>' . "\n" . $value;
		
		// wrap in form tags
		$value = '<form method="GET" action="' . $uri . '">' . "\n" . $value . "\n" . '</form>';
		
		if (empty($ts['noLiWrap'])) {
			$value = '<li' . $class . '>' . $value . '</li>';
		}
		
		if (!empty($ts['outerWrap' . $this->typoScriptSeparator])) {
			$value = $this->confStdWrap($ts, 'outerWrap', $value, NULL, 'page', $fields, $value);
		}
		
		return $value;
	}
	
	/**
	 * Renders a configuration option.
	 *
	 * @param array $conf The typoscript array.
	 * @param string $key The key of the typoscript option (flat).
	 * @param string $content The content to wrap.
	 * @param string $currentValue The value to set as current for typoscript.
	 * @param string $currentValueKey The key of the value in $data to set as current for typoscript.
	 * @param array $data The fields to be used in typoscript.
	 * @param string $default The fallback value.
	 * @return string The configuration value.
	 */
	public function confStdWrap($conf, $key, $content = '', $currentValue = NULL, $currentValueKey = NULL, $data = array(), $default = '') {
		if (!empty($conf[$key . $this->typoScriptSeparator])) {
			$this->contentObject->start($data);
			if ($currentValue !== NULL) {
				$this->contentObject->setCurrentVal($currentValue);
			} elseif ($currentValueKey !== NULL && isset($data[$currentValueKey])) {
				$this->contentObject->setCurrentVal($data[$currentValueKey]);
			}
			$content = !empty($content) ? $content : (!empty($conf[$key]) ? $conf[$key] : '');
			return $this->contentObject->stdWrap($content, $conf[$key . $this->typoScriptSeparator]);
		} else if (!empty($conf[$key])) {
			return $conf[$key];
		} else {
			return $default;
		}
	}
	
	/**
	 * Translate key from locallang.xml.
	 *
	 * @param string $key Key to translate
	 * @param array $arguments Array of arguments to be used with vsprintf on the translated item.
	 * @return string Translation output.
	 */
	public function translate($key, $arguments = null) {
		return Tx_Extbase_Utility_Localization::translate($key, $this->extensionName, $arguments);
	}
}

?>