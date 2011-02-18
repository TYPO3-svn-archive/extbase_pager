/**
 * Handles the keyup event
 *
 * @var Event event The event.
 * @var Dom el The Dom-Element.
 * @var int lower The lower boundry.
 * @var int upper The upper boundry.
 * @return void
 */
function pagerJSkeyup(event, el, lower, upper) {
	var value = pagerParseInt_(el.value);
	if (value < lower) {
		value = lower;
	} else if (value > upper) {
		value = upper;
	}
	el.value = value;
}

/**
 * Alternate parseInt to make sure 001 is parsed as decimal and no NaN is returned.
 *
 * @param string text Text to parse as int.
 * @return int Integer value of the text.
 */
function pagerParseInt_(text) {
	text = text.replace(/^(0+)[1-9]/, '');
	if (text == null || text == '' || isNaN(text)) return 0;
	
	var cleared = parseInt(text);
	if (cleared < 0) return 0;
	return cleared;
}

