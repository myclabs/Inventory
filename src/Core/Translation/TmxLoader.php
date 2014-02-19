<?php

namespace Core\Translation;

use SimpleXMLElement;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * Loads .tmx translation files for the Symfony translator.
 */
class TmxLoader implements LoaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function load($resource, $locale, $domain = 'messages')
    {
        $messageCatalogue = new MessageCatalogue($locale);

        // Load every .tmx file
        $finder = new Finder();
        $finder->files()->name('*.tmx')->in($resource);
        foreach ($finder as $file) {
            $messages = $this->readFile($file, $locale);

            $messageCatalogue->add($messages, $domain);
        }

        return $messageCatalogue;
    }

    /**
     * Charge un fichier .tmx
     *
     * @param SplFileInfo $file
     * @param string      $locale
     *
     * @return array
     */
    private function readFile(SplFileInfo $file, $locale)
    {
        $messages = [];

        $dirName = basename($file->getPath());
        $fileName = basename($file->getFilename(), '.tmx');

        $xml = new SimpleXMLElement($file->getContents());

        foreach ($xml->body->tu as $tu) {
            $ref = (string) $tu->attributes()->tuid;

            // Forme l'ID du message en concaténant avec des "."
            $id = $dirName . '.' . $fileName . '.' . $ref;

            foreach ($tu->tuv as $tuv) {
                if ((string) $tuv->attributes('xml', true)->lang === $locale) {
                    $messages[$id] = (string) $tuv->seg;
                }
            }
        }

        return $messages;
    }
}
