<?php

require dirname(__DIR__).'/src/.bootstrap.php';

use App\Kernel;
use App\Service\MailjetService;
use Symfony\Component\Dotenv\Dotenv;
use Doctrine\DBAL\DriverManager;

$kernel = new Kernel($_SERVER['APP_ENV'] ?? 'dev', (bool) ($_SERVER['APP_DEBUG'] ?? true));
$kernel->boot();
$container = $kernel->getContainer();

$qsoRepository = $container->get('doctrine')->getRepository(\App\Entity\QsoRecord::class);
$roundRepository = $container->get('doctrine')->getRepository(\App\Entity\Round::class);
$lastRound = $roundRepository->findOneBy([], ['date' => 'DESC']);

if (!$lastRound) {
    echo "No last round found.\n";
    exit(1);
}

$dateStr = $lastRound->getDate()->format('Y-m-d');
$missingLogs = $qsoRepository->getLogsNotReceived($dateStr);

if (empty($missingLogs)) {
    echo "No missing logs found for date: $dateStr\n";
    exit(0);
}

// Database connection for email lookup
$connection = DriverManager::getConnection([
    'driver' => 'pdo_mysql',
    'host' => $_ENV['DATABASE_HOST'] ?? 'mysql',
    'port' => $_ENV['DATABASE_PORT'] ?? 3306,
    'dbname' => $_ENV['DATABASE_NAME'] ?? 'lyac',
    'user' => $_ENV['DATABASE_USER'] ?? 'lyac',
    'password' => $_ENV['DATABASE_PASSWORD'] ?? 'lyac',
]);

// Email template
$emailTemplate = <<<EOT
Sveiki,

Pastebėjome, kad dar negavome jūsų LYAC žurnalo už {date}.
Primename, kad žurnalus reikia pateikti per dvi savaites po turo.

Žurnalą galite pateikti:
1. Svetainėje: https://lyac.qrz.lt/submit
2. Arba išsiųsti el. paštu REG1TEST (EDI) formatu

Jei jau išsiuntėte žurnalą, atsiprašome už priminimą.

73!
LYAC Robotas
EOT;

$emailsSent = [];
$emailsFound = [];

foreach ($missingLogs as $callsign) {
    // Look up email for callsign
    $stmt = $connection->prepare('
        SELECT DISTINCT e.email
        FROM emails e
        JOIN activities a ON e.emailID = a.emailID
        JOIN callsigns c ON a.listID = c.callsignID
        WHERE c.callsign = ?
    ');
    $result = $stmt->executeQuery([$callsign]);
    $email = $result->fetchOne();

    if ($email) {
        $emailsFound[$callsign] = $email;

        // Only send if "send=true" is in query params
        if (isset($_GET['send']) && $_GET['send'] === 'true') {
            /** @var MailjetService $mailjet */
            $mailjet = $container->get(MailjetService::class);
            
            $success = $mailjet->send(
                'LYAC Žurnalo priminimas - ' . $callsign,
                str_replace('{date}', $dateStr, $emailTemplate),
                $email
            );

            if ($success) {
                $emailsSent[] = $callsign;
            }
        }
    }
}

// Output results
echo "Date: $dateStr\n";
echo "Missing logs: " . implode(', ', $missingLogs) . "\n\n";

if (!empty($emailsFound)) {
    echo "Found emails for:\n";
    foreach ($emailsFound as $callsign => $email) {
        echo "$callsign => $email\n";
    }
} else {
    echo "No emails found for any callsigns.\n";
}

if (isset($_GET['send']) && $_GET['send'] === 'true') {
    echo "\nEmails sent to: " . implode(', ', $emailsSent) . "\n";
} 