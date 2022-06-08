<?php

/* ---------------------------------------------------
  This file is part of the library arousacode/html_form_liblet

  This is free software an it is distributed under the license
  GPL v3.
  
  This software is distributed without any guaranty at all.

  http://www.gnu.org/licenses/gpl.html
 * ************************************************** */

namespace ArousaCode\WebApp\Html;

use ArousaCode\WebApp\Types\Date;
use ArousaCode\WebApp\Types\DateTime;
use ArousaCode\WebApp\Types\Time;
use ArousaCode\WebApp\Types\TextArea;
use ArousaCode\WebApp\Types\DateTimeType;

trait FormOutput
{

    public function printHtmlInputField(string $name, $useObjectValue = true, string $elementExtraAttributes = '', bool $returnAsString = false): mixed
    {
        $ref = new \ReflectionClass(static::class);
        $prop = $ref->getProperty($name);
        if (!$prop->isInitialized($this)) {
            echo "Non inicializada!! ";
            $useObjectValue = false;
        }
        $type = strval($prop->getType());
        if ($type[0] == '?') {
            $type = substr($type, 1);
        }

        echo "TIPO : " . $type;
        switch ($type) {
            case 'bool':
                if ($prop->getType()->allowsNull()) {
                    return $this->_printHtmlNullableBoolean($name, $useObjectValue, $elementExtraAttributes, $returnAsString);
                } else {
                    return $this->_printHtmlNotNullableBoolean($name, $useObjectValue, $elementExtraAttributes, $returnAsString);
                }
                break;
            case 'DateTime':
                return $this->_printPHPDateTime($prop, $useObjectValue, $elementExtraAttributes, $returnAsString);
                break;
            case 'int':
                return $this->_printHtmlNumber($name, $useObjectValue, $elementExtraAttributes, $returnAsString);
                break;
            case 'float':
            case 'double':
                return $this->_printHtmlDouble($name, $useObjectValue, $elementExtraAttributes, $returnAsString);
                break;
                /* ## TBI 
            case 'array':
            case 'object':
                */
            default:
                return $this->_printHtmlText($prop, $useObjectValue, $elementExtraAttributes, $returnAsString);
        }
    }

    private function _printHtmlNotNullableBoolean(string $name, $useObjectValue = true, string $elementExtraAttributes = '', bool $returnAsString = false): mixed
    {
        if ($useObjectValue) {
            $initValue = $this->$name ? 1 : 0;
            $checkedAttribute = $this->$name ? ' checked ' : '';
        } else {
            $initValue = 0;
            $checkedAttribute = '';
        }
        $fieldHTMLsrc = "<input type='hidden' name='$name' value='$initValue'>";
        $fieldHTMLsrc .= "<input type='checkbox' onclick='this.previousSibling.value=1-this.previousSibling.value' $checkedAttribute $elementExtraAttributes> ";
        if ($returnAsString) {
            return $fieldHTMLsrc;
        } else {
            echo $fieldHTMLsrc;
            return null;
        }
    }

    private function _printHtmlNullableBoolean(string $name, $useObjectValue = true, string $elementExtraAttributes = '', bool $returnAsString = false): mixed
    {
        if (($useObjectValue) && ($this->$name  !== null)) {
            if ($this->$name) {
                $yesChecked = ' checked ';
                $noChecked = ' ';
            } else {
                $yesChecked = ' ';
                $noChecked = ' checked ';
            }
        } else {
            $yesChecked = '  ';
            $noChecked = '  ';
        }
        $fieldHTMLsrc = '<div $elementExtraAttributes> ';
        $fieldHTMLsrc .= "<input type='button' value='X' onclick='var v=document.getElementsByName(\"$name\"); for(i=0;i<v.length;i++){v[i].checked=false;}' >\n";
        $fieldHTMLsrc .= "<input type='radio' name='$name' id='{$name}Yes' value='1' $yesChecked ><label for='{$name}Yes'>Si</label>\n";
        $fieldHTMLsrc .= "<input type='radio' name='$name' id='{$name}No' value='0' $noChecked ><label for='{$name}No'>No</label>\n";
        $fieldHTMLsrc .= "</div> \n";
        if ($returnAsString) {
            return $fieldHTMLsrc;
        } else {
            echo $fieldHTMLsrc;
            return null;
        }
    }

    private function _printPHPDateTime(\ReflectionProperty $prop, $useObjectValue = true, string $elementExtraAttributes = '', bool $returnAsString = false): mixed
    {
        $name = $prop->getName();
        return match(DateTimeType::DateTimeTypeFromAttribute($prop)){
            DateTimeType::DateTime => $this->_printHtmlDateTime($name, $useObjectValue, $elementExtraAttributes, $returnAsString),
            DateTimeType::Date     => $this->_printHtmlDate($name, $useObjectValue, $elementExtraAttributes, $returnAsString),
            DateTimeType::Time     => $this->_printHtmlTime($name, $useObjectValue, $elementExtraAttributes, $returnAsString),
            default=> new \Exception("Error: the \DateTime property $name doesn't have defined the subtype using the library Attributes"),
        };
    }

    private function _printHtmlDateTime(string $name, $useObjectValue = true, string $elementExtraAttributes = '', bool $returnAsString = false): mixed
    {
        $initValueDate = $useObjectValue ? $this->$name?->format('Y-m-d') : '';
        $initValueTime = $useObjectValue ? $this->$name?->format('H:i:s') : '';
        $fieldHTMLsrc = "<input type='date' name='$name' id='$name' value='$initValueDate' $elementExtraAttributes /> : ";
        $fieldHTMLsrc .= "<input type='time' name='$name' id='$name' value='$initValueTime' $elementExtraAttributes />";
        if ($returnAsString) {
            return $fieldHTMLsrc;
        } else {
            echo $fieldHTMLsrc;
            return null;
        }
    }

    private function _printHtmlDate(string $name, $useObjectValue = true, string $elementExtraAttributes = '', bool $returnAsString = false): mixed
    {
        $initValue = $useObjectValue ? $this->$name?->format('Y-m-d') : '';
        $fieldHTMLsrc = "<input type='date' name='$name' id='$name' value='$initValue' $elementExtraAttributes />";
        if ($returnAsString) {
            return $fieldHTMLsrc;
        } else {
            echo $fieldHTMLsrc;
            return null;
        }
    }

    private function _printHtmlTime(string $name, $useObjectValue = true, string $elementExtraAttributes = '', bool $returnAsString = false): mixed
    {
        $initValue = $useObjectValue ? $this->$name?->format('H:i:s') : '';
        $fieldHTMLsrc = "<input type='time' name='$name' id='$name' value='$initValue' $elementExtraAttributes />";
        if ($returnAsString) {
            return $fieldHTMLsrc;
        } else {
            echo $fieldHTMLsrc;
            return null;
        }
    }

    private function _printHtmlNumber(string $name, $useObjectValue = true, string $elementExtraAttributes = '', bool $returnAsString = false): mixed
    {
        $initValue = $useObjectValue ? $this->$name : '';
        $fieldHTMLsrc = "<input type='number' name='$name' id='$name' value='$initValue' $elementExtraAttributes />";
        if ($returnAsString) {
            return $fieldHTMLsrc;
        } else {
            echo $fieldHTMLsrc;
            return null;
        }
    }

private function _printHtmlDouble(string $name, $useObjectValue = true, string $elementExtraAttributes = '', bool $returnAsString = false): mixed
    {
        $initValue = $useObjectValue ? $this->$name : '';
        $fieldHTMLsrc = "<input type='text' name='$name' title='Número decimal con signo, p.ex. 34.56 o -123.5'  pattern='^-?\d*(\.\d+)?$' id='$name' value='$initValue' $elementExtraAttributes />";
        if ($returnAsString) {
            return $fieldHTMLsrc;
        } else {
            echo $fieldHTMLsrc;
            return null;
        }
    }



    private function _printHtmlText(\ReflectionProperty $prop, $useObjectValue = true, string $elementExtraAttributes = '', bool $returnAsString = false): mixed
    {        
        $name=$prop->getName();
        $initValue = $useObjectValue ? $this->$name : '';
        if([] == $prop->getAttributes("ArousaCode\WebApp\Types\TextArea")){
            //Marked as TextArea
            $fieldHTMLsrc = "<textarea name='$name' id='$name' $elementExtraAttributes>$initValue  </textarea>";
        }
        else{
            $fieldHTMLsrc = "<input type='text' name='$name' id='$name' value='$initValue' $elementExtraAttributes />";
        }

        if ($returnAsString) {
            return $fieldHTMLsrc;
        } else {
            echo $fieldHTMLsrc;
            return null;
        }
    }

}