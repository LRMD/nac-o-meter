<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Callsign
 *
 * @ORM\Table(name="callsigns", uniqueConstraints={@ORM\UniqueConstraint(name="Pcall", columns={"callsign"})})
 * @ORM\Entity(repositoryClass="App\Repository\CallsignRepository")
 */
class Callsign
{
    /**
     * @var int
     *
     * @ORM\Column(name="callsignID", type="smallint", nullable=false, options={"unsigned"=true,"comment"="Call Sign ID"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $callsignid;

    /**
     * @var string
     *
     * @ORM\Column(name="callsign", type="string", length=16, nullable=false, options={"comment"="Call Sign"})
     */
    private $callsign;

    public function getCallsignid(): ?int
    {
        return $this->callsignid;
    }

    public function getCallsign(): ?string
    {
        return $this->callsign;
    }

    public function setCallsign(string $callsign): self
    {
        $this->callsign = $callsign;

        return $this;
    }
}
