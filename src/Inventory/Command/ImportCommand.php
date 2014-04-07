<?php

namespace Inventory\Command;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\Serializer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Parameter\Domain\Category;
use Parameter\Domain\Family\Dimension;
use User\Domain\User;

/**
 * Importe les données.
 *
 * @author matthieu.napoli
 */
class ImportCommand extends Command
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(Serializer $serializer, EntityManager $entityManager)
    {
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('import')
            ->setDescription('Importe les données');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $root = PACKAGE_PATH . '/data/exports/migration-3.0';

        $output->writeln('<comment>Importing users</comment>');
        $count = $this->importUsers($root . '/users.json');
        $output->writeln(sprintf('<info>%d users imported</info>', $count));
    }

    private function importUsers($file)
    {
        $json = file_get_contents($file);

        /** @var User[] $users */
        $users = $this->serializer->deserialize($json, 'ArrayCollection<User\Domain\User>', 'json');

        foreach ($users as $user) {
            $this->entityManager->persist($user);
        }
//        $this->entityManager->flush();

        return count($users);
    }

    private function importParameters($file)
    {
        $json = file_get_contents($file);

        /** @var Category[] $rootCategories */
        $rootCategories = $this->serializer->deserialize($json, 'ArrayCollection<Techno\Domain\Category>', 'json');

        foreach ($rootCategories as $category) {
            $this->browseParameterCategory($category);

            $this->entityManager->persist($category);
        }
        $this->entityManager->flush();
    }

    /**
     * Restaure les associations
     */
    private function browseParameterCategory(Category $category)
    {
        foreach ($category->getFamilies() as $family) {
            $this->setPropertyValue($family, 'category', $category);

            foreach ($family->getDimensions() as $dimension) {
                $this->setPropertyValue($dimension, 'family', $family);

                foreach ($dimension->getMembers() as $member) {
                    $this->setPropertyValue($member, 'dimension', $dimension);
                }
            }

            foreach ($family->getCells() as $cell) {
                $this->setPropertyValue($cell, 'family', $family);

                // Recrée l'association avec les membres (Many-To-Many bidirectionnelle)
                /** @var Dimension[] $dimensionsOrdered */
                $dimensionsOrdered = $family->getDimensions()->toArray();
                usort($dimensionsOrdered, function (Dimension $d1, Dimension $d2) {
                    return strcmp($d1->getRef(), $d2->getRef());
                });
                $members = new ArrayCollection();
                foreach (explode('|', $cell->getMembersHashKey()) as $i => $memberRef) {
                    $dimension = $dimensionsOrdered[$i];
                    $members[] = $dimension->getMember($memberRef);
                }
                $this->setPropertyValue($cell, 'members', $members);
            }
        }

        foreach ($category->getChildCategories() as $childCategory) {
            $this->setPropertyValue($childCategory, 'parentCategory', $category);

            $this->browseParameterCategory($childCategory);
        }
    }

    private function setPropertyValue($object, $property, $value)
    {
        $refl = new \ReflectionProperty($object, $property);
        $refl->setAccessible(true);
        $refl->setValue($object, $value);
    }
}
