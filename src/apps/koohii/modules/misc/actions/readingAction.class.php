<?php
/**
 * Sightreading page
 * 
 */

class readingAction extends sfAction
{
  public function execute($request)
  {
    $this->display_form = true;
    $this->display_kanji = false;
    $this->kanji_text = '';

    if ($request->getMethod() != sfRequest::POST)
    {
      // default text in utf-8 below
      $default_text = <<< EOD
むかし、むかし、ご存知のとおり、うさぎとかめは、山の上まで競争しました。誰もが、うさぎの方がかめよりも早くそこに着くと思いました。しかし迂闊にも、うさぎは途中で寝てしまいました。目が覚めた時は、もうあとのまつりでした。かめはすでに山のてっ辺に立っていました。
EOD;
      $request->setParameter('jtextarea', $default_text);

    }
    else
    {
      $validator = new coreValidator($this->getActionName());
      
      if ($validator->validate($request->getParameterHolder()->getAll()))
      {
        $this->display_form = false;
        $this->display_kanji = true;
        $this->kanji_text = $this->transformJapaneseText($request->getParameter('jtextarea'));
      }
    }
  }
  
  /**
   * Transform kanji in the input Japanese text into links to the Study area,
   * and add class for Javascript popup with the Heisig keywords.
   * 
   * @param  string  $j_text  Japanese text in utf-8 from validated post data.
   * @return string  Japanese text as HTML code.
   */
  protected function transformJapaneseText($j_text)
  {
    $context = sfContext::getInstance();

    sfProjectConfiguration::getActive()->loadHelpers(['Tag', 'Url']);
    $j_text = htmlspecialchars(trim($j_text), ENT_QUOTES, sfConfig::get('sf_charset'));

    // get custom keywords ONLY for existing flashcards
    $userId = $this->getContext()->getUser()->getUserId(); 
    $keywords = CustkeywordsPeer::getCustomKeywords($userId, true);

    // wrap known kanji in text with links to Study area and hooks for javascript tooltip
    foreach ($keywords as $ucsId => $info)
    {
      //$uri = $this->getContext()->getController()->genUrl('@study_edit?id='.$info['seq_nr']);
      $kanji = utf8::fromUnicode($ucsId);

      $title = $info['keyword'] . ' (#' . $info['seq_nr'] . ')';

      $html  = link_to($kanji, '@study_edit?id='.$kanji, ['data-text' => $title, 'class' => 'j JsTooltip']);

      $j_text = str_replace($kanji, $html, $j_text);
    }
    
    // assumes lines end with \r\n
    $j_text = preg_replace('/[\r\n]+/', '<br/>', $j_text);
    
    return $j_text;
  }
}
