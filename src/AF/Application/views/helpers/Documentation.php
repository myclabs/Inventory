<?php

use AF\Domain\AF;
use AF\Domain\Component\SubAF\RepeatedSubAF;
use AF\Domain\Algorithm\Numeric\NumericParameterAlgo;
use MyCLabs\MUIH\Collapse;
use Parameter\Domain\Family\Family;

/**
 * Génère la documentation d'un AF.
 *
 * @author matthieu.napoli
 * @author yoann.croizer
 */
class AF_View_Helper_Documentation extends Zend_View_Helper_Abstract
{
    /**
     * Renvoi le html à afficher dans l'onglet documentation
     * @param AF $af
     * @return string
     */
    public function documentation(AF $af)
    {
        $html = $this->renderAf($af, __('AF', 'inputDocumentation', 'masterForm'));

        foreach ($af->getSubAfList() as $subAFComponent) {
            $repeatedSubAf = ($subAFComponent instanceof RepeatedSubAF);

            $html .= $this->renderAf($subAFComponent->getCalledAF(), $subAFComponent->getLabel(), $repeatedSubAf);
        }

        return $html;
    }

    /**
     * Rend le html pour un AF
     * @param AF     $af
     * @param string $title
     * @param bool   $repeatedSubAf
     * @return string
     */
    protected function renderAf(AF $af, $title, $repeatedSubAf = false)
    {
        $collapse = new Collapse($af->getId(), $title);

        // Lien vers la saisie en test
        $html = __('AF', 'inputDocumentation', 'calledForm') . ' ' . $af->getLabel();
        if ($repeatedSubAf) {
            $html .= ' (' . __('AF', 'inputDocumentation', 'repeated') . ')';
        }
        $html .= '<br />';

        // Documentation du formulaire
        $html .= Core_Tools::textile($af->getDocumentation());

        // Liste des paramètres de la famille avec un lien vers la famille en consultation
        $families = $this->getFamilyList($af);
        $html .= '<p>'. __('AF', 'inputDocumentation', 'usedParameterFamilies');
        if (count($families) == 0) {
            $html .= '<em> ' . __('UI', 'other', 'noneFem') . '.</em>';
        } else {
            $html .= '<ul>';
            foreach ($families as $family) {
                $url = $this->view->baseUrl("parameter/family/details/id/" . $family->getId());
                $html .= "<li><a href=\"$url\" target=\"_blank\">{$family->getLabel()}</a></li>";
            }
            $html .= '</ul>';
        }
        $html .= "</p>";

        $collapse->setContent($html);

        return $collapse->render();
    }

    /**
     * Permet de récupérer la liste des familles de paramètres de parameter
     * @param AF $af
     * @return Family[]
     */
    protected function getFamilyList(AF $af)
    {
        $families = [];
        foreach ($af->getAlgos() as $algo) {
            if ($algo instanceof NumericParameterAlgo) {
                $families[] = $algo->getFamily();
            }
        }
        return $families;
    }
}
