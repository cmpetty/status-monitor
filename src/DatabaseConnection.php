<?php

/**
 * DatabaseConnection class
 * $Id: DatabaseConnection.php,v 1.1.1.1 2007/01/12 21:10:57 dias Exp $
 */

class DatabaseConnection {
  var $connection;
  var $error;

  /**
   * DatabaseConnection - returns database connect results
   */
  function DatabaseConnection() {
    $this->connection = FALSE;
    $this->error = FALSE;
    return ($this->connect());
  }

  /**
   * creates a connection to the database server
   * requires _DB_HOST_, _DB_USER_, _DB_PASS_ to be defined
   */
  function connect() {
    $constr = "host="._DB_HOST_." user="._DB_USER_." password="._DB_PASS_." port="._DB_PORT_." dbname="._DB_NAME_;       
    if (!$this->connection = pg_connect($constr) ) {
      $this->error = 'Could not connect: ' . pg_last_error();
      $this->error .= $constr;
      return FALSE;
        print_r($this);
    }
    return TRUE;
  }

  /**
   * selects database
   * requires _DB_NAME_ to be defined and valid db connection
   */
/*
  function select() {
    if (!mysql_select_db(_DB_NAME_,$this->connection)) {
      $this->error = "Database Select error: ". mysql_error();
      return FALSE; 
    }
    return TRUE;
  }
*/

  function disconnect() {
    if (pg_close($this->connection)){
      $this->connection = FALSE;
    };
  }
}

?>
