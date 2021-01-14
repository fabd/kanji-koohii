<?php
/**
 * Helpers for API requests.
 *
 * Helpers:
 *   isApiModule()
 * 
 * 
 * @author     Fabrice Denis
 */

class rtkApi
{
  const API_REVIEW_FETCH_LIMIT = 10;

  const API_REVIEW_SYNC_LIMIT  = 50;

  const API_DEBUG_SQL = false;

  /**
   * Checks whether the current request is an API method.
   * 
   * @return  bool    True if current action is within API module.
   */
  public static function isApiModule()
  {
    $moduleName = sfContext::getInstance()->getModuleName();
    return ('api' === $moduleName);
  }

  public static function getApiBaseUrl()
  {
    return sfConfig::get('app_website_url').'/api/v1';
  }

  public static function isContentTypeJson($request)
  {
    // looks for $_SERVER['CONTENT_TYPE']
    return strtolower($request->getHttpHeader('Content-Type', '')) === 'application/json';
  }

  public static function curlJson($url, $jsonData)
  {
    //Initiate cURL.
    $ch = curl_init($url);

    //Encode the array into JSON.
    $jsonDataEncoded = coreJson::encode($jsonData);
     
    //Tell cURL that we want to send a POST request.
    curl_setopt($ch, CURLOPT_POST, 1);
     
    //Attach our encoded JSON string to the POST fields.
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);
     
    //Set the content type to application/json
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']); 
     
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    //Execute the request
    $result = curl_exec($ch);
   
    return $result;
  }
}
