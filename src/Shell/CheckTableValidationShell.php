<?php
namespace CreditData\Cake\Shell;

use Cake\Collection\Collection;
use Cake\Console\Shell;
use Cake\Datasource\Exception\MissingModelException;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

/**
 * A Shell for checking for proper validation of Tables
 *   
 * <code>
 * $ bin/cake check_table_validation TableName
 * </code>
 */
class CheckTableValidationShell extends Shell
{
    static $_DEFAULT_IGNORES = [ 'created', 'modified', 'deleted' ];
    /**
     * Entry-point of the shell
     */
    public function main()
    {
        if (strlen($this->args[0]) < 1) {
            return $this->_displayHelp('TableName');
        }
        $tableName =  $this->args[0];
        if (! $tableName) {
            return $this->_displayHelp('t');  
        }
        $table = TableRegistry::getTableLocator()->get($tableName);
        return $this->checkValidationRulesFor($table, self::$_DEFAULT_IGNORES);
    }

    /**
     * Gets the option parser instance and configures it.
     *
     * @return \Cake\Console\ConsoleOptionParser
     */    
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser->setDescription([
            'Check Table Models'
        ])->addArgument('TableName', [
            'required' => true,
            'help'  => 'Name of the Table to Check',
        ]);
        return $parser;
    }

    /**
     * Checks for fields that don't have validation rules set on the given table
     * object in the table's `validationDefault` method
     * 
     * @param {\Cake\ORM\Table} $table An instance of a table
     * @param {array} An array of fields for which to skip the check for validation rules
     * @return {boolean} true if no fields were found without validation were found, false otherwise
     */
    public function checkValidationRulesFor($table, $ignoreList = [])
    {
        if (is_null($table)) {
            throw new MissingModelException('Table cannot be null');
        }

        if (! $table instanceof \Cake\ORM\Table) {
            throw new MissingModelException('Table MUST be an instance of \Cake\ORM\Table');
        }

        $validator = $table->validationDefault(new Validator());

        if (! $validator) {
            $this->setIo()->err('No Validator found for table: ' . $table->getTable());
            exit(-1);
        }
        $schemaFields = $this->getSchemaFields($table); 
        $fields = (new Collection($schemaFields))
            ->filter(function($field) use($ignoreList) {
                return !in_array($field, $ignoreList);
            })
            ->filter(function($field) use($validator) {
                return !$validator->hasField($field);
            })
            ->toArray();

        if (empty($fields)) {
            $this->setIo()->out('All fields have validation set');
        } else {
            $this->setIo()->err(__('The following {0} field(s) in table "{1}" DO NOT have validation rules set:', count($fields), $table->getTable()));
            foreach($fields as $f) {
                $this->setIo()->err(__('- {0}', $f));
            }
            return false;
        }
        return true;
    }

    /**
     * Get Schema Fields for the given table instance
     * 
     * @param {\Cake\ORM\Table} $table Instance of a table
     * @return {array} An array of the columns in the table
     */
    public function getSchemaFields($table)
    {
        $schema = $table->getSchema();
        if (! $schema instanceof \Cake\Database\Schema\TableSchema) { 
            return [];
        } 
        return $schema->columns();
    }

    private function _generateForMissing($table, $fields)
    {

    }
}
