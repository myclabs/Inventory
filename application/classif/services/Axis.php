<?php
/**
 * Classe Classif_Service_Axis
 * @author valentin.claras
 * @package    Classif
 * @subpackage Service
 */

/**
 * Service Axis.
 * @package    Classif
 * @subpackage Service
 */
class Classif_Service_Axis extends Core_Service
{
    /**
     * Renvoie l'instance Singleton de la classe.
     *
     * @return Classif_Service_Axis
     */
    public static function getInstance()
    {
        return parent::getInstance();
    }

    /**
     * Ajoute un Axis et le renvoie.
     *
     * @param string $ref
     * @param string $label
     * @param string $refParent
     *
     * @return Classif_Model_Axis
     */
    public function addService($ref, $label, $refParent=null)
    {
        $axis = new Classif_Model_Axis();

        if (empty($ref)) {
            $ref = $label;
        }
        $this->checkAxisRef($ref);

        $axis->setRef($ref);
        $axis->setLabel($label);
        if ($refParent != null) {
            $axis->setDirectNarrower(Classif_Model_Axis::loadByRef($refParent));
        }

        $axis->save();

        return $axis;
    }

    /**
     * Change la ref d'un Axis puis renvoie le label de ce dernier.
     *
     * @param string $axisRef
     * @param string $newRef
     *
     * @return string
     */
    public function updateRefService($axisRef, $newRef)
    {
        $axis = Classif_Model_Axis::loadByRef($axisRef);

        $this->checkAxisRef($newRef);

        $axis->setRef($newRef);

        return $axis->getLabel();
    }

    /**
     * Change le label d'un Axis puis renvoie ce dernier.
     *
     * @param string $axisRef
     * @param string $newLabel
     *
     * @return string
     */
    public function updateLabelService($axisRef, $newLabel)
    {
        $axis = Classif_Model_Axis::loadByRef($axisRef);

        $axis->setLabel($newLabel);

        return $axis->getLabel();
    }

    /**
     * Change le label d'un Axis puis renvoie ce dernier.
     *
     * @param string $axisRef
     * @param string $newRef
     * @param string $newLabel
     *
     * @return string
     */
    public function updateRefAndLabelService($axisRef, $newRef, $newLabel)
    {
        $axis = Classif_Model_Axis::loadByRef($axisRef);

        $this->checkAxisRef($newRef);

        $axis->setRef($newRef);
        $axis->setLabel($newLabel);

        return $axis->getLabel();
    }

    /**
     * Change le DirectNarrower d'un Axis puis renvoie ce dernier.
     *
     * @param string $axisRef
     * @param string $newParentRef
     * @param string $newPosition
     *
     * @return string
     */
    public function updateParentService($axisRef, $newParentRef, $newPosition=null)
    {
        $axis = Classif_Model_Axis::loadByRef($axisRef);

        if ($newParentRef === null) {
            $axis->setDirectNarrower();
        } else {
            $axis->setDirectNarrower(Classif_Model_Axis::loadByRef($newParentRef));
        }
        if ($newPosition !== null) {
            $axis->setPosition($newPosition);
        }

        return $axis->getLabel();
    }

    /**
     * Change la position d'un Axis puis renvoie ce dernier.
     *
     * @param string $axisRef
     * @param int $newPosition
     *
     * @return string
     */
    public function updatePositionService($axisRef, $newPosition)
    {
        $axis = Classif_Model_Axis::loadByRef($axisRef);

        $axis->setPosition($newPosition);

        return $axis->getLabel();
    }

    /**
     * Supprime un Axis.
     *
     * @param string $axisRef Ref du axis
     *
     * @return string Le label du Axis.
     */
    public function deleteService($axisRef)
    {
        $axis = Classif_Model_Axis::loadByRef($axisRef);

        if ($axis->hasDirectBroaders()) {
            throw new Core_Exception_User('Classif', 'axis', 'axisHasDirectBroaders');
        }
        if ($axis->hasMembers()) {
            throw new Core_Exception_User('Classif', 'axis', 'axisHasMembers');
        }
        foreach (Classif_Model_ContextIndicator::loadList() as $contextIndicator) {
            if ($contextIndicator->hasAxis($axis)) {
                throw new Core_Exception_User('Classif', 'axis', 'axisIsUsedInContextIndicator');
            }
        }

        $axis->delete();

        return $axis->getLabel();
    }

    /**
     * Renoie les messages d'erreur concernant la validation d'une ref.
     *
     * @param string $ref
     *
     * @return mixed string null
     */
    public function getErrorMessageForNewRef($ref)
    {
        try {
            Core_Tools::checkRef($ref);
        } catch (Core_Exception_User $e) {
            return $e->getMessage();
        }
        $queryRefUsed = new Core_Model_Query();
        $queryRefUsed->filter->addCondition(Classif_Model_Axis::QUERY_REF, $ref);
        if (Classif_Model_Axis::countTotal($queryRefUsed) > 0) {
            return __('UI', 'formValidation', 'alreadyUsedIdentifier');
        }

        return null;
    }

    /**
     * Vérifie la disponibilité d'une référence pour un Axis.
     *
     * @param string $ref
     *
     * @throws Core_Exception_User
     */
    protected function checkAxisRef($ref)
    {
        Core_Tools::checkRef($ref);
        $queryRefUsed = new Core_Model_Query();
        $queryRefUsed->filter->addCondition(Classif_Model_Axis::QUERY_REF, $ref);
        if (Classif_Model_Axis::countTotal($queryRefUsed) > 0) {
            throw new Core_Exception_User('UI', 'formValidation', 'alreadyUsedIdentifier');
        }
    }

}