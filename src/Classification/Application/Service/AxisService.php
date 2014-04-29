<?php

namespace Classification\Application\Service;

use Classification\Domain\ClassificationLibrary;
use Classification\Domain\ContextIndicator;
use Classification\Domain\Axis;
use Core\Translation\TranslatedString;
use Core_Exception_NotFound;
use Core_Exception_User;
use Core_Tools;
use Mnapoli\Translated\TranslationHelper;

/**
 * Service Axis.
 *
 * @author valentin.claras
 */
class AxisService
{
    /**
     * @var TranslationHelper
     */
    private $translationHelper;

    public function __construct(TranslationHelper $translationHelper)
    {
        $this->translationHelper = $translationHelper;
    }

    /**
     * Ajoute un Axis et le renvoie.
     *
     * @param ClassificationLibrary $library
     * @param string                $ref
     * @param string                $label
     * @param string                $idParent
     *
     * @return Axis
     */
    public function add(ClassificationLibrary $library, $ref, $label, $idParent = null)
    {
        $axis = new Axis($library);

        if (empty($ref)) {
            $ref = $label;
        }
        $this->checkAxisRef($library, $ref);

        $axis->setRef($ref);
        $this->translationHelper->set($axis->getLabel(), $label);
        if (!empty($idParent)) {
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
     * @return TranslatedString
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
     * @return TranslatedString
     */
    public function updateLabel($axisId, $newLabel)
    {
        $axis = Axis::load($axisId);

        $this->translationHelper->set($axis->getLabel(), $newLabel);

        return $axis->getLabel();
    }

    /**
     * Change le label d'un Axis puis renvoie ce dernier.
     *
     * @param string $axisId
     * @param string $newRef
     * @param string $newLabel
     *
     * @return TranslatedString
     */
    public function updateRefAndLabel($axisId, $newRef, $newLabel)
    {
        $axis = Axis::load($axisId);

        $this->checkAxisRef($axis->getLibrary(), $newRef);

        $axis->setRef($newRef);
        $this->translationHelper->set($axis->getLabel(), $newLabel);

        return $axis->getLabel();
    }

    /**
     * Change le DirectNarrower d'un Axis puis renvoie ce dernier.
     *
     * @param string $axisId
     * @param string $newParentRef
     * @param string $newPosition
     *
     * @return TranslatedString
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
     * @return TranslatedString
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
     * @return TranslatedString Le label du Axis.
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
