<?php declare(strict_types = 1);

namespace Zarganwar\LetsBet;


use Zarganwar\LetsBet\Applications\IClient;
use Zarganwar\LetsBet\Exceptions\Exception;
use Zarganwar\LetsBet\Storage\IStorage;
use Zarganwar\LetsBet\Values\Ticket;

class LetsBet
{
	/** @var IClient */
	private $client;

	public function __construct(IClient $client)
	{
		$this->client = $client;
	}

	public function refreshStorage(IStorage $storage): void
	{
		$ticketIds = $this->client->findTicketIds();
		foreach ($ticketIds as $ticketId) {
			try {
				$storage->save($this->client->getTicket($ticketId));
			} catch (Exception $exception) {
				// ...
			}
		}
	}

	public function balance(): float
	{
		return $this->client->balance();
	}
}