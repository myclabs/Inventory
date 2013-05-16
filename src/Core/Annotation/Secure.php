<?php
/**
 * @author matthieu.napoli
 * @package Core
 */

namespace Core\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Annotation to secure controller actions
 *
 * @Annotation
 * @Target({"METHOD"})
 *
 * @package Core
 */
class Secure
{

    /**
     * @var string
     */
    public $rule;

}
