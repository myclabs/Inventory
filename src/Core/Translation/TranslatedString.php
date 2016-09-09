<?php

namespace Core\Translation;

use Mnapoli\Translated\AbstractTranslatedString;
use Mnapoli\Translated\Translator;

class TranslatedString extends AbstractTranslatedString
{
    public $fr;
    public $en;

    /**
     * Joins strings into a single one.
     * Fix: use the translator fallback languages
     *
     * @param array $strings Array containing strings or AbstractTranslatedString
     *
     * @throws \InvalidArgumentException The array must contain null, string or AbstractTranslatedString
     *
     * @return AbstractTranslatedString
     */
    public static function join(array $strings)
    {
        /** @var AbstractTranslatedString $result */
        $result = new static();

        $container = \Core\ContainerSingleton::getContainer();
        $translator = $container->get(Translator::class);

        foreach ($result->getLanguages() as $language) {
            $s = '';
            foreach ($strings as $string) {
                if (! (is_null($string) || is_string($string) || $string instanceof AbstractTranslatedString)) {
                    throw new \InvalidArgumentException(sprintf(
                        'Arguments must be of type string or AbstractTranslatedString, %s given',
                        is_object($string) ? get_class($string) : gettype($string)
                    ));
                }

                if ($string instanceof AbstractTranslatedString) {

                    $s .= $string->get($language, $translator->getFallbacks($language));
                } else {
                    $s .= $string;
                }
            }
            $result->set($s, $language);
        }

        return $result;
    }
}
