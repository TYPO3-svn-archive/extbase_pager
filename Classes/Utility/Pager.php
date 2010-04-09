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
 * This class is a pager utility class. The main intention is to ease handling of pagers.
 * @see Tx_ExtbasePager_ViewHelpers_PagerViewHelper for the ViewHelper
 *
 * @package TYPO3
 * @subpackage cabag_extbase
 */
class Tx_ExtbasePager_Utility_Pager {
	/**
	 * Prepares a query for use with a pager and returns the amount of pages there are.
	 * Usage: Constrain the query, but don't set the offset/limit. Call this function with the query, if you do not precalculate the page argument, this function will do it for you. Then assign the final page value and the returned page count to your pager ViewHelper and display the data with fluid like you would normally.
	 *
	 * @param Tx_Extbase_Persistence_QueryInterface $query The query to prepare.
	 * @param int $page The current page. Note: This value can be altered by this function in respect to the total amount of pages available!
	 * @param int $itemsPerPage The amount of items will be displayed per page.
	 * @return int The total amount of pages.
	 */
	public static function prepareQuery(Tx_Extbase_Persistence_QueryInterface $query = null, &$page = 1, $itemsPerPage = 10) {
		if ($query === NULL) {
			// no query -> no pages
			return 0;
		}
		
		// make sure page/itemsPerPage are valid
		$page = intval($page);
		$itemsPerPage = intval($itemsPerPage);
		
		if ($page < 1) {
			$page = 1;
		}
		if ($itemsPerPage < 1) {
			$itemsPerPage = 1;
		}
		
		// count must be done before limit/offset!
		$count = $query->count();
		
		$pageCount = intval(ceil($count / $itemsPerPage));
		
		if ($page > $pageCount) {
			$page = $pageCount;
		}
		
		$query->setLimit($itemsPerPage)
			->setOffset($itemsPerPage * ($page - 1));
		
		return $pageCount;
	}
	
	/**
	 * Returns all the get and post arguments to be passed along by the pager.
	 *
	 * @return array All the get and post arguments.
	 */
	public static function getAllGPArguments() {
		$postParameter = t3lib_div::_POST();
		$getParameter = t3lib_div::_GET();
		
		$mergedParameters = t3lib_div::array_merge_recursive_overrule($getParameter, $postParameter);
		
		return $mergedParameters;
	}
}

?>
