<?php

namespace App\Bo;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

class BoServiceConfigurator
{
	public function configure(ContainerConfigurator $configurator)
	{
		$services = $configurator->services();
		
		$services->defaults()
			->autowire()
			->autoconfigure()
			->public();
		
		$services->load('App\\Bo\\', '../Bo/*')
			->exclude('../Bo/{Entity}Bo.php')
			->tag('app.bo');
	}
}