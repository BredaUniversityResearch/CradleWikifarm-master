<?php
/** Latvian (latviešu)
 *
 * To improve a translation please visit https://translatewiki.net
 *
 * @ingroup Language
 * @file
 *
 * @author Admresdeserv.
 * @author Dark Eagle
 * @author Edgars2007
 * @author FnTmLV
 * @author Geimeris
 * @author Geitost
 * @author Gleb Borisov
 * @author GreenZeb
 * @author Kaganer
 * @author Karlis
 * @author Kikos
 * @author Knakts
 * @author Marozols
 * @author Papuass
 * @author Reedy
 * @author Srolanh
 * @author Xil
 * @author Yyy
 * @author לערי ריינהארט
 */

/**
 * @copyright Copyright © 2006, Niklas Laxström
 * @license GPL-2.0-or-later
 */

$linkTrail = '/^([a-zA-ZĀāČčĒēĢģĪīĶķĻļŅņŠšŪūŽž]+)(.*)$/sDu';

$namespaceNames = [
	NS_MEDIA            => 'Media',
	NS_SPECIAL          => 'Special',
	NS_TALK             => 'Diskusija',
	NS_USER             => 'Dalībnieks',
	NS_USER_TALK        => 'Dalībnieka_diskusija',
	NS_PROJECT_TALK     => '{{grammar:ģenitīvs|$1}}_diskusija',
	NS_FILE             => 'Attēls',
	NS_FILE_TALK        => 'Attēla_diskusija',
	NS_MEDIAWIKI        => 'MediaWiki',
	NS_MEDIAWIKI_TALK   => 'MediaWiki_diskusija',
	NS_TEMPLATE         => 'Veidne',
	NS_TEMPLATE_TALK    => 'Veidnes_diskusija',
	NS_HELP             => 'Palīdzība',
	NS_HELP_TALK        => 'Palīdzības_diskusija',
	NS_CATEGORY         => 'Kategorija',
	NS_CATEGORY_TALK    => 'Kategorijas_diskusija',
];

$namespaceAliases = [
	'Lietotājs' => NS_USER,
	'Lietotāja_diskusija' => NS_USER_TALK,
];

$namespaceGenderAliases = [
	NS_USER => [ 'male' => 'Dalībnieks', 'female' => 'Dalībniece' ],
	NS_USER_TALK => [ 'male' => 'Dalībnieka_diskusija', 'female' => 'Dalībnieces_diskusija' ]
];

$separatorTransformTable = [ ',' => "\xc2\xa0", '.' => ',' ];

/**
 * A list of date format preference keys, which can be selected in user
 * preferences. New preference keys can be added, provided they are supported
 * by the language class's timeanddate(). Only the 5 keys listed below are
 * supported by the wikitext converter (parser/DateFormatter.php).
 *
 * The special key "default" is an alias for either dmy or mdy depending on
 * $wgAmericanDates
 */
$datePreferences = [
	'default',
	'ydm',
	'mdy',
	'dmy',
	'ymd',
	'ISO 8601',
];

/**
 * The date format to use for generated dates in the user interface.
 * This may be one of the above date preferences, or the special value
 * "dmy or mdy", which uses mdy if $wgAmericanDates is true, and dmy
 * if $wgAmericanDates is false.
 */
$defaultDateFormat = 'ydm';

/**
 * Associative array mapping old numeric date formats, which may still be
 * stored in user preferences, to the new string formats.
 */
$datePreferenceMigrationMap = [
	'default',
	'mdy',
	'dmy',
	'ymd'
];

/**
 * These are formats for dates generated by MediaWiki (as opposed to the wikitext
 * DateFormatter). Documentation for the format string can be found in
 * Language.php, search for sprintfDate.
 *
 * This array is automatically inherited by all subclasses. Individual keys can be
 * overridden.
 */
$dateFormats = [
	'ydm time' => 'H.i',
	'ydm date' => 'Y". gada" j. F',
	'ydm monthonly' => 'Y". gada" F',
	'ydm both' => 'Y". gada" j. F", plkst." H.i',
	'ydm pretty' => 'j F',

	'mdy time' => 'H:i',
	'mdy date' => 'F j, Y',
	'mdy monthonly' => 'F Y',
	'mdy both' => 'H:i, F j, Y',
	'mdy pretty' => 'F j',

	'dmy time' => 'H:i',
	'dmy date' => 'j F Y',
	'dmy monthonly' => 'F Y',
	'dmy both' => 'H:i, j F Y',
	'dmy pretty' => 'j F',

	'ymd time' => 'H:i',
	'ymd date' => 'Y F j',
	'ymd monthonly' => 'Y F',
	'ymd both' => 'H:i, Y F j',
	'ymd pretty' => 'F j',

	'ISO 8601 time' => 'xnH:xni:xns',
	'ISO 8601 date' => 'xnY-xnm-xnd',
	'ISO 8601 monthonly' => 'xnY-xnm',
	'ISO 8601 both' => 'xnY-xnm-xnd"T"xnH:xni:xns',
	'ISO 8601 pretty' => 'xnm-xnd'
];
