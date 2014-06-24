<?php
/**
 * @author     valentin.claras
 * @author     matthieu.napoli
 * @package    Core
 * @subpackage Strategy
 */

/**
 * Stratégie appliqué sur les objets ordonnées.
 * @package    Core
 * @subpackage Strategy
 */
trait Core_Strategy_Ordered
{
    /**
     * Tableaux de cache des entités indexées par leur position.
     *
     * @var array
     */
    private static $entities = null;

    /**
     * Dernière position connue pour un contexte donnée.
     *
     * @var int
     */
    private static $lastPosition = null;

    /**
     * Position de l'objet.
     *
     * @var int
     */
    protected $position = null;


    /**
     * Renvoi le nom de la classe a utiliser pour manipuler l'ordre sur les éléments proche de l'objet.
     * .
     * @return string
     */
    protected static function getOrderedBaseEntityName()
    {
        return get_called_class();
    }

    /**
     * Renvoi les valeurs du contexte pour l'objet.
     * .
     * @return array
     */
    protected function getContext()
    {
        return array();
    }

    /**
     * Sérialise le context de manière simple.
     *
     * @param mixed $contextElement
     *
     * @return string
     */
    protected static function serializeContext($contextElement)
    {
        if (is_array($contextElement)) {
            $arraySerilizedElements = array();
            foreach ($contextElement as $arrayElement) {
                $arraySerilizedElements[] = self::serializeContext($arrayElement);
            }
            return serialize($arraySerilizedElements);
        } else if (is_object($contextElement)) {
            return spl_object_hash($contextElement);
        } else {
            return serialize($contextElement);
        }
    }

    /**
     * Vérifie que l'objet possède bien une position.
     *
     * @throws Core_Exception_UndefinedAttribute
     */
    protected function checkHasPosition()
    {
        if ($this->position == null) {
            throw new Core_Exception_UndefinedAttribute("Can't move an object without established position.");
        }
    }

    /**
     * Échange la position de l'objet avec l'objet suivant.
     */
    protected function swapWithPrevious()
    {
        // Charge l'objet précédent, incrémente sa position et la sauvegarde.
        $previous = self::loadByPositionAndContext(($this->position - 1), $this->getContext());
        $previous->position++;
        $previous->hasMove();

        // Décrémente la position de l'objet et la sauvegarde.
        $this->position--;

        // Sauvegarde dans le cache.
        self::$entities[self::serializeContext($this->getContext())][$this->position] = $this;
        self::$entities[self::serializeContext($this->getContext())][$previous->position] = $previous;
    }

    /**
     * Échange la position de l'objet avec l'objet suivant.
     */
    protected function swapWithNext()
    {
        // Charge l'objet suivant, décrémente sa position et la sauvegarde.
        $next = self::loadByPositionAndContext(($this->position + 1), $this->getContext());
        $next->position--;
        $next->hasMove();

        // Incrémente la position de l'objet et la sauvegarde.
        $this->position++;

        // Sauvegarde dans le cache.
        self::$entities[self::serializeContext($this->getContext())][$next->position] = $next;
        self::$entities[self::serializeContext($this->getContext())][$this->position] = $this;
    }

    /**
     * Définit la position de l'objet et renvoi sa nouvelle position.
     *
     * @param int $position
     *
     * @throws Core_Exception_InvalidArgument Position invalide
     * @throws Core_Exception_UndefinedAttribute La position n'est pas déjà définie
     */
    protected function setPositionInternal($position=null)
    {
        if (($this->position == null) && ($position == null)) {
            $this->addPosition();
        } else if ($position != null) {
            $this->checkHasPosition();

            // Vérification que la position ne soit pas inférieure à la première et supérieure à la dernière.
            if (($position < 1) || ($position > self::getLastPositionByContext($this->getContext()))) {
                throw new Core_Exception_InvalidArgument("The position '$position' is out of range.");
            }

            // Tant que la position n'est pas celle souhaitée on la modifie.
            while ($this->position != $position) {
                if ($this->position < $position) {
                    $this->swapWithNext();
                } else if ($this->position > $position) {
                    $this->swapWithPrevious();
                }
            }

            $this->hasMove();
        } else {
            $this->deletePosition();
        }
    }

    /**
     * Permet une surcharge facile pour lancer des évents après qu'un objet ait été déplacé.
     */
    protected function hasMove()
    {
        // Par défaut rien n'est fait.
    }

    /**
     * Ajoute une position à l'objet.
     *
     * @return void
     */
    protected function addPosition()
    {
        if ($this->position == null) {
            $this->position = self::getLastPositionByContext($this->getContext()) + 1;
            $serializedContext = self::serializeContext($this->getContext());
            self::$lastPosition[$serializedContext]++;
            self::$entities[$serializedContext][self::$lastPosition[$serializedContext]] = $this;
        }
    }

    /**
     * Supprime la position de l'objet.
     *
     * @return void
     */
    protected function deletePosition()
    {
        if ($this->position != null) {
            if (self::getLastPositionByContext($this->getContext()) > 1) {
                $this->setPosition(self::getLastPositionByContext($this->getContext()));
            }
            $this->position = null;
            $serializedContext = self::serializeContext($this->getContext());
            unset(self::$entities[$serializedContext][self::$lastPosition[$serializedContext]]);
            self::$lastPosition[$serializedContext]--;
            if (self::$lastPosition[$serializedContext] < 0) {
                self::$lastPosition[$serializedContext] = 0;
            }
        }
    }

    /**
     * Met à jour le cache pour un objet.
     */
    protected function updateCachePosition()
    {
        $serializedContext = self::serializeContext($this->getContext());

        self::$entities[$serializedContext][$this->position] = $this;
    }

    /**
     * Renvoie la dernière position dans l'ordre.
     *
     * @param array $context
     *
     * @return bool
     */
    protected static function getLastPositionByContext($context=array())
    {
        $serializedContext = self::serializeContext($context);

        if (!(isset(self::$lastPosition[$serializedContext]))) {
            $entityName = self::getOrderedBaseEntityName();
            $lastPosition = $entityName::getEntityRepository()->getLastPositionByContext($context);
            self::$lastPosition[$serializedContext] = $lastPosition;
        }

        return self::$lastPosition[$serializedContext];
    }

    /**
     * Charge un objet métier grâce à son nom de classe et sa position.
     *
     * @param int $position
     * @param array $context
     *
     * @return Core_Model_Entity
     */
    protected static function loadByPositionAndContext($position, $context=array())
    {
        $serializedContext = self::serializeContext($context);

        // Chargement si non présent dans le cache.
        if (!(isset(self::$entities[$serializedContext][$position]))) {
            $entityName = self::getOrderedBaseEntityName();
            $entity = $entityName::getEntityRepository()->loadBy(array_merge(array('position' => $position), $context));
            self::$entities[$serializedContext][$position] = $entity;
        }

        return self::$entities[$serializedContext][$position];
    }

    /**
     * Récupère la position d'un objet métier grâce à son id.
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Récupère la position d'un objet métier grâce à son id.
     *
     * @return int
     */
    public function getLastEligiblePosition()
    {
        return self::getLastPositionByContext($this->getContext());
    }

    /**
     * Définit la position de l'objet et renvoi sa nouvelle position.
     *
     * @param int $position
     *
     * @return int Nouvelle position
     *
     * @throws Core_Exception_InvalidArgument Position invalide
     * @throws Core_Exception_UndefinedAttribute La position n'est pas déjà définie
     */
    public function setPosition($position=null)
    {
        $this->setPositionInternal($position);

        // Renvoie la nouvelle position de l'objet.
        return $this->position;
    }

    /**
     * Décrémente la position de l'objet et renvoi la nouvelle position.
     *
     * @return int
     */
    public function goUp()
    {
        // Si l'objet est le premier, il n'est pas déplacé.
        if ($this->position != 1) {
            $this->setPositionInternal($this->getPosition() - 1);
        }

        // Renvoie la nouvelle position de l'objet.
        return $this->position;
    }

    /**
     * Incrémente la position de l'objet et renvoi la nouvelle position.
     *
     * @return int
     */
    public function goDown()
    {
        // Si l'objet est le dernier, il n'est pas déplacé.
        if ($this->position != self::getLastPositionByContext($this->getContext())) {
            $this->setPositionInternal($this->getPosition() + 1);
        }

        // Renvoie la nouvelle position de l'objet.
        return $this->position;
    }

    /**
     * Positionne l'élément après un autre élément
     *
     * @param Core_Model_Entity|Core_Strategy_Ordered $entity
     *
     * @return int Nouvelle position
     *
     * @throws Core_Exception_InvalidArgument L'entité donnée n'a pas le même contexte que l'entité courante
     * @throws Core_Exception_UndefinedAttribute La position n'est pas déjà définie
     */
    public function moveAfter(Core_Model_Entity $entity)
    {
        // Vérifie que les 2 entités ont le même contexte
        if ($this->getContext() != $entity->getContext()) {
            throw new Core_Exception_InvalidArgument("Invalid move: the entities don't have the same context");
        }

        if ($this->getPosition() > $entity->getPosition()) {
            $this->setPositionInternal($entity->getPosition() + 1);
        } else {
            $this->setPositionInternal($entity->getPosition());
        }
    }

}
