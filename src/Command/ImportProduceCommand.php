<?php

namespace App\Command;

use App\Service\ProduceImporter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-produce',
    description: 'Import fruits and vegetables from a JSON file.',
)]
class ImportProduceCommand extends Command
{
    public function __construct(
        private readonly ProduceImporter $importer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('file', 'f', InputOption::VALUE_REQUIRED, 'Path to the JSON file', 'request.json');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $file = (string) $input->getOption('file');

        try {
            $result = $this->importer->importFromFile($file);
        } catch (\RuntimeException $exception) {
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }

        foreach ($result['warnings'] as $warning) {
            $io->warning($warning);
        }

        $io->success(sprintf('Imported %d items, skipped %d.', $result['created'], $result['skipped']));

        return Command::SUCCESS;
    }
}
