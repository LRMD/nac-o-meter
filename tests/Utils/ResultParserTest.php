<?php

namespace App\Tests\Utils;

use App\Utils\ResultParser;
use PHPUnit\Framework\TestCase;

class ResultParserTest extends TestCase
{
    private ResultParser $parser;

    protected function setUp(): void
    {
        // Use the actual results directory
        $this->parser = new ResultParser(__DIR__ . '/../../public_html/');
    }

    public function testGetMultiplierForPosition(): void
    {
        // Test position multipliers as per LYAC rules
        $this->assertEquals(10, $this->parser->getMultiplierForPosition(1));
        $this->assertEquals(8, $this->parser->getMultiplierForPosition(2));
        $this->assertEquals(6, $this->parser->getMultiplierForPosition(3));
        $this->assertEquals(5, $this->parser->getMultiplierForPosition(4));
        $this->assertEquals(4, $this->parser->getMultiplierForPosition(5));
        $this->assertEquals(3, $this->parser->getMultiplierForPosition(6));
        $this->assertEquals(2, $this->parser->getMultiplierForPosition(7));
        $this->assertEquals(1, $this->parser->getMultiplierForPosition(8));
        $this->assertEquals(0, $this->parser->getMultiplierForPosition(9));
        $this->assertEquals(0, $this->parser->getMultiplierForPosition(100));
    }

    public function testGetAllYears(): void
    {
        $years = $this->parser->getAllYears();

        $this->assertIsArray($years);
        // Results directory should have some years
        $this->assertNotEmpty($years);

        // Each year should have band rounds
        foreach ($years as $year => $rounds) {
            $this->assertIsArray($rounds);
            $this->assertMatchesRegularExpression('/^\d{4}$/', (string)$year);
        }
    }

    public function testGetCSVRecordsReturnsNullForNonExistentYear(): void
    {
        $records = $this->parser->getCSVRecords('1900', '144');
        $this->assertNull($records);
    }

    public function testGetTopScoresWithMultsStructure(): void
    {
        $years = $this->parser->getAllYears();

        if (!empty($years)) {
            $year = array_key_first($years);
            $result = $this->parser->getTopScoresWithMults($year);

            $this->assertIsArray($result);
            $this->assertArrayHasKey('scores', $result);
            $this->assertArrayHasKey('hasEmptyLastMonth', $result);
            $this->assertIsBool($result['hasEmptyLastMonth']);
        } else {
            $this->markTestSkipped('No result years available');
        }
    }
}
