<?php

use DI\Container;
use Doctrine\Common\Cache\Cache;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\Translator;

/**
 * Gère la traduction.
 */
class Core_Translate
{
    const CACHE_NAMESPACE = '/translations/';

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Cache
     */
    private $cache;

    public function __construct(Translator $translator, LoggerInterface $logger, Cache $cache)
    {
        $this->translator = $translator;
        $this->logger = $logger;
        $this->cache = $cache;
    }

    /**
     * Va chercher le texte correspondant à la référence dans la langue voulue.
     *
     * @param string $package      Nom du module d'où est issue la traducation.
     * @param string $file         Nom du fichier tmx dans lequel chercher la traduction.
     * @param string $ref          Référence du texte (datagrids, champs etc...)
     * @param array  $replacements (optional) Tableau de remplacement à effectuer,
     *  ces remplacement prennent la forme suivante : array('RECHERCHE' => 'remplacement').
     * @param string $locale
     *
     * @return string Traduction.
     */
    public function get($package, $file, $ref, $replacements = [], $locale = null)
    {
        $id = $package . '.' . $file . '.' . $ref;

        $message = $this->getFromCache($id, $replacements, $locale);

        if ($message === false) {
            $adaptedReplacements = [];
            foreach ($replacements as $key => $value) {
                $newKey = '[' . $key . ']';
                $adaptedReplacements[$newKey] = $value;
            }
            $message = $this->translator->trans($id, $adaptedReplacements, null, $locale);

            $this->saveToCache($id, $replacements, $locale, $message);
        }

        if ($message === $id) {
            $this->logger->warning('Missing translation: ' . $id);
        }

        return $message;
    }

    /**
     * Exporte une traduction dans le gestionnaire de traduction Javascript
     * @param string $package Nom du module d'où est issue la traducation.
     * @param string $file    Nom du fichier tmx dans lequel chercher la traduction.
     * @param string $ref     Référence du texte (datagrids, champs etc...)
     *
     * @return string Code javascript
     */
    public static function exportJS($package, $file, $ref)
    {
        /** @var Container $container */
        $container = Zend_Registry::get('container');
        /** @var Core_Translate $translate */
        $translate = $container->get('Core_Translate');

        $message = $translate->get($package, $file, $ref);

        // Encode pour échapper tous les caractères qui pourraient casser la chaine de caractère JS
        return sprintf(
            'Translate.addTranslation(%s, %s, %s, %s);',
            json_encode($package),
            json_encode($file),
            json_encode($ref),
            json_encode($message)
        );
    }

    private function getFromCache($id, $replacements, $locale)
    {
        $key = $this->buildCacheKey($id, $replacements, $locale);

        return $this->cache->fetch($key);
    }

    private function saveToCache($id, $replacements, $locale, $message)
    {
        $key = $this->buildCacheKey($id, $replacements, $locale);

        $this->cache->save($key, $message);
    }

    private function buildCacheKey($id, $replacements, $locale)
    {
        if (count($replacements) === 0) {
            return $locale . '-' . $id;
        }

        $replacementsHash = crc32(implode('', $replacements));

        return sprintf('%s-%s-%u', $locale, $id, $replacementsHash);
    }
}
