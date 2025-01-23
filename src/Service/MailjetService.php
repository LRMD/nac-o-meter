<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MailjetService
{
    private $apiKey;
    private $apiSecret;
    private $recipientEmail;
    private $client;
    private $logger;

    public function __construct(
        string $apiKey,
        string $apiSecret,
        string $recipientEmail,
        HttpClientInterface $client,
        LoggerInterface $logger
    ) {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->recipientEmail = $recipientEmail;
        $this->client = $client;
        $this->logger = $logger;
    }

    public function sendLog(string $callsign, \DateTime $date, string $content, string $filename): bool
    {
        $url = 'https://api.mailjet.com/v3.1/send';
        $data = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => 'noreply@logs.cqcq.lt',
                        'Name' => 'LYAC Robot'
                    ],
                    'To' => [
                        [
                            'Email' => $this->recipientEmail,
                            'Name' => 'LYAC Manager'
                        ]
                    ],
                    'Subject' => sprintf('LYAC Log from %s on %s', $callsign, $date->format('Y-m-d')),
                    'TextPart' => 'Please find the attached REG1TEST log file.',
                    'Attachments' => [
                        [
                            'ContentType' => 'text/plain',
                            'Filename' => $filename,
                            'Base64Content' => base64_encode($content)
                        ]
                    ]
                ]
            ]
        ];

        try {
            $response = $this->client->request('POST', $url, [
                'auth_basic' => [ $this->apiKey, $this->apiSecret ],
                'json' => $data,
            ]);

            $statusCode = $response->getStatusCode();
            $content = $response->toArray(false);
            
            $this->logger->debug('Mailjet API Response', [
                'auth_basic' => [ $this->apiKey, $this->apiSecret ],
                'request' => $data,
                'status_code' => $statusCode,
                'response' => $content,
                'headers' => $response->getHeaders(false)
            ]);

            return $statusCode === 200;
        } catch (\Exception $e) {
            $this->logger->error('Mailjet API Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
} 