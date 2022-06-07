<?php

/**
 * This file is part of the Liblet PDOEXtendedLiblet by Arousacode.
 * 
 * PDOEXtendedLiblet is free software. You can redistribute it, modify it
 * under GNU GPL v3.
 * 
 *  http://www.gnu.org/licenses/gpl.html
 *
 */

namespace ArousaCode\WebApp\Pdo;

/**
 * trait to add any object the cabability to be loaded and stored in a PDO database.
 * 
 * It is a very simple library to easylle cover most of the cases. The primary key 
 * is asummed to be the column id: integer (if other, override the property $idColumnName whith the column name).
 * It also asumes that the table name is the same as the class name of the object. If different,
 * override the property $tableName with the table name.
 * 
 * It doesn't care about relations or foreign keys. It manages database and PHP data types,
 * but it is intended just to afford repetitive code loading and storing objects, without the
 * need to learn or load a full ORM engine.
 * 
 * All property names must be equal to the database column names.
 * 
 */
trait PDOExtended
{
    private \PDO $_db;

    /**
     * Override this property with your id column name if differente from "id"
     */
    protected string $idColumnName = "id";
    /** Oberride this property if the table is located inside an schema. */
    protected string $schemaName = "";
    /**
     * Override this property with the table name if differente from ClassName
     */
    protected string $tableName = null;

    /**
     * It is mandatory to init the library calling once in every object to the init method.
     * PDO object is stored and table name calculated.
     */
    function initDb(\PDO $db)
    {
        $this->_db = $db;
        if ($this->tableName != null) {
            $this->tableName = end(explode('\\', static::class));
        }
        if ($this->tableName == null) {
            throw new \Exception("Error: can't find out ClassName from fqdn " . static::class);
        }
        if ($this->schemaName != "") {
            $this->tableName = $this->schemaName . "." . $this->tableName;
        }
    }

/**
 *
 * @return null|integer new ID in case of insert.
 */
    function upsert(): null|int
    {
        $cmd = "SELECT \"{$this->idColumnName}\" FROM \"{$this->tableName}\" ";
        $cmd .= "  WHERE  \"{$this->idColumnName}\" = ':ID' ";
        $sth = $this->_db->prepare($cmd);
        $sth->execute([':ID' => $this->getIdValue()]);
        if ($sth->rowCount() == 0) {
            return $this->insert();
        } else {
            return $this->update();
        }
    }

    function insert(): int
    {
        $ref = new \ReflectionClass(static::class);
        $props = $ref->getProperties();
        $columnNames=$this->_getColumnsNames();
        $columnValues=$this->_getColumnsValues();
        $cmd = "INSERT INTO {$this->tableName} ($columnNames) VALUES ($columnValues)";
        return 1;
    }

    function update()
    {
    }

    function load(?int $id): void
    {
    }

    function delete(): void
    {
        $this->_db->query("DELETE FROM {$this->tableName} WHERE {$this->idColumnName} = '" . $this->getIdValue() . "'")
            or throw new \Exception("Error trying to delete object in {$this->tableName} with  {$this->idColumnName} = '" . $this->getIdValue() . "'");
    }

    function getIdValue(): int
    {
        return $this->${$this->idColumnName};
    }
    private function _fetchEntries(string $cmd): null|array
    {
        $resSt = $this->_db->query($cmd);
        $rows = $resSt->fetchAll(\PDO::FETCH_ASSOC);
        return $rows;
    }
    private function _fetchEntry(string $cmd): null|array
    {
        $resSt = $this->_db->query($cmd);
        $rows = $resSt->fetchAll(\PDO::FETCH_ASSOC);
        return $rows;
    }
    private function _fetchValue(string $cmd): null|array
    {
        $resSt = $this->_db->query($cmd);
        $rows = $resSt->fetchAll(\PDO::FETCH_ASSOC);
        return $rows;
    }


    private function _getColumnsNames(): string
    {
        $ref = new \ReflectionClass(static::class);
        $props = $ref->getProperties();
        $names="";
        $separator="";
        foreach ($props as $prop) {
            $names.=$separator.$prop->getName();
            $separator=",";
        }
        return $names;
    }

    private function _getColumnsNamesForPreparedStatement(): string
    {
        $ref = new \ReflectionClass(static::class);
        $props = $ref->getProperties();
        $names="";
        $separator="";
        foreach ($props as $prop) {
            $names.=$separator.':'.$prop->getName();
            $separator=",";
        }
        return $names;
    }
    private function _getColumnsValues(): string
    {
        $ref = new \ReflectionClass(static::class);
        $props = $ref->getProperties();
        $values="";
        $separator="";
        foreach ($props as $prop) {
            $value=$this->{$prop->getName()};
            if($value === null){
                $values.=$separator.'NULL';

            }
            else{
                $values.=$separator."'".$value."'";
            }
            $separator=",";
        }
        return $values;
    }

    private function _getColumnsValuesForPreparedStatement(): array
    {
        $ref = new \ReflectionClass(static::class);
        $props = $ref->getProperties();
        $values=[];
        foreach ($props as $prop) {
            $values[$prop->getName()]=$this->{$prop->getName()};
        }
        return $values;
    }

    private function _getColumnsNamesValues(): string
    {
        $ref = new \ReflectionClass(static::class);
        $props = $ref->getProperties();
        $values="";
        $separator="";
        foreach ($props as $prop) {
            $values=$separator.$prop->getName()."=";
            $value=$this->{$prop->getName()};
            if($value === null){
                $values.='NULL';

            }
            else{
                $values.="'".$value."'";
            }
            $separator=",";
        }
        return $values;
    }
}
