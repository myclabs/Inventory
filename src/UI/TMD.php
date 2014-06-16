<?php
use MyCLabs\MUIH\Modal;

/**
 * Fichier de la classe TMD.
 *
 * @author     valentin.claras
 * @package    UI
 * @subpackage Tmd
 */

/**
 * Description of TMD.
 *
 * Une classe permettant de génèrer un Tableau mutlidimensionnel très simplement.
 *
 * @package    UI
 * @subpackage Tmd
 */
class UI_TMD extends UI_Generic
{
    /**
     * Définition du contenu d'une cellule vide.
     *
     * @var   string
     */
    public $textEmptyCell = null;

    /**
     * Définition du contenu d'une cellule pleine.
     *
     * @var   string
     */
    public $formatCells = null;

    /**
     * Définition des données de la cellules envoyées au popup.
     *  Tableau des index du tableau des données de la cellule.
     *
     * @var   array
     */
    public $dataCellsSent = null;

    /**
     * Identifiant unique du Tmd.
     *
     * @var   string
     */
    public $id = null;

    /**
     * Tableau des index des lignes du TMD.
     *
     * @var   array
     *
     * @see addDimensionIndexLine
     */
    protected $_lineDimensions = array();

    /**
     * Tableau des index des colonnes du TMD.
     *
     * @var   array
     *
     * @see addDimensionIndexCol
     */
    protected $_columnDimensions = array();

    /**
     * Tableau des cellules.
     *
     * Ce Tableau est un tableau multi-dimensionnel représentant les données sous la forme :
     *  tableau[dimLigne0][..][dimLigneN][dimCol0][..][dimColM] = array("formatx" => valeur)
     *
     * @var   array
     */
    public $cells = array();

    /**
     * Url permettant de remplir le contenu du popupAjax de détail.
     * Le popup s'ouvrira sur les cellules pleines.
     * Par défaut null.
     *
     * @var   string
     */
    public $urlDetailsCell = null;

    /**
     * Url permettant de remplir le contenu du popupAjax de création.
     * Le popup s'ouvrira sur les cellules pleines.
     * Par défaut null.
     *
     * @var   string
     */
    public $urlAddCell = null;

    /**
     * Popup affiché lorsque l'option de détail est activé.
     *
     * @var Modal
     */
    public $detailPanel = null;

    /**
     * Popup affiché lorsque l'option d'ajout est est activé.
     *
     * @var Modal
     */
    public $addPanel = null;

    /**
     * Callback à appeler pour récupérer la valeur d'une cellule
     * Prend en paramètre un tableau de coordonnées
     *
     * @var callable
     */
    public $cellValueCallback;


    /**
     * Constructeur de la classe Tmd.
     *
     * @param string $id Identifiant unique du tmd.
     *
     */
    public function  __construct($id)
    {
        $this->id = $id;

        // Définition des pseudo-constantes pouvant être redéfinies.
        $this->textEmptyCell = '-';
        $this->formatCells = '{value} ± {uncertainty} %';
        $this->dataCellsSent = array('value', 'uncertainty');
        $this->detailPanel = new Modal();
        $this->detailPanel->setAttribute('id', $this->id.'_detailPanel');
        $this->addPanel = new Modal();
        $this->addPanel->setAttribute('id', $this->id.'_addPanel');
    }

    /**
     * Ajoute une dimension indexant les lignes au tableau multi dimensionnel.
     *
     * @param  string        $name     Nom de la colonnes.
     * @param  array(string) $indexValues   Mots clef qui composent la ligne.
     * @param  int           $position Position de la ligne.
     *
     * @return void
     */
    public function addDimensionIndexLine($name, $indexValues, $position=null)
    {
        $lineDimension['name'] = $name;
        $lineDimension['indexValues'] = $indexValues;
        if ($position === null) {
            $this->_lineDimensions[] = $lineDimension;
        } else  if (!(isset($this->_lineDimensions[$position]))) {
            $this->_lineDimensions[$position] = $lineDimension;
        } else {
            throw new Core_Exception_InvalidArgument(
                'Line '.$this->_lineDimensions[$position]['name'].' already uses this position : '.$position.'.'
            );
        }
    }

    /**
     * Ajoute une dimension indexant les colonnes au tableau multi dimensionnel
     *
     * @param  string        $name      Nom de la colonnes.
     * @param  array(string) $indexValues Mots clef qui composent la colonne.
     * @param  int           $position Position de la ligne.
     *
     * @return void
     */
    public function addDimensionIndexCol($name, $indexValues, $position=null)
    {
        $columnDimension['name'] = $name;
        $columnDimension['indexValues'] = $indexValues;
        if ($position === null) {
            $this->_columnDimensions[] = $columnDimension;
        } else if (!(isset($this->_columnDimensions[$position]))) {
            $this->_columnDimensions[$position] = $columnDimension;
        } else {
            throw new Core_Exception_InvalidArgument(
                'Column '.$this->_columnDimensions[$position]['name'].' already uses this position : '.$position.'.'
            );
        }
    }

    /**
     * Tri les lignes et les colonnes afin d'obtenir un ensemble continu.
     *
     * @return void
     */
    protected function rearrangeAttributes()
    {
        // Réorganisation des dimensions de lignes.
        $repetition = 1;
        $y = 0;
        $index = 0;
        $limit = count($this->_lineDimensions);
        if ($limit > 0) {
            while ($index < $limit) {
                if (isset($this->_lineDimensions[$y])) {
                    if ($y > $index) {
                        $this->_lineDimensions[$index] = $this->_lineDimensions[$y];
                        unset($this->_lineDimensions[$y]);
                    }
                    $this->_lineDimensions[$index]['repetition'] = $repetition;
                    $repetition *= count($this->_lineDimensions[$index]['indexValues']);
                    $index++;
                }
                $y++;
            }
        }

        // Réorganisation des dimensions de colonnes.
        $repetition = 1;
        $x = 0;
        $index = 0;
        $limit = count($this->_columnDimensions);
        if ($limit > 0) {
            while ($index < $limit) {
                if (isset($this->_columnDimensions[$x]) !== false) {
                    if ($x > $index) {
                        $this->_columnDimensions[$index] = $this->_columnDimensions[$x];
                        unset($this->_columnDimensions[$x]);
                    }
                    $this->_columnDimensions[$index]['repetition'] = $repetition;
                    $repetition *= count($this->_columnDimensions[$index]['indexValues']);
                    $index++;
                }
                $x++;
            }
        }
    }

    /**
     * Renvoie le nombre total de colonnes du tableau.
     *
     * @return array Les différentes hauteurs du tableau : cellule, dimensions et total.
     */
    protected function getHeight()
    {
        $dimensionHeight = 0;
        $cellHeight = 1;

        foreach ($this->_lineDimensions as $line) {
            $cellHeight *= count($line['indexValues']);
        }

        if (count($this->_columnDimensions) > 0) {
            $dimensionHeight = 2 * count($this->_columnDimensions);
        } else {
            $dimensionHeight = 1;
        }

        return array(
            'cells' => $cellHeight,
            'dimensions' => $dimensionHeight,
            'total' => $dimensionHeight + $cellHeight
        );
    }

    /**
     * Renvoie le nombre total de colonnes du tableau.
     *
     * @return array Les différentes largeurs du tableau : cellule, dimensions et total.
     */
    protected function getWidth()
    {
        $dimensionWidth = 0;
        $cellWidth = 1;

        foreach ($this->_columnDimensions as $column) {
            $cellWidth *= count($column['indexValues']);
        }

        $dimensionWidth = count($this->_lineDimensions);

        return array(
            'cells' => $cellWidth,
            'dimensions' => $dimensionWidth,
            'total' => $dimensionWidth + $cellWidth
        );
    }


    /**
     * Renvoie le code html du bloc affichant les dimensions indexant les colonnes.
     *
     * @param  array $dimension Dimension à afficher sur la ligne.
     * @param  bool  $first     Indique si la dimension est la première.
     * @param  bool  $last      Indique si la dimension est la dernière.
     *
     * @return string Le code html
     */
    protected function generateDimensionCols($dimension, $first, $last)
    {
        $height = $this->getHeight();
        $width = $this->getWidth();

        $labelWidth = ($dimension['repetition'] > 0) ? $width['cells'] / $dimension['repetition'] : 0;
        if (count($dimension['indexValues']) > 0) {
            $indexValueWidth = $labelWidth / count($dimension['indexValues']);
        }

        $column = '';

        // Ajout du titre de la colonne
        $column .= '<tr>';
        // Création du bloc vide si nécéssaire.
        if (($first) && ($width['cells'] < $width['total'])) {
            $column .= '<th colspan="'.$width['dimensions'].'" rowspan="'.($height['dimensions'] - 1).'"></th>';
        }
        for ($i = 0; $i < $dimension['repetition']; $i++) {
            $column .= '<th colspan="'.$labelWidth.'">'.$dimension['name'].'</th>';
        }
        $column .= '</tr>';

        // Ajout des mots clés.
        if ($last) {
            // On affiche la dernière ligne plus tard (pour permettre l'affichage des titres de ligne).
            return $column;
        }
        $column .= '<tr>';
        for ($i = 0; $i < $dimension['repetition']; $i++) {
            foreach ($dimension['indexValues'] as $indexValue) {
                $column .= '<th colspan="'.$indexValueWidth.'" class="indexValue">'.$indexValue.'</th>';
            }
        }
        $column .= '</tr>';

        return $column;
    }


    /**
     * Renvoie le code html du bloc principal.
     *
     * @param  int   $indexDimension      Dimension à afficher dans la colonne, par défaut la première dimension.
     * @param  bool  $newLine             Indique si une nouvelle ligne doit être créee.
     * @param  array $listKeysIndexValues Liste des clés des index des valeurs parcourues.
     *
     * @return string Le code html
     */
    protected function generateDimensionLine($indexDimension=0, $newLine=true, $listKeysIndexValues=array())
    {
        $height = $this->getHeight();

        $line = '';

        if (count($this->_lineDimensions) > 0) {
            $dimension = $this->_lineDimensions[$indexDimension];
            $coeff = ($dimension['repetition'] * count($dimension['indexValues']));
            if ($coeff > 0) {
                $indexValueHeight = $height['cells'] / $coeff;
            }

            foreach ($this->_lineDimensions[$indexDimension]['indexValues'] as $keyIndexValue => $indexValue) {
                // Ajout de l'index en cours.
                $nextListKeysIndexValues = $listKeysIndexValues;
                $nextListKeysIndexValues[] = $keyIndexValue;
                // Ajout de la nouvelle ligne si besoin.
                if ($newLine === true) {
                    $line .= '<tr>';
                }
                $nextNewLine = false;

                // Ajout de l'index.
                $line .= '<th rowspan="'.$indexValueHeight.'" class="indexValue">'.$indexValue.'</th>';

                // Ajout des valeurs après le dernier index.
                if ($indexDimension == (count($this->_lineDimensions) - 1)) {
                    $line .= $this->generateLine($nextListKeysIndexValues);
                    // Fermeture de la ligne.
                    $line .= '</tr>';
                    $nextNewLine = true;
                // Sinon on passe au prochain index.
                } else {
                    $line .= $this->generateDimensionLine($indexDimension + 1, $nextNewLine, $nextListKeysIndexValues);
                }
            }
        } else {
            $line .= '<tr>'.$this->generateLine($listKeysIndexValues).'</tr>';
        }

        return $line;
    }


    /**
     * Renvoie le code html d'une ligne du tableau.
     *
     * @param  array  $listKeysIndexValues Liste des index des la cellule.
     * @param  int    $indexDimension      Identifiant de la dimension entrain d'être parcourue.
     *
     * @return string Chaine html du contenu de la cellule.
     */
    protected function generateLine($listKeysIndexValues, $indexDimension=0)
    {
        $line = '';

        if (count($this->_columnDimensions) > 0) {
            foreach ($this->_columnDimensions[$indexDimension]['indexValues'] as $keyIndexValue => $indexValue) {
                $nextListKeysIndexValues = $listKeysIndexValues;
                $nextListKeysIndexValues[] = $keyIndexValue;

                // Ajout des valeurs après le dernier index.
                if (!(isset($this->_columnDimensions[$indexDimension + 1]))) {
                    $line .= $this->generateCell($nextListKeysIndexValues);
                // Sinon on passe au prochain index.
                } else {
                    $line .= $this->generateLine($nextListKeysIndexValues, $indexDimension + 1);
                }
            }
        } else {
            $line .= $this->generateCell($listKeysIndexValues);
        }

        return $line;
    }


    /**
     * Renvoie le code html de la cellule.
     *
     * @param  array  $listKeysIndexValues Liste des index de la cellule.
     *
     * @return string Code html de la cellule.
     */
    protected function generateCell($listKeysIndexValues)
    {
        $cell = '';

        // Recherche de la valeur.
        if ($this->cellValueCallback !== null) {
            $callback = $this->cellValueCallback;
            $data = $callback($listKeysIndexValues);
        } else {
            $data = $this->cells;
            foreach ($listKeysIndexValues as $keyIndexValue) {
                if (!(isset($data[$keyIndexValue]))) {
                    $data = null;
                    break;
                } else {
                    $data = $data[$keyIndexValue];
                }
            }
        }

        $cell .= '<td id="'.$this->id.'-'.implode('-', $listKeysIndexValues).'">';
        if ((($this->urlDetailsCell !== null) && ($data !== null))
            || (($this->urlAddCell !== null) && ($data === null))) {
            $cell .= '<a ';
            $cell .= 'data-toggle="modal" data-remote="false" ';
            if ($data === null) {
                $cell .= 'data-target="#'.$this->id.'_addPanel"';
                $url = $this->urlAddCell;
            } else {
                $cell .= 'data-target="#'.$this->id.'_detailPanel"';
                $url = $this->urlDetailsCell;
            }

            if (preg_match('#\?#', $url) > 0) {
                if (preg_match('#&$#', $url) === 0) {
                    $url .= '&';
                }
            } else {
                $url .= '?';
            }
            // Ajout des paramètres de la cellule.
            foreach ($this->dataCellsSent as $indexData) {
                $url .= $indexData.'='.$data[$indexData].'&';
            }
            // Ajout des mots clefs identifiant la cellule.
            $url .= 'indexValues=[';
            foreach ($listKeysIndexValues as $keyIndexValue) {
                $url .= '&quot;'.$keyIndexValue.'&quot;,';
            }
            $url = substr($url, 0, -2);
            $url .= ']';

            $cell .= 'href="'.$url.'"';
            $cell .= '>';
        }
        $cell .= $this->getValue($data);
        if ((($this->urlDetailsCell !== null) && ($data !== null))
            || (($this->urlAddCell !== null) && ($data === null))) {
            $cell .= '</a>';
        }
        $cell .= '</td>';

        return $cell;
    }


    /**
     * Fonction qui parse les valeurs d'une cellule avec le format choisis.
     *
     * @param  array $data Cellule dans laquelle il faut extraire les données.
     *
     * @return string Données formatée.
     */
    protected function getValue($data)
    {
        if ($data === null) {
            $value = $this->textEmptyCell;
        } else if (is_array($data) === false) {
            $value = $data;
        } else {
            $value = $this->formatCells;
            preg_match_all('#{[a-zA-Z]+}#', $this->formatCells, $masks);
            foreach ($masks[0] as $mask) {
                $value = preg_replace('#'.$mask.'#', $data[substr($mask, 1, -1)], $value, 1);
            }
        }

        return $value;
    }

    /**
     * Génère le tableau html.
     *
     * @return string
     */
    protected function generateTMD()
    {
        $tmd = '';

        // Préparation du tableau.
        $this->rearrangeAttributes();
        $height = $this->getHeight();
        $width = $this->getWidth();

        // Création de la table.
        $tmd .= '<table id="'.$this->id.'" class="tmd table table-striped table-bordered small">';

        // Création du bloc des dimensions indexant les colonnes.
        foreach ($this->_columnDimensions as $position => $columnDimension) {
            $first = ($position == 0) ? true : false;
            $last = ($position == (count($this->_columnDimensions) - 1)) ? true : false;
            $tmd .= $this->generateDimensionCols($columnDimension, $first, $last);
        }

        // Dernière ligne d'entête (titre des Dimensions de ligne et derniers mots cles des dimensions de colonnes).
        $tmd .= '<tr>';

        // Ajout des titres des dimensions indexant les lignes.
        if (count($this->_lineDimensions) > 0) {
            foreach ($this->_lineDimensions as $lineDimension) {
                $tmd .= '<th>'.$lineDimension['name'].'</th>';
            }
        }

        // Ajout du dernier bloc de mot clés.
        if (count($this->_columnDimensions) > 0) {
            $indexLastValues = (count($this->_columnDimensions) - 1);
            for ($i = 0; $i < $this->_columnDimensions[$indexLastValues]['repetition']; $i++) {
                foreach ($this->_columnDimensions[$indexLastValues]['indexValues'] as $lastValue) {
                    $tmd .= '<th class="indexValue">'.$lastValue.'</th>';
                }
            }
        } else {
            $tmd .= '<th></th>';
        }

        // Fin de l'entête.
        $tmd .= '</tr>';

        // Ajout du bloc principal des lignes.
        $tmd .= $this->generateDimensionLine();

        // Fin de la création de la table.
        $tmd .= '</table>';

        return $tmd;
    }

    /**
     * Renvoi le javascript de l'interface.
     *
     * @return string
     */
    public function getScript()
    {
        $script = '';

        // Ajout du popup de consultation/edition si besoin.
        if ($this->urlDetailsCell !== null) {
            $script .= $this->detailPanel->getScript();
        }
        if ($this->urlAddCell !== null) {
            $script .= $this->addPanel->getScript();
        }

        return $script;
    }

    /**
     * Génère le code HTML.
     *
     * @return string
     */
    public function getHTML()
    {
        $html = '';

        // Ajout du TMD.
        $html .= $this->generateTMD();

        // Ajout du popup de consultation/edition si besoin.
        if ($this->urlDetailsCell !== null) {
            $html .= $this->detailPanel->getHTML();
        }
        if ($this->urlAddCell !== null) {
            $html .= $this->addPanel->getHTML();
        }

        return $html;
    }

}
