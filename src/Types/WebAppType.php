<?php

/* ---------------------------------------------------
  This file is part of the library arousacode/html_form_liblet

  This is free software an it is distributed under the license
  GPL v3.
  
  This software is distributed without any guaranty at all.

  http://www.gnu.org/licenses/gpl.html
 * ************************************************** */

namespace ArousaCode\WebApp\Types;

/**
 * 
 * In orther to help with the WebApp form, we need to know about the property type, beyond
 * the types description of PHP.
 * 
 * PHP has only \DateTime in orther to store time related values, but when working with HTMl forms
 * (and also databases) we will stora a Date, a Time our a DateTime (a timestamp).
 * 
 * Also we want to use different HTML inputs for nullable or non nullable booleans·
 * 
 * And finally, we want to define witch text fields use a TextArea.
 * 
 */

enum WebAppType
{
    case Date;
    case Time;
    case DateTime;
    case Text;
    case TextArea;
    case Int;
    case Float;
    case Bool;
    case NullableBool;
    case NonNullableBool;
    case Image;

    /**
     * Find out if the Web app type of the property
     *
     * @param \ReflectionProperty $prop 
     * @return HtmlFormWebAppType|null
     */
    public static function WebAppTypeFromProperty(\ReflectionProperty $prop): ?WebAppType
    {/*
        echo "<h1> Verificando tipo de data </h1>";

        echo "Verificando {$prop->getName()} -- {$prop->getAttributes()[0]->getName()} <br/>\n";

        $res1=$prop->getAttributes("ArousaCode\WebApp\Html\HtmlFormDateAttr");
        $res2=$prop->getAttributes("ArousaCode\WebApp\Html\HtmlFormTimeAttr");
        $res3=$prop->getAttributes("ArousaCode\WebApp\Html\HtmlFormDatetimeAttr");

        echo "<pre>";
        print_r($res1);
        print_r($res2);
        print_r($res3);
        echo "</pre>";
*/

        if ([] !== $prop->getAttributes("ArousaCode\WebApp\Types\Date")) {
            return WebAppType::Date;
        } elseif ([] !== $prop->getAttributes("ArousaCode\WebApp\Types\Time")) {
            return WebAppType::Time;
        } elseif ([] !== $prop->getAttributes("ArousaCode\WebApp\Types\DateTime")) {
            return WebAppType::DateTime;
        } elseif ([] !== $prop->getAttributes("ArousaCode\WebApp\Types\TextArea")) {
            return WebAppType::TextArea;
        } elseif ([] !== $prop->getAttributes("ArousaCode\WebApp\Types\DateTime")) {
            return WebAppType::DateTime;
        } elseif ([] !== $prop->getAttributes("ArousaCode\WebApp\Types\Image")) {
            return WebAppType::Image;
        } elseif ($prop->getType()->getName() == 'bool') {
            if ($prop->getType()->allowsNull()) {
                return WebAppType::NullableBool;
            } else {
                return WebAppType::NonNullableBool;
            }
        } elseif ($prop->getType()->getName() == 'int') {
            return WebAppType::Int;
        } elseif (($prop->getType()->getName() == 'float') || ($prop->getType()->getName() == 'double')) {
            return WebAppType::Float;
        } elseif ($prop->getType()->getName() == 'string') {
            return WebAppType::Text;
        } else {
            return null;
        }
    }

     /**
     * Find out if the Web app type of the database column
     *
     * @param string $dbTypeM 
     * @return HtmlFormWebAppType|null
     */
    public static function WebAppTypeFromDatabaseType(string $dbType): ?WebAppType
    {
        return match($dbType){
            'int4'=>WebAppType::Int,
            'numeric'=>WebAppType::Float,
            'date'=>WebAppType::Date,
            'time'=>WebAppType::Time,
            'timestamp'=>WebAppType::DateTime,
            'bool'=>WebAppType::Bool,
            'text'=>WebAppType::Text,
            default=>WebAppType::Text
        };
    }
}
