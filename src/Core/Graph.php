<?php
/**
 * @author xavier.perraud
 * @package    Core
 * @subpackage Graph
 */

/**
 * This class check if a loop is contained in a graph
 * @package    Core
 * @subpackage Graph
 */
class Core_Graph
{
    private $nodes = array();
    private $withCycles = false;


    /**
     * Ajout un tableau de noeud au graph
     * @param array $nodes
     */
    public function setNodes($nodes)
    {
        $this->nodes = $nodes;
    }

    /**
     * renvoie un tableau de noeuds
     * @return array Core_Graph_Node
     */
    public function getNodes()
    {
        return $this->nodes;
    }

    /**
     * retourne si le noeud existe déjà dans le tableau de noeuds
     * @param object $entity
     * @return Core_GraphNode
     */
    public function getNodeByEntity($entity)
    {
        foreach ($this->nodes as $nod) {
            if ($entity->id === $nod->getEntity()->id) {
                return $nod;
            }
        }
        return null;
    }

    /**
    * retourne si le noeud existe déjà dans le tableau de noeuds
    * @param Core_GraphNode $node
    * @return integer
    */
    public function getNodePosition($node)
    {
        for ($i = 0; $i<count($this->nodes); $i++) {
            if ($this->nodes[$i]->getEntity()->id === $node->getEntity()->id) {
                return $i;
            }
        }
        return null;
    }
    /**
     * Retourne vrai si le grahpe contient une boucle
     */
    public function hasCycles()
    {
        return $this->withCycles;
    }

    /**
     * Change la valeur de l'attibut withCycles
     * @param bool $cycles
     */
    public function setCycles($cycles)
    {
        $this->withCycles = $cycles;
    }

    /**
     * Récupère les noeuds du niveau donné en paramètre
     * @param integer $level
     * @return array
     */
    public function getNodesByLevel($level) {
        $result = array();
        foreach ($this->nodes as $node) {
            if ($node->getLevel() === $level) {
                $result[] = $node;
            }
        }
        return $result;
    }
    /**
     * Ajoute les niveaux au noeud du graphe et vérifie que le grahpe ne comporte pas de boucle
     */
    public function sort()
    {
        $noeuds = $this->getNodes();

        $noeudsDegreZero = array();
        $i = 0;
        $degre = array();
        foreach ($noeuds as $node) {
            $degre[$i] = sizeof($node->getPredecessors());
            if ($degre[$i] == 0) {
                $noeudsDegreZero[] = $i;
            }
            $node->setLevel(0);
            $i++;
        }
        foreach ($noeuds as $node) {
            $premierNoeudDegreZero = -1;
            while ($premierNoeudDegreZero == -1) {
                if (sizeof($noeudsDegreZero) == 0) {
                    $this->setCycles(true);
                    throw new Core_Exception(__('Core', 'exception', 'cycleGraph'));
                }
                $premierNoeudDegreZero = array_shift($noeudsDegreZero);
            }
            $degre[$premierNoeudDegreZero] = -1;
            $nodeSucc = $noeuds[$premierNoeudDegreZero];
            foreach ($nodeSucc->getSuccessors() as $noeudSuccesseur) {
                $degre[$this->getNodePosition($noeudSuccesseur)]--;
                if ($degre[$this->getNodePosition($noeudSuccesseur)] == 0) {
                    $noeudsDegreZero[] = $this->getNodePosition($noeudSuccesseur);
                }
                $nodePremierD = $noeuds[$premierNoeudDegreZero];
                $noeudSuccesseur->setLevel(max($noeudSuccesseur->getLevel(),$nodePremierD->getLevel()+1));
            }
        }

    }
}