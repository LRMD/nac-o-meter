<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Message
 */
#[ORM\Table(name: 'messages')]
#[ORM\Index(name: 'date', columns: ['date'])]
#[ORM\Entity(repositoryClass: \App\Repository\MessageRepository::class)]
class Message
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'messageID', type: 'integer', nullable: false, options: ['unsigned' => true, 'comment' => 'Message ID'])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $messageid;

    /**
     * @var int
     */
    #[ORM\Column(name: 'emailID', type: 'smallint', nullable: false, options: ['unsigned' => true, 'comment' => 'eMail ID'])]
    private $emailid;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'date', type: 'datetime', nullable: false, options: ['comment' => 'date of message'])]
    private $date;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'name', type: 'string', length: 128, nullable: true, options: ['comment' => 'sender name'])]
    private $name;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'subject', type: 'string', length: 128, nullable: true, options: ['comment' => 'subject of message'])]
    private $subject;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'cc', type: 'string', length: 1024, nullable: true, options: ['comment' => 'cc of message'])]
    private $cc;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'stamp', type: 'datetime', nullable: false, options: ['default' => 'CURRENT_TIMESTAMP', 'comment' => 'Timestamp'])]
    private $stamp = 'CURRENT_TIMESTAMP';

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

}
