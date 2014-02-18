<?php
/**
 * @author  matthieu.napoli
 * @author  cyril.perraud
 * @package AF
 */

use AF\Domain\Algorithm\Algo;
use AF\Domain\Algorithm\Index\AlgoResultIndex;
use AF\Domain\Algorithm\Index\FixedIndex;
use AF\Domain\Algorithm\Numeric\NumericAlgo;
use AF\Domain\Algorithm\Selection\TextKeySelectionAlgo;
use Classification\Domain\AxisMember;
use Classification\Domain\IndicatorAxis;
use Core\Annotation\Secure;

/**
 * Elements Controller
 * @package AF
 */
class AF_Datagrid_Edit_Algos_IndexationController extends UI_Controller_Datagrid
{

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::getelementsAction()
     * @Secure("editAF")
     */
    public function getelementsAction()
    {
        /** @var $algo NumericAlgo */
        $algo = NumericAlgo::load($this->getParam('idAlgo'));
        $contextIndicator = $algo->getContextIndicator();
        // S'il n'y a pas d'indicateur de défini on n'affiche rien dans le datagrid
        if ($contextIndicator != null) {
            foreach ($contextIndicator->getAxes() as $axis) {
                $data = [];
                $data['index'] = $axis->getId();
                $data['axis'] = $axis->getLabel();
                $index = $algo->getIndexForAxis($axis);
                if ($index) {
                    $data['type'] = $this->cellList(get_class($index));
                    if ($index instanceof FixedIndex) {
                        $member = $index->getClassificationMember();
                        if ($member) {
                            $data['value'] = $this->cellList($member->getRef());
                        }
                    } elseif ($index instanceof AlgoResultIndex) {
                        $valueAlgo = $index->getAlgo();
                        $data['value'] = $this->cellList($valueAlgo->getRef());
                    }
                } else {
                    $type = $this->getChosenIndexType($algo, $axis);
                    if ($type) {
                        $data['type'] = $this->cellList($type);
                    }
                }
                $this->addLine($data);
            }
        }
        $this->send();
    }

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::addelementAction()
     * @Secure("editAF")
     */
    public function addelementAction()
    {
        throw new Core_Exception_InvalidHTTPQuery("Invalid action");
    }

    /**
     * Modifie les valeurs d'un élément
     * cf. documentation
     * @Secure("editAF")
     */
    public function updateelementAction()
    {
        /** @var $algo NumericAlgo */
        $algo = NumericAlgo::load($this->getParam('idAlgo'));
        /** @var $axis IndicatorAxis */
        $axis = IndicatorAxis::load($this->update['index']);
        $newValue = $this->update['value'];
        switch ($this->update['column']) {
            case 'type':
                $index = $algo->getIndexForAxis($axis);
                if ($index) {
                    // Modification du type d'index
                    if ($newValue && $newValue != get_class($index)) {
                        // Suppression de l'ancien index
                        $algo->removeIndex($index);
                        $algo->save();
                        $this->entityManager->flush();
                    }
                }
                $this->setChosenIndexType($algo, $axis, $newValue);
                $this->data = $this->cellList($newValue);
                break;
            case 'value':
                $index = $algo->getIndexForAxis($axis);

                if ($index) {

                    $newType = $this->getChosenIndexType($algo, $axis);
                    // Modification du type d'index
                    if ($newType && $newType != get_class($index)) {
                        // Suppression de l'ancien index
                        $algo->removeIndex($index);
                        // Création du nouvel index
                        $index = new $newType($axis, $algo);
                        $algo->addIndex($index);
                        $algo->save();
                    }
                    // Modification de la valeur d'un index
                    if ($index instanceof FixedIndex) {
                        $newMember = AxisMember::loadByRefAndAxis($newValue, $axis);
                        $index->setClassificationMember($newMember);
                    } elseif ($index instanceof AlgoResultIndex) {
                        /** @var $newAlgo TextKeySelectionAlgo */
                        $newAlgo = TextKeySelectionAlgo::loadByRef($algo->getSet(), $newValue);
                        $index->setAlgo($newAlgo);
                    }

                } else {

                    // Création du nouvel index
                    $type = $this->getChosenIndexType($algo, $axis);
                    $index = new $type($axis, $algo);
                    $algo->addIndex($index);
                    $algo->save();
                    // Définition de la valeur de l'index
                    if ($index instanceof FixedIndex) {
                        $newMember = AxisMember::loadByRefAndAxis($newValue, $axis);
                        $index->setClassificationMember($newMember);
                    } elseif ($index instanceof AlgoResultIndex) {
                        /** @var $newAlgo TextKeySelectionAlgo */
                        $newAlgo = TextKeySelectionAlgo::loadByRef($algo->getSet(), $newValue);
                        $index->setAlgo($newAlgo);
                    }

                }
                $index->save();
                $this->entityManager->flush();
                break;
        }
        $this->message = __('UI', 'message', 'updated');
        $this->send();
    }

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::deleteelementAction()
     * @Secure("editAF")
     */
    public function deleteelementAction()
    {
        throw new Core_Exception_InvalidHTTPQuery("Invalid action");
    }

    /**
     * Retourne la liste des membres ou des algo en fonction du type d'indexation
     * @Secure("editAF")
     */
    public function getValueListAction()
    {
        /** @var $algo NumericAlgo */
        $algo = NumericAlgo::load($this->getParam('idAlgo'));
        /** @var $axis IndicatorAxis */
        $axis = IndicatorAxis::load($this->getParam('index'));
        $index = $algo->getIndexForAxis($axis);
        if ($index) {
            $type = get_class($index);
        } else {
            $type = $this->getChosenIndexType($algo, $axis);
        }
        switch ($type) {
            case FixedIndex::class:
                // Liste des membres de l'axe
                foreach ($axis->getMembers() as $member) {
                    $this->addElementList($member->getRef(), $member->getLabel());
                }
                break;
            case AlgoResultIndex::class:
                // Liste des algos de type sélection
                foreach ($algo->getSet()->getAlgos() as $algo) {
                    if ($algo instanceof TextKeySelectionAlgo) {
                        $this->addElementList($algo->getRef(), $algo->getRef());
                    }
                }
                break;
        }
        $this->send();
    }

    /**
     * Saves the index type that the user chosen, stored in session
     * @param Algo               $algo
     * @param IndicatorAxis $axis
     * @param string             $type
     * @return void
     */
    private function setChosenIndexType(Algo $algo, IndicatorAxis $axis, $type)
    {
        $session = new Zend_Session_Namespace(get_class());
        if (!isset($session->chosenType)) {
            /** @noinspection PhpUndefinedFieldInspection */
            $session->chosenType = [];
        }
        /** @noinspection PhpUndefinedFieldInspection */
        $session->chosenType[$algo->getRef()][$axis->getRef()] = $type;
    }

    /**
     * Returns the index type that the user chosen, stored in session
     * @param Algo               $algo
     * @param IndicatorAxis $axis
     * @return string|null
     */
    private function getChosenIndexType(Algo $algo, IndicatorAxis $axis)
    {
        $session = new Zend_Session_Namespace(get_class());
        if (isset($session->chosenType[$algo->getRef()][$axis->getRef()])) {
            return $session->chosenType[$algo->getRef()][$axis->getRef()];
        }
        return null;
    }

}