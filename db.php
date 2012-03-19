<?php

Class DB {
  private static $conn;
  
  function __construct() {
    self::$conn = mysql_connect('YOUR_DB_SERVER', 'YOUR_DB_USERNAME', 'YOUR_DB_PASSWORD');
    
    if (!self::$conn) {
      die('Could not connect: ' . mysql_error());
    }
    
    mysql_select_db("YOUR_DB_NAME", self::$conn);
  }
  
  
  function install_tables() {
    $this->query(
      "CREATE TABLE user_likes (
        id INT NOT NULL AUTO_INCREMENT,
        like_id BIGINT NOT NULL DEFAULT 0,
        like_name VARCHAR(255) NOT NULL DEFAULT '',
        user_name VARCHAR(255) NOT NULL DEFAULT '',
        like_category VARCHAR(255) NOT NULL DEFAULT '',
        uid BIGINT NOT NULL DEFAULT 0,
        PRIMARY KEY (id)
      );");
  }
  
  function empty_tables() {
    $this->query(
      "DELETE from user_likes;");
  }

  /* Takes a format string and an list of arguments and substitutes the format
  codes in the format string for the elements of the arguments list. Similar to
  vsprintf, but specialized for generating SQL queries.
  
  Format codes are denoted by a percent sign (%) followed by another character. To
  insert a literal percent character into the output string, escape it (%%). Each
  successive format code is replaced by its corresponding argument in the list.
  
  %%: Replaced by a literal percent sign.
  %s: The argument, which must be a string, is used verbatim.
  
  %S: Replaced by the argument, which is escaped with addslashes.
  %q: Use for quoted strings. Like %S, but the value is also surrounded with quote
  marks.
  %l: Use for LIKE strings. Like %q, but the _ and % characters are also escaped.
  %d: The argument, which should be numeric, is used verbatim. Non-numeric
  arguments will be converted into numbers first.
  %n: Like %q, but empty strings are replaced with NULL instead of ''.
  %t: Like %n, but converts the result to a timestamp with FROM_UNIXTIME().
  
  Parameters:
  1. The format string.
  2+. Any arguments needed by the format string.
  */
  function query($query) {
    $argument = 0;
    
    for ($i = strpos($query, '%'); $i !== false; $i = strpos($query, '%', $i + strlen($replace))) {
      $value = func_get_arg(++$argument);
      
      switch (substr($query, $i + 1, 1)) {
        case '%': $replace = '%'; --$argument; break;
        case 's': $replace = mysql_real_escape_string($value); break;
        
        case 'S': $replace = mysql_real_escape_string($value); break;
        case 'q': $replace = format('"%S"', $value); break;
        case 'l': $replace = preg_replace('/([%_\\\'"])/', '\\\\1', $value); break;
        case 'd': $replace = mysql_real_escape_string($value); break;
        case 'n': $replace = (strlen($value) == 0 ? 'NULL' : format('%q', $value)); break;
        case 't': $replace = format('FROM_UNIXTIME(%n)', $value); break;
        
        default: $replace = '';
      }
      
      $query = substr_replace($query, $replace, $i, 2);
    }
    
    $result = mysql_query($query);
    
    $mysql_error_num = mysql_errno(self::$conn);
    $mysql_error = mysql_error(self::$conn);
    
    if ($result != 'true') {
      error_log('MYSQL QUERY: ' . var_export($query,1));
      
      //error_log('MYSQL ERROR: ' . var_export($mysql_error_num, 1) . ": " . var_export($mysql_error, 1). "\n");
      //error_log('MYSQL RESULT: ' . var_export($result,1));
    }
    
    return $result;
  }
}
