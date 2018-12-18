<?php declare(strict_types = 1);

namespace Zarganwar\LetsBet\Storage;


use Zarganwar\LetsBet\Exceptions\TicketNotFoundException;
use Zarganwar\LetsBet\Values\Ticket;

class Memory implements IStorage
{
	/** @var Ticket[] */
	private $data;

	public function save(Ticket $ticket): bool
	{
		$this->data[$ticket->getId()] = $ticket;
		return true;
	}

	public function get(string $id): Ticket
	{
		if (!isset($this->data[$id])) {
			throw new TicketNotFoundException("Ticket '{$id}' not found");
		}
		return $this->data[$id] ?? null;
	}

	/** @return Ticket[] */
	public function getAll(): array
	{
		return array_values($this->data);
	}
}