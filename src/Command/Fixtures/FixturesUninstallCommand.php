<?php

declare(strict_types=1);

namespace Kiener\MolliePayments\Command\Fixtures;

use Kiener\MolliePayments\Components\Fixtures\FixturesInstaller;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

class FixturesUninstallCommand extends Command
{
    /** @var string */
    public static $defaultName = 'mollie:fixtures:uninstall';

    private string $appEnv;

    private FixturesInstaller $fixturesInstaller;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(string $appEnv, FixturesInstaller $fixturesInstaller, LoggerInterface $logger)
    {
        $this->appEnv = $appEnv;
        $this->fixturesInstaller = $fixturesInstaller;
        $this->logger = $logger;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Removes the Mollie Payments demo data fixtures.');;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Mollie Payments - Uninstalling Fixtures');

        try {

            $this->logger->info('Uninstalling Mollie Payments fixtures via CLI command.');

            $this->fixturesInstaller->uninstall();

            $io->success('Mollie Payments fixtures have been uninstalled successfully.');

            $this->logger->info('Mollie Payments fixtures uninstallation completed successfully.');

            return Command::SUCCESS;
        } catch (\Throwable $ex) {
            $this->logger->error($ex->getMessage(), ['exception' => $ex]);

            return Command::FAILURE;
        }
    }
}
