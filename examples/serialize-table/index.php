<?php declare(strict_types = 1);

require_once __DIR__ . '/../../vendor/autoload.php';

use Zarganwar\LetsBet\Applications\Fortuna\Fortuna;
use Zarganwar\LetsBet\LetsBet;
use Zarganwar\LetsBet\Storage\Serialize;
use Zarganwar\LetsBet\Values\Credentials;

\Tracy\Debugger::enable(\Tracy\Debugger::DEVELOPMENT, __DIR__ . '/log');

$storage = new Serialize(__DIR__, 'data.txt');

if ($_GET['refresh'] ?? false) {
	$client = new Fortuna(new Credentials($_GET['login'] ?? '', $_GET['password'] ?? ''));
	$letsBet = new LetsBet($client);
	$letsBet->refreshStorage($storage);
}
?>
<style>
	* {
		font-size: 12px;
	}
	.active { background-color: lightskyblue; }
	.win { background-color: greenyellow; }
	.lost { background-color: coral; }

	.ticket-row {
		background-color: silver;
	}
</style>

<table
	<tr>
		<th colspan="5">TICKET</th>
		<th colspan="5">ITEM</th>
	</tr>
	<tr>
		<th>ID</th>
		<th>STATE</th>
		<th>RATE</th>
		<th>BET</th>
		<th>WIN</th>
		<th>NAME</th>
		<th>STATUS</th>
		<th>DATE</th>
		<th>TIP</th>
		<th>RATE</th>
	</tr>
<tbody>
<?php
$d = [
	'count' => 0,
	'win' => 0,
	'bet' => 0,
	'pureWin' => 0,
];
$rep = [
	\Zarganwar\LetsBet\Values\Ticket::STATUS_ACTIVE => $d,
	\Zarganwar\LetsBet\Values\Ticket::STATUS_WIN => $d,
	\Zarganwar\LetsBet\Values\Ticket::STATUS_LOST => $d,
	'total' => $d,
];
foreach ($storage->getAll() as $ticket) {
if ($ticket->getState() !== \Zarganwar\LetsBet\Values\Ticket::STATUS_ACTIVE) {

	$rep[$ticket->getState()]['count']++;
	$rep[$ticket->getState()]['win'] += $ticket->getWin();
	$rep[$ticket->getState()]['bet'] += $ticket->getDeposit();
	$rep[$ticket->getState()]['pureWin'] +=
		$ticket->getState() === \Zarganwar\LetsBet\Values\Ticket::STATUS_LOST
			? (0 - $ticket->getDeposit())
			: ($ticket->getWin() - $ticket->getDeposit())
	;
	$rep['total']['count']++;
	$rep['total']['win'] += $ticket->getWin();
	$rep['total']['bet'] += $ticket->getDeposit();
	$rep['total']['pureWin'] +=
		$ticket->getState() === \Zarganwar\LetsBet\Values\Ticket::STATUS_LOST
			? (0 - $ticket->getDeposit())
			: ($ticket->getWin() - $ticket->getDeposit())
	;
}
?>
	<tr class="ticket-row">
		<td><?php echo $ticket->getId(); ?></td>
		<td class="<?php echo $ticket->getState(); ?>"><?php echo $ticket->getState(); ?></td>
		<td><?php echo $ticket->getRate(); ?></td>
		<td><?php echo $ticket->getDeposit(); ?></td>
		<td><?php echo $ticket->getWin(); ?></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	</tr>
	<?php foreach ($ticket->getItems() as $item) { ?>
			<tr>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td><?php echo $item->getName(); ?></td>
			<td class="<?php echo $item->getStatus(); ?>"><?php echo $item->getStatus(); ?></td>
			<td><?php echo $item->getDateTime()->format('d.m.Y H:i'); ?></td>
			<td><?php echo $item->getTip(); ?></td>
			<td><?php echo $item->getRate(); ?></td>
			</tr>
		<?php } ?>

	<?php } ?>
</tbody>
</table>
<?php

dump($rep);
?>

