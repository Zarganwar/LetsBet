<?php declare(strict_types = 1);

namespace Zarganwar\LetsBet\Storage;


use \SQLite3;
use Zarganwar\LetsBet\Exceptions\TicketNotFoundException;
use Zarganwar\LetsBet\Values\Ticket;
use Zarganwar\LetsBet\Values\TicketItem;


class Sqlite implements IStorage
{
	/** @var SQLite3 */
	private $connection;

	public function __construct(string $location)
	{
		$this->connection = new SQLite3($location);
		$this->initializeStorage();
	}

	private function initializeStorage(): void
	{

		// create tables if not exists
		$this->connection->query('
			CREATE TABLE IF NOT EXISTS ticket (
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				client_id TEXT NOT NULL,
				created TEXT,
				deposit TEXT,
				rate TEXT				
			);
		');

		$this->connection->query('
			CREATE TABLE IF NOT EXISTS ticket_item (
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				ticket_id INTEGER NOT NULL,
				name TEXT,
				tip TEXT,
				status TEXT,
				rate TEXT,		
				date_time TEXT				
			);
		');
	}

	public function save(Ticket $ticket): bool
	{
		$this->connection->query('BEGIN TRANSACTION;');
		try {
			$this->connection->query("
				INSERT INTO ticket SET (
					client_id = '" . SQLite3::escapeString($ticket->getId()) ."',
					created = date('now'),
					deposit = '" . SQLite3::escapeString($ticket->getDeposit()) ."',
					rate = '" . SQLite3::escapeString($ticket->getRate()) ."'
				)
			");
			$ticketId = $this->connection->lastInsertRowID();
			foreach ($ticket->getItems() as $item) {
				$this->connection->query("
				INSERT INTO ticket_item SET (
					ticket_id = '{$ticketId}',
					date_time = '" . SQLite3::escapeString($item->getDateTime()) . "',
					name = '" . SQLite3::escapeString($item->getName()) . "',
					tip = '" . SQLite3::escapeString($item->getTip()) ."',
					status = '" . TicketItem::STATUS_ACTIVE ."'
					rate = '" . SQLite3::escapeString($item->getRate()) ."'
				)
			");
			}
			$this->connection->query('COMMIT TRANSACTION;');
		} catch (\Throwable $throwable) {
			$this->connection->query('ROLLBACK TRANSACTION;');
		}
	}

	public function update(Ticket $ticket)
	{
		try {
			$ticket = $this->get($ticket->getId());
		} catch (TicketNotFoundException $exception) {

		}
	}

	public function get(string $id): Ticket
	{
		$clientId = SQLite3::escapeString($id);
		$result = $this->connection->querySingle("SELECT * FROM ticket WHERE client_id = '{$clientId}';");
		// todo ...
		return new Ticket(123);
	}

	public function getAll(): array
	{
		$result = $this->connection->query('SELECT * FROM ticket;');
		// todo ...
		return [new Ticket(123)];

	}
}