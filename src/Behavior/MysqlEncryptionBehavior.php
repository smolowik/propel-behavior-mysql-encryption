<?php

declare(strict_types=1);

namespace Smolowik\Propel\Behavior;

use Propel\Generator\Exception\BuildException;
use Propel\Generator\Model\Behavior;
use Propel\Generator\Model\Domain;
use Propel\Generator\Model\PropelTypes;
use Propel\Generator\Model\Table;

class MysqlEncryptionBehavior extends Behavior
{
    /**
     * @var array
     */
    protected $parameters = array(
        'columns' => null,
    );

    /**
     * @var MysqlEncryptionBehaviorTableMapBuilderModifier
     */
    protected $mysqlEncryptionBehaviorTableMapBuilderModifier;

    /**
     * @var MysqlEncryptionBehaviorQueryBuilderModifier
     */
    protected $mysqlEncryptionBehaviorQueryBuilderModifier;

    /**
     * @var MysqlEncryptionBehaviorObjectBuilderModifier
     */
    protected $mysqlEncryptionBehaviorObjectBuilderModifier;

    /**
     * @var Table
     */
    protected $table;

    public function modifyTable()
    {
        $this->table = $this->getTable();
//var_dump($this->table->getDatabase()->getPlatform()->getDatabaseType());
//        if ($this->table->getDatabase()->getPlatform()->getDatabaseType() !== 'mysql') {
//            throw new BuildException(sprintf(
//                'You can use this behavior (%s) only for mysql databases.',
//                $this->getName()
//            ));
//        }

        $columns = explode(',', $this->getParameter('columns'));
        foreach ($columns as $columnName) {
            if (!$this->table->hasColumn($columnName)) {
                $this->table->addColumn(array(
                    'name' => $columnName,
                    'type' => PropelTypes::LONGVARCHAR,
                    'required' => false,
                ));
            } else {
                $column = $this->table->getColumn($columnName);
                $domain = $this->getTable()->getPlatform()->getDomainForType(PropelTypes::LONGVARCHAR);
                $column->setDomain($this->getSuitableDomain($column->getDomain()));
            }
        }
    }

    /**
     * @return MysqlEncryptionBehaviorTableMapBuilderModifier
     */
    public function getTableMapBuilderModifier(): MysqlEncryptionBehaviorTableMapBuilderModifier
    {
        if ($this->mysqlEncryptionBehaviorTableMapBuilderModifier === null) {
            $this->mysqlEncryptionBehaviorTableMapBuilderModifier =
                new MysqlEncryptionBehaviorTableMapBuilderModifier($this);
        }

        return $this->mysqlEncryptionBehaviorTableMapBuilderModifier;
    }

    /**
     * @return MysqlEncryptionBehaviorQueryBuilderModifier
     */
    public function getQueryBuilderModifier(): MysqlEncryptionBehaviorQueryBuilderModifier
    {
        if ($this->mysqlEncryptionBehaviorQueryBuilderModifier === null) {
            $this->mysqlEncryptionBehaviorQueryBuilderModifier =
                new MysqlEncryptionBehaviorQueryBuilderModifier($this);
        }

        return $this->mysqlEncryptionBehaviorQueryBuilderModifier;
    }

    /**
     * @return MysqlEncryptionBehaviorObjectBuilderModifier
     */
    public function getObjectBuilderModifier(): MysqlEncryptionBehaviorObjectBuilderModifier
    {
        if ($this->mysqlEncryptionBehaviorObjectBuilderModifier === null) {
            $this->mysqlEncryptionBehaviorObjectBuilderModifier =
                new MysqlEncryptionBehaviorObjectBuilderModifier($this);
        }

        return $this->mysqlEncryptionBehaviorObjectBuilderModifier;
    }

    private function getSuitableDomain(Domain $domain)
    {
        $typesMaxSize = array(
            PropelTypes::VARCHAR => 255,
            PropelTypes::LONGVARCHAR => 65535,
            PropelTypes::CLOB => 4294967295,
        );
        $selectedType = PropelTypes::VARCHAR;
        $fieldSize = $domain->getSize() ? $domain->getSize() : 255;
        $fieldSize = 16 * (floor($fieldSize / 16) + 1);
        foreach ($typesMaxSize as $type => $size) {
            if ($fieldSize < $size) {
                $selectedType = $type;
                break;
            }
        }
        $result = $this->table->getPlatform()->getDomainForType($selectedType);
        $result->setSize($fieldSize);

        return $result;
    }
}
