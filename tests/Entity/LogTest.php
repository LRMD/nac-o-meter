<?php

namespace App\Tests\Entity;

use App\Entity\Log;
use PHPUnit\Framework\TestCase;

class LogTest extends TestCase
{
    private Log $log;

    protected function setUp(): void
    {
        $this->log = new Log();
    }

    public function testLogInitialState(): void
    {
        $this->assertNull($this->log->getLogid());
        $this->assertNull($this->log->getDate());
        $this->assertNull($this->log->getSection());
        $this->assertNull($this->log->getClub());
    }

    public function testLogDateMutability(): void
    {
        $date = new \DateTime('2024-01-15');
        $this->log->setDate($date);

        $this->assertEquals($date, $this->log->getDate());
    }

    public function testLogCallsignIdMutability(): void
    {
        $this->log->setCallsignid(123);
        $this->assertEquals(123, $this->log->getCallsignid());
    }

    public function testLogSectionMutability(): void
    {
        $this->log->setSection('SINGLE-OP');
        $this->assertEquals('SINGLE-OP', $this->log->getSection());

        // Test null value
        $this->log->setSection(null);
        $this->assertNull($this->log->getSection());
    }

    public function testLogClubMutability(): void
    {
        $this->log->setClub('LRMD');
        $this->assertEquals('LRMD', $this->log->getClub());
    }

    public function testLogFluentInterface(): void
    {
        $result = $this->log
            ->setCallsignid(1)
            ->setDate(new \DateTime())
            ->setSection('TEST')
            ->setClub('TEST-CLUB');

        $this->assertSame($this->log, $result);
    }
}
