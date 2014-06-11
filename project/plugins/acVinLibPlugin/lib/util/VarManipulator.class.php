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
}