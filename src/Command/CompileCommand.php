<?php

namespace App\Command;

use App\Controller\AppController;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('compile')]
class CompileCommand extends Command
{
	public function __construct(
		protected readonly AppController $bag,
		string $name = null
	) {
		parent::__construct($name);
	}
	
	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$this->bag->doLoadRoutes();
		$output->writeln('Rotas geradas com sucesso!');
		return Command::SUCCESS;
	}
}