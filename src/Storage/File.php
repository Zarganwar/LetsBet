<?php declare(strict_types = 1);

namespace Zarganwar\LetsBet\Storage;


use Zarganwar\LetsBet\Values\Ticket;

class File implements IStorage
{
	/** @var string */
	private $path;

	public function __construct(string $path)
	{
		$this->path = $path;
	}

	public function save(Ticket $ticket): bool
	{
		self::prepareDir($this->path);

		return file_put_contents($this->path, $ticket) !== false;
	}

	public function get(string $id): ?Ticket
	{
		// find files in path
//
//		$contents = file_get_contents($this->path);
//		if ($contents === false) {
//			return null;
//		}
//		return $contents;
	}

	/** @inheritdoc */
	public function getAll(): array
	{
		// return all tickets
	}


	public static function prepareDir(string $dir): void
	{
		if (!is_dir($dir) && !mkdir($dir) && !is_dir($dir)) {
			throw new \RuntimeException("Directory '{$dir}' was not created");
		}
	}
}