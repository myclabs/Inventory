<?php

namespace Account\Architecture\Repository;

use Account\Domain\AccountRepository;
use Core\Domain\DoctrineEntityRepository;

/**
 * Account repository.
 *
 * @author matthieu.napoli
 */
class DoctrineAccountRepository extends DoctrineEntityRepository implements AccountRepository
{
}
