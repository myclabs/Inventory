<?php
/**
 * @author     matthieu.napoli
 * @package    Core
 * @subpackage Tool
 */

use Netcarver\Textile;

/**
 * Classe regroupant des fonctions utilitaires transverses.
 *
 * Toutes ses méthodes sont statiques pour des raisons de simplicité.
 *
 * @package    Core
 * @subpackage Tool
 */
abstract class Core_Tools
{

    /**
     * Instance de la classe textile.
     * @var Textile
     */
    private static $textile = null;

    /**
     * Dump d'une variable.
     *
     * Utilise Core_Error_Log::dump en simplifiant l'utilisation.
     *
     * @codeCoverageIgnore
     *
     * @param mixed $var Variable à afficher.
     *
     * @return void
     */
    public static function dump($var)
    {
        Core_Error_Log::getInstance()->dump($var);
    }

    /**
     * Génère une chaine de caractère aléatoire.
     *
     * La chaine générée a une longueur maximale de 32 caractères et ne contient que
     * des caractères alphanumériques.
     *
     * @param string $nbCaracteres Nombre de caractères de la chaine générée, valeur par défaut, et maximum : 32.
     *
     * @return string Chaine de caractère.
     */
    public static function generateString($nbCaracteres = 32)
    {
        $caracteres = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        $longueur = mb_strlen($caracteres);
        srand((double) microtime() * 1000000);
        $chaine = '';
        for ($i = 0; $i < $nbCaracteres; $i++) {
            $chaine .= $caracteres[rand(0, $longueur - 1)];
        }
        return $chaine;
    }

    /**
     * Vérifie qu'une chaîne donnée remplit les critères d'une référence.
     *
     * @param string $ref
     *
     * @throws Core_Exception_User
     */
    public static function checkRef($ref)
    {
        if (empty($ref)) {
            throw new Core_Exception_User('Core', 'exception', 'emptyRequiredField');
        } elseif (!preg_match('#^[a-z0-9_]+$#', $ref)) {
            throw new Core_Exception_User('Core', 'exception', 'unauthorizedRef');
        }

        return true;
    }

    /**
     * Applique les regles de construction des referents textuels a la chaine passee en parametre, puis la renvoie.
     *
     * @link http://dev.myc-sense.com/wiki/index.php/R%C3%A9f%C3%A9rents_textuels
     * @param string $text Chaine a remettre en forme.
     *
     * @return string
     */
    public static function refactor($text)
    {
        if ($text == null || $text == '') {
            return '';
        }

        $old = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï',
                     'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á',
                     'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò',
                     'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą',
                     'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ',
                     'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ',
                     'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ',
                     'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ',
                     'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ',
                     'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ',
                     'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ',
                     'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ',
                     'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ',
                     'ǽ', 'Ǿ', 'ǿ',);

        $new = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I',
                     'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a',
                     'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o',
                     'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A',
                     'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E',
                     'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H',
                     'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J',
                     'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N',
                     'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r',
                     'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't',
                     'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y',
                     'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I',
                     'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE',
                     'ae', 'O', 'o',);

        $toUnderscore = array(' ', '\'', '-',);

        $text = str_replace($old, $new, $text);
        $text = str_replace($toUnderscore, '_', $text);

        $text = preg_replace('#[^\w\d_]#', '', $text);
        $text = preg_replace('#_+#', '_', $text);

        $text = trim($text, '_');

        return strtolower($text);
    }

    /**
     * Convertit une chaîne texte en chaîne html.
     * @param string $text
     * @return string
     */
    public static function textile($text)
    {
        if (self::$textile === null) {
            self::$textile = new Textile\Parser();
        }
        return self::$textile->textileThis($text);
    }

    /**
     * Retire les balises textiles d'une chaîne texte.
     * @param string $text
     * @return string
     */
    public static function removeTextileMarkUp($text)
    {
        return $text;
    }

    /**
     * Tronque une chaine de caractère si elle est trop longue et ajoute … à la fin si c'est le cas
     *
     * @param string $str
     * @param int    $size Taille max de la chaine renvoyée
     *
     * @return string
     */
    public static function truncateString($str, $size)
    {
        if (strlen($str) > $size) {
            return substr($str, 0, $size - 1) . "…";
        }
        return $str;
    }

    /**
     * Transforme la première lettre en majuscule
     *
     * Équivalent de ucfirst, mais supporte UTF-8 (mb_ucfirst n'existe pas)
     *
     * @param string $str
     * @return string
     */
    public static function ucFirst($str)
    {
        return mb_strtoupper(mb_substr($str, 0, 1)) . mb_substr($str, 1);
    }

}
