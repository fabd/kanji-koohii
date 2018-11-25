<?php

define('RSS_FEED_NUM_ENTRIES', 30);
define('RSS_FEED_DESCRIPTION', "A feed for Kanji Koohii news & updates.");

class RssFeedComponent extends sfComponent
{
  public function execute($request)
  {
    $xml = new SimpleXMLElement('<rss/>');
    $xml->addAttribute("version", "2.0");

    // prep
    $website_url = sfConfig::get('app_website_url');

    // channel 
    $channel = $xml->addChild("channel");
    $channel->addChild("title", "Kanji Koohii Blog");
    $channel->addChild("link", $website_url);
    $channel->addChild("description", htmlspecialchars(RSS_FEED_DESCRIPTION));
    $channel->addChild("language", "en-us");

    // items
    $posts = SitenewsPeer::getMostRecentPosts(RSS_FEED_NUM_ENTRIES);
    foreach ($posts as $post)
    {
      $item_title       = htmlspecialchars($post->subject);
      $item_link        = url_for('@news_by_id?id='.$post->id, /*absolute*/true);
      $item_pubDate     = date(DateTime::RSS, $post->date);
      $item_description = htmlspecialchars($post->text); // full text feed

      // include_partial('news/_jpodBannerForRSS')

      $item = $channel->addChild("item");
      $item->addChild("title",   $item_title);
      $item->addChild("link",    $item_link);
      $item->addChild("guid",    $item_link);
      $item->addChild("pubDate", $item_pubDate);
      $item->addChild("description", $item_description);
    }

    $this->feed_content = $xml->asXML();

    return sfView::SUCCESS;
  }
}

