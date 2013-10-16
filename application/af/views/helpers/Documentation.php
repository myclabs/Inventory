<?php
/**
 * Génère la documentation d'un AF
 * @author     matthieu.napoli
 * @author     yoann.croizer
 * @package    AF
 * @subpackage View
 */
use Techno\Domain\Family\Family;

/**
 * @package    AF
 * @subpackage View
 */
class AF_View_Helper_Documentation extends Zend_View_Helper_Abstract
{

    /**
     * Renvoi le html à afficher dans l'onglet documentation
     * @param AF_Model_AF $af
     * @return string
     */
    public function documentation(AF_Model_AF $af)
    {
        $html = $this->renderAf($af, __('AF', 'inputDocumentation', 'masterForm'));

        foreach ($af->getSubAfList() as $subAFComponent) {
            $repeatedSubAf = ($subAFComponent instanceof AF_Model_Component_SubAF_Repeated);

            $html .= $this->renderAf($subAFComponent->getCalledAF(), $subAFComponent->getLabel(), $repeatedSubAf);
        }

        return $html;
    }

    /**
     * Rend le html pour un AF
     * @param AF_Model_AF $af
     * @param string      $title
     * @param bool        $repeatedSubAf
     * @return string
     */
    protected function renderAf(AF_Model_AF $af, $title, $repeatedSubAf = false)
    {
        $collapse = new UI_HTML_Collapse($af->getRef(), $title);

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
                $url = $this->view->baseUrl("techno/family/details/id/" . $family->getId());
                $html .= "<li><a href=\"$url\" target=\"_blank\">{$family->getLabel()}</a></li>";
            }
            $html .= '</ul>';
        }
        $html .= "</p>";

        $collapse->body = $html;

        return $collapse->render();
    }

    /**
     * Permet de récupérer la liste des familles de paramètres de techno
     * @param AF_Model_Af $af
     * @return Family[]
     */
    protected function getFamilyList(AF_Model_Af $af)
    {
        $families = [];
        foreach ($af->getAlgos() as $algo) {
            if ($algo instanceof Algo_Model_Numeric_Parameter) {
                $families[] = $algo->getFamily();
            }
        }
        return $families;
    }

}
