<?php
/**
 * @author xavier.perraud
 * @package    Core
 * @subpackage Graph
 */

/**
 * Classe noeud
 * @package    Core
 * @subpackage Graph
 */
class Core_GraphNode
{
    private $entity;
    private $predecessors = array();
    private $successors = array();
    private $level;

    /**
     * Simplifie le constructeur
     */
    public function __construct()
    {

    }


    /**
     * Charge l'objet passé en parametre
     * @param objet $entity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
    }

    /**
     * Renvoi l'objet
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * ajout un noeud précédant
     * @param Core_Graph_Node $node
     */
    public function addPredecessor($node)
    {
        $this->predecessors[] = $node;
    }

    /**
     * Charge le tableau de noeuds
     * @param array $nodes
     */
    public function setPredecessors($nodes)
    {
        $this->predecessors = $nodes;
    }

    /**
     * ajout un noeud suivant
     * @param Core_Graph_Node $node
     */
    public function addSuccessor($node)
    {
        $this->successors[] = $node;
    }

    /**
     * Charge le tableau de noeuds
     * @param array $nodes
     */
    public function setSuccessors($nodes)
    {
        $this->successors = $nodes;
    }

    /**
     * Modifie le niveau du noeud
     * @param int $level
     */
    public function setLevel($level)
    {
        $this->level = $level;
    }

    /**
     * Retourne le niveau du noeud
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Retourne les predecesseurs
     * @return array
     */
    public function getPredecessors()
    {
        return $this->predecessors;
    }

    /**
     * Retourne les successeurs
     * @return array
     */
    public function getSuccessors()
    {
        return $this->successors;
    }
}