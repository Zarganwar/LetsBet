<?php declare(strict_types = 1);

namespace Zarganwar\LetsBet\Values;


use DateTimeInterface;
use Zarganwar\LetsBet\Exception;

class TicketItem
{
	public const STATUS_ACTIVE = 'active';
	public const STATUS_WIN = 'win';
	public const STATUS_LOST = 'lost';

	/** @var string */
	private $id;

	/** @var DateTimeInterface */
	private $dateTime;

	/** @var string */
	private $name;

	/** @var float */
	private $rate;

	/** @var string */
	private $tip;

	/** @var string */
	private $status;

	public function __construct(
		DateTimeInterface $dateTime,
		string $name,
		float $rate,
		string $tip,
		string $status = self::STATUS_ACTIVE
	) {
		$this->id = md5($dateTime->format(DATE_ATOM) . $name . $rate . $tip);
		$this->dateTime = $dateTime;
		$this->name = $name;
		$this->rate = $rate;
		$this->tip = $tip;
		$this->status = $status;
	}

	public function getId(): string
	{
		return $this->id;
	}

	public function getDateTime(): DateTimeInterface
	{
		return $this->dateTime;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getRate(): float
	{
		return $this->rate;
	}

	public function getTip(): string
	{
		return $this->tip;
	}

	public function isWin(): bool
	{
		return $this->status === self::STATUS_WIN;
	}

	public function isLost(): bool
	{
		return $this->status === self::STATUS_LOST;
	}

	public function isActive(): bool
	{
		return $this->status === self::STATUS_ACTIVE;
	}

	public function getStatus(): string
	{
		return $this->status;
	}

	public function updateStatus(string $result): TicketItem
	{
		if (!\in_array($result, [self::STATUS_ACTIVE, self::STATUS_LOST, self::STATUS_WIN], true)) {
			throw new Exception("Unknown status '{$result}'");
		}

		if (!$this->isActive()) {
			throw new Exception("Result can not be updated if is not '".self::STATUS_ACTIVE."'");
		}

		$this->status = $result;
		return $this;
	}

}