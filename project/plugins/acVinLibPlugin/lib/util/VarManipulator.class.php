<?php
class VarManipulator
{
	public static function objectToArray($d)
	{
		if (is_object($d)) {
			$d = get_object_vars($d);
		}
		return (is_array($d))? array_map(array(__CLASS__, __FUNCTION__), $d) : $d;
	}

	public static function arrayToObject($d)
	{
		return (is_array($d))? (object) array_map(array(__CLASS__, __FUNCTION__), $d) : $d;
	}

	public static function floatize($v)
	{
		$v = str_replace(',', '.', $v);
		return (is_numeric($v))? floatval($v) : 0;
	}

	public static function protectStrForCsv($str)
	{
		return '"'.preg_replace('/[\n\r]+/' , ' ', str_replace(array('"', ';'), array('', '−'), trim($str))).'"';
	}

	public static function floatizeForCsv($v)
	{
		return str_replace(array('.', ' '), array(',', ''), $v);
	}
}
