<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BandGroup
 */
#[ORM\Table(name: 'bands_groups')]
#[ORM\Entity]
class BandGroup
{
    /**
     * @var bool
     */
    #[ORM\Column(name: 'groupID', type: 'boolean', nullable: false, options: ['comment' => 'Bands group  ID'])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $groupid;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'group_bands', type: 'boolean', nullable: false, options: ['comment' => 'Group of bands'])]
    private $groupBands;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'bandID', type: 'boolean', nullable: false, options: ['comment' => 'Band ID'])]
    private $bandid;
}
