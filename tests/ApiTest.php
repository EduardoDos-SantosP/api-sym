<?php

namespace App\Tests;

use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Routing\RequestContext;

class ApiTest extends KernelTestCase
{
	private static Client $client;
	
	public static function setUpBeforeClass(): void
	{
		parent::setUpBeforeClass();
		self::bootKernel();
		self::configureClient();
	}
	
	private static function configureClient(): void
	{
		/** @var RequestContext $rc */
		$rc = self::getContainer()->get('router.request_context');
		$baseUrl = $rc->getScheme() . '://' . $rc->getHost();
		
		self::$client = new Client([
			'base_url' => $baseUrl
		]);
	}
	
	public function testApi(): void
	{
//		$client = new Client([
//			'base_uri' =>
//		]);
		dump(self::$client->get('/'));
		self::assertTrue(true);
	}
}