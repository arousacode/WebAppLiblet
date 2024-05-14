<?php

/* ---------------------------------------------------
  This file is part of the library arousacode/html_form_liblet

  This is free software an it is distributed under the license
  GPL v3.
  
  This software is distributed without any guaranty at all.

  http://www.gnu.org/licenses/gpl.html
 * ************************************************** */

namespace ArousaCode\WebApp\Types;

use ArousaCode\WebApp\Pdo\PDOExtended;

/**
 * This attribute is used to declare an image (small) that will be stored in a string and embeded in the HTML document.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Selection
{
  function __construct(
    public  string $tableName,
    public  ?string $schemaName=null,
    public string $valueColumn,
    public ?string $descColumn = null,
    public bool $multiple = false,
    /**
     * User must guaranty sql safe values.
     * Use double colons to special characters or upptercase column names
     */
    public ?string $sqlOrder = null,
    /**
     * User must guaranty sql safe values
     * Use double colons to special characters or upptercase column names
     */
    public ?string $sqlFilter = null
  ){    
  }

}
