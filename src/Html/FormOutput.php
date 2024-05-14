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
use ArousaCode\WebApp\Types\WebAppType;

trait FormOutput
{

    public function printHtmlLabel(string $name, string $text = null, string $labelExtraAttributes = '', bool $returnAsString = false): mixed
    {
        if ($text == null) {
            $text = $name;
        }
        $labelHTMLsrc = "<label $labelExtraAttributes for='$name'>$text</label>";
        if ($returnAsString) {
            return $labelHTMLsrc;
        } else {
            echo $labelHTMLsrc;
            return null;
        }
    }
    public function printHtmlInputField(string $name, $useObjectValue = true, string $elementExtraAttributes = '', bool $returnAsString = false, \Pdo $pdo = null): mixed
    {
        $ref = new \ReflectionClass(static::class);
        $prop = $ref->getProperty($name);
        $required = !$prop->getType()->allowsNull();
        if (!$prop->isInitialized($this)) {
            /* !! Very important.
            If we print the Form for an empty object, with unitialized properties, thosw shouldn't be used or
            an error will be raised */
            $useObjectValue = false;
        }
        $type = strval($prop->getType());
        if ($type[0] == '?') {
            $type = substr($type, 1);
        }

        /** IF marked as Hidden, then input type=hidden */
        if (WebAppType::Hidden == WebAppType::WebAppTypeFromProperty($prop)) {
            return $this->_printHtmlHidden($prop, $useObjectValue, $elementExtraAttributes, $returnAsString);
        }
        /** IF marked as Selection, then <select ... */
        if (WebAppType::Selection == WebAppType::WebAppTypeFromProperty($prop)) {
            return $this->_printHtmlSelection($prop, $useObjectValue, $elementExtraAttributes, $returnAsString, $pdo);
        }
        //##DEBUG echo "TIPO : " . $type;
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
                return $this->_printHtmlNumber($name, $required, $useObjectValue, $elementExtraAttributes, $returnAsString);
                break;
            case 'float':
            case 'double':
                return $this->_printHtmlDouble($name, $required, $useObjectValue, $elementExtraAttributes, $returnAsString);
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
        $fieldHTMLsrc = "<div $elementExtraAttributes> ";
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
        $required = !$prop->getType()->allowsNull();
        return match (WebAppType::WebAppTypeFromProperty($prop)) {
            WebAppType::DateTime => $this->_printHtmlDateTime($name, $required, $useObjectValue, $elementExtraAttributes, $returnAsString),
            WebAppType::Date     => $this->_printHtmlDate($name, $required, $useObjectValue, $elementExtraAttributes, $returnAsString),
            WebAppType::Time     => $this->_printHtmlTime($name, $required, $useObjectValue, $elementExtraAttributes, $returnAsString),
            default => new \Exception("Error: the \DateTime property $name doesn't have defined the subtype using the library Attributes"),
        };
    }

    private function _printHtmlDateTime(string $name, bool $required, $useObjectValue = true, string $elementExtraAttributes = '', bool $returnAsString = false): mixed
    {
        $requiredAttribute = $required ? " required " : "";
        $initValueDate = $useObjectValue ? $this->$name?->format('Y-m-d') : '';
        $initValueTime = $useObjectValue ? $this->$name?->format('H:i:s') : '';
        $fieldHTMLsrc = "<input $requiredAttribute type='date' name='$name' id='$name' value='$initValueDate' $elementExtraAttributes /> : ";
        $fieldHTMLsrc .= "<input $requiredAttribute type='time' name='$name' id='$name' value='$initValueTime' $elementExtraAttributes />";
        if ($returnAsString) {
            return $fieldHTMLsrc;
        } else {
            echo $fieldHTMLsrc;
            return null;
        }
    }

    private function _printHtmlDate(string $name, bool $required, $useObjectValue = true, string $elementExtraAttributes = '', bool $returnAsString = false): mixed
    {
        $requiredAttribute = $required ? " required " : "";
        $initValue = $useObjectValue ? $this->$name?->format('Y-m-d') : '';
        $fieldHTMLsrc = "<input  $requiredAttribute type='date' name='$name' id='$name' value='$initValue' $elementExtraAttributes />";
        if ($returnAsString) {
            return $fieldHTMLsrc;
        } else {
            echo $fieldHTMLsrc;
            return null;
        }
    }

    private function _printHtmlTime(string $name, bool $required, $useObjectValue = true, string $elementExtraAttributes = '', bool $returnAsString = false): mixed
    {
        $requiredAttribute = $required ? " required " : "";
        $initValue = $useObjectValue ? $this->$name?->format('H:i:s') : '';
        $fieldHTMLsrc = "<input  $requiredAttribute type='time' name='$name' id='$name' value='$initValue' $elementExtraAttributes />";
        if ($returnAsString) {
            return $fieldHTMLsrc;
        } else {
            echo $fieldHTMLsrc;
            return null;
        }
    }

    private function _printHtmlNumber(string $name, bool $required, $useObjectValue = true, string $elementExtraAttributes = '', bool $returnAsString = false): mixed
    {
        $requiredAttribute = $required ? " required " : "";
        $initValue = $useObjectValue ? $this->$name : '';
        $fieldHTMLsrc = "<input $requiredAttribute  type='number' name='$name' id='$name' value='$initValue' $elementExtraAttributes />";
        if ($returnAsString) {
            return $fieldHTMLsrc;
        } else {
            echo $fieldHTMLsrc;
            return null;
        }
    }

    private function _printHtmlDouble(string $name, bool $required, $useObjectValue = true, string $elementExtraAttributes = '', bool $returnAsString = false): mixed
    {
        $requiredAttribute = $required ? " required " : "";
        $initValue = $useObjectValue ? $this->$name : '';
        $fieldHTMLsrc = "<input  $requiredAttribute type='text' name='$name' title='Número decimal con signo, p.ex. 34.56 o -123.5'  pattern='^-?\d*(\.\d+)?$' id='$name' value='$initValue' $elementExtraAttributes />";
        if ($returnAsString) {
            return $fieldHTMLsrc;
        } else {
            echo $fieldHTMLsrc;
            return null;
        }
    }



    private function _printHtmlText(\ReflectionProperty $prop, $useObjectValue = true, string $elementExtraAttributes = '', bool $returnAsString = false): mixed
    {
        $name = $prop->getName();
        $requiredAttribute = (!$prop->getType()->allowsNull()) ? " required " : "";
        $initValue = $useObjectValue ? $this->$name : '';
        if (WebAppType::Image == WebAppType::WebAppTypeFromProperty($prop)) {
            $src = 'data: ' . mime_content_type($initValue) . ';base64,' . $initValue;

            // Echo out a sample image
            echo '<img $requiredAttribute src="' . $src . '">';
        } elseif (WebAppType::TextArea == WebAppType::WebAppTypeFromProperty($prop)) {
            //Marked as TextArea
            $fieldHTMLsrc = "<textarea $requiredAttribute name='$name' id='$name' $elementExtraAttributes>$initValue</textarea>";
        } else {
            $fieldHTMLsrc = "<input $requiredAttribute type='text' name='$name' id='$name' value='$initValue' $elementExtraAttributes />";
        }

        if ($returnAsString) {
            return $fieldHTMLsrc;
        } else {
            echo $fieldHTMLsrc;
            return null;
        }
    }

    private function _printHtmlHidden(\ReflectionProperty $prop, $useObjectValue = true, string $elementExtraAttributes = '', bool $returnAsString = false): mixed
    {
        $name = $prop->getName();
        $requiredAttribute = (!$prop->getType()->allowsNull()) ? " required " : "";
        $initValue = $useObjectValue ? $this->$name : '';
        $fieldHTMLsrc = "<input $requiredAttribute type='hidden' name='$name' id='$name' value='$initValue' $elementExtraAttributes />";
        if ($returnAsString) {
            return $fieldHTMLsrc;
        } else {
            echo $fieldHTMLsrc;
            return null;
        }
    }

    private function _printHtmlSelection(\ReflectionProperty $prop, $useObjectValue = true, string $elementExtraAttributes = '', bool $returnAsString = false, \PDO $pdo): mixed
    {
        $name = $prop->getName();
        $requiredAttribute = (!$prop->getType()->allowsNull()) ? " required " : "";
        $initValue = $useObjectValue ? $this->$name : '';
        $attributes = $prop->getAttributes('ArousaCode\WebApp\Types\Selection');
        $arguments = $attributes[0]->getArguments();

        $fullSqlTableName = '"' . $arguments['tableName'] . '"';
        if (isset($arguments['schemaName'])) {
            $fullSqlTableName = '"' . $arguments['schemaName'] . '"."' . $arguments['tableName'] . '"';
        } else {
            $fullSqlTableName = '"' . $arguments['tableName'] . '"';
        }

        $sqlValueCol = '"' . $arguments['valueColumn'] . '"';

        if (isset($arguments['descColum'])) {

            $sqlDescCol = '"' . $arguments['descColumn'] . '"';
        } else {
            $sqlDescCol = $sqlValueCol;
        }

        $multiple=$arguments['multiple']? "multiple ":"";

        if (isset($arguments['sqlOrder'])) {
            $sqlOrder=" ORDER BY ".$arguments['sqlOrder']." ";
        }
        else{
            $sqlOrder="";
        }

        if (isset($arguments['sqlFilter'])) {
            $sqlFilter=" WHERE ".$arguments['sqlFilter']." ";
        }
        else{
            $sqlFilter="";
        }

        
        $cmd="SELECT $sqlValueCol, $sqlDescCol FROM  $fullSqlTableName $sqlFilter $sqlOrder";
        //echo "$cmd <br/>";
        $resSt = $this->_db->query($cmd);
        $rows = $resSt->fetchAll(\PDO::FETCH_BOTH);

        $fieldHTMLsrc = "<select  $requiredAttribute  name='$name' id='$name' $multiple $elementExtraAttributes />";
        foreach($rows as $row){
            $selected=($initValue==$row[0])?" selected ":"";
            echo "<option $selected value='{$row[0]}'>{$row[1]}</option>";

        }
        $fieldHTMLsrc.=" </select>";
        if ($returnAsString) {
            return $fieldHTMLsrc;
        } else {
            echo $fieldHTMLsrc;
            return null;
        }
    }
}
