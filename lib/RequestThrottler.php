<?php
/**
 * Simple class to check a minimum time period between two requests from the same user.
 *
 *   setInterval($seconds)
 *   isValid()
 *   setTimeout()
 * 
 * @author     Fabrice Denis
 */

class RequestThrottler
{
  // Minimum interval between requests (cf. strtotime())
  const    DEFAULT_TIMEOUT = '+10 seconds';
  
  protected
    $user      = null,
    $name      = '',
    $interval  = '';

  /**
   * 
   * @return
   */
  public function __construct($user, $id)
  {
    $this->user     = $user;
    $this->name     = 'request.throttle.'.$id;

    $this->interval = self::DEFAULT_TIMEOUT;
  }


  public function setInterval($seconds)
  {
    $n = BaseValidators::validateInteger($seconds) ? intval($seconds) : 1;

    // string used by 
    $this->interval = '+'.$n.' seconds';
  }

  /**
   * Returns true if enough time has elapsed since last request,
   * otherwise false to indicate the minimum time has not elapsed between
   * requests.
   * 
   * @param coreUser $user
   * @param string   $id     Request id can be a number or string
   * 
   * @return boolean
   */
  public function isValid()
  {
    $waittime = $this->user->getAttribute($this->name, null);

    if (!is_null($waittime))
    {
      $nowtime = time();
    
      return ($nowtime >= $waittime);
    }
    
    return true;
  }

  /**
   * Marks the current timestamp for a succesful request, necessary
   * to throttle the following requests.
   * 
   * @param coreUser $user
   * @param string   $id     Request id can be a number or string
   * 
   * @return 
   */
  public function setTimeout()
  {
    $this->user->setAttribute($this->name, strtotime($this->interval));
  }
}
