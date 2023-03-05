<?php

namespace Koohii\Scripts\Lib;

use sfException;

class ParserUtils
{
  /**
   * Output a single row of tab-delimited data, meant for MySQL LOAD DATA with
   * the default options:.
   *
   *   FIELDS TERMINATED BY '\t' ENCLOSED BY '' ESCAPED BY '\\'
   *   LINES TERMINATED BY '\n' STARTING BY ''
   *
   * @see   http://dev.mysql.com/doc/refman/5.1/en/load-data.html
   *
   * @param mixed $fileHandle
   */
  public static function outputTabularData($fileHandle, array $rowData)
  {
    $line = implode("\t", $rowData)."\n";
    fwrite($fileHandle, $line);
  }

  public static function fileOpen($fileName, $mode = 'r')
  {
    try
    {
      $handle = fopen($fileName, $mode);
    }
    catch (sfException $e)
    {
      echo sprintf('Error opening file: "%s" with mode "%s"', $fileName, $mode);
    }

    return $handle;
  }

  public static function fileClose($fileHandle)
  {
    fclose($fileHandle);
  }
}
