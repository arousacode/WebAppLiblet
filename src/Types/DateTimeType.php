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
 * PHP has only \DateTime in orther to store time related values, but when working with HTMl forms
 * (and also databases) we will stora a Date, a Time our a DateTime (a timestamp).
 * 
 */

enum DateTimeType{
    case Date;
    case Time;
    case DateTime;

    /**
     * Find out if the \DateTime property is used to stoer Date, Time or both using Attributes defined in library.
     *
     * @param \ReflectionProperty $prop 
     * @return HtmlFormDateTimeType|null
     */
    public static function DateTimeTypeFromAttribute(\ReflectionProperty $prop): ?DateTimeType
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
            return DateTimeType::Date;
        } elseif ([] !== $prop->getAttributes("ArousaCode\WebApp\Types\Time")) {
            return DateTimeType::Time;
        } elseif ( [] !== $prop->getAttributes("ArousaCode\WebApp\Types\DateTime")) {
            return DateTimeType::DateTime;
        } else {
            return null;
        }
    }
}