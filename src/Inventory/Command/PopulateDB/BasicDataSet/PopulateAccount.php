<?php

namespace Inventory\Command\PopulateDB\BasicDataSet;

use Account\Domain\Account;
use Account\Domain\AccountRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Output\OutputInterface;

class PopulateAccount
{
    /**
     * @Inject
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @Inject
     * @var AccountRepository
     */
    private $accountRepository;

    public function run(OutputInterface $output)
    {
        $output->writeln('  <info>Populating My C-Sense account</info>');

        // Crée le compte client My C-Sense
        $account = new Account('My C-Sense');
        $this->accountRepository->add($account);

        $this->entityManager->flush();
        $this->entityManager->clear($account);
    }
}
