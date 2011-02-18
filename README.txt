Usage:

In the repository you need the following:


    /**
     * Finds the questions with a category
     *
     * @var Tx_MyExt_Domain_Model_Category $category The category to search for.
     * @var int $page The active page.
     * @var int $itemsPerPage The items per page.
     * @var int $pageCount The pagecount, will be set by the function.
     * @return array The result array.
     */
    public function findByCategory(Tx_MyExt_Domain_Model_Category $category = null, &$page = 1, $itemsPerPage = 10, &$pageCount = 1) {
        if($category === null) {
            return array();
        }
       
        $query = $this->createQuery();
       
        $query->matching(
                    $query->equals('category', $category)
                )
            ->setOrderings(
                    array(
                        'date' => Tx_Extbase_Persistence_QueryInterface::ORDER_DESCENDING
                    )
                );
       
        $pageCount = Tx_ExtbasePager_Utility_Pager::prepareQuery($query, $page, $itemsPerPage);
       
       
        return  $query->execute();
    }

Attention: The &'s before $page and $pageCount are important as they allow the function to overrule those variables from within.
The function prepareQuery() must be run after any matching etc but before execute().
It will do a limit (based on the $itemsPerPage) so do NOT limit the query yourself.

There are two new static functions given by the utility class:
- int prepareArray(array &$array, int &$page, int $itemsPerPage)
	-> This does works the same as prepareQuery but for arrays. The array must be the full result and it will be cropped (as it takes a reference, the given array will be the right size after)
- int prepareNumbers(int &$page, int &$itemsPerPage, int $count)
	-> This lets you calculate the page count etc. yourself (for example if you have to constrain a MSSQL query).

In the Controller:

    public function questionsAction(Tx_MyExt_Domain_Model_Category $activeCategory = null, $page = 1) {

        /* ... */

        $pageCount = 0;
       
        $this->questions = $this->questionsRepository->findByCategory($ activeCategory, $page, 10, $pageCount);
       
        /* ... */

        $this->view->assign('questions', $this->questions);
        $this->view->assign('page', $page);
        $this->view->assign('pageCount', $pageCount);
    }

-> The returned Objects are already limited to 10 in this case.

Typoscript:

lib.extbasepager {
   order = current,previous,items,next,jumpto

   active {
   }
   item {
       separator = <li class="separator">|</li>
   }
   more {
       doNotDisplay = 1
   }
   first {
       doNotDisplay = 1
   }
   last {
       doNotDisplay = 1
   }
   previous {
   }
   next {
   }
   jumpto {
   }
   current {
       value = {field:title} <span class="actPage">{field:activePage}</span> | {field:lastPage}
       value.insertData = 1
   }
}


And finally in fluid:

{namespace pager=Tx_ExtbasePager_ViewHelpers}
......
<pager:pager typoscript="lib.extbasepager" page="{page}" lastPage="{pageCount}" />

Alternatively typoscript can be filled with an array directly like so:

<pager:pager typoscript="{settings.pager}" page="{page}" lastPage="{pageCount}" />

TypoScript reference:

// in each subitem, value + outerWrap have stdWrap properties
// current is set to the page number the link will point to
// following fields are given:
//	activePage => page that is shown at the moment
//	title => title of the current item (can be a number for items or localized text for last/next etc)
//	lastPage => last page that could be shown
//	page => page number the link will point to
plugin.tx_myext.settings.pager {
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