<?php
namespace Bitrix\Main\Type;

use Bitrix\Main;
use Bitrix\Main\Context;

class DateTime
{
	const DATE_WITH_TIME = 0;
	const DATE_WITHOUT_TIME = 1;

	/**
	 * @var \DateTime
	 */
	protected $value;

	/** @var int|null */
	protected $dateType;

	public function __construct($time = null, $format = null, \DateTimeZone $timezone = null)
	{
		if (($time === null) || ($time === ""))
		{
			if ($timezone === null)
				$this->value = new \DateTime();
			else
				$this->value = new \DateTime(null, $timezone);
		}
		else
		{
			if ($format === null || $format === self::DATE_WITH_TIME || $format === self::DATE_WITHOUT_TIME)
			{
				if ($format === null)
				{
					// try to detect automatically
					$this->dateType = self::DATE_WITH_TIME;
					$format = static::getFormatFromCulture($this->dateType);

					if (!\DateTime::createFromFormat($format, $time))
					{
						$this->dateType = self::DATE_WITHOUT_TIME;
						$format = static::getFormatFromCulture($this->dateType);
					}
				}
				else
				{
					$this->dateType = $format;
					$format = static::getFormatFromCulture($this->dateType);
				}
			}

			if ($timezone === null)
			{
				$this->value = \DateTime::createFromFormat($format, $time);
			}
			else
			{
				$this->value = \DateTime::createFromFormat($format, $time, $timezone);
			}

			if (empty($this->value))
			{
				// exception
			}
		}
	}

	public static function createFromPhp(\DateTime $datetime)
	{
		$d = new static();
		$d->value = $datetime;
		return $d;
	}

	public function format($format)
	{
		return $this->value->format($format);
	}

	public function __clone()
	{
		$this->value = clone $this->value;
	}

	/**
	 * @param $interval
	 *    Each duration period is represented by an integer value followed by a period
	 *    designator. If the duration contains time elements, that portion of the
	 *    specification is preceded by the letter T.
	 *    Period Designators: Y - years, M - months, D - days, W - weeks, H - hours,
	 *    M - minutes, S - seconds.
	 *    Examples: two days - 2D, two seconds - T2S, six years and five minutes - 6YT5M.
	 *    The unit types must be entered from the largest scale unit on the left to the
	 *    smallest scale unit on the right.
	 *    Use first "-" char for negative periods.
	 *    OR
	 *    Relative period.
	 *    Examples: "+5 weeks", "12 day", "-7 weekdays", '3 months - 5 days'
	 *
	 * @return DateTime
	 */
	public function add($interval)
	{
		$i = null;
		try
		{
			$intervalTmp = strtoupper($interval);
			$isNegative = false;
			$firstChar = substr($intervalTmp, 0, 1);
			if ($firstChar === "-")
			{
				$isNegative = true;
				$intervalTmp = substr($intervalTmp, 1);
				$firstChar = substr($intervalTmp, 0, 1);
			}

			if ($firstChar !== "P")
				$intervalTmp = "P".$intervalTmp;
			$i = new \DateInterval($intervalTmp);
			if ($isNegative)
				$i->invert = 1;
		}
		catch (\Exception $e)
		{
		}

		if ($i == null)
			$i = \DateInterval::createFromDateString($interval);

		$this->value->add($i);

		return $this;
	}

	public function getOffset()
	{
		return $this->value->getOffset();
	}

	public function getTimestamp()
	{
		return $this->value->getTimestamp();
	}

	public function getTimeZone()
	{
		return $this->value->getTimezone();
	}

	public static function __set_state($arr)
	{
		$obj = new DateTime();
		$obj->value = $arr['value'];
		return $obj;
	}

	public function toString($type = null, Context\Culture $culture = null)
	{
		if ($type === null)
		{
			if ($this->dateType !== null)
			{
				$type = $this->dateType;
			}
			else
			{
				$type = self::DATE_WITH_TIME;
			}
		}

		$format = static::getFormatFromCulture($type, $culture);
		return $this->format($format);
	}

	public function __toString()
	{
		return $this->toString();
	}

	protected static function getFormatFromCulture($type = self::DATE_WITH_TIME, Context\Culture $culture = null)
	{
		if ($culture == null)
		{
			$context = Main\Application::getInstance()->getContext();
			$culture = $context->getCulture();
		}
		$format = ($type == self::DATE_WITH_TIME) ? $culture->getDateTimeFormat() : $culture->getDateFormat();
		return static::convertDateTimeFormatToPhp($format);
	}

	public static function convertDateTimeFormatToPhp($format)
	{
		$format = str_replace("YYYY", "Y", $format);		// 1999
		$format = str_replace("MMMM", "F", $format);		// January - December
		$format = str_replace("MM", "m", $format);		// 01 - 12

		$old_f = $format = str_replace("DD", "d", $format);	// 01 - 31
		$format = str_replace("HH", "H", $format);		// 00 - 24
		if ($old_f === $format)
			$format = str_replace("H", "h", $format);		// 01 - 12

		$format = str_replace("TT", "A", $format);		// AM - PM

		$old_f = $format = str_replace("T", "a", $format);	// am - pm
		$format = str_replace("GG", "G", $format);		// 0 - 24
		if ($old_f === $format)
			$format = str_replace("G", "g", $format);		// 1 - 12

		$format = str_replace("MI", "i", $format);		// 00 - 59
		return str_replace("SS", "s", $format);		// 00 - 59
	}

	public static function isCorrect($time, $format = null)
	{
		if (empty($time))
			return false;

		$result = true;

		try
		{
			$obj = new static($time, $format);
		}
		catch (\Exception $ex)
		{
			$result = false;
		}

		return $result;
	}

	/**
	 * @return \DateTime
	 */
	public function getValue()
	{
		return $this->value;
	}
}
