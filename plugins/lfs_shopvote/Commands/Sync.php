<?php
/**
 * shopvote:sync
 *
 * @author Martin Gelder
 * @created Mon, 02 Dec 2024 10:48:18 +0100
 */
declare(strict_types=1);

namespace Plugin\lfs_shopvote\Commands;

use JTL\Console\Command\Command;
use JTL\Plugin\Helper;
use JTL\Shop;
use Plugin\lfs_shopvote\classes\LfsShopvote;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Sync extends Command
{
    protected function configure()
    {
        $this->setName('reviews:sync')
            ->setDescription('Initiates a synchronization of product reviews from shopvote.de')
            ->addArgument(
                'days',
                InputArgument::OPTIONAL,
                sprintf('Number of days to sync (maximum is %d)', LfsShopvote::REVIEW_SYNC_DAYS_MAX),
                LfsShopvote::REVIEW_SYNC_DAYS_DEFAULT
            );
    }

    /**
    * @param InputInterface $input
    * @param OutputInterface $output
    */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $lfsShopvotePlugin = Helper::getPluginById('lfs_shopvote');
        $lfsShopvote       = new LfsShopvote($lfsShopvotePlugin, Shop::Container()->getDB());

        try {
            $result = $lfsShopvote->syncNewReviews((int) $input->getArgument('days'));

            $output->writeln(strtoupper($lfsShopvotePlugin->getPluginID()) . ': Sync Results:');
            $output->writeln(sprintf('> Total reviews: %d', $result['total']));
            $output->writeln(sprintf('> Successfully synced: %d', $result['success']));
            $output->writeln(sprintf('> Errors: %d', count($result['errors'])));

            foreach ($result['errors'] as $error) {
                $output->writeln(sprintf('    > %s', $error));
            }
        }
        catch (\Exception $e) {
            $output->writeln(strtoupper($lfsShopvotePlugin->getPluginID()) . ': Fehler! - ' . $e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
