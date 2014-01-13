<?php

namespace Tests\Core;

use Core\Test\TestCase;
use Core_ORM_DBALExceptionParser;
use Core_ORM_DuplicateEntryException;
use Core_ORM_ForeignKeyViolationException;
use Core_ORM_NotNullViolationException;
use Doctrine\DBAL\DBALException;

class DBExceptionParserTest extends TestCase
{
    /**
     * Test basique
     */
    public function testBasic()
    {
        $e = new DBALException();
        $parser = new Core_ORM_DBALExceptionParser($e);
        $this->assertFalse($parser->isForeignKeyViolation());
        $this->assertFalse($parser->isDuplicateEntry());
        $this->assertFalse($parser->isNotNullViolation());
    }

    /**
     * Détection d'une contrainte de clé étrangère
     */
    public function testParseForeignKeyViolation()
    {
        $e = new DBALException(
            "An exception occurred while executing "
            . "'DELETE FROM mytable WHERE id = ?' with params "
            . "{\"1\":10}: SQLSTATE[23000]: Integrity constraint violation: "
            . "1451 Cannot delete or update a parent row: a foreign key constraint fails "
            . "(`mydb`.`source_table`, CONSTRAINT `FK_2A1E03594C310645` "
            . "FOREIGN KEY (`source_field`) "
            . "REFERENCES `target_table` (`target_field`))"
        );
        $parser = new Core_ORM_DBALExceptionParser($e);
        $this->assertTrue($parser->isForeignKeyViolation());
        $exception = $parser->getForeignKeyViolationException();
        $this->assertInstanceOf(Core_ORM_ForeignKeyViolationException::class, $exception);
        // Tables non reconnues
        $this->assertEquals('source_table', $exception->getSourceEntity());
        $this->assertEquals('source_field', $exception->getSourceField());
        $this->assertEquals('target_table', $exception->getReferencedEntity());
        $this->assertEquals('target_field', $exception->getReferencedField());
    }

    /**
     * Test de la classe Core_ORM_ForeignKeyViolation
     */
    public function testForeignKeyViolation()
    {
        $violation = new Core_ORM_ForeignKeyViolationException(
            DBExceptionParserFixture::class,
            'myField',
            DBExceptionParserFixture::class,
            'myField'
        );

        $this->assertTrue($violation->isSourceEntityInstanceOf(DBExceptionParserFixture::class));
        $this->assertTrue($violation->isSourceEntityInstanceOf(DBExceptionParserAbstractFixture::class));

        $this->assertTrue($violation->isReferencedEntityInstanceOf(DBExceptionParserFixture::class));
        $this->assertTrue($violation->isReferencedEntityInstanceOf(DBExceptionParserAbstractFixture::class));

        $this->assertFalse($violation->isSourceEntityInstanceOf(Core_ORM_ForeignKeyViolationException::class));
        $this->assertFalse($violation->isReferencedEntityInstanceOf(Core_ORM_ForeignKeyViolationException::class));
    }

    /**
     * Détection d'une contrainte de clé unique
     */
    public function testParseDuplicateEntry()
    {
        $e = new DBALException(
            "An exception occurred while executing 'X' with params X:"
            . PHP_EOL . PHP_EOL
            . "SQLSTATE[23000]: Integrity constraint violation: "
            . "1062 Duplicate entry 'MYENTRY' for key 'MYKEY'"
        );
        $parser = new Core_ORM_DBALExceptionParser($e);
        $this->assertTrue($parser->isDuplicateEntry());
        $exception = $parser->getDuplicateEntryException();
        $this->assertInstanceOf(Core_ORM_DuplicateEntryException::class, $exception);
        $this->assertEquals('MYENTRY', $exception->getEntry());
        $this->assertEquals('MYKEY', $exception->getKey());
    }

    /**
     * Détection d'une contrainte not null
     */
    public function testParseNotNullViolation()
    {
        $e = new DBALException(
            "An exception occurred while executing 'X' with params X:"
            . PHP_EOL . PHP_EOL
            . "SQLSTATE[23000]: Integrity constraint violation: "
            . "1048 Column 'MYCOLUMN' cannot be null"
        );
        $parser = new Core_ORM_DBALExceptionParser($e);
        $this->assertTrue($parser->isNotNullViolation());
        $exception = $parser->getNotNullViolationException();
        $this->assertInstanceOf(Core_ORM_NotNullViolationException::class, $exception);
        $this->assertEquals('MYCOLUMN', $exception->getColumn());
    }

}

/**
 * Abstract class for fixture
 */
abstract class DBExceptionParserAbstractFixture
{
}

/**
 * Class for fixture
 */
class DBExceptionParserFixture extends DBExceptionParserAbstractFixture
{
    private $myField;
}
