<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Round
 *
 * @ORM\Table(name="rounds", uniqueConstraints={@ORM\UniqueConstraint(name="date", columns={"date"})})
 * @ORM\Entity(repositoryClass="App\Repository\RoundRepository")
 */
class Round
{
    /**
     * @var bool
     *
     * @ORM\Column(name="roundID", type="boolean", nullable=false, options={"comment"="Round ID"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $roundid;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date", nullable=false, options={"comment"="Date of round"})
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=16, nullable=false, options={"comment"="Round name"})
     */
    private $name;

    /**
     * @var bool
     *
     * @ORM\Column(name="group_bands", type="boolean", nullable=false, options={"comment"="Group of bands"})
     * @ORM\ManyToOne(targetEntity="App\Entity\BandGroup", inversedBy="group_bands")
     */
    private $groupBands;

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

}
