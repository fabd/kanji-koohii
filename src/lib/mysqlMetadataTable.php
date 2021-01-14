<?php
/**
 * mysqlMetadataTable provides useful methods to get metadata from a MySQL database table.
 * 
 * array<object>  getColumnsInfo()
 * array<string>  getColumnNames()
 * object         getColumnDescription($colName)
 * object         getAutoIncrementColumn()
 * object         getColumnTypeInfo($colName)
 * array<string>  getPrimaryKeys()
 * array          getPrimaryKeysFromArray(array $colData)
 * boolean        isNumeric($colName)
 * boolean        isDateTime($colName)
 * 
 * 
 * @author  Fabrice Denis
 */

class mysqlMetadataTable
{
  protected
    $columns = null;
  
  /**
   * 
   * @return 
   * @param object $db  coreDatabase
   * @param object $tableName
   */
  public function __construct(coreDatabase $db, $tableName)
  {
    $this->db = $db;
    $this->tableName = $tableName;
  }
  
  /**
   * Return the DESCRIBE query information from a MySQL table, as an array of objects.
   * 
   * Each objet in the array contain the properties:
   * 
   *   Field   : column name
   *   Type    : column data type
   *   Null    : "YES" if NULL values can be stored in the column or "NO" (or "" prior to MySQL 5.0.3, equivalent to "NO")
   *   Key     : "PRI" if the column is a PRIMARY KEY or is one of the columns in a multiple-column PRIMARY KEY.
   *             "UNI" for first column of a unique-valued index that cannot contain NULL values
   *             "MUL" the column is the first column of a non-unique index or a unique-valued
   *                   index that can contain NULL values.
   *   Default : indicates the default value that is assigned to the column.
   *   Extra   : "auto_increment" if the column was created with the AUTO_INCREMENT keyword and empty otherwise.
   * 
   * @see    http://dev.mysql.com/doc/refman/5.0/en/show-columns.html
   * @return array Array of objects, one for each column
   */
  public function getColumnsInfo()
  {
    if ($this->columns===null)
    {
      $fetchMode = $this->db->setFetchMode(coreDatabase::FETCH_OBJ);
      $this->columns = $this->db->fetchAll('SHOW COLUMNS FROM ?', new coreDbExpr($this->tableName));
      $this->db->setFetchMode($fetchMode);
    }

    return $this->columns;
  }

  /**
   * Return array of column names from this table.
   * 
   * @return 
   */
  public function getColumnNames()
  {
    $colnames = [];
    foreach ($this->getColumnsInfo() as $colInfo)
    {
      $colnames[] = $colInfo->Field;
    }
    return $colnames;
  }

  /**
   * Return DESCRIBE data for one named column as an object.
   * 
   * Properties of returned object:
   * 
   *   Field
   *   Type
   *   Null
   *   Key
   *   Default
   *   Extra
   *   
   * @see    getColumnsInfo()
   * @return object 
   */
  public function getColumnDescription($colName)
  {
    foreach ($this->getColumnsInfo() as $colInfo)
    {
      if ($colInfo->Field==$colName)
      {
        return $colInfo;
      }
    }
    return null;
  }

  /**
   * Return DESCRIBE data for the auto_increment column if present,
   * otherwise returns null.
   * 
   * @return mixed  Describe object (see getColumnDescription()), or null
   */
  public function getAutoIncrementColumn()
  {
    foreach ($this->getColumnsInfo() as $colInfo)
    {
      if ($colInfo->Extra=='auto_increment')
      {
        return $colInfo;
      }
    }
    return null;
  }

  /**
   * Returns mysql DESCRIBE 'Type' as object with ->type and ->length properties
   * 
   * @return object
   */
  public function getColumnTypeInfo($colName)
  {
    $colDesc = $this->getColumnDescription($colName);
    return $this->parseColumnType($colDesc->Type);
  }

  /**
   * Parse mysql DESCRIBE 'Type' into base type and length.
   * 
   * eg.  'char(32)' 
   * 
   * Returns object with ->type and ->length properties.
   * Float length eg 'float(3,4)' returns only the integer part.
   * 
   * @return object 
   * @param string $col_type  MySQL column Type eg. varchar(32)
   */
  public function parseColumnType($col_type)
  {
    $matches = [];
    $typeInfo = [];

    if (preg_match('/^(\w+)\(([0-9,]+)\)/', $col_type, $matches))
    {
//echo '<p>'.$col_type;
//DBG::printr($matches);exit;
      $typeInfo['type'] = $matches[1];
      $typeInfo['length'] = (int) $matches[2];
    }
    else
    {
      
      $typeInfo['type'] = $col_type;
      $typeInfo['length'] = null;
    }

    return (object) $typeInfo;
  }

  /**
   * Returns array of one or more primary keys.
   * 
   * @return array   Name of columns that are primary keys
   */
  public function getPrimaryKeys()
  {
    $primaryKeys = [];
    foreach ($this->getColumnsInfo() as $colInfo)
    {
      if ($colInfo->Key=='PRI')
      {
        $primaryKeys[] = $colInfo->Field;
      }
    }
    return $primaryKeys;
  }
  
  /**
   * Returns array of primary keys and values obtained from given array of column data.
   * 
   * Throws an exception if one of the primary key doesn't exist in given array.
   *
   * @param  array  Associative array of colmun data
   * @return array  Associative array containing only the primary keys and their values.
   */
  public function getPrimaryKeysFromArray(array $colData)
  {
    $primaryKeyValues = [];
    foreach ($this->getPrimaryKeys() as $keyName)
    {
      if (!array_key_exists($keyName, $colData))
      {
        throw new sfException(__METHOD__.' Primary key "'.$keyName.'" not present in given array.');
      }
      
      $primaryKeyValues[$keyName] = $colData[$keyName];
    }
    return $primaryKeyValues;
  }

  
  /**
   * Checks if the column data type is one of the MySQL numeric data types.
   * 
   * @return boolean  True if the column data type is numeric
   * @param  string $colName
   */
  public function isNumeric($colName)
  {
    switch ($this->getColumnTypeInfo($colName)->type)
    {
      case 'tinyint':
      case 'smallint':
      case 'mediumint':
      case 'int':
      case 'bigint':
      case 'float':
        return true;
        break;
      default:
        break;
    }
    return false;
  }

  /**
   * Checks if the column data type is one of the MySQL date and time types.
   * 
   * @return boolean  True if the column data type is one of the date and time types
   * @param  string $colName
   */
  public function isDateTime($colName)
  {
    switch ($this->getColumnTypeInfo($colName)->type)
    {
      case 'date':
      case 'datetime':
      case 'timestamp':
      case 'time':
      case 'year':
        return true;
        break;
      default:
        break;
    }
    return false;
  }
}
