<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RulesControllerTest extends WebTestCase
{
    public function testRulesPageEnglish(): void
    {
        $client = static::createClient();
        $client->request('GET', '/en/rules');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.card');
    }

    public function testRulesPageLithuanian(): void
    {
        $client = static::createClient();
        $client->request('GET', '/lt/rules');

        $this->assertResponseIsSuccessful();
    }

    public function testRulesPageUkrainian(): void
    {
        $client = static::createClient();
        $client->request('GET', '/uk/rules');

        $this->assertResponseIsSuccessful();
    }
}
