<?php
namespace Bitrix\Main\DB;

class MysqliSqlHelper extends SqlHelper
{

	/**
	 * Identificator escaping - left char
	 * @return string
	 */
	public function getLeftQuote()
	{
		return '`';
	}

	/**
	 * Identificator escaping - left char
	 * @return string
	 */
	public function getRightQuote()
	{
		return '`';
	}

	public function getQueryDelimiter()
	{
		return ';';
	}

	public function getAliasLength()
	{
		return 256;
	}

	public function forSql($value, $maxLength = 0)
	{
		if ($maxLength > 0)
			$value = substr($value, 0, $maxLength);

		$con = $this->dbConnection->getResource();
		/** @var $con \mysqli */

		return $con->real_escape_string($value);
	}

	public function getCurrentDateTimeFunction()
	{
		return "NOW()";
	}

	public function getCurrentDateFunction()
	{
		return "CURDATE()";
	}

	public function addSecondsToDateTime($seconds, $from = null)
	{
		if ($from === null)
		{
			$from = static::getCurrentDateTimeFunction();
		}

		return 'DATE_ADD('.$from.', INTERVAL '.$seconds.' SECOND)';
	}

	public function getConcatFunction()
	{
		$str = "";
		$ar = func_get_args();
		if (is_array($ar))
			$str .= implode(", ", $ar);
		if (strlen($str) > 0)
			$str = "CONCAT(".$str.")";
		return $str;
	}

	public function getIsNullFunction($expression, $result)
	{
		return "IFNULL(".$expression.", ".$result.")";
	}

	public function getLengthFunction($field)
	{
		return "LENGTH(".$field.")";
	}

	public function getDatetimeToDbFunction(\Bitrix\Main\Type\DateTime $value, $type = \Bitrix\Main\Type\DateTime::DATE_WITH_TIME)
	{
		$customOffset = $value->getOffset();

		$serverTime = new \Bitrix\Main\Type\DateTime();
		$serverOffset = $serverTime->getOffset();

		$diff = $customOffset - $serverOffset;
		$valueTmp = clone $value;

		$dateInterval = new \DateInterval(sprintf("PT%sS", abs($diff)));

		if($diff < 0)
		{
			$dateInterval->invert = 1;
		}

		$valueTmp->getValue()->sub($dateInterval);

		$format = ($type == \Bitrix\Main\Type\DateTime::DATE_WITHOUT_TIME ? "Y-m-d" : "Y-m-d H:i:s");
		$date = "'".$valueTmp->format($format)."'";

		return $date;
	}

	public function getDateTimeFromDbFunction($fieldName)
	{
		return $fieldName;
	}

	public function getDatetimeToDateFunction($value)
	{
		return 'DATE('.$value.')';
	}

	public function formatDate($format, $field = null)
	{
		static $search  = array(
			"YYYY",
			"MMMM",
			"MM",
			"MI",
			"DD",
			"HH",
			"GG",
			"G",
			"SS",
			"TT",
			"T"
		);
		static $replace = array(
			"%Y",
			"%M",
			"%m",
			"%i",
			"%d",
			"%H",
			"%h",
			"%l",
			"%s",
			"%p",
			"%p"
		);

		foreach ($search as $k=>$v)
		{
			$format = str_replace($v, $replace[$k], $format);
		}

		if (strpos($format, '%H') === false)
		{
			$format = str_replace("H", "%h", $format);
		}

		if (strpos($format, '%M') === false)
		{
			$format = str_replace("M", "%b", $format);
		}

		if($field === null)
		{
			return $format;
		}
		else
		{
			return "DATE_FORMAT(".$field.", '".$format."')";
		}
	}

	public function getToCharFunction($expr, $length = 0)
	{
		return $expr;
	}

	public function prepareInsert($tableName, $arFields)
	{
		$strInsert1 = "";
		$strInsert2 = "";

		$arColumns = $this->dbConnection->getTableFields($tableName);
		foreach ($arColumns as $columnName => $columnInfo)
		{
			if (array_key_exists($columnName, $arFields))
			{
				$strInsert1 .= ", `".$columnName."`";
				$strInsert2 .= ", ".$this->convertValueToDb($arFields[$columnName], $columnInfo);
			}
			elseif (array_key_exists("~".$columnName, $arFields))
			{
				$strInsert1 .= ", `".$columnName."`";
				$strInsert2 .= ", ".$arFields["~".$columnName];
			}
		}

		if ($strInsert1 != "")
		{
			$strInsert1 = " ".substr($strInsert1, 2)." ";
			$strInsert2 = " ".substr($strInsert2, 2)." ";
		}

		return array($strInsert1, $strInsert2, array());
	}

	protected function convertValueToDb($value, array $columnInfo)
	{
		if ($value === null)
		{
			return "NULL";
		}

		if ($value instanceof SqlExpression)
		{
			return $value->compile();
		}

		switch ($columnInfo["TYPE"])
		{
			case "datetime":
				if (empty($value))
					$result = "NULL";
				else
					$result = $this->getDatetimeToDbFunction($value, \Bitrix\Main\Type\DateTime::DATE_WITH_TIME);
				break;
			case "date":
				if (empty($value))
					$result = "NULL";
				else
					$result = $this->getDatetimeToDbFunction($value, \Bitrix\Main\Type\DateTime::DATE_WITHOUT_TIME);
				break;
			case "int":
				$result = "'".intval($value)."'";
				break;
			case "real":
				$result = "'".doubleval($value)."'";
				break;
			default:
				$result = "'".$this->forSql($value)."'";
				break;
		}

		return $result;
	}

	public function getTopSql($sql, $limit, $offset = 0)
	{
		$offset = intval($offset);
		$limit = intval($limit);

		if ($offset > 0 && $limit <= 0)
			throw new \Bitrix\Main\ArgumentException("Limit must be set if offset is set");

		if ($limit > 0)
		{
			$sql .= "\nLIMIT ".$offset.", ".$limit."\n";
		}

		return $sql;
	}
}
