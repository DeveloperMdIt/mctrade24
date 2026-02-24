<?php

declare(strict_types=1);

namespace Plugin\s360_clerk_shop5\Commands;

use JTL\Console\Command\Command;
use Plugin\s360_clerk_shop5\src\Entities\StoreEntity;
use Plugin\s360_clerk_shop5\src\Export\FeedGenerator;
use Plugin\s360_clerk_shop5\src\Models\StoreModel;
use Plugin\s360_clerk_shop5\src\Utils\Logger;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class CronCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setName('cron')->setDescription('Run the Cronjob to generate the Data Feeds')
            ->setDefinition(
                new InputDefinition([
                    new InputOption('id', 'i', InputOption::VALUE_OPTIONAL, 'ID of the feed')
                ])
            );
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->getIO()->writeln(__('Data Feeds erstellen:'));
        $id = $input->getOption('id');

        try {
            $generator = new FeedGenerator();
            $model = new StoreModel();

            if ($id) {
                $store = $model->find((int) $id);

                if ($store) {
                    $stores = [$store];
                }
            } else {
                $stores = $model->all();
            }

            if (empty($stores)) {
                $this->getIO()->warning(__('Keine Feeds vorhanden!'));
                return self::FAILURE;
            }

            foreach ($stores as $store) {
                /** @var StoreEntity $store */
                $this->getIO()->writeln(sprintf(
                    __('- Feed ID %s [lang=%s;customer_group=%s]'),
                    $store->getId(),
                    $store->getLanguage()->getLocalizedName(),
                    $store->getCustomerGroup()->getName()
                ));

                $store = $model->loadSettings($store);
                $generator->createFeed($store);
            }

            $this->getIO()->writeln(__('Data Feeds wurden erfolgreich erstellt'));
        } catch (Throwable $err) {
            Logger::error(sprintf(
                'Error during feed generation: %s in %s on line %s',
                $err->getMessage(),
                $err->getFile(),
                $err->getLine()
            ));

            $this->getIO()->error(sprintf(
                __('Fehler beim Erstellen des Feed: %s in %s in Zeile %s'),
                $err->getMessage(),
                $err->getFile(),
                $err->getLine()
            ));
        }

        return self::SUCCESS;
    }
}
