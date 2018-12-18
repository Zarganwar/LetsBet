<?php
/**
 * Created by PhpStorm.
 * User: Martin Jirásek <martin.jirasek@nms.cz>
 * Date: 11.10.2018
 * Time: 17:28
 */

namespace Zarganwar\LetsBet\Storage;


use Zarganwar\LetsBet\Exceptions\TicketNotFoundException;
use Zarganwar\LetsBet\Values\Ticket;

class Serialize implements IStorage
{
	/** @var string */
	private $path;
	/**
	 * @var string
	 */
	private $filename;

	public function __construct(string $path, string $filename)
	{
		$this->path = $path;
		$this->filename = $filename;
	}

	public function save(Ticket $ticket): bool
	{
		self::prepareDir($this->path);

		if (file_exists($this->filePath()) === false) {
			file_put_contents($this->filePath(), []);
		}

		$content = file_get_contents($this->filePath());
		$rawData = \unserialize($content);
		$rawData[$ticket->getId()] = $ticket;
		$data = serialize($rawData);
		return file_put_contents($this->filePath(), $data);
	}

	public function get(string $id): Ticket
	{
		self::prepareDir($this->path);
		if (file_exists($this->filePath()) === false) {
			file_put_contents($this->filePath(), []);
		}

		$content = file_get_contents($this->filePath());
		$rawData = \unserialize($content);

		if (!isset($rawData[$id])) {
			throw new TicketNotFoundException("Ticket '{$id}' not found");
		}
		return $rawData[$id] ?? null;
	}

	/** @return Ticket[] */
	public function getAll(): array
	{
		self::prepareDir($this->path);

		if (file_exists($this->filePath()) === false) {
			file_put_contents($this->filePath(), []);
		}

		$content = file_get_contents($this->filePath());
		return \unserialize($content);
	}

	public static function prepareDir(string $dir): void
	{
		if (!is_dir($dir) && !mkdir($dir) && !is_dir($dir)) {
			throw new \RuntimeException("Directory '{$dir}' was not created");
		}
	}

	/**
	 * @return string
	 */
	protected function filePath(): string
	{
		return $this->path . '/' . $this->filename;
	}
}