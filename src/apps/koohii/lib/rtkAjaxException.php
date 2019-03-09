<?php
/**
 * Exception thrown by ajax actions.
 *
 * During development, sends back the printout of the json data as a php object,
 * to verify the data integrity.
 * 
 * All json requests should use a "json" POST variable with the json data as a string.
 * 
 * 
 * 
 * @author     Fabrice Denis
 */

class rtkAjaxException extends sfException
{
  public function printStackTrace()
  {
    $exception = is_null($this->wrappedException) ? $this : $this->wrappedException;
    $message   = $exception->getMessage();

    $response = sfContext::getInstance()->getResponse();
    $response->setStatusCode(500);

    // clean current output buffer
    while (@ob_end_clean());
    
    ob_start(sfConfig::get('sf_compressed') ? 'ob_gzhandler' : null);
    
    header('HTTP/1.1 500 Internal Server Error');
    header('Content-Type: text/plain');
  
    /* Obsolete now that JsTron returns global errors  */
    if ($message!=='') {
      header('RTK-Error: ' . $message);
    }

    // during development, send back ajax request for debugging
    if (sfConfig::get('sf_debug'))
    {
      // send back global error message
      $tron = new JsTron();
      $tron->setStatus(JsTron::STATUS_FAILED);
      $tron->setError($message);
      echo coreJson::encode($tron->getJson());
    }

    exit(1);
  }
}
