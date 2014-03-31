<?php

namespace Classification\Application\Service;

use Classification\Domain\ContextIndicator;
use Classification\Domain\Axis;
use Core_Exception_User;
use Core_Model_Query;
use Core_Tools;

/**
 * Service Axis.
 *
 * @author valentin.claras
 */
class IndicatorAxisService
{
    /**
     * Ajoute un Axis et le renvoie.
     *
     * @param string $ref
     * @param string $label
     * @param string $refParent
     *
     * @return Axis
     */
    public function add($ref, $label, $refParent = null)
    {
        $axis = new Axis();

        if (empty($ref)) {
            $ref = $label;
        }
        $this->checkAxisRef($ref);

        $axis->setRef($ref);
        $axis->setLabel($label);
        if ($refParent != null) {
            $axis->setDirectNarrower(Axis::loadByRef($refParent));
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
    public function updateRef($axisRef, $newRef)
    {
        $axis = Axis::loadByRef($axisRef);

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
    public function updateLabel($axisRef, $newLabel)
    {
        $axis = Axis::loadByRef($axisRef);

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
    public function updateRefAndLabel($axisRef, $newRef, $newLabel)
    {
        $axis = Axis::loadByRef($axisRef);

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
    public function updateParent($axisRef, $newParentRef, $newPosition = null)
    {
        $axis = Axis::loadByRef($axisRef);

        if ($newParentRef === null) {
            $axis->setDirectNarrower();
        } else {
            $axis->setDirectNarrower(Axis::loadByRef($newParentRef));
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
     * @param int    $newPosition
     *
     * @return string
     */
    public function updatePosition($axisRef, $newPosition)
    {
        $axis = Axis::loadByRef($axisRef);

        $axis->setPosition($newPosition);

        return $axis->getLabel();
    }

    /**
     * Supprime un Axis.
     *
     * @param string $axisRef Ref du axis
     *
     * @throws Core_Exception_User
     * @return string Le label du Axis.
     */
    public function delete($axisRef)
    {
        $axis = Axis::loadByRef($axisRef);

        if ($axis->hasDirectBroaders()) {
            throw new Core_Exception_User('Classification', 'axis', 'axisHasDirectBroaders');
        }
        if ($axis->hasMembers()) {
            throw new Core_Exception_User('Classification', 'axis', 'axisHasMembers');
        }
        foreach (ContextIndicator::loadList() as $contextIndicator) {
            /** @var ContextIndicator $contextIndicator */
            if ($contextIndicator->hasAxis($axis)) {
                throw new Core_Exception_User('Classification', 'axis', 'axisIsUsedInContextIndicator');
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
        $queryRefUsed->filter->addCondition(Axis::QUERY_REF, $ref);
        if (Axis::countTotal($queryRefUsed) > 0) {
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
    private function checkAxisRef($ref)
    {
        Core_Tools::checkRef($ref);
        $queryRefUsed = new Core_Model_Query();
        $queryRefUsed->filter->addCondition(Axis::QUERY_REF, $ref);
        if (Axis::countTotal($queryRefUsed) > 0) {
            throw new Core_Exception_User('UI', 'formValidation', 'alreadyUsedIdentifier');
        }
    }
}
