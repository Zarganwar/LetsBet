<?php declare(strict_types=1);

namespace Zarganwar\LetsBet\Applications\Fortuna;


use DOMDocument;
use DOMXPath;
use GuzzleHttp\Client;
use function GuzzleHttp\Psr7\str;
use Nette\Utils\DateTime;
use Nette\Utils\Strings;
use Zarganwar\LetsBet\Applications\Fortuna\Detail\Values\Currency;
use Zarganwar\LetsBet\Applications\Fortuna\Detail\Values\Name;
use Zarganwar\LetsBet\Applications\Fortuna\Detail\Values\Rate;
use Zarganwar\LetsBet\Applications\IClient;
use Zarganwar\LetsBet\Exceptions\AuthenticationException;
use Zarganwar\LetsBet\Exceptions\BalanceNotFoundException;
use Zarganwar\LetsBet\Exceptions\Exception;
use Zarganwar\LetsBet\Exceptions\TicketNotFoundException;
use Zarganwar\LetsBet\Values\Credentials;
use Zarganwar\LetsBet\Values\Ticket;
use Zarganwar\LetsBet\Values\TicketItem;


class Fortuna implements IClient
{
	/** @var Client */
	private $client;

	/** @var Credentials */
	private $credentials;

	/** @var string */
	public $rootUrl = 'https://www.ifortuna.cz';
	/** @var int */
	public $ticketListOffset = 30;
	/** @var string */
	public $ticketItemXpath = '';
	/** @var string */
	public $ticketItemDetailRegex = '';
	/** @var string */
	public $ticketDetailRegex = '';

	public function __construct(Credentials $credentials)
	{
		$this->client = new Client(['cookies' => true]);
		$this->credentials = $credentials;
	}

	public function authenticate(): bool
	{
		if ($this->isAuthenticated()) {
			return true;
		}

		$loginUrl = $this->rootUrl . '/cz/home/index$541644-login.html';
		$this->client->post(
			$loginUrl,
			[
				'form_params' => [
					'login' => $this->credentials->getUsername(),
					'password' => $this->credentials->getPassword(),
					'cid' => null,
					'redirectTo' => $this->rootUrl,
				]
			]
		);

		if (!$this->isAuthenticated()) {
			throw new AuthenticationException("Unable to authenticate on '{$loginUrl}'");
		}
		return true;
	}

	public function isAuthenticated(): bool
	{
		$domXpath = $this->getDomXpath($this->rootUrl);
		$nodeList = $domXpath->query("//span[contains(@class, 'user-profile-username')]");
		return $nodeList->length > 0;
	}

	/**
	 * @return string[]
	 * @throws Exception
	 */
	public function findTicketIds(): array
	{
		$this->authenticate();

		$ids = [];
		$url = $this->rootUrl . '/cz/uzivatel/sazeni/prehled_sazenek/index.html?offset=';
		foreach (range(0, 300, $this->ticketListOffset) as $offset) {
			$domXpath = $this->getDomXpath($url . $offset);
			$nodeList = $domXpath->query("//table[contains(@class, 'ticket_list')]/tr/td[@class='id']/a[contains(@href, 'ticket_id')]/@href");
			foreach ($nodeList as $node) {
				$match = Strings::match((string)$node->value, '~ticket_id=([a-zA-Z0-9]+)~');
				if ($match && isset($match[1])) {
					$ids[] = $match[1];
				}
			}
		}

		return $ids;
	}

	/**
	 * @param string $id
	 * @return Ticket
	 * @throws AuthenticationException
	 * @throws Exception
	 * @throws TicketNotFoundException
	 */
	public function getTicket(string $id): Ticket
	{
		$this->authenticate();

		$domXpath = $this->getDomXpath($this->rootUrl . '/cz/sazeni/tiket/index.html?ticket_id=' . $id);

		$items = [];
		$tpItems = $domXpath->query("//table[contains(@class, 'ticket') and contains(@class, 'noprint')]/*/tr[starts-with(@class, 'tp_result_')]");
		/** @var \DOMElement $tpItem */
		foreach ($tpItems as $tpItem) {

			if (!$tpItem->hasChildNodes()) {
				throw new Exception("Ticket ({$id}) item has no child nodes.");
			}

			$name = '';
			$dateTime = null;
			$rate = 0.0;

			/** @var \DOMElement $childNode */
			foreach ($tpItem->childNodes as $childNode) {
				if (!($childNode instanceof \DOMElement)) {
					continue;
				}
				$class = $childNode->getAttribute('class');

				if ($class == 'tp_item') {
					foreach ($childNode->childNodes as $itemChildNode) {
						if ($itemChildNode instanceof \DOMText) {
							$name .= $itemChildNode->nodeValue;
						}
					}
				}

				if ($class == 'tp_date') {
					$dateTime = DateTime::createFromFormat('d.m. H:i', self::valueFromDOMElement($childNode));
				}
				if ($class == 'tp_rate') {
					$rate = (float)self::valueFromDOMElement($childNode);
				}
			}
			$items[] = new TicketItem($dateTime, $name, $rate, '');
		}

		if (empty($items)) {
			throw new Exception("Ticket {$id} has not any items");
		}

		$state = self::valueFromDOMNodeList($domXpath->query("//*[@id='ticket-state']/text()"));
		$win = new Currency(self::valueFromDOMNodeList($domXpath->query("//*[@id='pay_off']/text()")));
		$rate = new Rate(self::valueFromDOMNodeList($domXpath->query("//*[@id='total-rate']/text()")));
		$bet = new Currency(self::valueFromDOMNodeList($domXpath->query("//*[@id='bet']/text()")));
		return new Ticket(
			$id,
			self::translateState($state),
			$bet->get(),
			$rate->get(),
			$win->get(),
			$items
		);
	}

	public static function translateState(string $state): string
	{
		static $fortunaStates = [
			'UNRESOLVED' => Ticket::STATUS_ACTIVE,
			'PAID' => Ticket::STATUS_WIN,
			'LOST' => Ticket::STATUS_LOST,
		];

		if (!isset($fortunaStates[$state])) {
			throw new Exception("Unknown Fortuna state '{$state}'");
		}

		return $fortunaStates[$state];
	}
	private static function valueFromDOMElement(\DOMElement $element): string
	{
		return trim($element->nodeValue);
	}

	private static function valueFromDOMNodeList(\DOMNodeList $nodeList): string
	{
		if ($nodeList->length === 0) {
			throw new Exception('DOMNodeList is empty');
		}
		$node = $nodeList->item(0);
		return trim($node->nodeValue);
	}

	private function getDomXpath(string $url): DOMXPath
	{
		$response = $this->client->get($url);
		if ($response->getStatusCode() !== 200) {
			throw new Exception("Response '{$url}' failed");
		}
		$content = $response->getBody()->getContents();

		$dom = new DOMDocument('1.0', 'UTF-8');
		$internalErrors = libxml_use_internal_errors(true);
		$dom->loadHTML($content);
		libxml_use_internal_errors($internalErrors);

		return new DOMXPath($dom);
	}

	public function balance(): float
	{
		$this->authenticate();

		$domXpath = $this->getDomXpath($this->rootUrl);
		$nodeList = $domXpath->query("//span[contains(@class, 'user-profile-field') and contains(@class, 'money')]/a/text()");

		if ($nodeList->length === 0) {
			throw new BalanceNotFoundException('Balance not found');
		}
		$node = $nodeList->item(0);

		$balance = trim($node->nodeValue);
		if ($balance === '') {
			throw new BalanceNotFoundException('Balance not found');
		}

		return (float)$balance;
	}


}