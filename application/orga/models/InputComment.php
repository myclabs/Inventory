<?php

/**
 * Value Object représentant un commentaire sur une saisie d'Orga.
 *
 * Cette classe sert de wrapper à Social_Model_Comment afin de garder la trace de la saisie.
 * Je n'ai pas trouvé meilleure façon de faire car j'avais besoin d'utiliser des criterias sur des objets :'(
 */
class Orga_Model_InputComment
{
    /**
     * @var Orga_Model_Cell
     */
    private $cell;

    /**
     * @var Social_Model_Comment
     */
    private $comment;

    public function __construct(Orga_Model_Cell $cell, Social_Model_Comment $comment)
    {
        $this->cell = $cell;
        $this->comment = $comment;
    }

    /**
     * @return Orga_Model_Cell
     */
    public function getCell()
    {
        return $this->cell;
    }

    public function __call($name, $parameters)
    {
        // Forwarde tous les appels vers Social_Model_Comment
        return call_user_func_array([$this->comment, $name], $parameters);
    }
}
