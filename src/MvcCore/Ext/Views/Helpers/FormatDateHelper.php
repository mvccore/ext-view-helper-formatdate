<?php

/**
 * MvcCore
 *
 * This source file is subject to the BSD 3 License
 * For the full copyright and license information, please view
 * the LICENSE.md file that are distributed with this source code.
 *
 * @copyright	Copyright (c) 2016 Tom Flídr (https://github.com/mvccore/mvccore)
 * @license		https://mvccore.github.io/docs/mvccore/5.0.0/LICENCE.md
 */

namespace MvcCore\Ext\Views\Helpers;

/**
 * Responsibility - format given date by `Intl` extension or by `strftime()` as fallback.
 * - Possibility to configure `Intl` datetime formatter default arguments.
 * - Possibility to configure format mask used by PHP `strftime(); for fallback`.
 * - System locale settings for fallback conversion automatically configured by request language and request locale.
 * - Fallback result string always returned in response encoding, in UTF-8 by default.
 * @method \MvcCore\Ext\Views\Helpers\FormatDateHelper GetInstance()
 */
class FormatDateHelper extends \MvcCore\Ext\Views\Helpers\InternationalizedHelper
{
	/**
	 * MvcCore Extension - View Helper - Assets - version:
	 * Comparison by PHP function version_compare();
	 * @see http://php.net/manual/en/function.version-compare.php
	 */
	const VERSION = '5.0.0-alpha';

	/**
	 * If this static property is set - helper is possible
	 * to configure as singleton before it's used for first time.
	 * Example:
	 *	`\MvcCore\Ext\Views\Helpers\FormatDateHelper::GetInstance()`
	 * @var \MvcCore\Ext\Views\Helpers\FormatDateHelper
	 */
	protected static $instance;

	/**
	 * Default date type to use:
	 * - `\IntlDateFormatter::NONE`		- Do not include this element
	 * - `\IntlDateFormatter::SHORT`	- Most abbreviated style, only essential data (12/13/52 or 3:30pm)
	 * - `\IntlDateFormatter::MEDIUM`	- Medium style (Jan 12, 1952)
	 * - `\IntlDateFormatter::LONG`		- Long style (January 12, 1952 or 3:30:32pm)
	 * - `\IntlDateFormatter::FULL`		- Completely specified style (Tuesday, April 12, 1952 AD or 3:30:42pm PST)
	 * If `NULL`, ICUʼs default date type will be used.
	 * @see http://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants
	 * @var int|NULL
	 */
	protected $intlDefaultDateFormatter = NULL;

	/**
	 * Default time type to use:
	 * - `\IntlDateFormatter::NONE`		- Do not include this element
	 * - `\IntlDateFormatter::SHORT`	- Most abbreviated style, only essential data (12/13/52 or 3:30pm)
	 * - `\IntlDateFormatter::MEDIUM`	- Medium style (Jan 12, 1952)
	 * - `\IntlDateFormatter::LONG`		- Long style (January 12, 1952 or 3:30:32pm)
	 * - `\IntlDateFormatter::FULL`		- Completely specified style (Tuesday, April 12, 1952 AD or 3:30:42pm PST)
	 * If `NULL`, ICUʼs default time type will be used.
	 * @see http://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants
	 * @var int|NULL
	 */
	protected $intlDefaultTimeFormatter = NULL;

	/**
	 * Time zone ID. The default (and the one used if `NULL` is given)
	 * is the one returned by `date_default_timezone_get()` or, if applicable,
	 * that of the `\IntlCalendar` object passed for the calendar parameter.
	 * This ID must be a valid identifier on ICUʼs database or an ID
	 * representing an explicit offset, such as GMT-05:30.
	 * @var string|\IntlTimeZone|\DateTimeZone|NULL
	 */
	protected $intlDefaultTimeZone = NULL;

	/**
	 * Calendar to use for formatting or parsing. The default value is NULL,
	 * which corresponds to `\IntlDateFormatter::GREGORIAN`. This can either be
	 * one of the `\IntlDateFormatter` calendar constants or an `\IntlCalendar`.
	 * Any `\IntlCalendar` object passed will be clone; it will not be changed
	 * by the `\IntlDateFormatter`. This will determine the calendar type used
	 * (`gregorian`, `islamic`, `persian`, etc.) and, if `NULL` is given for the
	 * timezone parameter, also the timezone used.
	 * @see http://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants.calendartypes
	 * @var int|NULL
	 */
	protected $intlDefaultCalendar = NULL;

	/**
	 * System `setlocale()` category to set up system locale automatically
	 * in `parent::setUpSystemLocaleAndEncodings()` method.
	 * This property is used only for fallback if formatting is not by `Intl` extension.
	 * @var \int[]
	 */
	protected $localeCategories = [LC_TIME];

	/**
	 * Custom format mask in used by PHP `strftime();`:
	 * This property is used only for fallback if formatting is not by `Intl` extension.
	 * @see http://php.net/strftime
	 * @var string
	 */
	protected $strftimeFormatMask = '%e. %B %G, %H:%M:%S';

	/**
	 * Set default date type to use:
	 * - `\IntlDateFormatter::NONE`		- Do not include this element
	 * - `\IntlDateFormatter::SHORT`	- Most abbreviated style, only essential data (12/13/52 or 3:30pm)
	 * - `\IntlDateFormatter::MEDIUM`	- Medium style (Jan 12, 1952)
	 * - `\IntlDateFormatter::LONG`		- Long style (January 12, 1952 or 3:30:32pm)
	 * - `\IntlDateFormatter::FULL`		- Completely specified style (Tuesday, April 12, 1952 AD or 3:30:42pm PST)
	 * If `NULL`, ICUʼs default date type will be used.
	 * @param int|NULL $intlDefaultDateFormatter
	 * @return \MvcCore\Ext\Views\Helpers\FormatDateHelper
	 */
	public function & SetIntlDefaultDateFormatter ($intlDefaultDateFormatter) {
		$this->intlDefaultDateFormatter = $intlDefaultDateFormatter;
		return $this;
	}

	/**
	 * Set default time type to use:
	 * - `\IntlDateFormatter::NONE`		- Do not include this element
	 * - `\IntlDateFormatter::SHORT`	- Most abbreviated style, only essential data (12/13/52 or 3:30pm)
	 * - `\IntlDateFormatter::MEDIUM`	- Medium style (Jan 12, 1952)
	 * - `\IntlDateFormatter::LONG`		- Long style (January 12, 1952 or 3:30:32pm)
	 * - `\IntlDateFormatter::FULL`		- Completely specified style (Tuesday, April 12, 1952 AD or 3:30:42pm PST)
	 * If `NULL`, ICUʼs default time type will be used.
	 * @param int|NULL $intlDefaultTimeFormatter
	 * @return \MvcCore\Ext\Views\Helpers\FormatDateHelper
	 */
	public function & SetIntlDefaultTimeFormatter ($intlDefaultTimeFormatter) {
		$this->intlDefaultTimeFormatter = $intlDefaultTimeFormatter;
		return $this;
	}

	/**
	 * Set default time zone ID. The default (and the one used if `NULL` is given)
	 * is the one returned by `date_default_timezone_get()` or, if applicable,
	 * that of the `\IntlCalendar` object passed for the calendar parameter.
	 * This ID must be a valid identifier on ICUʼs database or an ID
	 * representing an explicit offset, such as GMT-05:30.
	 * @param string|\IntlTimeZone|\DateTimeZone|NULL $intlDefaultTimeZone
	 * @return \MvcCore\Ext\Views\Helpers\FormatDateHelper
	 */
	public function & SetIntlDefaultTimeZone ($intlDefaultTimeZone) {
		$this->intlDefaultTimeZone = $intlDefaultTimeZone;
		return $this;
	}

	/**
	 * Set default calendar to use for formatting or parsing. The default value is NULL,
	 * which corresponds to `\IntlDateFormatter::GREGORIAN`. This can either be
	 * one of the `\IntlDateFormatter` calendar constants or an `\IntlCalendar`.
	 * Any `\IntlCalendar` object passed will be clone; it will not be changed
	 * by the `\IntlDateFormatter`. This will determine the calendar type used
	 * (`gregorian`, `islamic`, `persian`, etc.) and, if `NULL` is given for the
	 * timezone parameter, also the timezone used.
	 * @see http://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants.calendartypes
	 * @param int|NULL $intlDefaultCalendar
	 * @return \MvcCore\Ext\Views\Helpers\FormatDateHelper
	 */
	public function & SetIntlDefaultCalendar ($intlDefaultCalendar) {
		$this->intlDefaultCalendar = $intlDefaultCalendar;
		return $this;
	}

	/**
	 * Set custom format mask used by PHP `strftime();`.
	 * This method is used only for fallback if formatting is not by `Intl` extension.
	 * @see http://php.net/strftime
	 * @param string $formatMask
	 * @return \MvcCore\Ext\Views\Helpers\FormatDateHelper
	 */
	public function & SetStrftimeFormatMask ($strftimeFormatMask = '%e. %B %G, %H:%M:%S') {
		$this->strftimeFormatMask = $strftimeFormatMask;
		return $this;
	}

	/**
	 * Format given date by `datefmt_format()` (in `Intl` extension) or by `strftime()` as fallback.
	 * If you don't want to specify all arguments for each helper callback, use setters
	 * instead to set up default values for `Intl` extension formatting r for `strftime()`  formatting.
	 * You can use `$this->GetHelper('FormatDate')->SetAnything(...);` in view template
	 * or `\MvcCore\Ext\Views\Helpers\FormatDateHelper::GetInstance()->SetAnything(...);` anywhere else.
	 * @see http://php.net/manual/en/intldateformatter.create.php
	 * @see http://php.net/strftime
	 * @param \DateTime|\IntlCalendar|int|NULL $dateTimeOrTimestamp Value to format. This may be a `\DateTime\ object, an `\IntlCalendar\ object,
	 *																a numeric type representing a (possibly fractional) number of seconds since
	 *																epoch or an array in the format output by localtime().
	 * @param int|string|NULL $dateTypeOrFormatMask Any custom `\IntlDateFormatter` constant to specify second argument `int $datetype` for
	 *												`datefmt_create()` function or custom `strftime()` format mask used as fallback.
	 *												Default date types to use (if `NULL`, ICUʼs default date type will be used):
	 *												- `\IntlDateFormatter::NONE`	- Do not include this element
	 *												- `\IntlDateFormatter::SHORT`	- Most abbreviated style, only essential data (12/13/52 or 3:30pm)
	 *												- `\IntlDateFormatter::MEDIUM`	- Medium style (Jan 12, 1952)
	 *												- `\IntlDateFormatter::LONG`	- Long style (January 12, 1952 or 3:30:32pm)
	 *												- `\IntlDateFormatter::FULL`	- Completely specified style (Tuesday, April 12, 1952 AD or 3:30:42pm PST)
	 *												Fallback format mask for `strftime()` could look like `"%e. %B %G, %H:%M:%S"`.
	 * @param int|NULL $timeType Any custom `\IntlDateFormatter` constant to specify third argument `int $timetype` for `datefmt_create()`.
	 *							 Time types to use (if `NULL`, ICUʼs default time type will be used):
	 *							 - `\IntlDateFormatter::NONE`	- Do not include this element
	 *							 - `\IntlDateFormatter::SHORT`	- Most abbreviated style, only essential data (12/13/52 or 3:30pm)
	 *							 - `\IntlDateFormatter::MEDIUM`	- Medium style (Jan 12, 1952)
	 *							 - `\IntlDateFormatter::LONG`	- Long style (January 12, 1952 or 3:30:32pm)
	 *							 - `\IntlDateFormatter::FULL`	- Completely specified style (Tuesday, April 12, 1952 AD or 3:30:42pm PST)
	 * @param string|\IntlTimeZone|\DateTimeZone|NULL $timeZone Any custom time zone ID. The default (and the one used if `NULL` is given)
	 *															is the one returned by `date_default_timezone_get()` or, if applicable, that
	 *															of the `\IntlCalendar` object passed for the calendar parameter. This ID must
	 *															be a valid identifier on ICUʼs database or an ID representing an explicit
	 *															offset, such as GMT-05:30. If you want to specify custom timezone for whole
	 *															application, use `date_default_timezone_set('Europe/Prague');`...
	 * @param int|NULL $calendar Calendar to use for formatting or parsing. The default value is `NULL`, which corresponds to
	 *							 `\IntlDateFormatter::GREGORIAN`. This can either be one of the `\IntlDateFormatter` calendar
	 *							 constants or an IntlCalendar. Any IntlCalendar object passed will be clone; it will not be
	 *							 changed by the IntlDateFormatter. This will determine the calendar type used (gregorian,
	 *							 islamic, persian, etc.) and, if NULL is given for the timezone parameter, also the timezone used.
	 * @return string
	 */
	public function FormatDate (
		$dateTimeOrTimestamp = NULL,
		$dateTypeOrFormatMask = NULL,
		$timeType = NULL,
		$timeZone = NULL,
		$calendar = NULL
	) {
		$dateTimeToFormat = $dateTimeOrTimestamp === NULL
			? time()
			: $dateTimeOrTimestamp;
		if ($this->intlExtensionFormatting) {
			$dateType = $dateTypeOrFormatMask;
			$formatter = $this->getIntlDatetimeFormatter(
				$this->langAndLocale,
				$dateType !== NULL
					? $dateType
					: $this->intlDefaultDateFormatter,
				$timeType !== NULL
					? $timeType
					: $this->intlDefaultTimeFormatter,
				$timeZone !== NULL
					? $timeZone
					: $this->intlDefaultTimeZone,
				$calendar !== NULL
					? $calendar
					: $this->intlDefaultCalendar
			);
			return \datefmt_format($formatter, $dateTimeToFormat);
		} else {
			if ($this->encodingConversion === NULL)
				$this->setUpSystemLocaleAndEncodings();
			if ($this->systemEncoding === NULL)
				$this->SetLangAndLocale('en', 'US')
					->setUpSystemLocaleAndEncodings();
			$result = \strftime(
				$dateTypeOrFormatMask !== NULL
					? $dateTypeOrFormatMask
					: $this->strftimeFormatMask,
				$dateTimeToFormat
			);
			return $this->encode($result);
		}
	}

	/**
	 * Get stored `\IntlDateFormatter` instance or create new one.
	 * @param string|NULL $langAndLocale
	 * @param int|NULL $dateType
	 * @param int|NULL $timeType
	 * @param string|\IntlTimeZone|\DateTimeZone|NULL $timeZone
	 * @param int|NULL $calendar
	 * @return \IntlDateFormatter
	 */
	protected function & getIntlDatetimeFormatter ($langAndLocale = NULL, $dateType = NULL, $timeType = NULL, $timeZone = NULL, $calendar = NULL) {
		$key = implode('_', [
			'datetime',
			serialize(func_get_args())
		]);
		if (!isset($this->intlFormatters[$key])) {
			$this->intlFormatters[$key] = \datefmt_create(
				$this->langAndLocale, $dateType, $timeType, $timeZone, $calendar
			);
		}
		return $this->intlFormatters[$key];
	}
}
