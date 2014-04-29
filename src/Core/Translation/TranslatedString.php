<?php

namespace Core\Translation;

use Mnapoli\Translated\TranslatedStringInterface;
use Mnapoli\Translated\TranslatedStringTrait;

class TranslatedString implements TranslatedStringInterface
{
    use TranslatedStringTrait;

    protected $fr;
    protected $en;
}
