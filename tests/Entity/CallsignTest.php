<?php

namespace App\Tests\Entity;

use App\Entity\Callsign;
use PHPUnit\Framework\TestCase;

class CallsignTest extends TestCase
{
    private Callsign $callsign;

    protected function setUp(): void
    {
        $this->callsign = new Callsign();
    }

    public function testCallsignInitialState(): void
    {
        $this->assertNull($this->callsign->getCallsignid());
        $this->assertNull($this->callsign->getCallsign());
    }

    public function testCallsignMutability(): void
    {
        $testCallsign = 'LY2EN';
        $this->callsign->setCallsign($testCallsign);
        
        $this->assertEquals($testCallsign, $this->callsign->getCallsign());
    }

    public function testCallsignValidation(): void
    {
        $validCallsigns = ['LY2EN', 'LY/DL3XYZ/P', 'W1AW'];
        foreach ($validCallsigns as $call) {
            $this->callsign->setCallsign($call);
            $this->assertEquals($call, $this->callsign->getCallsign());
        }
    }
} 