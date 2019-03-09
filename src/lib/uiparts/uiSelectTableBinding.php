<?php
/**
 * uiSelectTableBinding configures the uiSelectTable component.
 * 
 * Configuration (properties of the configuration object returned by getConfig():
 * 
 * 'settings':
 *   editable:      <boolean>    Output html structure for editable data (uiforms, unfinished)
 *   primaryKey:    <string|array>  One or multiple primary keys, very important for editable data
 *   sortColumn:    <string>     Initial sort column
 *   sortOrder:     <0|1>        Initial sort order
 *   rows_per_page: <int>        Defaults to 10 (cf. uiSelectTable)
 * 
 * 'columns':
 * 
 *   An array of column definitions:
 * 
 *   caption:       <string>     Column head title
 *   width:          <int>        Html width attribute (percent)
 *   cssClass:      <string>     Css class to apply on this column's cells
 *   colData:        <string>     Column used for display, sort and updates by default.
 *                               colData is escaped for display!
 *                               If not set, colDisplay may be used, and the column is not sortable.
 *   colDisplay:    <string>     If set, this will be the display value, while colData is used for sorting.
 *                               The display value is NOT escaped!
 *   colSort:        <string>     If set, column to use for sorting.
 *   editable:      <boolean>    If true, editable and sent with post data. Default false.
 *   default:       <string>     Default value for new rows
 * 
 *  
 * @package    UiParts
 * @author     Fabrice Denis
 */

interface uiSelectTableBinding
{
  /**
   * This function returns the configuration for the component.
   * 
   * Settings have default values and can be overwritten (@see uiTable->settings).
   *
   */
  public function getConfig();
  
  /**
   * Format values for output, or add custom column data.
   * 
   * Notes:
   * - Editable fields should not be modified by this function!
   * - colData is escaped, to prevent escaping, add colDisplay and set it in this method
   * - Set the 'cssClass' key in $rowData to add custom classes on the TR element.
   * 
   * @param  array  $rowData   Associative array of field => value pairs
   * 
   * @return array  $rowData
   */
  public function filterDisplayData(uiSelectTableRow $row);

  /**
   * Validate row data
   * 
   * Implement any validation you want here. 
   * 
   * @param  array  $rowData Associative array of data for one row of the table
   * @return bool            Return true if the data is valid and ready to go in the database
   *                         (not including backslashing which is done at the database layer)
   */
  public function validateRowData(array $rowData);
  
  /**
   * Save row data permanently
   * 
   * This function is called when the data was validated succesfully,
   * It must now be saved permanently in the data source linked to the table.
   * 
   * Must return false if save didn't work otherwise the user's post data
   * for a new row disappears from server response.
   * 
   * @param  array  $rowData
   * @return bool             Return true if save went succesfully
   */
  public function saveRowData(array $rowData);
  
  /**
   * Delete one or more rows (if the row deletion icon was enabled)
   * 
   * @param array   Array of key => value for each primary key set in table config
   */
  public function deleteRow(array $row_ids);
}
