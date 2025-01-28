<?php

require dirname(__DIR__).'/src/.bootstrap.php';

use App\Kernel;
use App\Service\MailjetService;
use Symfony\Component\Dotenv\Dotenv;
use Doctrine\DBAL\DriverManager;
use Symfony\Component\HttpClient\HttpClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttplugClient;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$kernel = new Kernel($_SERVER['APP_ENV'] ?? 'dev', (bool) ($_SERVER['APP_DEBUG'] ?? true));
$kernel->boot();
$container = $kernel->getContainer();

$qsoRepository = $container->get('doctrine')->getRepository(\App\Entity\QsoRecord::class);

// Find last Tuesday
$today = new \DateTime();
$lastTuesday = new \DateTime();
$daysToSubtract = ($today->format('N') - 2 + 7) % 7;
$lastTuesday->modify("-$daysToSubtract days");
$dateStr = $lastTuesday->format('Y-m-d');

$missingLogs = $qsoRepository->getLogsNotReceived($dateStr);
$missingCallsigns = array_map(function($log) {
    return $log['callsign'];
}, $missingLogs);

if (empty($missingCallsigns)) {
    echo "No missing logs found for date: $dateStr\n";
    exit(0);
}

// Create logger
$logger = new Logger('mailjet');
$logger->pushHandler(new StreamHandler(dirname(__DIR__) . '/var/log/mailjet.log', Logger::DEBUG));

// Create HTTP client
$httpClient = HttpClient::create();

// Create MailjetService instance
$mailjetService = new MailjetService(
    $_ENV['MAILJET_API_KEY'] ?? '',
    $_ENV['MAILJET_API_SECRET'] ?? '',
    $_ENV['MAILJET_RECIPIENT_EMAIL'] ?? 'lyac@qrz.lt',
    $httpClient,
    $logger
);

// Database connection for email lookup
$connection = DriverManager::getConnection([
    'driver' => 'pdo_mysql',
    'url' => $_ENV['DATABASE_URL'] ?? 'mysql://lyac:lyac@localhost:3306/lyac'
]);

// Email template
$emailTemplate = <<<EOT
Sveiki,

Pastebėjome, kad dar negavome jūsų LYAC žurnalo už {date}.
Primename, kad žurnalus reikia pateikti per dvi savaites po turo.

Žurnalą galite pateikti:
1. Svetainėje: https://lyac.qrz.lt/submit
2. Arba išsiųsti el. paštu REG1TEST (EDI) formatu lyac@qrz.lt

Jei jau išsiuntėte žurnalą, atsiprašome už priminimą.

73!
LYAC Robotas
EOT;

$emailsSent = [];
$emailsFound = [];

foreach ($missingCallsigns as $callsign) {
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
            $success = $mailjetService->send(
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
echo "Missing logs: " . implode(', ', $missingCallsigns) . "\n\n";

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