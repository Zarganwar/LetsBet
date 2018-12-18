<?php declare(strict_types = 1);

namespace Zarganwar\LetsBet\Values;


use Zarganwar\LetsBet\Exception;

class Ticket
{
	public const STATUS_ACTIVE = 'active';
	public const STATUS_WIN = 'win';
	public const STATUS_LOST = 'lost';

	/** @var string */
	private $id;

	/** @var float */
	private $deposit;

	/** @var string */
	private $state;
	
	/** @var TicketItem[] */
	private $items;
	/**
	 * @var float
	 */
	private $win;
	/**
	 * @var float
	 */
	private $rate;

	public function __construct(
		string $id,
		string $state,
		float $deposit,
		float $rate,
		float $win,
		array $items = []
	) {
		$this->id = $id;
		$this->state = $state;
		$this->deposit = $deposit;
		$this->rate = $rate;
		$this->win = $win;

		foreach ($items as $item) {
			if (!($item instanceof TicketItem)) {
				throw new Exception('Item must be instance of ' . TicketItem::class);
			}
		}
		$this->items = $items;
	}

	public function getId(): string
	{
		return $this->id;
	}

	public function updateStatus(string $state): self
	{
		$this->state = $state;
		return $this;
	}

	public function updateWin(float $win): self
	{
		$this->win = $win;
		return $this;
	}

	public function getDeposit(): float
	{
		return $this->deposit;
	}

	/** @return TicketItem[] */
	public function getItems(): array
	{
		return $this->items;
	}

	public function getWin(): float
	{
		return $this->win;
	}

	public function getState(): string
	{
		return $this->state;
	}

	public function getRate(): float
	{
		return $this->rate;
	}

}