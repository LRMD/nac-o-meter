<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Bands
 */
#[ORM\Table(name: 'bands')]
#[ORM\UniqueConstraint(name: 'band', columns: ['band'])]
#[ORM\UniqueConstraint(name: 'band_freq', columns: ['band_freq'])]
#[ORM\Entity]
class Band
{
    /**
     * @var bool
     */
    #[ORM\Column(name: 'bandID', type: 'boolean', nullable: false, options: ['comment' => 'Band ID'])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $bandid;

    /**
     * @var string
     */
    #[ORM\Column(name: 'band', type: 'string', length: 16, nullable: false, options: ['comment' => 'Band [m]'])]
    private $band;

    /**
     * @var string
     */
    #[ORM\Column(name: 'band_freq', type: 'string', length: 16, nullable: false, options: ['comment' => 'Band [Hz]'])]
    private $bandFreq;

    /**
     * @var float
     */
    #[ORM\Column(name: 'lower_freq', type: 'float', precision: 10, scale: 0, nullable: false, options: ['comment' => 'MHz'])]
    private $lowerFreq;

    /**
     * @var float
     */
    #[ORM\Column(name: 'upper_freq', type: 'float', precision: 10, scale: 0, nullable: false, options: ['comment' => 'MHz'])]
    private $upperFreq;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'factor', type: 'boolean', nullable: false, options: ['comment' => 'Band multiplier'])]
    private $factor;
}
