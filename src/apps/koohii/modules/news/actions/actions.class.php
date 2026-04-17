<?php

/**
 * @property array<int>|string   $select
 * @property false|object        $post
 * @property array<object>|false $posts
 */
class newsActions extends sfActions
{
  /**
   * News Archive.
   *
   *   /news
   *
   * News by month
   *
   *   /news/:year/:month
   */
  public function executeIndex(sfWebRequest $request)
  {
    $year  = $request->getParameter('year');
    $month = $request->getParameter('month');

    if (!$year) {
      $this->select = 'recent';
    } elseif ($month >= 1 && $month <= 12) {
      $this->select = [$year, $month];
    } else {
      $this->forward404();
    }
  }

  public function executePost(coreRequest $request)
  {
    $user = kk_get_user();

    // admin only
    $this->forward404Unless($user->getUserName() === 'fuaburisu' || $user->isAdministrator());

    $postId    = (int) $request->getParameter('post_id', '0');
    $postTitle = trim($request->getParameter('post_title', ''));
    $postBody  = trim($request->getParameter('post_body', ''));
    $postDate  = trim($request->getParameter('post_date', ''));

    $postPreview = false;

    $isNewPost = $postId === 0;
    $isCommit  = $request->hasParameter('commit');
    $isPreview = $request->hasParameter('do_preview');

    if ($request->getMethod() !== sfRequest::POST) {
      // start a new post
      if ($postId === 0) {
        $request->getParameterHolder()->add([
          'post_date' => date('Y-m-d'),
        ]);
      } else {
        // edit existing post

        if (false !== ($data = SitenewsPeer::getRawPostById($postId))) {
          $request->getParameterHolder()->add([
            'post_title' => $data->subject,
            'post_body'  => $data->text,
            'post_date'  => $data->created_on,
          ]);
        }
      }
    } else {
      $postBodyPreview = SitenewsPeer::formatPost($postBody);

      // cf. news/_list partial
      $postPreview = (object) [
        'id'      => $postId,
        'date'    => strtotime($postDate), // unix time
        'subject' => $postTitle,
        'text'    => $postBodyPreview,
      ];

      if ($isPreview) {
      } elseif ($isCommit) {
        $postData = [
          'created_on' => $postDate != '' ? $postDate : new coreDbExpr('NOW()'),
          'subject'    => $postTitle,
          'text'       => $postBody,
        ];
        // DBG::printr($postData);exit;

        if ($isNewPost && SitenewsPeer::getInstance()->insert($postData)) {
          $postId = SitenewsPeer::lastInsertId();
          $request->setParameter('post_id', $postId);
        } elseif (!$isNewPost && SitenewsPeer::getInstance()->update($postData, 'id = ?', $postId)) {
        } else {
          $request->setError('replace', 'Update failed.');
        }

        if (!$request->hasErrors()) {
          /** @var ?sfViewCacheManager incorrect Symfony phpDoc */
          $cacheManager = $this->getContext()->getViewCacheManager();

          // invalidate cached templates
          if (null !== $cacheManager) {
            ManageSfCache::clearCacheWildcard('home', '_RssFeed');
            ManageSfCache::clearCacheWildcard('news', '_recent');
            ManageSfCache::clearCacheWildcard('news', 'index');
          }
        }
      }
    }

    $this->post = $postPreview;
  }

  /**
   * News by article.
   *
   *   /news/id/:id
   */
  public function executeDetail(coreRequest $request)
  {
    $postId = (int) $request->getParameter('id');
    $this->forward404Unless($postId > 0, 'This news post does not exist.');

    if (false !== $post = SitenewsPeer::getPostById($postId)) {
      $this->posts = [$post];
    } else {
      $this->posts = false;
    }
  }
}
