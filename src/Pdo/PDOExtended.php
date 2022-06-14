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

use ArousaCode\WebApp\Types\WebAppType;

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
     * WebAppType[]  [name=>WebAppType]
     */
    private array $_fields = [];

    /**
     * Override this property with your id column name if differente from "id"
     */
    protected string $idColumnName = "id";
    /** Oberride this property if the table is located inside an schema. */
    protected ?string $schemaName = null;
    /**
     * Override this property with the table name if differente from ClassName
     */
    protected ?string $tableName = null;

    /**
     * It is mandatory to init the library calling once in every object to the init method.
     * PDO object is stored and table name calculated.
     */
    function initDb(\PDO $db)
    {
        $this->_db = $db;
        if ($this->tableName == null) {
            $classNameArr=explode('\\', static::class);
            $this->tableName = end($classNameArr);
        }
        if ($this->tableName == null) {
            throw new \Exception("Error: can't find out ClassName from fqdn " . static::class);
        }
        if ($this->schemaName != null) {
            $this->tableName = $this->schemaName . "." . $this->tableName;
        }

        $fieldCacheName = "AROUSA_CODE_" . static::class;
        $res = false; /// DEBUG descomenar. apcu_fetch($fieldCacheName);
        if ($res !== false) {
            $this->_fields = $res;
        } else {

            $ref = new \ReflectionClass(static::class);
            //Construct array with object property names.
            $props = $ref->getProperties();
            $propertyNames = [];
            foreach ($props as $prop) {
                $propertyNames[] = $prop->getName();
            }

            //Query field names to be loades/stored in database.
            $rs = $db->query("SELECT * FROM \"{$this->tableName}\" LIMIT 0");
            for ($i = 0; $i < $rs->columnCount(); $i++) {
                $name = $rs->getColumnMeta($i)['name'];
                $type = WebAppType::WebAppTypeFromDatabaseType($rs->getColumnMeta($i)['native_type']);
                //IF database field has not a property with the same name, we don't use it.
                if (in_array($name, $propertyNames)) {
                    $this->_fields[$name] = $type;
                }
            }
            apcu_store($fieldCacheName, $this->_fields);
        }
        echo "<h1> DB TYPES </h1><pre>";
        print_r($this->_fields);
        echo "</pre><hr/>";
    }

    /**
     *
     * @return null|integer new ID in case of insert.
     */
    function upsert(): mixed
    {
        if ($this->getIdValue() == null) {
            return $this->insert();
        }

        $cmd = "SELECT \"{$this->idColumnName}\" FROM \"{$this->tableName}\" ";
        $cmd .= "  WHERE  \"{$this->idColumnName}\" = ':IDWAPDOTAG' ";
        $sth = $this->_db->prepare($cmd);
        $sth->execute(['IDWAPDOTAG' => $this->getIdValue()]);
        if ($sth->rowCount() == 0) {
            return $this->insert();
        } else {
            return $this->update();
        }
    }

    /**
     * If the object doesn't have an ID value it will be generated so:
     *  - We have to be in a transactin. If not in one, we will begin and commit.
     *  - The new ID value should be get from database.
     */
    function insert(): int
    {
        $idValueExists = ($this->getIdValue() != null);
        $inTransaction = $this->_db->inTransaction();

        if ((!$inTransaction) && (!$idValueExists)) {
            $this->_db->beginTransaction();
        }
        $columnNames = $this->_getColumnNames();
        $columnLabels = $this->_getColumnLabelsForPreparedStatement();
        $columnValuesSt = $this->_getColumnValuesForPreparedStatement();
        $cmd="INSERT INTO \"{$this->tableName}\" ($columnNames) VALUES ($columnLabels)";
        $st = $this->_db->prepare($cmd);
        echo "<pre> $cmd \n ";
        print_r($columnValuesSt);
        echo "</pre>";
        $st->execute($columnValuesSt);
        if ($idValueExists) {
            $id = $this->getIdValue();
        } else {
            $id = $this->_db->lastInsertId();
        }
        if ((!$inTransaction) && (!$idValueExists)) {
            $this->_db->commit();
        }
        return $id;
    }

    function update(): void
    {
        $updateSt = $this->_getColumnNamesAndLabelsForUpdatePreparedStatement();
        $columnValuesSt = $this->_getColumnValuesForPreparedStatement();
        $st = $this->_db->prepare("UPDATE \"{$this->tableName}\" SET $updateSt WHERE \"{$this->idColumnName}\"=':IDWAPDOTAG'");
        $st->execute($columnValuesSt + ["IDWAPDOTAG" => $this->getIdValue()]);
    }

    function load(?int $id = null): void
    {
        $resSt = $this->_db->query("SELECT * FROM  \"{$this->tableName}\"   WHERE \"{$this->idColumnName}\"='{$this->getIdValue()}' ");
        $rows = $resSt->fetchAll(\PDO::FETCH_ASSOC);
        $this->_loadDataIntoObject($rows);
    }

    function delete(): void
    {
        if($this->getIdValue() == null){
            throw new \Exception("Error: the object has no ID value");
        }
        $st = $this->_db->prepare("DELETE FROM \"{$this->tableName}\" WHERE \"{$this->idColumnName}\" = ':IDWAPDOTAG'");
        $st->execute(['IDWAPDOTAG'=>$this->getIdValue() ])
            or throw new \Exception("Error trying to delete object in {$this->tableName} with  {$this->idColumnName} = '" . $this->getIdValue() . "'");
    }

    function getIdValue(): mixed
    {
        return $this->{$this->idColumnName};
    }

    private function _fetchEntries(string $cmd): null|array
    {
        $resSt = $this->_db->query($cmd);
        $rows = $resSt->fetchAll(\PDO::FETCH_ASSOC);
        $res=[];
        foreach($rows as $row){
            $obj=new static();
            $obj->_loadDataIntoObject($row);
            $res[]=$obj;
        }
        return $res;
    }
    private function _fetchEntry(string $cmd): null|array
    {
        $resSt = $this->_db->query($cmd);
        $rows = $resSt->fetchAll(\PDO::FETCH_ASSOC);
        $obj=new static();
        $obj->_loadDataIntoObject($rows);
        return $obj;
    }

/* It doesn't make sense.
      private function _fetchValue(string $cmd): null|array
    {
        $resSt = $this->_db->query($cmd);
        $rows = $resSt->fetchAll(\PDO::FETCH_NUM);
        return $rows[0][0]; //### !!!!!!!!! TBI: comprobacións.
    }
*/

    private function _getColumnNames(): string
    {
        $names = "";
        $separator = "";
        foreach (array_keys($this->_fields) as $name  ) {
            $names .= $separator . '"'.$name. '"';
            $separator = ",";
        }
        return $names;
    }

    private function _getColumnLabelsForPreparedStatement(): string
    {
        $names = "";
        $separator = "";
        foreach (array_keys($this->_fields) as $name  ) {
            $names .= $separator . ':' . $name;
            $separator = ",";
        }
        return $names;
    }
    
    private function _getColumnNamesAndLabelsForUpdatePreparedStatement(): string
    {
        
        $data = "";
        $sep = "";
        foreach (array_keys($this->_fields) as $name  ) {
            $data .= $sep . "\"{$name}\"=:{$name}";
            $sep = ",";
        }
        return $data;
    }
    private function _getColumnValuesForPreparedStatement(): array
    {
        $values = [];
        foreach ($this->_fields as $name => $type) {
            $value=$this->{$name};
            if($value !== null) {
                $value=match($type){
                    WebAppType::Date=>$value->format('Y-m-d'),
                    WebAppType::DateTime=>$value->format('Y-m-d H:i:s'),
                    WebAppType::Time=>$value->format('H:i:s'),
                    WebAppType::Bool=>$value?'true':'false',
                    default=>$this->{$name}
                };
            }
            $values[$name] = $value;
        }
        return $values;
    }


    private function _loadDataIntoObject(array &$dbData): void{
        foreach($this->_fields as $name => $type){
            $dbValue=$dbData[$name];
            $this->{$name}=$this->_readColData($type, $dbValue);
        }
    }

    private function _readColData(WebAppType $type, mixed $data){
        if($data === null ){
            return null;
        }
        return match($type){
            WebAppType::Date,WebAppType::DateTime, WebAppType::Time=>new \DateTime($data),
            WebAppType::Bool=>($data==='t'),
            default=>$data

        };
    }
    /*


    private function _getColumnValues(): string
    {
        $ref = new \ReflectionClass(static::class);
        $this->_props = $ref->getProperties();
        $values = "";
        $separator = "";
        foreach ($this->_props as $prop) {
            $value = $this->{$prop->getName()};
            if ($value === null) {
                $values .= $separator . 'NULL';
            } else {
                $values .= $separator . "'" . $value . "'";
            }
            $separator = ",";
        }
        return $values;
    }


    private function _getColumnNamesValues(): string
    {
        $ref = new \ReflectionClass(static::class);
        $this->_props = $ref->getProperties();
        $values = "";
        $separator = "";
        foreach ($this->_props as $prop) {
            $values = $separator . $prop->getName() . "=";
            $value = $this->{$prop->getName()};
            if ($value === null) {
                $values .= 'NULL';
            } else {
                $values .= "'" . $value . "'";
            }
            $separator = ",";
        }
        return $values;
    }
    */
}
