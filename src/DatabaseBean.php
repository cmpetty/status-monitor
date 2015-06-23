<?php

/**
 * DatabaseBean class
 * $Id: DatabaseBean.php,v 1.2 2007/01/18 23:40:59 dias Exp $
 */

include_once 'DatabaseConnection.php';

class DatabaseBean {
  var $connobj;
  var $query;
  var $result;
  var $error;

  function DatabaseBean() {
    if (!$this->connobj = new DatabaseConnection()) {
      $this->error = "Database connect error: ".$connobj->error;
      return FALSE;
    } else {
//      if (!$this->connobj->select()) {
//	$this->error = "Database "._DB_NAME_." could not be selected";
//	return FALSE;
//      }
    }
    register_shutdown_function( array( &$this, "_destructor_DatabaseBean_" ) );
  }
  
  function _destructor_DatabaseBean_ () {
    if($this->connobj != FALSE) {
      $this->connobj->disconnect();
    }
  }

  /**
   * This function escapes all characters and quotes
   * them in preparation for database inserts
   */
  function cleanString($cval,$maxlength=FALSE) {
    $qval = pg_escape_string($this->connobj->connection,$cval);
    return "'".$qval."'";
  }

  function escapeString($cval) {
    return pg_escape_string($this->connobj->connection,$cval);
  }

  function lastInsertID() {
    $this->doquery("SELECT lastval()");
    $id = pg_fetch_assoc($this->result);
    return $id['lastval'];
  }

  function numRows() {
    return pg_num_rows($this->result);
  }

  function affectedRows() {
    return pg_affected_rows($this->result);
  }

  function doQuery($query=FALSE) {
    if ($query)
      $this->query = $query;

    $this->result = pg_query($this->connobj->connection,$this->query);

    //print "<pre>";print_r($this);print "</pre>";   

    if (!$this->result) {
      $this->error = 'Query failed: ' . pg_last_error();
      return FALSE;
    }
    return TRUE;
  }
  
  function getFields($table) {
    $this->do_query("SHOW COLUMNS FROM $table");
    $fields = array();

    while($field_row = pg_fetch_array($this->result,PGSQL_ASSOC)) {
      $fields[$field_row["Field"]] = "";
    }
    return array_keys($fields);
  }

  function getRow() {
    $fields = pg_fetch_assoc($this->result);
    //print "<pre>";print_r($fields);print "</pre>";   
    return $fields;
  }

  /**
   *
   *
   * $orderby is column name as a string or column name and order as
   *   an array
   * 
   */

  function selectRows($table,$cols=FALSE,$fields=FALSE,$orderby=FALSE,$limit=FALSE) {
    if (!is_array($table))
      $table = array($table);
    $table_str = implode(',',$table);
    
    //column construction
    if ($cols) {
      if (!is_array($cols))
        $cols = array($cols);
      $col_str = implode(',',$cols);
    } else {
      $col_str = '*' ;
    }

    // field construction
    $where_str = "";
    if ($fields) {
      $where_str = "WHERE ";
      $field_expr = array();
      foreach ($fields as $key => $value) 
        array_push($field_expr,"$key=$value");
      $where_str .= implode(' AND ',$field_expr);
    }

    $query = "SELECT {$col_str} FROM {$table_str} {$where_str}";
    if ($orderby) {
      if (is_array($orderby)) {
	list($orderby,$sortorder) = $orderby;
	if ($sortorder && ($sortorder == 'ASC' || $sortorder == 'DESC'))
	  $query = $query . " ORDER BY " . $orderby . " " . $sortorder;
      } else {
	$query = $query . " ORDER BY " . $orderby;
      }
    }

    if ($limit && is_numeric($limit))
      $query = $query . " LIMIT " . $limit;

    $this->query = $query;
    return $this->doQuery();
  }

  function deleteRows($table,$where_fields) {
    if (!is_array($where_fields))
      die("deleteRows passed a non-array for 'where_fields'");

    $field_expr = array();
    $where_str = 'WHERE ';

    foreach ($where_fields as $key => $value) 
      array_push($field_expr,"$key=$value");
    $where_str .= implode(' AND ',$field_expr);
    $this->query = "DELETE FROM $table " . $where_str;

    return ($this->doQuery());
  }

  function updateRows($table,$set_fields,$where_fields=FALSE) {
    //table construction 
    $table_str = $table;

    //field construction
    if (!is_array($set_fields))
      die("updateRows passed a non-array for 'set_fields'");
    
    $set_str = 'SET ';
    $field_expr = array();
    foreach ($set_fields as $key => $value) 
      array_push($field_expr,"$key=$value");
    $set_str .= implode(',',$field_expr);

    if ($where_fields) {
      $field_expr = array();
      $where_str = 'WHERE ';
      foreach ($where_fields as $key => $value) 
	array_push($field_expr,"$key=$value");
      $where_str .= implode('AND',$field_expr);
    }

    $this->query = "UPDATE {$table_str} {$set_str} {$where_str}";
    return $this->doQuery();
  }

  function insertRows($table,$values) {
    $query = "INSERT INTO $table ";
    if (!is_array($values))
      return FALSE;

    $field_str = implode(',',array_keys($values));
    $value_str = implode(',',array_values($values));
    $this->query = "INSERT INTO $table (".$field_str.") VALUES(".$value_str.")";
    return $this->doQuery();
  }
} /*DatabaseBean*/

?>
