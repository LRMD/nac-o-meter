<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Wwl - Worldwide locator
 */
#[ORM\Table(name: 'wwls')]
#[ORM\UniqueConstraint(name: 'wwl', columns: ['wwl'])]
#[ORM\Entity(repositoryClass: \App\Repository\WwlRepository::class)]
class Wwl
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'wwlID', type: 'smallint', nullable: false, options: ['unsigned' => true, 'comment' => 'WWL ID'])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $wwlid;

    /**
     * @var string
     */
    #[ORM\Column(name: 'wwl', type: 'string', length: 6, nullable: false, options: ['comment' => 'WWL'])]
    private $wwl;


}
