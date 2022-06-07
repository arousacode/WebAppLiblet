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
   
namespace ArousaCode\WebApp\Types;

/**
 * Attribute to declare that the ObjectProperty of
 * type \DateTime will be stored in the database with Timezone
 *
 * @author arousacode
 */

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class WithTimeZone {     
}
