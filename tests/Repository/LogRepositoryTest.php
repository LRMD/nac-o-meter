<?php

namespace App\Tests\Repository;

use App\Entity\Log;
use App\Repository\LogRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class LogRepositoryTest extends KernelTestCase
{
    private $entityManager;
    private $repository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        
        $this->repository = $this->entityManager->getRepository(Log::class);
    }

    public function testFindLastCallsigns(): void
    {
        $lastCallsigns = $this->repository->findLastCallsigns(5);
        $this->assertIsArray($lastCallsigns);
        $this->assertLessThanOrEqual(5, count($lastCallsigns));
    }

    public function testFindLastMonthStats(): void
    {
        $date = new \DateTime();
        $stats = $this->repository->findLastMonthStats($date);
        $this->assertIsArray($stats);
        
        foreach ($stats as $stat) {
            $this->assertArrayHasKey('bandFreq', $stat);
            $this->assertArrayHasKey('count', $stat);
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }
} 