<?php
/**
 * Created by PhpStorm.
 * User: Martin Jirásek <martin.jirasek@nms.cz>
 * Date: 13.10.2018
 * Time: 22:20
 */

namespace Zarganwar\LetsBet\Applications\Fortuna\Detail\Values;


use Nette\Utils\Strings;
use Zarganwar\LetsBet\Exceptions\Exception;

class Currency extends BaseValue
{
	public function get(): float
	{
		$str = (string)$this->value;
		$str = trim($str);
		$match = Strings::match($str, '~^(\d+\.\d+)~');

		if (!isset($match[1])) {
			throw new Exception("");
		}

		return (float)$match[1];
	}
}