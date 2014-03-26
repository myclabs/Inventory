<?php

namespace Account\Application\Command;

use Account\Domain\Account;
use Account\Domain\AccountRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Commande permettant de créer un compte.
 *
 * @author matthieu.napoli
 */
class CreateAccountCommand extends Command
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var AccountRepository
     */
    private $accountRepository;

    public function __construct(EntityManager $entityManager, AccountRepository $accountRepository)
    {
        $this->entityManager = $entityManager;
        $this->accountRepository = $accountRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('account:create')
            ->setDescription('Create an account')
            ->addArgument('name', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $account = new Account($input->getArgument('name'));
        $this->accountRepository->add($account);

        $this->entityManager->flush();
    }
}
