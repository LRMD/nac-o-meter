<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Country
 */
#[ORM\Table(name: 'countries')]
#[ORM\UniqueConstraint(name: 'code', columns: ['code'])]
#[ORM\Entity]
class Country
{
    /**
     * @var bool
     */
    #[ORM\Column(name: 'countryID', type: 'boolean', nullable: false, options: ['comment' => 'countryID'])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $countryid;

    /**
     * @var string
     */
    #[ORM\Column(name: 'country', type: 'string', length: 16, nullable: false, options: ['comment' => 'country'])]
    private $country;

    /**
     * @var string
     */
    #[ORM\Column(name: 'code', type: 'string', length: 2, nullable: false, options: ['comment' => 'Country codes'])]
    private $code;

    /**
     * @var string
     */
    #[ORM\Column(name: 'PHPpattern', type: 'string', length: 128, nullable: false, options: ['comment' => 'Regular expression pattern'])]
    private $phppattern;

    /**
     * @var string
     */
    #[ORM\Column(name: 'MYSQLpattern', type: 'string', length: 128, nullable: false, options: ['comment' => 'Regular expression pattern'])]
    private $mysqlpattern;
}
