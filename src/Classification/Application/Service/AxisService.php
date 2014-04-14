<?php

namespace Classification\Application\Service;

use Classification\Domain\ClassificationLibrary;
use Classification\Domain\ContextIndicator;
use Classification\Domain\Axis;
use Core_Exception_NotFound;
use Core_Exception_User;
use Core_Tools;

/**
 * Service Axis.
 *
 * @author valentin.claras
 */
class AxisService
{
    /**
     * Ajoute un Axis et le renvoie.
     *
     * @param \Classification\Domain\ClassificationLibrary $library
     * @param string $ref
     * @param string $label
     * @param string $idParent
     *
     * @return Axis
     */
    public function add(ClassificationLibrary $library, $ref, $label, $idParent = null)
    {
        $axis = new Axis($library);

        if (empty($ref)) {
            $ref = $label;
        }
        $this->checkAxisRef($axis->getLibrary(), $ref);

        $axis->setRef($ref);
        $axis->setLabel($label);
        if ($idParent != null) {
            $axis->setDirectNarrower(Axis::load($idParent));
        }

        $axis->save();

        return $axis;
    }

    /**
     * Change la ref d'un Axis puis renvoie le label de ce dernier.
     *
     * @param string $axisId
     * @param string $newRef
     *
     * @return string
     */
    public function updateRef($axisId, $newRef)
    {
        $axis = Axis::load($axisId);

        $this->checkAxisRef($axis->getLibrary(), $newRef);

        $axis->setRef($newRef);

        return $axis->getLabel();
    }

    /**
     * Change le label d'un Axis puis renvoie ce dernier.
     *
     * @param string $axisId
     * @param string $newLabel
     *
     * @return string
     */
    public function updateLabel($axisId, $newLabel)
    {
        $axis = Axis::load($axisId);

        $axis->setLabel($newLabel);

        return $axis->getLabel();
    }

    /**
     * Change le label d'un Axis puis renvoie ce dernier.
     *
     * @param string $axisId
     * @param string $newRef
     * @param string $newLabel
     *
     * @return string
     */
    public function updateRefAndLabel($axisId, $newRef, $newLabel)
    {
        $axis = Axis::load($axisId);

        $this->checkAxisRef($axis->getLibrary(), $newRef);

        $axis->setRef($newRef);
        $axis->setLabel($newLabel);

        return $axis->getLabel();
    }

    /**
     * Change le DirectNarrower d'un Axis puis renvoie ce dernier.
     *
     * @param string $axisId
     * @param string $newParentRef
     * @param string $newPosition
     *
     * @return string
     */
    public function updateParent($axisId, $newParentRef, $newPosition = null)
    {
        $axis = Axis::load($axisId);

        if ($newParentRef === null) {
            $axis->setDirectNarrower();
        } else {
            $axis->setDirectNarrower(Axis::load($newParentRef));
        }
        if ($newPosition !== null) {
            $axis->setPosition($newPosition);
        }

        return $axis->getLabel();
    }

    /**
     * Change la position d'un Axis puis renvoie ce dernier.
     *
     * @param string $axisId
     * @param int    $newPosition
     *
     * @return string
     */
    public function updatePosition($axisId, $newPosition)
    {
        $axis = Axis::load($axisId);

        $axis->setPosition($newPosition);

        return $axis->getLabel();
    }

    /**
     * Supprime un Axis.
     *
     * @param string $axisId
     *
     * @throws Core_Exception_User
     * @return string Le label du Axis.
     */
    public function delete($axisId)
    {
        $axis = Axis::load($axisId);

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
     * @param string $libraryId
     * @param string $ref
     *
     * @return mixed string null
     */
    public function getErrorMessageForNewRef($libraryId, $ref)
    {
        try {
            Core_Tools::checkRef($ref);
        } catch (Core_Exception_User $e) {
            return $e->getMessage();
        }
        try {
            $library = ClassificationLibrary::load($libraryId);
            $library->getAxisByRef($ref);
            return __('UI', 'formValidation', 'alreadyUsedIdentifier');
        } catch (Core_Exception_NotFound $e) {
            // Ref utilisable
        }

        return null;
    }

    /**
     * Vérifie la disponibilité d'une référence pour un Axis.
     *
     * @param string $libraryId
     * @param string $ref
     *
     * @throws Core_Exception_User
     */
    private function checkAxisRef($libraryId, $ref)
    {
        Core_Tools::checkRef($ref);
        try {
            $library = ClassificationLibrary::load($libraryId);
            $library->getAxisByRef($ref);
            throw new Core_Exception_User('UI', 'formValidation', 'alreadyUsedIdentifier');
        } catch (Core_Exception_NotFound $e) {
            // Ref utilisable
        }
    }
}
