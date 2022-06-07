<?php

/* ---------------------------------------------------
  This file is part of the library arousacode/html_form_liblet

  This is free software an it is distributed under the license
  GPL v3.
  
  This software is distributed without any guaranty at all.

  http://www.gnu.org/licenses/gpl.html
 * ************************************************** */

namespace ArousaCode\WebApp\Html;

trait FormInput
{
     /**
     * Load html data from Form.
     * 
     * It only load data to properties if field with its name is recibed in GET or POST.
     * 
     * @param int $type One of INPUT_GET, INPUT_POST
     * @throws Exception
     */
    public function loadData(int $type, $filter = FILTER_DEFAULT): void
    {
        $ref = new \ReflectionClass(static::class);
        $props = $ref->getProperties();

        $emptyMandatoryProperties = [];

        foreach ($props as $prop) {
            $propName = $prop->getName();

            $data = filter_input($type, $propName, $filter);

            if ($data === false) {
                throw new \Exception("Erro filtrando propieade $propName con valor " . filter_input($type, $propName));
            }

            if ($data !== null) {
                $this->$propName = $this->_getPropertyValueFromData($prop, $data);
            } elseif (!$prop->isInitialized($this)) {
                //If we don't receive data, we respect previously assigned data. Else, we set null to initialize.
                $this->$propName = null;
            }
        }
    }

    private function _getPropertyValueFromData(\ReflectionProperty $prop, mixed $data): mixed
    {
        $type = strval($prop->getType());
        if ($type[0] == '?') {
            $type = substr($type, 1);
        }
        switch ($type) {
            case 'bool':
                return ($data == '1');
                break;
            case 'DateTime':
                return ($data != '') ? new \DateTime($data) : null;
                break;
            case 'int':
                return intval($data);
                break;
            case 'float':
            case 'double':
                return doubleval($data);
                break;
                /* ## TBI 
                case 'array':
                case 'object':
                    */
            default:
                return $data;
        }
    }
/*
    private function _getDateTimeValue(\ReflectionProperty $prop, mixed $data): ?\DateTime{

    }




    return match(HtmlFormDateTimeType::getDateTimeFormTypeFromAttribute($prop)){
        HtmlFormDateTimeType::DateTime => $this->_printHtmlDateTime($name, $useObjectValue, $elementExtraAttributes, $returnAsString),
        HtmlFormDateTimeType::Date     => $this->_printHtmlDate($name, $useObjectValue, $elementExtraAttributes, $returnAsString),
        HtmlFormDateTimeType::Time     => $this->_printHtmlTime($name, $useObjectValue, $elementExtraAttributes, $returnAsString),
        default=> new \Exception("Error: the \DateTime property $name doesn't have defined the subtype using the library Attributes"),
    }*/
}