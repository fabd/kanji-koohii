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

  public function executePost($request)
  {
    $user = $this->getUser();
    
    // admin only
    $this->forward404Unless($user->getUserName() === 'fuaburisu' || $user->isAdministrator());

    //
    $postId      = (int)$request->getParameter('post_id', 0);
    $postTitle   = trim($request->getParameter('post_title', ''));
    $postBody    = trim($request->getParameter('post_body', ''));
    $postDate    = trim($request->getParameter('post_date', ''));

    $postPreview = false;

    $isNewPost = $postId === 0;
    $isCommit  = $request->hasParameter('commit');
    $isPreview = $request->hasParameter('do_preview');

    if ($request->getMethod() !== sfRequest::POST)
    {
      // start a new post
      if ($postId === 0)
      {
        $request->getParameterHolder()->add(array(
          'post_date'  => date('Y-m-d')
        ));
      }
      else
      {
        // edit existing post
      
        if (false !== ($data = SitenewsPeer::getRawPostById($postId)))
        {
          $request->getParameterHolder()->add(array(
            'post_title' => $data->subject,
            'post_body'  => $data->text,
            'post_date'  => $data->created_on
          ));
        }
      }
    }
    else
    {
      //
      $postBodyPreview = SitenewsPeer::formatPost($postBody);

      // cf. news/_list partial
      $postPreview = (object)array(
        'id'       => $postId,
        'date'     => strtotime($postDate), // unix time
        'subject'  => $postTitle,
        'text'     => $postBodyPreview
      );

      if ($isPreview)
      {
      }
      else if ($isCommit)
      {

        $postData = array(
          'created_on'   => $postDate != '' ? $postDate : new coreDbExpr('NOW()'),
          'subject'      => $postTitle,
          'text'         => $postBody
        );
        // DBG::printr($postData);exit;

        if ($isNewPost && SitenewsPeer::getInstance()->insert($postData))
        {
          $postId = SitenewsPeer::lastInsertId();
          $request->setParameter('post_id', $postId);
        }
        else if (!$isNewPost && SitenewsPeer::getInstance()->update($postData, 'id = ?', $postId))
        {
          
        }
        else
        {
          $request->setError('replace', 'Update failed.');
        }

        if (!$request->hasErrors())
        {
          // invalidate cached templates
          $path = ManageSfCache::getRealPathForCache('news/_recent');
          ManageSfCache::recursiveDeleteFromPath($path);

          $path = ManageSfCache::getRealPathForCache('news/index');
          ManageSfCache::recursiveDeleteFromPath($path);
        }
      }
    }

    $this->post = $postPreview;
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
