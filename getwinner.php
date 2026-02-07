<?php

/**
 * @var array $config
 * @var mysqli $con
 */

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/init.php';

$configMail = $config['mail'];
$baseUrl = getBaseUrl();

$dsn = sprintf(
    'smtp://%s:%s@%s:%d?encryption=%s',
    $configMail['login'],
    $configMail['password'],
    $configMail['host'],
    $configMail['port'],
    $configMail['encryption']
);

$transport = Transport::fromDsn($dsn);
$mailer = new Mailer($transport);

$lots = getFinishedLotsWithBets($con);

if ($lots) {
    if (updateLotsWinnersFromArray($con, $lots) > 0) {
        foreach ($lots as $lot) {
            $winner = getUsersById($con, $lot['winner_id']);
            $emailContent = includeTemplate('email.php', [
            'winner' => $winner,
            'baseUrl' => $baseUrl,
            'lot' => $lot
            ]);
            var_dump($lot);
            $email = new Email()
                ->from('keks@phpdemo.ru')
                ->to($winner['email'])
                ->subject('Ваша ставка победила')
                ->text('Текстовое содержимое')
                ->html($emailContent);
            $mailer->send($email);
        }
    }
}
