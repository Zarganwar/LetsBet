<?php declare(strict_types = 1);

namespace Zarganwar\LetsBet\Applications;

use Zarganwar\LetsBet\Values\Ticket;

interface IClient
{
	/** @return string[] */
	public function findTicketIds(): array;

	public function getTicket(string $id): Ticket;

	public function balance(): float ;

}