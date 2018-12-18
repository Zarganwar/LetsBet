<?php
/**
 * Created by PhpStorm.
 * User: Martin Jirásek <martin.jirasek@nms.cz>
 * Date: 13.10.2018
 * Time: 22:21
 */

namespace Zarganwar\LetsBet\Applications\Fortuna\Detail\Values;


abstract class BaseValue
{
	/** @var mixed */
	protected $value;

	public function __construct($value)
	{
		$this->value = $value;
	}

}