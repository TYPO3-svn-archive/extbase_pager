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
 * @see Tx_ExtbasePager_Utility_Pager for utility
 *
 * @package TYPO3
 * @subpackage extbase_pager
 */
class Tx_ExtbasePager_ViewHelpers_PagerViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {
	/**
	 * @var tslib_cObj
	 */
	protected $contentObject;

	/**
	 * @var array
	 */
	protected $typoScriptSetup;
	
	/**
	 * @var string
	 */
	protected $extensionName;
	
	/**
	 * @var string
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
			$this->typoScriptSetup = &$typoScriptSetup;
		} else {
			$configurationManager = Tx_Extbase_Dispatcher::getConfigurationManager();
			$this->typoScriptSetup = &$configurationManager->loadTypoScriptSetup();
		}
		if (TYPO3_MODE === 'BE') {
				// this is a hacky work around to enable this view helper for backend mode
			$GLOBALS['TSFE']->cObjectDepthCounter = 100;
		}
	}
	
	/*
// in each subitem, value + outerWrap have stdWrap properties
// current is set to the page number the link will point to
// following fields are given:
//	activePage => page that is shown at the moment
//	title => title of the current item (can be a number for items or localized text for last/next etc)
//	lastPage => last page that could be shown
//	page => page number the link will point to
plugin.tx_sdfdsifj.settings.pager {
	// basic configuration
	
	// can be static -> same amount (max) on each side of the current page (default), auto -> tries to always show the same amount of pager links (ignores maxBefore/maxAfter if only one is set), growing -> tries to give as many links to previous pages as possible
	style = auto
	// if set with maxAfter it will override style and max, overrides growing style
	maxBefore = 3
	// if set with maxBefore it will override style and max
	maxAfter = 5
	// total maximum of pages shown, default is 5. If it is an even number, pages towards the end are favoured. 5 is default.
	max = 5
	// if the max is even the pages towards the start are favoured
	favourStart = 1
	// does not wrap the pager in ul tags
	noUlWrap = 1
	// the class to add to the ul, "pager" by default
	class = someClassForUl
	// stdwrap around the pager, simple wrap if only a string, full stdwrap if its an array
	stdWrap = <div id="someId">|</div>
	stdWrap {
		wrap3 = <div id="someId">|</div>
	}
	// namespace for the page/lastPage arguments default is the namespace of the extension running the pager
	GPnamespace = tx_extkey_pi1
	// order of display, omitted keywords are appended. This is the default order.
	order = current,first,previous,items,next,last,jumpto
	// reverse the order, say for float: right (also reverses the items etc)
	reverse = 1
	// alternate page key for the GPvars, 'page' by default
	pageKey = page
	// alternate lastPage key for the GPvars, 'lastPage' by default
	lastPageKey = lastPage
	// include the lastPage parameter to the links, not included by default
	includeLastPage = 1
	
	active {
		// the current page item
		// hide the current page
		doNotDisplay = 1
		// do not link the current page
		doNotLinkIt = 1
		// default is 'active'
		class = someClass
		// this is the default if value is not set
		value.current = 1
		// active page is not wrapped in li tags
		noLiWrap = 1
		outerWrap {
			// stdWrap around the link
		}
	}
	item {
		// the items shown before/after the current
		// hide the current item
		doNotDisplay = 1
		// no class as default
		class = someClass
		// this is the default if value is not set
		value.current = 1
		// pages are not wrapped in li tags
		noLiWrap = 1
		outerWrap {
			// stdWrap around the link
		}
		// added between the items (including active item)
		separator = <li class="separator">|</li>
	}
	more {
		// ... before and after the page items
		// hide the ...
		doNotDisplay = 1
		// 'more' as default
		class = someClass
		// this is the default if value is not set (stdwrap as the other values)
		value = ...
		// ... are not wrapped in li tags
		noLiWrap = 1
		outerWrap {
			// stdWrap around the ...
		}
	}
	first {
		// the link pointing to the first page (1)
		// hide the 'first page' link
		doNotDisplay = 1
		// default is 'first'
		class = someClass
		// this is the default if value is not set
		value.field = title
		// hidden by default if only one page exists
		showIfOnePage = 1
		// hidden by default if the current page is the first
		showIfFirstPage = 1
		// first page link is not wrapped in li tags
		noLiWrap = 1
		outerWrap {
			// stdWrap around the link
		}
	}
	last {
		// the link pointing to the last page that can be shown
		// hide the 'last page' link
		doNotDisplay = 1
		// default is 'last'
		class = someClass
		// this is the default if value is not set
		value.field = title
		// hidden by default if only one page exists
		showIfOnePage = 1
		// hidden by default if the current page is the last
		showIfLastPage = 1
		// last page link is not wrapped in li tags
		noLiWrap = 1
		outerWrap {
			// stdWrap around the link
		}
	}
	previous {
		// the link pointing to the previous page
		// not shown when there is no page before!
		// hide the 'previous page' link
		doNotDisplay = 1
		// default is 'previous'
		class = someClass
		// this is the default if value is not set
		value.field = title
		// previous page link is not wrapped in li tags
		noLiWrap = 1
		outerWrap {
			// stdWrap around the link
		}
	}
	next {
		// the link pointing to the next page
		// not shown when there is no page after!
		// hide the 'next page' link
		doNotDisplay = 1
		// default is 'next'
		class = someClass
		// this is the default if value is not set
		value.field = title
		// next page link is not wrapped in li tags
		noLiWrap = 1
		outerWrap {
			// stdWrap around the link
		}
	}
	jumpto {
		// the form field to jump to a page directly
		// hide the jumpto field
		doNotDisplay = 1
		// default is 'jumpto'
		class = someClass
		// this is the default if value is not set
		value.current = 1
		// jumpto field is not wrapped in li tags
		noLiWrap = 1
		outerWrap {
			// stdWrap around the link
		}
	}
	current {
		// the item displaying what page is currently displayed
		// hide the current info
		doNotDisplay = 1
		// default is 'current'
		class = someClass
		// this is the default if value is not set
		value = {field:title}: {field:activePage} / {field:lastPage}
		// current page info is not wrapped in li tags
		noLiWrap = 1
		outerWrap {
			// stdWrap around the info
		}
	}
}
	*/
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
	 public function render($typoscript = array(), $page = -1, $lastPage = -1, $localLang = 'EXT:cabag_extbase/Resources/Private/Language/locallang.xml', $llPrefix = 'pager.') {
		$request = $this->controllerContext->getRequest();
		$this->extensionName = $request->getControllerExtensionName();
		$this->pluginName = $request->getPluginName();
		
		// uri builder
		$this->uriBuilder = $this->controllerContext->getUriBuilder();
		
		$this->request = $request;
		
		// get the typoscript configuration either from an object path or from the array given
		$conf = array();
		if (is_string($typoscript)) {
			// code taken from CObjectViewHelper
			$pathSegments = t3lib_div::trimExplode('.', $typoscript, true);
			$conf = $this->typoScriptSetup;
			foreach ($pathSegments as $segment) {
				if (!array_key_exists($segment . '.', $conf)) {
					throw new Tx_Fluid_Core_ViewHelper_Exception('TypoScript object path "' . htmlspecialchars($typoscript) . '" does not exist' , 1253191023);
				}
				$conf = $conf[$segment . '.'];
			}
		} else if (is_array($typoscript)) {
			// $typoscript is supposed to be the config directly
			$conf = $typoscript;
		} else {
			$type = get_class($typoscript);
			$type = $type ? $type : gettype($typoscript);
			throw new Tx_Fluid_Core_ViewHelper_Exception('Property typoscript must be of type array or string, ' . $type . ' given.' , 1253191023);
		}
		
		$content = '';
		
		// configurations
		$style = !empty($conf['style']) ? strtolower($conf['style']) : false;
		$maxBefore = !empty($conf['maxBefore']) ? intval($conf['maxBefore']) : 0;
		$maxAfter = !empty($conf['maxAfter']) ? intval($conf['maxAfter']) : 0;
		$max = !empty($conf['max']) ? intval($conf['max']) : 5;
		$noUlWrap = !empty($conf['noUlWrap']) ? true : false;
		$favourStart = empty($conf['favourStart']) ? false : true;
		$includeLastPage = empty($conf['includeLastPage']) ? false : true;
		$pageKey = !empty($conf['pageKey']) ? $conf['pageKey'] : 'page';
		$lastPageKey = !empty($conf['lastPageKey']) ? $conf['lastPageKey'] : 'lastPage';
		$reverse = empty($conf['reverse']) ? false : true;
		
		if (isset($conf['class'])) {
			$ulClass = !empty($conf['class']) ? ' class="' . $conf['class'] . '"' : '';
		} else {
			$ulClass = ' class="pager"';
		}
		
		// get the proper arguments
		$arguments = Tx_ExtbasePager_Utility_Pager::getAllGPArguments();
		unset($arguments['L']);
		unset($arguments['cHash']);
		
		if (!empty($conf['GPnamespace'])) {
			$namespace = $conf['GPnamespace'];
			
		} else {
			$namespace = 'tx_' . strtolower($this->extensionName) . '_' . strtolower($this->pluginName);
		}
		
		// manage the order
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
		
		if (!empty($conf['order'])) {
			// there is a order defined by typoscript
			$tOrder = t3lib_div::trimExplode(',', $conf['order'], true);
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
		
		if ($reverse) {
			// reverse the array but preserve the keys!
			$order = array_reverse($order, true);
		}
		
		// make sure that the page and lastPage are valid or get them from the request if possible
		$page = intval($page);
		if ($page < 1) {
			$page = intval($arguments[$namespace][$pageKey]);
		}
		$page = $page < 1 ? 1 : $page;
		$lastPage = intval($lastPage);
		if ($lastPage < 1) {
			$lastPage = intval($arguments[$namespace][$lastPageKey]);
		}
		if (!$includeLastPage) {
			unset($arguments[$namespace][$lastPageKey]);
		}
		$lastPage = $lastPage > $page ? $lastPage : $page;
		
		if ($maxBefore) {
			if ($maxAfter) {
				$style = 'static';
				$max = $maxAfter + $maxBefore + 1;
			} else if ($style == 'growing') {
				$style = 'static';
			}
		}
		
		$start = 1;
		$end = $lastPage;
		if ($style == 'auto') {
			if ($maxBefore) {
				// $maxAfter cannot be set
				if ($page > $maxBefore) {
					// active page is 'far' away from the start (page 1)
					$start = $page - $maxBefore;
				}
				$end = $end > ($start + $max) ? $start + $max : $end;
			} else if ($maxAfter) {
				// $maxBefore cannot be set
				if ($page < $end - $maxAfter) {
					// active page is 'far' away from the end
					$end = $page + $maxAfter;
				}
				$start = $start < ($end - $max) ? $end - $max : $start;
			} else {
				// neither $maxBefore nor $maxAfter is set
				$side = floor(($max - 1) / 2);
				if ($start < $page - $side) {
					// enough space towards first page
					$start = $end > ($page + $side) ? $page - $side : $end - $max;
					$start = $start < 1 ? 1 : $start;
				}
				$end = $end > ($start + $max) ? $start + $max : $end;
			}
		} else if ($style == 'growing') {
			// TODO
		} else {
			if (!$maxBefore) {
				$maxBefore = $maxAfter ? $max - 1 - $maxAfter : floor(($max - 1) / 2);
			}
			$maxAfter = $maxAfter ? $maxAfter : $max - 1 - $maxBefore;
			
			if ($favourStart && ($max % 2) == 0) {
				$maxAfter--;
				$maxBefore++;
			}
			
			$start = $page > $maxBefore ? $page - $maxBefore : $start;
			$end = $page + $maxAfter > $end ? $end : $page + $maxAfter;
		}
		
		// translation prefix
		$llPrefix = empty($localLang) ? $llPrefix : 'LLL:' . $localLang . ':' . $llPrefix;
		
		// fields that will be accessible through typoscript
		$fields = array(
			'activePage' => $page,
			'lastPage' => $lastPage,
			'page' => 0,
			'title' => '',
		);
		
		
		// link to first page
		$ts = $conf['first.'];
		if (empty($ts['doNotDisplay']) && ($page > 1 || !empty($ts['showIfFirstPage'])) && ($lastPage > 1 || !empty($ts['showIfOnePage']))) {
			$ts = empty($ts) ? array() : $ts;
			$fields['page'] = 1;
			$title = $this->translate($llPrefix . 'first');
			$fields['title'] = $title;
			$class = empty($ts['class']) ? 'first' : $ts['class'];
			$class = ' class="' . $class . '"';
			
			$value = $this->confStdWrap($ts, 'value', '', NULL, 'page', $fields, $title);
			
			$arguments[$namespace][$pageKey] = $fields['page'];
			if ($includeLastPage) {
				$arguments[$namespace][$lastPageKey] = $lastPage;
			}
			
			// generate the uri to itself
			$uri = $this->uriBuilder
				->reset()
				->setArguments($arguments)
				->build();
				
			$value = '<a href="' . $uri . '" title="' . $title . '">' . $value . '</a>';
			
			if (empty($ts['noLiWrap'])) {
				$value = '<li' . $class . '>' . $value . '</li>';
			}
			if (!empty($ts['outerWrap.'])) {
				$value = $this->confStdWrap($ts, 'outerWrap', $value, NULL, 'page', $fields, $value);
			}
			
			$order['first'] = $value;
		}
		
		// link to previous page
		$ts = $conf['previous.'];
		if (empty($ts['doNotDisplay']) && ($page > 1)) {
			$ts = empty($ts) ? array() : $ts;
			$fields['page'] = $page - 1;
			$title = $this->translate($llPrefix . 'previous');
			$fields['title'] = $title;
			$class = empty($ts['class']) ? 'previous' : $ts['class'];
			$class = ' class="' . $class . '"';
			
			$value = $this->confStdWrap($ts, 'value', '', NULL, 'page', $fields, $title);
			
			$arguments[$namespace][$pageKey] = $fields['page'];
			if ($includeLastPage) {
				$arguments[$namespace][$lastPageKey] = $lastPage;
			}
			
			// generate the uri to itself
			$uri = $this->uriBuilder
				->reset()
				->setArguments($arguments)
				->build();
				
			$value = '<a href="' . $uri . '" title="' . $title . '">' . $value . '</a>';
			
			if (empty($ts['noLiWrap'])) {
				$value = '<li' . $class . '>' . $value . '</li>';
			}
			if (!empty($ts['outerWrap.'])) {
				$value = $this->confStdWrap($ts, 'outerWrap', $value, NULL, 'page', $fields, $value);
			}
			
			$order['previous'] = $value;
		}
		
		// statics
		$itemClass = empty($conf['item.']['class']) ? 'item' : $conf['item.']['class'];
		$activeClass = empty($conf['active.']['class']) ? 'active' : $conf['active.']['class'];
		$separator = empty($conf['item.']['separator']) ? '' : $conf['item.']['separator'];
		
		// go through each item
		for ($p = $start; $p <= $end; $p++) {
			$linkIt = true;
			$class = $itemClass;
			if ($page == $p) {
				$key = 'active';
				$class .= ' ' . $activeClass;
				if (!empty($conf['active.']['doNotLinkIt'])) {
					$linkIt = false;
				}
			} else {
				$key = 'item';
			}
			
			$ts = $conf[$key . '.'];
			$ts = empty($ts) ? array() : $ts;
			
			if (empty($ts['doNotDisplay'])) {
				$ts = empty($ts) ? array() : $ts;
				$fields['page'] = $p;
				$title = $p;
				$fields['title'] = $title;
				$class = ' class="' . $class . '"';
				$value = $this->confStdWrap($ts, 'value', '', NULL, 'page', $fields, $title);
				
				$arguments[$namespace][$pageKey] = $p;
				if ($includeLastPage) {
					$arguments[$namespace][$lastPageKey] = $lastPage;
				}
				
				// generate the uri to itself
				$uri = $this->uriBuilder
					->reset()
					->setArguments($arguments)
					->build();
					
				if ($linkIt) {
					$value = '<a href="' . $uri . '" title="' . $title . '">' . $value . '</a>';
				}
				
				if (empty($ts['noLiWrap'])) {
					$value = '<li' . $class . '>' . $value . '</li>';
				}
				if (!empty($ts['outerWrap.'])) {
					$value = $this->confStdWrap($ts, 'outerWrap', $value, NULL, 'page', $fields, $value);
				}
				
				if ($reverse) {
					$order['items'] = $value . "\n" . ($p != $start ? $separator : '') . $order['items'];
				} else {
					$order['items'] .= ($p != $start ? $separator : '') . $value . "\n";
				}
			}
		}
		
		// info about the current page/last page
		$ts = $conf['more.'];
		if (empty($ts['doNotDisplay'])) {
			$ts = empty($ts) ? array() : $ts;
			$fields['page'] = $page;
			$title = $this->translate($llPrefix . 'more');
			$fields['title'] = $title;
			$class = empty($ts['class']) ? 'more' : $ts['class'];
			$class = ' class="' . $class . '"';
			
			$value = $this->confStdWrap($ts, 'value', '', NULL, 'page', $fields, $title);
			
			if (empty($ts['noLiWrap'])) {
				$value = '<li' . $class . '>' . $value . '</li>';
			}
			if (!empty($ts['outerWrap.'])) {
				$value = $this->confStdWrap($ts, 'outerWrap', $value, NULL, 'page', $fields, $value);
			}
			
			if ($start > 1) {
				if ($reverse) {
					$order['items'] .= $value . "\n";
				} else {
					$order['items'] = $value . "\n" . $order['items'];
				}
			}
			if ($end < $lastPage) {
				if ($reverse) {
					$order['items'] = $value . "\n" . $order['items'];
				} else {
					$order['items'] .= $value . "\n";
				}
			}
		}
		
		// link to next page
		$ts = $conf['next.'];
		if (empty($ts['doNotDisplay']) && ($page < $lastPage)) {
			$ts = empty($ts) ? array() : $ts;
			$fields['page'] = $page + 1;
			$title = $this->translate($llPrefix . 'next');
			$fields['title'] = $title;
			$class = empty($ts['class']) ? 'next' : $ts['class'];
			$class = ' class="' . $class . '"';
			
			$value = $this->confStdWrap($ts, 'value', '', NULL, 'page', $fields, $title);
			
			$arguments[$namespace][$pageKey] = $fields['page'];
			if ($includeLastPage) {
				$arguments[$namespace][$lastPageKey] = $lastPage;
			}
			
			// generate the uri to itself
			$uri = $this->uriBuilder
				->reset()
				->setArguments($arguments)
				->build();
				
			$value = '<a href="' . $uri . '" title="' . $title . '">' . $value . '</a>';
			
			if (empty($ts['noLiWrap'])) {
				$value = '<li' . $class . '>' . $value . '</li>';
			}
			if (!empty($ts['outerWrap.'])) {
				$value = $this->confStdWrap($ts, 'outerWrap', $value, NULL, 'page', $fields, $value);
			}
			
			$order['next'] = $value;
		}
		
		// link to last page
		$ts = $conf['last.'];
		if (empty($ts['doNotDisplay']) && ($page < $lastPage || !empty($ts['showIfLastPage'])) && ($lastPage > 1 || !empty($ts['showIfOnePage']))) {
			$ts = empty($ts) ? array() : $ts;
			$fields['page'] = $lastPage;
			$title = $this->translate($llPrefix . 'last');
			$fields['title'] = $title;
			$class = empty($ts['class']) ? 'last' : $ts['class'];
			$class = ' class="' . $class . '"';
			
			$value = $this->confStdWrap($ts, 'value', '', NULL, 'page', $fields, $title);
			
			$arguments[$namespace][$pageKey] = $fields['page'];
			if ($includeLastPage) {
				$arguments[$namespace][$lastPageKey] = $lastPage;
			}
			
			// generate the uri to itself
			$uri = $this->uriBuilder
				->reset()
				->setArguments($arguments)
				->build();
				
			$value = '<a href="' . $uri . '" title="' . $title . '">' . $value . '</a>';
			
			if (empty($ts['noLiWrap'])) {
				$value = '<li' . $class . '>' . $value . '</li>';
			}
			if (!empty($ts['outerWrap.'])) {
				$value = $this->confStdWrap($ts, 'outerWrap', $value, NULL, 'page', $fields, $value);
			}
			
			$order['last'] = $value;
		}
		
		// jumpto field
		$ts = $conf['jumpto.'];
		if (empty($ts['doNotDisplay'])) {
			$ts = empty($ts) ? array() : $ts;
			$fields['page'] = $page;
			$fields['title'] = $fields['page'];
			$class = empty($ts['class']) ? 'jumpto' : $ts['class'];
			// add pagerJumptoJS class so the javascript hooks onto the input field
			$class = ' class="' . $class . ' pagerJumptoJS"';
			$title = $this->translate($llPrefix . 'jumptoTitle');
			
			$value = $this->confStdWrap($ts, 'value', '', NULL, 'page', $fields, $fields['page']);
			
			// generate the uri to itself
			$uri = $this->uriBuilder
				->reset()
				->build();
			
			$value = '<input type="text" name="' . $namespace . '[' . $pageKey . ']" value="' . $value . '" onkeyup="pagerJSkeyup(event, this, 1, ' . $lastPage . ')" title="' . $title . '" />' . "\n";
			
			if ($includeLastPage) {
				$value .= '<input type="hidden" name="' . $namespace . '[' . $lastPageKey . ']" value="' . $lastPage . '" />';
			}
			
			// page/lastpage should not be in arguments array for the calculations that follow
			unset($arguments[$namespace][$pageKey]);
			unset($arguments[$namespace][$lastPageKey]);
			
			$argString = http_build_query($arguments, NULL, '&');
			
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
			$value = '<script type="text/javascript" src="typo3conf/ext/cabag_extbase/Resources/Public/JavaScript/pager.js"></script>' . "\n" . $value;
			
			// wrap in form tags
			$value = '<form method="GET" action="' . $uri . '">' . "\n" . $value . "\n" . '</form>';
			
			if (empty($ts['noLiWrap'])) {
				$value = '<li' . $class . '>' . $value . '</li>';
			}
			if (!empty($ts['outerWrap.'])) {
				$value = $this->confStdWrap($ts, 'outerWrap', $value, NULL, 'page', $fields, $value);
			}
			
			$order['jumpto'] = $value;
		}
		
		// info about the current page/last page
		$ts = $conf['current.'];
		if (empty($ts['doNotDisplay'])) {
			$ts = empty($ts) ? array() : $ts;
			$fields['page'] = $page;
			$title = $this->translate($llPrefix . 'current');
			$fields['title'] = $title;
			$class = empty($ts['class']) ? 'current' : $ts['class'];
			$class = ' class="' . $class . '"';
			
			$value = $this->confStdWrap($ts, 'value', '', NULL, 'page', $fields, $title . ': ' . $page . '/' . $lastPage);
			
			if (empty($ts['noLiWrap'])) {
				$value = '<li' . $class . '>' . $value . '</li>';
			}
			if (!empty($ts['outerWrap.'])) {
				$value = $this->confStdWrap($ts, 'outerWrap', $value, NULL, 'page', $fields, $value);
			}
			
			$order['current'] = $value;
		}
		
		// reassemble the content by order
		$content = "\n" . implode("\n", $order) . "\n";
		
		if (!$noUlWrap) {
			$content = '<ul' . $ulClass . '>' . $content . '</ul>';
		}
		
		if (!empty($conf['stdWrap.'])) {
			$content = $this->contentObject->stdWrap($content, $conf['stdWrap.']);
		}
		if (!empty($conf['stdWrap'])) {
			$content = $this->contentObject->wrap($content, $conf['stdWrap']);
		}
		return $content;
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
		if (!empty($conf[$key . '.'])) {
			$this->contentObject->start($data);
			if ($currentValue != NULL) {
				$this->contentObject->setCurrentVal($currentValue);
			} elseif ($currentValueKey !== NULL && isset($data[$currentValueKey])) {
				$this->contentObject->setCurrentVal($data[$currentValueKey]);
			}
			$content = !empty($content) ? $content : (!empty($conf[$key]) ? $conf[$key] : '');
			return $this->contentObject->stdWrap($content, $conf[$key . '.']);
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
