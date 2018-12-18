<?php declare(strict_types = 1);

namespace Zarganwar\LetsBet\Storage;


use Zarganwar\LetsBet\Values\Ticket;

interface IStorage
{
	public function save(Ticket $ticket): bool;

	public function get(string $id): Ticket;

	/** @return Ticket[] */
	public function getAll(): array;

}