<?php
/**
 * @author matthieu.napoli
 * @package Core
 */

use Doctrine\ORM\Tools\SchemaValidator;

/**
 * Validation du mapping Doctrine
 * @package Core
 */
abstract class Core_Test_MappingValidationTest extends Core_Test_TestCase
{

    /**
     * Doctrine schema validation
     */
    public function testValidateSchema()
    {
        $validator = new SchemaValidator($this->entityManager);
        $errors = $validator->validateMapping();

        if (count($errors) > 0) {
            $message = PHP_EOL;
            foreach ($errors as $class => $classErrors) {
                $message .= "- " . $class . ":" . PHP_EOL . implode(PHP_EOL, $classErrors) . PHP_EOL . PHP_EOL;
            }
            $this->fail($message);
        }
    }

}
