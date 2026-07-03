<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ResultsControllerTest extends WebTestCase
{
    public function testResultsIndexPage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/results');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.navbar-brand');
    }

    public function testResultsWithYearAndBand(): void
    {
        $client = static::createClient();
        // Test with a year that should exist in the results directory
        $client->request('GET', '/results/2021/144');

        $this->assertResponseIsSuccessful();
    }

    public function testResultsWithInvalidYear(): void
    {
        $client = static::createClient();
        $client->request('GET', '/results/1900/144');

        // Should still return 200 but with empty results
        $this->assertResponseIsSuccessful();
    }
}
