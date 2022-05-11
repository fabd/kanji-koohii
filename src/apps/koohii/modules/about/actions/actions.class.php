<?php

class aboutActions extends sfActions
{
  public function executeIndex()
  {
    $this->forward('about', 'about');
  }

  public function executeAbout()
  {
    $response = $this->getResponse();

    // test et preuve pour HostGator après l'attaque 2014/02/19
    $throttler = new RequestThrottler($this->getUser(), 'baduser');
    $throttler->setInterval(2);

    if (!$throttler->isValid())
    {
      $throttler->setTimeout(); // reset le timer

      //  $response->setContentType('text/plain; charset=utf-8');
      $response->setContentType('html');

      return $this->renderPartial('misc/requestThrottleError');
    }

    $throttler->setTimeout();
  }

  public function executeLicense()
  {
  }

  public function executeLearnmore()
  {
    $filename = dirname(__FILE__).'/../templates/learnmore.md';
    if (false === ($contents = file_get_contents($filename)))
    {
      $contents = 'ERROR.';
    }

    $parsedown = new ParsedownExtra();

    // do not escape custom html in the markdown text
    $parsedown->setMarkupEscaped(false);

    // prevents automatic linking of URLs
    $parsedown->setUrlsLinked(false);

    // parse the headings to build the sidebar TOC
    $tocMarkdown = $this->parseMarkdownHeadings($contents);
    $tocHtml = $parsedown->text($tocMarkdown);
    $docHtml = $parsedown->text($contents);

    // template vars
    $this->docHtml = $docHtml;
    $this->tocHtml = $tocHtml;
  }

  public function executeSupport()
  {
  }

  /**
   * @param string $markdown the main document from which to retrieve TOC
   *
   * @return string the TOC as a list in markdown format
   */
  private function parseMarkdownHeadings(string &$markdown)
  {
    // ignore h1, make h2 the first level in the TOC
    $minLevel = 2;

    $tocList = [];

    // match any lines with `## heading text {#optional-fragment-url)`
    $markdown = preg_replace_callback(
      '/^ *(#+)\s+(.+?) *(\{#.+\})? *$/m',
      function ($matches) use (&$tocList, $minLevel)
      {
        // LOG::info($matches);

        $level = strlen($matches[1]);
        $title = $matches[2];

        // use existing fragment if provided in the markdown heading, or create one
        if (isset($matches[3]))
        {
          $fragment = trim($matches[3], '{}');
          $fragmentMd = '';
        }
        else
        {
          $fragment = '#'.$this->slugify($title);
          $fragmentMd = ' {'.$fragment.'}';
        }

        if ($level > 6)
        {
          return $matches[0];
        }

        $itemText = "[{$title}]({$fragment})";

        // note! Parsedown has a bug whereby 2 spaces isn't enough indentation
        //       and causes level 3 list items to become level 2
        $tocList[] = str_repeat('    ', $level - $minLevel).'- '.$itemText;

        // if we generated a `#fragment` url for this TOC list item, put it in the document
        return $matches[0].$fragmentMd;
      },
      $markdown
    );

    // LOG::info($tocList);

    return implode("\n", $tocList);
  }

  private function slugify(string $text)
  {
    $text = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $text)));

    // remove trailing dashes
    return trim($text, '-');
  }
}
