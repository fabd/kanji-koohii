<?php
class newsActions extends sfActions
{
  /**
   * News Archive
   *
   *   /news
   *
   * News by month
   *
   *   /news/:year/:month
   *
   */
  public function executeIndex($request)
  {
    list($year, $month) = phpToolkit::array_splice_values($request->getParameterHolder()->getAll(), array('year', 'month'));

    if (!$year)
    {
      $this->select = 'recent';
    }
    else if ($month >= 1 && $month <= 12)
    {
      $this->select = array($year, $month);
    }
    else
    {
      $this->forward404();
    }
  }
  
  /**
   * News by article
   *
   *   /news/id/:id
   *
   */
  public function executeDetail($request)
  {
    $postId = (int)$request->getParameter('id');
    $this->forward404Unless($postId > 0, "This news post does not exist.");
    
    if (false !== $post = SitenewsPeer::getPostById($postId))
    {
      $this->posts = array($post);
    }
    else
    {
      $this->posts = false;
    }
  }

}
