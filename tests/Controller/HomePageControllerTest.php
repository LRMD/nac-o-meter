<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class HomePageControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'LYAC');
    }

    public function testCallsignSearch(): void
    {
        $client = static::createClient();
        
        // Test valid callsign search
        $client->request('GET', '/call_search_handle', ['callsign' => 'LY2EN']);
        $this->assertResponseRedirects('/call_search?callsign=LY2EN');

        // Test empty callsign search
        $client->request('GET', '/call_search_handle');
        $this->assertResponseRedirects('/');
    }

    public function testLanguageSwitching(): void
    {
        $client = static::createClient();
        
        // Test English version
        $client->request('GET', '/en/');
        $this->assertResponseIsSuccessful();
        
        // Test Lithuanian version
        $client->request('GET', '/');
        $this->assertResponseIsSuccessful();
        
        // Test Ukrainian version
        $client->request('GET', '/uk/');
        $this->assertResponseIsSuccessful();
    }
} 