<?php
/**
 * uiSelectTable manages a data table with column sorting and row editing.
 * 
 * Column sorting is handled through get requests with a query string updated
 * in each column head.
 * 
 * Methods that can be used in view template:
 *  getTableHead()     Return HTML for the <thead> section
 *  getTableBody()     Return HTML for the <tbody> section
 *  
 * TODO
 * - Only print 7 chars of the checksum for row id validation purposes,
 *   reduce the html footprint with many rows (think git sha eg. 98c317e)
 * - Add odd/even rows (cf. uiTabular css .odd) to getTableBody.
 * 
 * NICETOHAVE
 * - Do not require 'primaryKey' if not using editable rows.
 *  
 * 
 * @package    UiParts
 * @author     Fabrice Denis
 */

class uiSelectTable
{
  const
    QUERY_SORTCOLUMN = 'sort',
    QUERY_SORTORDER  = 'order',
  
    // separator used to concatenate ids if the primary key use more than one column
    COMPOUNDROWID_SEPARATOR = '-',
  
    // salt used in checksum for row ids
    CHECKSUM_MAGICWORD     = 'ErikaSako',
  
    // row ids in post data
    POSTDATA_ROWIDS        = 'rowids',
    POSTDATA_NEW_ROWID     = '*',
    POSTDATA_DELETE_ROWIDS = 'deletedRowIds',

    // uiform.js javascript constants must match!
    JSNEWROW_CLASS = 'JsNewRow';
  
  protected
    // the binding interface for this table instance
    $binding  = null,
    
    // request parameter holder
    $request  = null,
    
    $sortOrders = [0 => 'ASC', 1 => 'DESC'],

    // this will hold the configuration data returned by getConfig() as native php objects
    $columns = null,
    
    // fetch results from Select object prepared for getTableBody()
    $rowdata = null,

    // each one of those default settings can be overwritten in the config
    $settings = [
      //
      'editable' => false,
      
      // show the delete icon in each row
      'deleteicon' => false,
      
      // add a checkbox to each row for multiple selection
      'rowselection' => false,
      
      // default sort column (null will be set to first defined column)
      'sortColumn' => null,
      
      // default sort order
      'sortOrder' => 0,
      
      // primary key(s) must always be set in config data
      'primaryKey' => null,
      
      // "module/action" part of ajax url for sorting & updates, @see ajaxFrontController
      'ajaxAction' => null
    ],
    
    // flag set to true when ready to render
    $processed = false;
    
  /**
   * 
   * @param  uiSelectTableBinding $binding
   * @param  coreDatabaseSelect   $select
   * @param  sfParameterHolder    $request
   * 
   * @return 
   */
  public function __construct(uiSelectTableBinding $binding, coreDatabaseSelect $select, sfParameterHolder $request)
  {
    $this->binding = $binding;
    $this->select  = $select;
    $this->request = $request;

    $this->bind($this->binding);
  }

  /**
   * Configure settings after instantiation, pass an array of parameters
   * that correspond to the uiSelectTableBinding 'settings' parameters.
   * 
   * @param  array  $options   Array of settings to be set
   */
  public function configure($settings)
  {
    $this->configureSettings($settings);
  }

  /**
   * Configure the 'settings' part of the table binding options.
   * 
   * @param  array  Hash with parameters to match $settings (cf. uiSelectTableBinding)
   */
  private function configureSettings($settings)
  {
    foreach ($settings as $name => $value)
    {
      // warn if settings unrecognised (probably typo?)
      if (!array_key_exists($name, $this->settings))
      {
        throw new sfException("Unknown parameter '{$name}' in uiSelectTable settings.");
      }
      $this->settings[$name] = $value;
    }
  }

  /**
   * 
   * 
   * @return object $this (allows chaining calls)
   */
  private function bind(uiSelectTableBinding $bindObj)
  {
    // check
    if (!$bindObj instanceof uiSelectTableBinding)
    {
      throw new sfException('Binding parameter not of required class');
    }
  
    // get configuration as object, or convert from JSON if string
    $config = $bindObj->getConfig();
    if (is_string($config))
    {
      // read config as a json string
      $config = coreJson::decode($config);
    }
//DBG::printr($config);exit;
    
    // overwrite default settings with config values
    if (isset($config->settings))
    {
      $this->configureSettings($config->settings);
    }
    else {
      throw new sfException(__CLASS__.'::bind() Config "settings" missing');
    }

    // setup some default values for columns
    $this->columns =& $config->columns;
    foreach ($this->columns as $colDef)
    {
      assert('isset($colDef->colData) || isset($colDef->colDisplay)');

      // columns are not editable by default
      if (!isset($colDef->editable)) {
        $colDef->editable = false;
      }
      
      // sort on raw or display data if sort column is not explicitly set
      if (!isset($colDef->colSort)) {
        $colDef->colSort = isset($colDef->colData) ? $colDef->colData : null;
      }
    }

//DBG::printr($this->settings);exit;
  }

  /**
   * Process editable data requests, and prepare data to render table.
   * 
   */
  public function process()
  {
    // some configuration checks
    if ($this->settings['primaryKey']===null) {
      throw new sfException(__METHOD__."() primaryKey not set");
    }

    // validate data
    $this->errorMessages = [];

    if ($this->request->get('saveChanges'))
    {
      // delete rows
      $this->deleteRows();
      
      // modify and add rows
      $this->getPostRowData();
      $this->validatePostRowData();
    }
    else
    {
      $this->badRows = [];
      $this->newRows = [];
    }

    if (count($this->badRows) > 0)
    {
      $this->errors = '1';
      $this->errorMessages[] = "Some validation errors occured. Please correct the data and then save again.";
    }
    else
    {
      $this->errors = '0';
    }

    // apply sorting to data source and fetch data
    $this->rowdata = sfProjectConfiguration::getActive()->getDatabase()->fetchAll($this->applySorting($this->select));

    // ready for view template
    $this->processed = true;
  }

  /**
   * If any rows are marked for deletion, call the user-defined delete method,
   * and pass the row ids to it after verifying and "unscrambling" them.
   * 
   */
  private function deleteRows()
  {
    // verify row ids are valid
    $rowids = $this->request->get(self::POSTDATA_DELETE_ROWIDS, []);
    if (!$this->verifyRowIds($rowids)) {
      die();
    }

    foreach ($rowids as $row_id)
    {
      if (!$this->binding->deleteRow($row_id))
      {
        // todo
      }
    }
  }

  /**
   * Verifies an array of row ids to see that they were not tampered with.
   * 
   * @param array Array of row ids from post data (ie. with checksum)
   * @return boolean True if all row ids are valid.
   */
  private function verifyRowIds(array $rowids)
  {
    foreach($rowids as $id) {
      if ($id!==self::POSTDATA_NEW_ROWID && !$this->isValidRowId($id)) {
        throw new sfException(__CLASS__.'::'.__METHOD__.'Unexpected row id!');
      }
    }
  }


  /**
   * Turn post data (one array per column) into rows.
   * 
   * If deleting rows and no post data, just creates empty arrays.
   * 
   * For new rows, fill in blank values for non-editable (=no post data) columns.
   * For new rows, generate primary keys to uniquely identify each row.
   *
   */
  private function getPostRowData()
  {
    // verify row ids are valid
    $rowids = $this->request->get(self::POSTDATA_ROWIDS, []);
    if (!$this->verifyRowIds($rowids)) {
      die();
    }

    $num_rows = count($rowids);

    // collect columns of editable data
    $postCols = [];
    if ($num_rows>0)
    {
      foreach ($this->columns as $colDef)
      {
        if ($colDef->editable)
        {
          $colData = $this->request->get($colDef->colData);
          if ($colData===null) {
            throw new sfException(__CLASS__.":: column data for '{$colDef->colData}' missing");
          }
          $postCols[$colDef->colData] = $colData;
          if (count($colData)!==$num_rows) {
            throw new sfException(__CLASS__.":: column data incomplete");
          }
        }
      }

      // generate rows of data from columns of data
      $postRows = [];
      for ($i = 0; $i < $num_rows; $i++)
      {
        $row = [];
        foreach ($postCols as $bind => &$colData) {
          $row[$bind] = $colData[$i];
        }
        $postRows[] = $row;
      }
    }

    // add the primary key values back into the row data
    // for new rows, generate incremental primary keys
    $primaryKeys = (array) $this->settings['primaryKey'];
    for ($i = 0; $i < $num_rows; $i++)
    {
      $bNewRow = $rowids[$i]===self::POSTDATA_NEW_ROWID;
      if (!$bNewRow) {
        $rowid_parts = explode(self::COMPOUNDROWID_SEPARATOR, $rowids[$i]);
        $ipk = 1;
      } else {
        // fixme : generated values should never match possible key values
        // space character is unlikely to appear in primary key values
        $newRowId = sprintf('_ %04d', $i);
      }
      foreach($primaryKeys as $pk) {
        $postRows[$i][$pk] = $bNewRow ? $newRowId : $rowid_parts[$ipk++];
      }
//      echo "<dd>".print_r($postRows[$i],true)."</dd>";
    }
    
    // save post data with unique row key, to post back invalid fields
    $this->postRowData = [];
    $this->newRows = [];
    for ($i = 0; $i < $num_rows; $i++)
    {
      $rowData = $postRows[$i];
      $row_id = $this->getRowId($rowData);
      $this->postRowData[$row_id] = $rowData;
    
      // remember new rows
      if ($rowids[$i]===self::POSTDATA_NEW_ROWID) {
        $this->newRows[$row_id] = true;
      }
    }
  }


  /**
   * Validate post data for modified rows and new rows,
   * save valid rows, and mark invalid rows 
   * 
   * @return true if no validation errors
   */
  private function validatePostRowData()
  {
    $this->badRows = [];

    foreach ($this->postRowData as $row_id => &$rowData)
    {
      $validation = $this->binding->validateRowData($rowData);

      if ($validation===true)
      {
        // save data
        if ($this->binding->saveRowData($rowData))
        {
          // save went well, this is no longer a new row
          if (isset($this->newRows[$row_id]))
          {
            unset($this->newRows[$row_id]);
          }
        }
        else
        {
          $this->errorMessages[] = 'Error while saving data.';
        }
      }
      else
      {
        // mark invalid data for response
        $this->badRows[$row_id] = $validation;
      }

      // non-editable fields are not posted, fill in with blanks for building table later
      if (isset($this->newRows[$row_id]))
      {
        foreach ($this->columns as $colDef)
        {
          if (!$colDef->editable)
          {
            $rowData[$colDef->colData] = '';
          }
        }
      }
    }

    return (count($this->badRows)==0);
  }

  /**
   * Return a copy of the Select object with current sorting applied to it.
   * 
   * @param  coreDatabaseSelect $select
   * @return coreDatabaseSelect
   */
  private function applySorting(coreDatabaseSelect $select)
  {
    $this->sortColumn = $this->request->get(self::QUERY_SORTCOLUMN, $this->settings['sortColumn']);
    
    // filter bad characters from the query for potential XSS attacks
    if ($this->sortColumn !== null)
    {
      if (!preg_match('/^[a-zA-Z_]+$/', $this->sortColumn))
      {
        throw new sfException('Invalid query parameter');
      }
    }
    else
    {
      // sort by default on first data column, ascending
      $this->sortColumn = isset($this->columns[0]->colSort) ? $this->columns[0]->colSort : $this->columns[0]->colData;
    }

    $this->sortOrder = intval($this->request->get(self::QUERY_SORTORDER, $this->settings['sortOrder']));
    $this->sortOrder = $this->sortOrder % count($this->sortOrders);
    
    // there is only one column so we quote here, ideally should be quoted in coreDatabaseSelect
    return $select->order('`'.$this->sortColumn.'` '.$this->sortOrders[$this->sortOrder]);
  }

  /**
   * Return table heads for <thead> section of table
   *
   * @return string  Html string of one or more <th>...</th>
   */
  public function getTableHead()
  {
    if (!$this->processed)
    {
      $this->process();
    }
    
    $columnHeads = '';
    
    // row selection checkbox
    if ($this->settings['rowselection']) {
      $columnHeads = $columnHeads . <<<EOD
      <th width="1%" class="aleft"><input type="checkbox" class="JsSelRow" /></th>

EOD;
    }
    
    // data column heads
    foreach ($this->columns as $colDef)
    {
      $columnHeads = $columnHeads . $this->getColHead($colDef);
    }

    // edit row caption
    /*
    $columnHeads = $columnHeads . <<<EOD
      <th width="4%">Edit</th>

EOD;
    */

    // delete row caption
    if ($this->settings['editable'] && $this->settings['deleteicon'])
    {
      $columnHeads = $columnHeads . <<<EOD
      <th width="4%">&nbsp;</th>

EOD;
    }

    return $columnHeads;
  }
  
  /**
   * Return a single <TH> for a data column
   * 
   *
   * @param $colDef Object
   */
  private function getColHead($colDef)
  {
    $width = !empty($colDef->width) ? 'width="'.$colDef->width.'%"' : '';
    $href = '#';
    $caption = $colDef->caption;
    
    // set css hook for sort order icon
    if ($this->sortColumn == $colDef->colSort) {
      $classAttr = 'class="JSTableSort active sort'.strtolower($this->sortOrders[$this->sortOrder]).'" ';
      $next_order = $this->sortOrder ? 0 : 1;
    }
    elseif (isset($colDef->colSort))
    {
      $classAttr = 'class="JSTableSort sort" ';
      $next_order = 0;
    }

    if (isset($colDef->colSort))
    {
      // sortable
      $href = $this->getColQuery($colDef->colSort, $next_order);
      $colHtml = "\t\t\t<th {$width}><a {$classAttr}href=\"{$href}\">{$caption}</a></th>\n";
    }
    else
    {
      // not sortable
      $colHtml = "\t\t\t<th {$width}>{$caption}</th>\n";
    }

    return $colHtml;
  }
  
  /**
   * Returns query string with parameters for given sort column and sort order.
   * 
   * @param $sort_column column bind name
   * @param $sort_order One of $this->sortOrders[] keys
   * 
   * @return string
   */
  protected function getColQuery($sort_column, $sort_order)
  {
    $q = '?'.self::QUERY_SORTCOLUMN.'='.$sort_column . 
         '&'.self::QUERY_SORTORDER.'='.$sort_order;
    return $q;
  }

  /**
   * Return content for the <tbody> part of the table.
   *
   * @return string  One or more <tr>...</tr>
   */
  public function getTableBody()
  {
    if (!$this->processed)
    {
      $this->process();
    }

    $rows = $this->rowdata;

    $rowsHtml = '';
    
    // make the row template
    if ($this->settings['editable'])
    {
      $oRow = new uiSelectTableRow();
      $rowsHtml = $rowsHtml . $this->getTableRow($oRow, true);
    }
    
    // make the data rows
    foreach ($rows as $row)
    {
      $oRow = new uiSelectTableRow();
      $oRow->setRowData($row);
      $this->binding->filterDisplayData($oRow);
      $rowsHtml = $rowsHtml . $this->getTableRow($oRow, false);
    }

    // add new rows that couldn't be saved
    foreach ($this->newRows as $row_id => $v)
    {
      $oRow = new uiSelectTableRow();
      $row = $this->postRowData[$row_id];
      $oRow->setRowData($row);
      $rowsHtml = $rowsHtml . $this->getTableRow($oRow, false);
    }
    
    return $rowsHtml;
  }
  
  /**
   * Return compound rowid for table row to be posted back as post data by Javascript.
   * 
   * Use a checksum to make it difficult to tamper with the ids.
   * 
   */
  private function getRowId($rowData)
  {
    $primaryKeys = (array) $this->settings['primaryKey'];
    $primaryKeyIds = [];
    foreach($primaryKeys as $column)
    {
      if (!array_key_exists($column, $rowData))
      {
        DBG::printr($rowData);
        throw new sfException(__METHOD__);
      }
      $primaryKeyIds[] = $rowData[$column];
    }
    $sPrimaryKeys = implode('', $primaryKeyIds);
    $checksum = md5(self::CHECKSUM_MAGICWORD.$sPrimaryKeys);
    array_unshift($primaryKeyIds, $checksum);
    return implode(self::COMPOUNDROWID_SEPARATOR, $primaryKeyIds);
  }
  
  /**
   * The counterpart to getRowId(), verify the the row ids match the checksum
   * 
   * @return true if it's correct
   */
  private function isValidRowId($rowid)
  {
    $ids = explode(self::COMPOUNDROWID_SEPARATOR, $rowid);
    assert('count($ids)>=2');
    $postChecksum = array_shift($ids);
    $sPrimaryKeys = implode('', $ids);
    $trueChecksum = md5(self::CHECKSUM_MAGICWORD.$sPrimaryKeys);
    return ($postChecksum===$trueChecksum);
  }
  
  
  /**
   * keys in data correspond to columns.bind
   *
   * @param $data Object
   */
  private function getTableRow(uiSelectTableRow $oRow, $bTemplateRow)
  {
    $rowData = $oRow->getRowData();
    $rowHtml = '';

    // get unique row id to match with invalid row data
    if (!$bTemplateRow)
    {
      $row_id = $this->getRowId($rowData);
      // validation data
      $bad_row = isset($this->badRows[$row_id]) ? $this->badRows[$row_id] : false;
    }
    else
    {
      $bad_row = false;
    }


    if ($bTemplateRow)
    {
      $rowHtml = $rowHtml . <<<EOD
    <tr class="JsRowTemplate" style="display:none;">

EOD;
    }
    else if (isset($this->newRows[$row_id]))
    {
      $newRowClass = self::JSNEWROW_CLASS;
      $rowHtml = $rowHtml . <<<EOD
    <tr class="{$newRowClass}">

EOD;
    }
    else
    {
      if ($bad_row) {
        $oRow->addCssClass(['validation-error']);
      }
      $options = [
        'id' => $this->getRowId($rowData),
        'class' => implode(',', $oRow->getCssClass())
      ];
      $rowHtml = $rowHtml . tag('tr', $options, true);
    }

    // row selection column
    if ($this->settings['editable'] && $this->settings['rowselection'])
    {
      $rowHtml = $rowHtml . <<<EOD
      <td><input type="checkbox" class="JsSelRow" />
        <input type="hidden" class="JsRowId" name="rowId" value="new" />
      </td>

EOD;
    }


    foreach ($this->columns as $colDef)
    {
      $bUsePostData = !$bTemplateRow && (($bad_row!==false && $colDef->editable) || isset($this->newRows[$row_id]));
//if ($bad_row!==false) echo '@@@@'.print_r($bad_row, true);
      if ($bUsePostData)
      {
        // bad data, or valid data in a new row that could not be saved yet : use post data
        $cellData = $this->postRowData[$row_id][$colDef->colData];
      }
      elseif ($bTemplateRow)
      {
        // template row : use default value for editable columns, otherwise blank
        $cellData = $colDef->editable ? $colDef->default : '';
      }
      else {
        if (isset($colDef->colDisplay)) {
          // 
          $cellData = $rowData[$colDef->colDisplay];
        }
        else {
          // raw data
          $cellData = $rowData[$colDef->colData];
        }
      }
      
      $bValidationError = !$bTemplateRow && ($bad_row!==false && isset($bad_row[$colDef->colData]));

      $rowHtml = $rowHtml . $this->getTableCell($colDef, $cellData, $bValidationError);
    }

    // edit icon column
    if ($this->settings['editable'] && $this->settings['deleteicon'])
    {
      $rowHtml = $rowHtml . <<<EOD
      <td class="JsDelRow"><img src="/img/forms/ico-delete.gif" alt="Delete" title="Delete this item" /></td>

EOD;
    }

    $rowHtml = $rowHtml . <<<EOD
    </tr>

EOD;

    return $rowHtml;
  }

  /**
   * Return html for a single table cell and data, add styles for validation errors.
   * 
   */
  private function getTableCell($colDef, $colData, $isError = false)
  {
    $cssClass = isset($colDef->cssClass) ? $colDef->cssClass : '';
    if ($isError) {
      $cssClass .= ' error';
    }
    $tdClass = !empty($cssClass) ? " class=\"{$cssClass}\"" : '';

    $cellHtml = "\t\t\t<td{$tdClass}>";

    if ($colData!==null)
    {
      // always escape raw data and post data, display data is handled by uiTable_Binding method
      if (!isset($colDef->colDisplay)) {
        $colData = htmlspecialchars($colData);
      }

      // emtpy values need &nbsp; or table borders won't display properly
      if (!isset($colData)) {
        $colData = '&nbsp;';
      }
      
      if ($this->settings['editable'] && $colDef->editable) {
        $cellHtml = $cellHtml . "<input type=\"hidden\" name=\"{$colDef->colData}[]\" value=\"{$colData}\" />";
        $cellHtml = $cellHtml . '<var>' . $colData . '</var>';
      }
      else {
        $cellHtml = $cellHtml . $colData;
      }
    }
    else {
      $cellHtml = $cellHtml . '&nbsp;';
    }
    
    $cellHtml = $cellHtml . "</td>\n";
    
    return $cellHtml;
  }
}

/**
 * uiSelectTableRow allows to configure individual rows of uiSelectTable for display.
 * 
 * An instance of this is passed to the binding object's filterDisplayData() method,
 * for each row, before the row is rendered. Some row properties can be set on this
 * object before it is rendered, such as css classes.
 * 
 * @author  Fabrice Denis
 */
class uiSelectTableRow
{
  protected
    /**
     * This associative array contains data for all columns specified in
     * the binding object.
     * 
     * @var
     */
    $rowData   = null,
    $cssClass  = [];
  
  public function __construct()
  {
  }
  
  public function setRowData(array $rowData)
  {
    $this->rowData = $rowData;
  }
  
  public function & getRowData()
  {
    return $this->rowData;
  }
  
  /**
   * Set one or multiple css classes, which will be set on the TR element.
   * 
   * @param  mixed  $cssClass  An array of class names, or a single class name as a string
   */
  public function addCssClass($cssClass)
  {
    if (!is_array($cssClass))
    {
      $cssClass = (array)$cssClass;
    }

    $this->cssClass = array_merge($this->cssClass, $cssClass);
  }

  public function getCssClass()
  {
    return $this->cssClass;
  }
}
