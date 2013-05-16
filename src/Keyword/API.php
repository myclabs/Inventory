<?php
/**
 * Classe Keyword_API
 * @author valentin.claras
 * @author bertrand.ferry
 * @package    Keyword
 * @subpackage API
 */

/**
 * Classe metier de APIKeyword.
 * Design pattern multiton.
 * @package    Keyword
 * @subpackage API
 */
class Keyword_API
{
    /**
     * Referent textuel du Keyword.
     *
     * @var string
     */
    protected $ref = '';

    /**
     * Keyword utilisé par l'API.
     *
     * @var Keyword_Model_Keyword
     */
    protected $keyword;


    /**
     * Constructeur.
     *
     * @param string $ref
     */
    public function __construct($ref='')
    {
        if ($ref instanceof Keyword_Model_Keyword) {
            $this->keyword = $ref;
            $this->ref = $ref->getRef();
        } else if ($ref !== '') {
            $this->ref = $ref;
        }
    }

    /**
     * Charge le keyword, seulement s'il n'est pas déjà chargé.
     *
     * @return Keyword_Model_Keyword
     */
    protected function getKeyword()
    {
        if (is_null($this->keyword)) {
            $this->keyword = Keyword_Model_Keyword::loadByRef($this->ref);
        }
        return $this->keyword;
    }

    /**
     * Retourne le label du mot-cle (ou null si le mot-cle n'est pas valide).
     *
     * @return string Label
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * Retourne le label du mot-cle (ou null si le mot-cle n'est pas valide).
     *
     * @return string Label
     */
    public function getLabel()
    {
        return $this->getKeyword()->getLabel();
    }

    /**
     * Renvoie une liste de Keyword_API en fonction d'une requête donnée.
     *
     * @param string $expressionQuery
     *
     * @return Keyword_API[]
     */
    public static function getKeywordsByQuery($expressionQuery)
    {
        $keywords = array();

        self::checkExpressionQuery($expressionQuery);
        foreach (Keyword_Model_Keyword::loadListMatchingQuery($expressionQuery) as $keyword) {
            $keywords[] = new self($keyword);
        }

        return $keywords;
    }

    /**
     * Vérifie qu'une expressionQuery est bien écrite.
     *
     * @param string $expressionQuery
     *
     * @throws Core_Exception_User
     */
    protected static function checkExpressionQuery($expressionQuery)
    {
        $expressionQuery = str_replace(' ', '', $expressionQuery);
        if (preg_match('#^[\|\&]|[\|\&]$#', $expressionQuery)) {
            throw new Core_Exception_User('Keyword', 'query', 'expressionQueryStartEndOperator',
                    array('QUERY' => $expressionQuery)
                );
        }

        if (strpos($expressionQuery, '|')) {
            $orSubQueries = explode('|', $expressionQuery);
            foreach ($orSubQueries as $orSubQuery) {
                self::checkExpressionQuery($orSubQuery);
            }
        } else if (strpos($expressionQuery, '&')) {
            $andSubQueries = explode('&', $expressionQuery);
            foreach ($andSubQueries as $andSubQuery) {
                self::checkExpressionQuery($andSubQuery);
            }
        } else {
            if (count(explode(',', $expressionQuery)) !== 3) {
                throw new Core_Exception_User(
                            'Keyword', 'query', 'elementaryConditionNeeds3Parts',
                            array('QUERY' => $expressionQuery)
                        );
            }
            if (preg_match_all('#^this,|,this$#', $expressionQuery) !== 1) {
                throw new Core_Exception_User(
                            'Keyword', 'query', 'elementaryConditionNeedsOneThisSubjectObject',
                            array('QUERY' => $expressionQuery)
                        );
            }
        }
    }

}