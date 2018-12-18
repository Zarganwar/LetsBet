<?php
/**
 * Created by PhpStorm.
 * User: Martin Jirásek <martin.jirasek@nms.cz>
 * Date: 13.10.2018
 * Time: 22:18
 */

namespace Zarganwar\LetsBet\Applications\Fortuna\Detail\Values;


class Rate extends BaseValue
{
	public function get(): float
	{
		$str = (string)$this->value;
		$str = trim($str);

		return (float)$str;
	}
}