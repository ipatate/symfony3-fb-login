<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Log in with Facebook!', $crawler->filter('#connect_fb a')->text());
    }

    public function testFBAccess()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/fb-access');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }
}
