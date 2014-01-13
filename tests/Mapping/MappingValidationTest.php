<?php

namespace Tests\Mapping;

use Core\Test\TestCase;
use Doctrine\ORM\Tools\SchemaValidator;

class MappingValidationTest extends TestCase
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
