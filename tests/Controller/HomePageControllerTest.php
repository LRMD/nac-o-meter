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
        $this->assertSelectorExists('.navbar-brand');
    }

    public function testCallsignSearch(): void
    {
        $client = static::createClient();

        // Test valid callsign search - redirects to /call/{callsign}
        $client->request('GET', '/call_search_handle', ['callsign' => 'LY2EN']);
        $this->assertResponseRedirects('/call/LY2EN');

        // Test empty callsign search - redirects to home
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