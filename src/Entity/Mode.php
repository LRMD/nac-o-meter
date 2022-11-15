<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Mode
 *
 * @ORM\Table(name="modes")
 * @ORM\Entity(repositoryClass="App\Repository\ModeRepository")
 */
class Mode
{
    /**
     * @var int
     *
     * @ORM\Column(name="modeID", type="smallint", nullable=false, options={"unsigned"=true,"comment"="Mode ID"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $modeid;

    /**
     * @var string
     *
     * @ORM\Column(name="mode", type="string", length=16, nullable=false, options={"comment"="Mode"})
     */
    private $mode;
}
