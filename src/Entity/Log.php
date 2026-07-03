<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Logs
 */
#[ORM\Table(name: 'logs')]
#[ORM\Index(name: 'date', columns: ['date'])]
#[ORM\Entity(repositoryClass: \App\Repository\LogRepository::class)]
class Log
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'logID', type: 'integer', nullable: false, options: ['unsigned' => true, 'comment' => 'Log ID'])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $logid;

    /**
     * @var int
     */
    #[ORM\Column(name: 'attachmentID', type: 'integer', nullable: false, options: ['unsigned' => true, 'comment' => 'Attachment ID'])]
    private $attachmentid;

    /**
     * @var int
     */
    #[ORM\Column(name: 'callsignID', type: 'smallint', nullable: false, options: ['unsigned' => true, 'comment' => 'Contest participant'])]
    private $callsignid;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'date', type: 'date', nullable: false, options: ['comment' => 'Date of contest'])]
    private $date;

    /**
     * @var bool
     * @ManyToOne(targetEntity="Bands")
     */
    #[ORM\Column(name: 'bandID', type: 'boolean', nullable: false, options: ['comment' => 'Band ID'])]
    private $bandid;

    /**
     * @var int
     */
    #[ORM\Column(name: 'wwlID', type: 'smallint', nullable: false, options: ['unsigned' => true, 'comment' => 'WWL ID'])]
    private $wwlid;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'section', type: 'string', length: 64, nullable: true, options: ['comment' => 'Psect'])]
    private $section;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'club', type: 'string', length: 64, nullable: true, options: ['comment' => 'Pclub'])]
    private $club;

    public function getLogid(): ?int
    {
        return $this->logid;
    }

    public function getAttachmentid(): ?int
    {
        return $this->attachmentid;
    }

    public function setAttachmentid(int $attachmentid): self
    {
        $this->attachmentid = $attachmentid;

        return $this;
    }

    public function getCallsignid(): ?int
    {
        return $this->callsignid;
    }

    public function setCallsignid(int $callsignid): self
    {
        $this->callsignid = $callsignid;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getBandid(): ?bool
    {
        return $this->bandid;
    }

    public function setBandid(bool $bandid): self
    {
        $this->bandid = $bandid;

        return $this;
    }

    public function getWwlid(): ?int
    {
        return $this->wwlid;
    }

    public function setWwlid(int $wwlid): self
    {
        $this->wwlid = $wwlid;

        return $this;
    }

    public function getSection(): ?string
    {
        return $this->section;
    }

    public function setSection(?string $section): self
    {
        $this->section = $section;

        return $this;
    }

    public function getClub(): ?string
    {
        return $this->club;
    }

    public function setClub(?string $club): self
    {
        $this->club = $club;

        return $this;
    }
}
