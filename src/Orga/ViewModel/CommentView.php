<?php

namespace Orga\ViewModel;

class CommentView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $text;

    /**
     * @var string
     */
    public $html;

    /**
     * @var string
     */
    public $author;

    /**
     * @var string
     */
    public $date;

    /**
     * @var bool
     */
    public $canBeEdited = false;
}
