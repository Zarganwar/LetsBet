<?php
/**
 * Created by PhpStorm.
 * User: Martin Jirásek <martin.jirasek@nms.cz>
 * Date: 13.10.2018
 * Time: 22:20
 */

namespace Zarganwar\LetsBet\Applications\Fortuna\Detail\Values;



class Name extends BaseValue
{
	public function get(): string
	{
		return strip_tags((string)$this->value);
	}
}