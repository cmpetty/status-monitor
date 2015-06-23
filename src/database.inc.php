<?php

include_once 'DatabaseConnection.php';
include_once 'DatabaseBean.php';

if (!defined("_DB_NAME_"))
  define ('_DB_NAME_','BIAC3');
if (!defined("_DB_USER_"))
  define ('_DB_USER_','webuser');
if (!defined("_DB_HOST_"))
  define ('_DB_HOST_','localhost');
if (!defined("_DB_PASS_"))
  define ('_DB_PASS_','popd94!');
if (!defined("_DB_PORT_"))
  define ('_DB_PORT_','5432');

function showerror() {
  die("Error : " . pg_last_error());
}

function mysqlclean($array, $index, $maxlength, $connection) {
  if (isset($array["{$index}"])) {
    $input = substr($array["{$index}"], 0, $maxlength);
    $input = pg_escape_string($connection,$input);
    return ($input);
  }
  return NULL;
}

function shellclean($array, $index, $maxlength) {
  if (isset($array["{$index}"])) {
    $input = substr($array["{$index}"], 0, $maxlength);
    $input = EscapeShellArg($input);
    return ($input);
  }
  return NULL;
}

?>
