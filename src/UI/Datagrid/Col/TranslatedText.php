<?php

use Mnapoli\Translated\Translator;

/**
 * Colonne contenant des textes traduits.
 */
class UI_Datagrid_Col_TranslatedText extends UI_Datagrid_Col_Text
{
    public function getFullFilterName($datagrid)
    {
        /** @var Translator $translator */
        $translator = \Core\ContainerSingleton::getContainer()->get(Translator::class);

        return $this->entityAlias . '.' . $this->filterName . '.' . $translator->getLanguage();
    }
}
