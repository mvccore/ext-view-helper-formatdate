<?php

/**
 * MvcCore
 *
 * This source file is subject to the BSD 3 License
 * For the full copyright and license information, please view
 * the LICENSE.md file that are distributed with this source code.
 *
 * @copyright	Copyright (c) 2016 Tom Flidr (https://github.com/mvccore)
 * @license		https://mvccore.github.io/docs/mvccore/5.0.0/LICENSE.md
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
class FormatDateHelper extends \MvcCore\Ext\Views\Helpers\InternationalizedHelper {

	/**
	 * MvcCore Extension - View Helper - Assets - version:
	 * Comparison by PHP function version_compare();
	 * @see http://php.net/manual/en/function.version-compare.php
	 */
	const VERSION = '5.1.1';

	/**
	 * If this static property is set - helper is possible
	 * to configure as singleton before it's used for first time.
	 * Example:
	 *	`\MvcCore\Ext\Views\Helpers\FormatDateHelper::GetInstance()`
	 * @var \MvcCore\Ext\Views\Helpers\FormatDateHelper
	 */
	protected static $instance;
	
	/**
	 * System `setlocale()` category to set up system locale automatically
	 * in `parent::setUpSystemLocaleAndEncodings()` method.
	 * This property is used only for fallback if formatting is not by `Intl` extension.
	 * @var \int[]
	 */
	protected $localeCategories = [LC_TIME];

	/**
	 * Default format mask for `Intl` formatter (by ICU) 
	 * or default format mask for deprecated PHP function `strftime();`.
	 * Default value is format mask for `Intl` formatter: `d. MMMM yyyy, kk:mm:ss`.
	 * The same format for deprecated PHP function `strftime()` could be: `%e. %B %G, %H:%M:%S`.
	 * @see https://unicode-org.github.io/icu/userguide/format_parse/datetime/
	 * @see http://php.net/strftime
	 * @var string
	 */
	protected $defaultFormatMask = 'd. MMMM yyyy, kk:mm:ss';
	
	/**
	 * Default timezone string ID or default timezone object. 
	 * Default value of this property is timezone returned by 
	 * `date_default_timezone_get()`. The value has to be valid 
	 * identifier in ICU database or an ID representing 
	 * an explicit offset, such as GMT-05:30.
	 * @var string|\DateTimeZone|\IntlTimeZone|NULL
	 */
	protected $defaultTimeZone = NULL;

	/**
	 * Default `Intl` formatter date type:
	 * - `\IntlDateFormatter::NONE`   - Do not include this element.
	 * - `\IntlDateFormatter::SHORT`  - Most abbreviated style, only essential data (12/13/52 or 3:30pm).
	 * - `\IntlDateFormatter::MEDIUM` - Medium style (Jan 12, 1952).
	 * - `\IntlDateFormatter::LONG`   - Long style (January 12, 1952 or 3:30:32pm).
	 * - `\IntlDateFormatter::FULL`   - Completely specified style (Tuesday, April 12, 1952 AD or 3:30:42pm PST).
	 * `\IntlDateFormatter::MEDIUM` is used by default.
	 * @see http://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants
	 * @var int|NULL
	 */
	protected $defaultIntlDateType = \IntlDateFormatter::MEDIUM;

	/**
	 * Default `Intl` formatter time type:
	 * - `\IntlDateFormatter::NONE`   - Do not include this element.
	 * - `\IntlDateFormatter::SHORT`  - Most abbreviated style, only essential data (12/13/52 or 3:30pm).
	 * - `\IntlDateFormatter::MEDIUM` - Medium style (Jan 12, 1952).
	 * - `\IntlDateFormatter::LONG`   - Long style (January 12, 1952 or 3:30:32pm).
	 * - `\IntlDateFormatter::FULL`   - Completely specified style (Tuesday, April 12, 1952 AD or 3:30:42pm PST).
	 * `\IntlDateFormatter::MEDIUM` is used by default.
	 * @see http://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants
	 * @var int|NULL
	 */
	protected $defaultIntlTimeType = \IntlDateFormatter::MEDIUM;

	/**
	 * Calendar to use for formatting. The default value is NULL,
	 * which corresponds to `\IntlDateFormatter::GREGORIAN`. This can either be
	 * one of the `\IntlDateFormatter` calendar constants or an `\IntlCalendar`.
	 * Any `\IntlCalendar` object passed will be clone; it will not be changed
	 * by the `\IntlDateFormatter`. This will determine the calendar type used
	 * (`gregorian`, `islamic`, `persian`, etc.) and, if `NULL` is given for the
	 * timezone parameter, also the timezone used.
	 * @see http://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants.calendartypes
	 * @var int|NULL
	 */
	protected $defaultIntlCalendar = NULL;

	
	/**
	 * Create new datetime helper instance, set boolean about 
	 * `Intl` extension formatting by loaded extension and set
	 * default format mask by determinated formatting.
	 * @return void
	 */
	public function __construct () {
		parent::__construct();
		if (!$this->intlExtensionFormatting)
			$this->defaultFormatMask = '%e. %B %G, %H:%M:%S';
	}

	/**
	 * Set default format mask for `Intl` formatter (by ICU) 
	 * or set default format mask for deprecated PHP function `strftime();`.
	 * Default value is format mask for `Intl` formatter: `d. MMMM yyyy, kk:mm:ss`.
	 * The same format for deprecated PHP function `strftime()` could be: `%e. %B %G, %H:%M:%S`.
	 * @see https://unicode-org.github.io/icu/userguide/format_parse/datetime/
	 * @see http://php.net/strftime
	 * @param  string $defaultFormatMask
	 * @return \MvcCore\Ext\Views\Helpers\FormatDateHelper
	 */
	public function SetDefaultFormatMask ($defaultFormatMask) {
		$this->defaultFormatMask = $defaultFormatMask;
		return $this;
	}

	/**
	 * Set default timezone string ID or default timezone object. 
	 * Default value of this property is timezone returned by 
	 * `date_default_timezone_get()`. The value has to be valid 
	 * identifier in ICU database or an ID representing 
	 * an explicit offset, such as GMT-05:30.
	 * @param  string|\IntlTimeZone|\DateTimeZone|NULL $defaultTimeZone
	 * @return \MvcCore\Ext\Views\Helpers\FormatDateHelper
	 */
	public function SetDefaultTimeZone ($defaultTimeZone) {
		$this->defaultTimeZone = $defaultTimeZone;
		return $this;
	}

	/**
	 * Set default `Intl` formatter date type:
	 * - `\IntlDateFormatter::NONE`   - Do not include this element.
	 * - `\IntlDateFormatter::SHORT`  - Most abbreviated style, only essential data (12/13/52 or 3:30pm).
	 * - `\IntlDateFormatter::MEDIUM` - Medium style (Jan 12, 1952).
	 * - `\IntlDateFormatter::LONG`   - Long style (January 12, 1952 or 3:30:32pm).
	 * - `\IntlDateFormatter::FULL`   - Completely specified style (Tuesday, April 12, 1952 AD or 3:30:42pm PST).
	 * If `NULL`, ICU's default date type will be used.
	 * @param  int|NULL $defaultIntlDateType
	 * @return \MvcCore\Ext\Views\Helpers\FormatDateHelper
	 */
	public function SetDefaultIntlDateType ($defaultIntlDateType) {
		$this->defaultIntlDateType = $defaultIntlDateType;
		return $this;
	}

	/**
	 * Set default `Intl` formatter time type:
	 * - `\IntlDateFormatter::NONE`   - Do not include this element.
	 * - `\IntlDateFormatter::SHORT`  - Most abbreviated style, only essential data (12/13/52 or 3:30pm).
	 * - `\IntlDateFormatter::MEDIUM` - Medium style (Jan 12, 1952).
	 * - `\IntlDateFormatter::LONG`   - Long style (January 12, 1952 or 3:30:32pm).
	 * - `\IntlDateFormatter::FULL`   - Completely specified style (Tuesday, April 12, 1952 AD or 3:30:42pm PST).
	 * If `NULL`, ICU's default time type will be used.
	 * @param  int|NULL $defaultIntlTimeType
	 * @return \MvcCore\Ext\Views\Helpers\FormatDateHelper
	 */
	public function SetDefaultIntlTimeType ($defaultIntlTimeType) {
		$this->defaultIntlTimeType = $defaultIntlTimeType;
		return $this;
	}

	/**
	 * Set default calendar to use for formatting. The default value is NULL,
	 * which corresponds to `\IntlDateFormatter::GREGORIAN`. This can either be
	 * one of the `\IntlDateFormatter` calendar constants or an `\IntlCalendar`.
	 * Any `\IntlCalendar` object passed will be clone; it will not be changed
	 * by the `\IntlDateFormatter`. This will determine the calendar type used
	 * (`gregorian`, `islamic`, `persian`, etc.) and, if `NULL` is given for the
	 * timezone parameter, also the timezone used.
	 * @see http://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants.calendartypes
	 * @param  int|NULL $defaultIntlCalendar
	 * @return \MvcCore\Ext\Views\Helpers\FormatDateHelper
	 */
	public function SetDefaultIntlCalendar ($defaultIntlCalendar) {
		$this->defaultIntlCalendar = $defaultIntlCalendar;
		return $this;
	}


	/**
	 * Format given date by `datefmt_format()` (with `Intl` extension) 
	 * or by `strftime()` by deprecated PHP fallback.
	 * If you don't want to specify all arguments for each helper call, 
	 * use helper setter methods to set up helper call default arguments:
	 *  - `$dateHelper->SetDefaultFormatMask()`   - to set `$formatMask` argument
	 *  - `$dateHelper->SetDefaultTimeZone()`     - to set `$timeZone` argument
	 *  - `$dateHelper->SetDefaultIntlDateType()` - to set `$intlDateType` argument
	 *  - `$dateHelper->SetDefaultIntlTimeType()` - to set `$intlTimeType` argument
	 *  - `$dateHelper->SetDefaultIntlCalendar()` - to set `$intlCalendar` argument
	 * @see http://php.net/manual/en/intldateformatter.create.php
	 * @see https://unicode-org.github.io/icu/userguide/format_parse/datetime/
	 * @see http://php.net/strftime
	 * @param  \IntlCalendar|\DateTimeInterface|array|string|int|float|NULL $dateTime
	 *         Value to format. This may be a `\DateTime\ object, an `\IntlCalendar\ object,
	 *         a numeric type representing a (possibly fractional) number of seconds since
	 *         epoch or an array in the format output by `localtime()`.
	 * @param  string|NULL                                                  $formatMask
	 *         Format mask for `Intl` formatter (by ICU) or format mask for deprecated 
	 *         PHP function `strftime();`. Default value is format mask for `Intl` 
	 *         formatter is `d. MMMM yyyy, kk:mm:ss`. The same format for deprecated 
	 *         PHP function `strftime()` could be: `%e. %B %G, %H:%M:%S`.
	 * @param  string|\DateTimeZone|\IntlTimeZone|NULL                      $timeZone
	 *         Timezone string ID or timezone object. Default value is timezone 
	 *         returned by `date_default_timezone_get()`. The value has to be valid 
	 *         identifier in ICU database or an ID representing an explicit offset, 
	 *         such as GMT-05:30.
	 * @param  int|NULL                                                     $intlDateType
	 *         Date type format constant to specify `datefmt_create()` second argument 
	 *         `int $datetype`. If `NULL`, there will be used ICU's default date type.
	 *         Date types to use
	 *         - `\IntlDateFormatter::NONE`   - Do not include this element.
	 *         - `\IntlDateFormatter::SHORT`  - Most abbreviated style, only essential data (12/13/52 or 3:30pm).
	 *         - `\IntlDateFormatter::MEDIUM` - Medium style (Jan 12, 1952).
	 *         - `\IntlDateFormatter::LONG`   - Long style (January 12, 1952 or 3:30:32pm).
	 *         - `\IntlDateFormatter::FULL`   - Completely specified style (Tuesday, April 12, 1952 AD or 3:30:42pm PST).
	 * @param  int|NULL                                                     $intlTimeType
	 *         Time type format constant to specify `datefmt_create()` third argument 
	 *         `int $timetype`. If `NULL`, there will be used ICU's default time type.
	 *         Time types to use:
	 *         - `\IntlDateFormatter::NONE`   - Do not include this element.
	 *         - `\IntlDateFormatter::SHORT`  - Most abbreviated style, only essential data (12/13/52 or 3:30pm).
	 *         - `\IntlDateFormatter::MEDIUM` - Medium style (Jan 12, 1952).
	 *         - `\IntlDateFormatter::LONG`   - Long style (January 12, 1952 or 3:30:32pm).
	 *         - `\IntlDateFormatter::FULL`   - Completely specified style (Tuesday, April 12, 1952 AD or 3:30:42pm PST).
	 * @param  int|NULL                                                     $intlCalendar
	 *         Calendar to use for formatting. The default value is `NULL`, 
	 *         which corresponds to `\IntlDateFormatter::GREGORIAN`. This can either 
	 *         be one of the `\IntlDateFormatter` calendar constants or an `\IntlCalendar`. 
	 *         Any `\IntlCalendar` object passed will be clone; it will not be changed 
	 *         by the `\IntlDateFormatter`. This will determine the calendar type used 
	 *         (gregorian, islamic, persian, etc.) and, if `NULL` is given for the timezone 
	 *         parameter, also the timezone used.
	 * @return string
	 */
	public function FormatDate (
		$dateTime = NULL,
		$formatMask = NULL,
		$timeZone = NULL,
		$intlDateType = NULL,
		$intlTimeType = NULL,
		$intlCalendar = NULL
	) {
		if ($dateTime === NULL) 
			return '';
		if ($this->intlExtensionFormatting) {
			$formatter = $this->getIntlDatetimeFormatter(
				$this->langAndLocale,
				$formatMask !== NULL
					? $formatMask
					: $this->defaultFormatMask,
				$timeZone !== NULL
					? $timeZone
					: $this->defaultTimeZone,
				$intlDateType !== NULL
					? $intlDateType
					: $this->defaultIntlDateType,
				$intlTimeType !== NULL
					? $intlTimeType
					: $this->defaultIntlTimeType,
				$intlCalendar !== NULL
					? $intlCalendar
					: $this->defaultIntlCalendar
			);
			return \datefmt_format($formatter, $dateTime);
		} else {
			if ($this->encodingConversion === NULL)
				$this->setUpSystemLocaleAndEncodings();
			if ($this->systemEncoding === NULL)
				$this->SetLangAndLocale('en', 'US')
					->setUpSystemLocaleAndEncodings();
			$result = \strftime(
				$formatMask !== NULL
					? $formatMask
					: $this->defaultFormatMask,
				$dateTime instanceof \DateTime || $dateTime instanceof \DateTimeImmutable
					? $dateTime->getTimestamp()
					: intval($dateTime)
			);
			return $this->encode($result);
		}
	}

	/**
	 * Get stored `\IntlDateFormatter` instance or create new one.
	 * @param  string|NULL                             $langAndLocale
	 * @param  string|NULL                             $formatMask
	 * @param  string|\DateTimeZone|\IntlTimeZone|NULL $timeZone
	 * @param  int|NULL                                $dateType
	 * @param  int|NULL                                $timeType
	 * @param  int|NULL                                $calendar
	 * @return \IntlDateFormatter
	 */
	protected function getIntlDatetimeFormatter ($langAndLocale, $formatMask, $timeZone, $dateType, $timeType, $calendar) {
		if ($timeZone === NULL) {
			$timeZoneStr = '';
		} else if (is_string($timeZone)) {
			$timeZoneStr = $timeZone;
		} else if ($timeZone instanceof \DateTimeZone) {
			$timeZoneStr = $timeZone->getName();
		} else if ($this->intlExtensionFormatting && $timeZone instanceof \IntlTimeZone) {
			$timeZoneStr = $timeZone->toDateTimeZone()->getName();
		}
		$key = implode('_', [
			'datetime', 
			$langAndLocale,
			$formatMask,
			$timeZoneStr,
			$dateType, 
			$timeType, 
			$calendar
		]);
		if (!isset($this->intlFormatters[$key])) {
			$this->intlFormatters[$key] = \datefmt_create(
				$this->langAndLocale, 
				$dateType, 
				$timeType, 
				$timeZone, 
				$calendar,
				$formatMask
			);
		}
		return $this->intlFormatters[$key];
	}
}
