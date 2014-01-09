<?php

use DI\Container;

/**
 * Alias de la fonction statique de traduction.
 * Permet de gagner du temps dans les vues.
 *
 * @param string $package      Nom du module d'où est issue la traducation.
 * @param string $file         Nom du fichier tmx dans lequel chercher la traduction.
 * @param string $ref          Référence du texte (datagrids, champs etc...)
 * @param array  $replacements (optionnel) Tableau de remplacement à effectuer,
 *                             ces remplacement prennent la forme suivante : array('RECHERCHE' => 'remplacement').
 *
 * @return string Texte traduit
 */
function __($package, $file, $ref, array $replacements = [])
{
    /** @var Core_Translate $translate */
    $translate = \Core\ContainerSingleton::getContainer()->get('Core_Translate');

    // Force la locale par défaut, sinon Zend ne prend pas en compte les changements en cours d'exécution de l'appli
    $locale = Core_Locale::loadDefault()->getId();

    return $translate->get($package, $file, $ref, $replacements, $locale);
}
