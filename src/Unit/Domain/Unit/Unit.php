<?php

namespace Unit\Domain\Unit;

use Core_Model_Entity;
use Core_Model_Entity_Translatable;

/**
 * Unité
 * @author  valentin.claras
 * @author  hugo.charbonniere
 * @author  yoann.croizer
 */
abstract class Unit
{
    use Core_Model_Entity_Translatable;

    /**
     * Identifiant d'une unité
     * @var int
     */
    protected $id;

    /**
     * Référent textuel d'une unité
     * @var string
     */
    protected $ref;

    /**
     * Nom d'une unité
     * @var string
     */
    protected $name;

    /**
     * Symbole d'une unité
     * @var string
     */
    protected $symbol;


    /**
     * Défini la ref de l'unité.
     * @param string $ref
     */
    public function setRef($ref)
    {
        $this->ref = $ref;
    }

    /**
     * Renvoi la ref de l'unité.
     * @return string
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * Défini le nom de l'unité.
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Renvoi le nom de l'unité.
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Défini le symbole de l'unité.
     * @param string $symbol
     */
    public function setSymbol($symbol)
    {
        $this->symbol = $symbol;
    }

    /**
     * Renvoi le symbole de l'unité.
     * @return string
     */
    public function getSymbol()
    {
        return $this->symbol;
    }

    /**
     * Renvoi l'unité de reference.
     */
    abstract public function getReferenceUnit();

    /**
     * Renvoi le facteur de Conversion de l'unité
     * @param Unit $unit
     */
    abstract public function getConversionFactor(Unit $unit);

    /**
     * @todo Supprimer
     * @return Unit
     */
    public static function loadByRef($ref)
    {
        /** @var UnitRepository $repository */
        $repository = self::getEntityRepository();
        return $repository->findByRef($ref);
    }
    /**
     * @todo Supprimer
     */
    public function save()
    {
        /** @var UnitRepository $repository */
        $repository = self::getEntityRepository();
        $repository->add($this);
    }

    /**
     * @todo Supprimer
     */
    public function delete()
    {
        /** @var UnitRepository $repository */
        $repository = self::getEntityRepository();
        $repository->remove($this);
    }

    /**
     * @todo Supprimer
     */
    public static function load($id)
    {
        /** @var UnitRepository $repository */
        $repository = self::getEntityRepository();
        $entityName = get_called_class();

        $entity = $repository->find($id);
        if (empty($entity)) {
            // Nécessaire pour contourner un problème de récursivité lorsque la clé contient un objet.
            ob_start();
            var_dump($id);
            $exportedId = ob_get_clean();
            throw new \Core_Exception_NotFound('No "' . $entityName . '" matching key ' . $exportedId);
        }
        return $entity;
    }

    /**
     * @todo Supprimer
     */
    public static function loadList(\stdClass $foo = null)
    {
        /** @var UnitRepository $repository */
        $repository = self::getEntityRepository();
        return $repository->findAll();
    }

    /**
     * @todo Supprimer
     */
    public static function countTotal(\stdClass $foo = null)
    {
        /** @var UnitRepository $repository */
        $repository = self::getEntityRepository();
        return $repository->count();
    }

    /**
     * @todo Supprimer
     */
    protected static function getEntityRepository()
    {
        $entityManagers = \Zend_Registry::get('EntityManagers');
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $entityManagers['default'];
        return $em->getRepository(get_called_class());
    }
}