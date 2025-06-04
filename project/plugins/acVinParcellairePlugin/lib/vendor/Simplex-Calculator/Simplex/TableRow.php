<?php

/**
 * This file is part of the SimplexCalculator library
 *
 * Copyright (c) 2014 Petr Kessler (https://kesspess.cz)
 *
 * @license  MIT
 * @link     https://github.com/uestla/Simplex-Calculator
 */

namespace Simplex;


final class TableRow extends VariableSet
{

	/** @var string */
	private $var;

	/** @var Fraction */
	private $b;


	/**
	 * @param  string $var
	 * @param  array<string, Fraction|numeric> $set
	 * @param  Fraction|numeric $b
	 */
	public function __construct($var, array $set, $b)
	{
		parent::__construct($set);

		$this->var = (string) $var;
		$this->b = Fraction::create($b);
	}


	/** @return string */
	public function getVar()
	{
		return $this->var;
	}


	/** @return Fraction */
	public function getB()
	{
		return $this->b;
	}


	/** Deep copy */
	public function __clone()
	{
		parent::__clone();

		$this->b = clone $this->b;
	}

}
