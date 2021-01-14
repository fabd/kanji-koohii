<?php 
/**
 * Encode url and text for Twitter buttons.
 *
 *   https://twitter.com/intent/tweet?url={url}&amp;text={text}
 */
function koohii_get_tweet_q($url, $text)
{
  $data = ['url' => $url, 'text' => $text];
  return http_build_query($data); //'', '&amp;
}

$koohii_tweet_params = koohii_get_tweet_q(sfConfig::get('app_website_url'), 'Kanji Koohii');
?>
<?php use_helper('Widgets') ?>
<div class="col-sm-2">
  <div id="home-partners">
    <?php #echo image_tag('/images/2.0/home/sidebar-'.CJ_MODE.'-badge.gif', 'size="131x131" style="margin:0 0 0 5px;"') ?>

    <?php #echo ui_ibtn('&nbsp;&nbsp;Contact', '@contact', array('icon' => 'edit', 'style' => 'display:block')) ?>

    <div id="custom-tweet-button">
      <a href="https://twitter.com/intent/tweet?<?php echo $koohii_tweet_params ?>" target="_blank" rel="nofollow"><i class="fa fa-twitter"></i><span>Tweet</span></a>
    </div>

    <?php echo link_to('<i class="fa fa-envelope-o"></i>&nbsp;Contact', '@contact', ['class' => '', 'style' =>
      'display:block;max-width:125px;text-align:center;padding:0.3em 0;margin:1em 0 0;font-size:15px;text-decoration:none;'
      ]) ?>
  </div>
</div>
