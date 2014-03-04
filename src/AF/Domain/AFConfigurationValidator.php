<?php

namespace AF\Domain;

use AF\Domain\Component\Component;
use AF\Domain\Algorithm\Algo;
use AF\Domain\Algorithm\AlgoConfigurationError;
use AF\Domain\Condition\Condition;

/**
 * Vérifie la configuration des AF.
 *
 * @author matthieu.napoli
 * @author hugo.charbonnier
 */
class AFConfigurationValidator
{
    /**
     * Méthode qui gère le controle de la configuration des AFs.
     * @param AF $af
     * @return AFConfigurationError[]
     */
    public function validateAF(AF $af)
    {
        return array_merge(
            $this->validateFormElements($af),
            $this->validateAlgos($af),
            $this->validateConditions($af)
        );
    }

    /**
     * Méthode qui gère le control de la configuration des champs d'un Af.
     * @param AF $af
     * @return AFConfigurationError[]
     */
    protected function validateFormElements(AF $af)
    {
        return $af->getRootGroup()->checkConfig();
    }

    /**
     * Méthode qui gère le control de la configuration des algos associés à un Af.
     * @param AF $af
     * @return AFConfigurationError[]
     */
    protected function validateAlgos(AF $af)
    {
        // Vérifie les algos de l'AF
        $errors = $this->toAFConfigErrors($this->getErrors($af->getAlgos()), $af);
        // Valide les algos des sous-AF
        foreach ($af->getSubAfList() as $subAF) {
            $calledAF = $subAF->getCalledAF();
            $errors = array_merge(
                $errors,
                $this->toAFConfigErrors($this->getErrors($calledAF->getAlgos()), $calledAF)
            );
        }
        return $errors;
    }

    /**
     * Méthode qui gère le control de la configuration des conditions associées à un Af.
     * @param AF $af
     * @return AFConfigurationError[]
     */
    protected function validateConditions(AF $af)
    {
        return $this->getErrors($af->getConditions());
    }

    /**
     * Méthode qui récupère les erreurs sur une liste d'éléments
     * @param Algo[]|Component[]|Condition[] $elementList
     * @return array
     */
    protected function getErrors($elementList)
    {
        $errors = [];
        foreach ($elementList as $element) {
            $errors = array_merge($errors, $element->checkConfig());
        }
        return $errors;
    }

    /**
     * @param AlgoConfigurationError[] $errors
     * @param AF                       $af
     * @return AFConfigurationError[]
     */
    protected function toAFConfigErrors(array $errors, AF $af)
    {
        $returnedArray = [];
        foreach ($errors as $error) {
            $returnedArray[] = $this->toAFConfigError($error, $af);
        }
        return $returnedArray;
    }

    /**
     * @param AlgoConfigurationError $error
     * @param AF                     $af
     * @return AFConfigurationError
     */
    protected function toAFConfigError(AlgoConfigurationError $error, AF $af)
    {
        return new AFConfigurationError($error->getMessage(), $error->getFatal(), $af);
    }
}
