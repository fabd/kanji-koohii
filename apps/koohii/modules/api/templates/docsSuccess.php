<?php
use_helper('Decorator');
// https://highlightjs.org/download/
$sf_response->addJavaScript('http://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.10.0/highlight.min.js', 'last');
$sf_response->addStylesheet('/revtk/api/highlight/code-style.css', 'last');

function method_header($text)
{
  $anchor = strtr( ltrim($text, '/'), '/', '-');
  echo '<h4 id="'.$anchor.'">'.$text.'</h4>';
}

$api_url = rtkApi::getApiBaseUrl();

//echo '<pre>'.print_r($_SERVER, true).'</pre>';
//DBG::user(); //)printr($sf_user->getAttributeHolder()->getAll());
//echo '<pre>http://kanji.dev.localhost/review/ajaxsrs?json={"put":[{"id":19968,"r":2}],"opt":{"yomi":0}}</pre>';

?>
<?php slot('inline_styles') ?>
/* <style> */

/* type */
h3 { color:#ff0084; }
p, pre, code  { margin:0 0 1.2em; }

/* example value */
samp { font-family:Consolas; font-weight:normal; font-style:normal; background-color:#F7F7F8; color:#C45F73; padding:3px 5px; }

/* placeholder value / variable */
var { font-family:monospace; font-weight:bold; color:#C45F73; font-style:normal; }
code var { color:red; }

/* highlight.js */
pre { padding:0.5em; color:#E6E1DC; }
pre code.hljs { padding:0; margin:0; }
pre, code.hljs { background:#232323; }

/* method styles */
.doc-m { border-top:1px solid #dad9c7; }
h4 { padding:0; color:#4a4; font-size:20px; margin:0.5em 0; }

/* request params */
dl { }
dt { list-style:disc; }
dt code { font:bold 12px/1em "Courier New", monospace; }
dd { margin:0 0 5px 1em; }

/* API custom sidenav */
.side-menu li { border-bottom:none; }


<?php end_slot() ?>

<?php decorate_start('SideTabs', array('active' => 'overview')) ?>

  <h2>The Kanji Koohii API</h2>

  <h3>Overview</h3>

  <p>The API is currently <strong>in development</strong>.


  <h3>Terms of Use</h3>

  <p>You agree to the terms below when using the API and the API key. <?php echo link_to('Contact Fabrice', '@contact') ?> for questions/suggestions.

  <p><strong>The goal of the API is to allow Kanji Koohii users to review more efficiently on mobile devices and
     tablets, and overall improve their mobile experience</strong>. This includes potentially offline functionality.
      An app may also go beyond the website functionality by providing all kinds of lookups and touch based features.

  <ul>
    <li>For sign ups (new accounts): the user is sent to the website in a web view, or separate browser
        app. Automated creation of account is not allowed. The user must visit the site to create his account. That said,
        you can still have some functionality that requires no account and let the user join his Koohii account later.
    <li>Login of course can be automated transparently for the user with their provided credentials.
    <li>The app does not replace the Study experience on the website. Specifically that means the app should not
        feature more than one story from Kanji Koohii per kanji. To create a selection of stories for use in your 
        app (should you want to) you can favorite or copy stories with your own Kanji Koohii account, or a new
        account for that purpose, and make your own selection. You can then use your Stories export to provide
        a single mnemonic per kanji in your app.<br/>
        Better yet, provide a link to "Browse more stories at Kanji Koohii" for each kanji. Assuming the user is connected,
        this can open a web view in which the user can view additional mnemonics.
    <li>By creating an app (desktop, mobile, any kind) that connects to the Kanji Koohii API you agree that your app
        does NOT feature stories <a href="https://en.wikipedia.org/wiki/Web_scraping">scraped</a> from the website.
        This applies to other kanji information as well. Please don't waste server resources: use <a href="http://www.edrdg.org/kanjidic/kanjd2index.html">KANJIDIC2</a>, JMDICT,
        etc. If you need some data like 5th/6th edition RTK indexes, just ask.
  </ul>

  <p>Further on the correct API usage:

  <ul>
    <li>To avoid stressing the server (keep in mind we're not Facebook or Twitter and my site is not using expensive
        servers!) as a general rule of thumb you should always use a <strong>minimum</strong> of one second between
        any API http requests. Note that mass updates such as updating a bunch of flashcards usually take an array
        of data, so it is not necessary, and should be avoided as much as possible to loop over singular items
        and make a request for each. Some large scale websites have APIs that work like this, not mine. Caching is
        pretty basic and many small requests is inefficient.
  </ul>

  <p>Paid apps and in-app advertising are OK as long as requirements are met.


  <h3>Obtaining your API key</h3>

  <p><?php echo link_to('Contact Fabrice', '@contact') ?> to request an API key.


  <h2>API Usage</h2>

  <h3>OAuth endpoints</h3>

  <p>TODO

  <p>
  <code><?php echo $api_url ?>/oauth/authorize/<br/>
<?php echo $api_url ?>/oauth/request_token/<br/>
<?php echo $api_url ?>/oauth/access_token/<br/>
</code></p>


  <h3>Glossary</h3>

  <p><strong>UCS codes</strong> : Kanji Koohii uses the codes from the <a href="https://en.wikipedia.org/wiki/Universal_Coded_Character_Set">Universal Coded Character Set</a>
  to uniquely identify kanji and their corresponding flashcards. This allows to refer to kanji independentily of the Heisig index, which sadly
  has changed between editions of the RTK books. The website uses UCS-2 codes (16 bit value) as unique keys. This covers all but the rarest forms of Japanese
  and Chinese characters. All Japanese characters in the range 0x4e00 - 0x9faf which are also documented in KANJIDIC are supported.
  See <a href="http://www.rikai.com/library/kanjitables/kanji_codes.unicode.shtml">Unicode Kanji Code Table</a> (warning: long page).
  All characters present in RSH and RTH books (Chinese) have also been added to the database (many simplified Chinese characters
  are not listed in KANJIDIC).</p>

<?php /*
  <h3>Programming Requirements</h3>

  <dl>
    <dt><strong>Conversion between UTF-8 and UCS</strong></dt>
    <dd>You'll probably want a way to convert between a UCS-2 code (unicode value) and the UTF-8 character corresponding to a kanji.
    Alternatively if you use HTML templates, you can easily display the kanji by creating an HTML entity out of the UCS code
    (eg. UCS-2 code <var>20809</var> =&gt; <var>&amp;#20809;</var> =&gt; &#20809;)</dd>

    <dt><strong>JSON library to encode &amp; decode</strong></dt>
    <dd>Mainly for decoding JSON response, in a few cases for encoding data in a POST request.</dd>
  </dl>
*/ ?>

  <h3>Request Format</h3>

<p>All requests are made in REST format.

<p>The REQUIRED parameter <var>api_key</var> is used to specify your API Key.



  <h3>Response Format</h3>

<p>All API responses are sent in JSON format (see <a href="http://json.org">json.org</a>).

<p>A succesful response has <samp>Content-Type: application/json; charset=utf-8</samp> in the HTTP response header.


<p>When a request is succesful the response starts with the "stat" field with the value "ok":

<pre><code class="json">{
  "stat": "ok",
  (misc. data follows...)
}
</code></pre>

<p>When a request fails the following format is used. Note that this is a failure from the API logic, which is always
HTTP 200 OK. A low level failure needs to be handled separately, such as HTTP 403, etc. and will not contain JSON.

<pre><code class="json">{
  "stat":    <span style="color:red">"fail"</span>,
  "code":    10,
  "message": "Error message"
}
</code></pre>


  <h2>API Methods</h2>

  <h3>Account</h3>

<div class="doc-m">
  <?php echo method_header('/account/info') ?>

<p>Returns information about the current user.</p>

<h5>URL STRUCTURE</h5>
<p><samp><?php echo $api_url ?>/account/info</samp>?api_key=TESTING</p>

<h5>METHOD</h5>
<p><samp>GET</samp></p>

<h5>SAMPLE JSON RESPONSE</h5>
<pre><code class="json">{
  "stat": "ok",
  "username": "hanky420",
  "location": "Tokyo Japan",
  "srs_info": {
    "flashcard_count": 500,
    "total_reviews": 124
  }
}
</code></pre>

  <h3>Keywords</h3>

<div class="doc-m">
  <?php echo method_header('/keywords/list') ?>

  <p>TODO</p>

</div>


  <h3>Review</h3>


<div class="doc-m">
  <?php echo method_header('/review/fetch') ?>

  <p>Fetch flashcard information for up to N cards at a time (<var>limit_fetch</var> in /review/start response).</p>

  <p>Use this to fetch flashcard displayed information as you advance through the items returned
     by /review/start or your own custom set of flashcard ids (UCS codes). Ideally, you
     would call this once at the start of a review to get the first batch of cards, and then request
     more card data as the user advances through the set of cards.

  <p>This API method is NOT designed to return all card data at once, which is why it has a limit
     on the number of returned items. The reason for this is that in the future the flashcards
     may display "possible words" and any number of information that requires additional queries
     <em>for each</em> card, which is not efficient at all for the web app when requesting
     hundreds of cards at once.

  <p>A suggestion to handle reviews similarly to the Kanji Koohi javascripts works like this:<br/>
     - store card data in a hash, using the card id as the key to manage a "cache" of card data<br/>
     - fetch the initial set of card data at position #0 in your array of card ids (such as returned
     by /review/start or a custom sequence)<br/>
     - fetch 10 more cards at position #5, #15, #25, etc. through the array of card ids.
     That way, the user will not wait for new cards to load since you will be fetching the next
     ten cards before the user finished reviewing the previously fetched cards.


  <dl>
    <dt><code>yomi</code> (optional)</dt>
    <dd><var>1</var> to include sample On/Kun readings, <var>0</var> to disable.</dd>
  </dl>

<h5>URL STRUCTURE</h5>
<p><samp><?php echo $api_url ?>/review/fetch</samp>?api_key=TESTING&amp;yomi=1&amp;items=20108,20845,19968,20843,19977,21313,22235,19971,20061,20116</p>

<h5>METHOD</h5>
<p><samp>GET</samp></p>

<h5>SAMPLE JSON RESPONSE</h5>
<p>The response contains an array of objects. The kanji is <em>not</em> included. 

<pre><code class="json">{
  "stat":       "ok",
  "card_data":      [
    {
      "keyword":      "correct",
      "id":           27491,
      "strokecount":  5,
      "framenum":     379
    }
  ]
} 
</code></pre>

<p>If the <var>yomi</var> option is enabled, each <strong>card_data</strong> entry will also contain a sample on and kun words
(when available, based on word frequency) that illustrate one On and Kun readings.

<p>In this example the kanji &#27491; is enclosed in brackets along with the part of the reading that corresponds. You can use
simple string substitition here to insert HTML elements for styling, or just remove them.

<pre><code class="json">{
  "card_data":      [
    {
      (...)
      "v_on": {
         "compound": "[&#27491;]午",
         "reading":  "[ショウ]ゴ",
         "gloss":    "noon; mid-day"
      },
      "v_kun":       false
    }
  ]
} 
</code></pre>


</div>


<div class="doc-m">
  <?php echo method_header('/review/start') ?>

  <p>Obtain a selection of flashcards for a review session. Mode is required and should be either <var>free</var> for
     unlimited reviews (no saved state), or <var>srs</var> to select cards from the user's flashcard set based on the
     card's status (new, due and failed cards).</p>

  <p>The <strong>free reviews</strong> selection is really just a helper, since you can easily make your own selection
     of flashcard ids (kanji UCS code) by translating a range of Heisig indices, or any other criteria (eg. JLPT).</p>

  <p>Note while testing you can repeat a SRS review any number of times so long as you don't update the flashcards.
     Keep in mind the selection returned for a SRS review can seem to change because it is shuffled by default
     (further, the shuffling of cards is done in sets of cards that expire on the same date, so that the
      most "urgent" cards still appear sooner in the selection).</p>

  <dl>
    <dt><code>mode</code></dt>
    <dd><var>free</var> for free (infinite) reviews, <var>srs</var> for spaced repetitions</dd>
    <!--<dt><code>yomi</code></dt>
    <dd>(FREE &amp; SRS) : <var>1</var> to include sample On/Kun readings, <var>0</var> to disable.</dd>-->

    <dt><code>from</code> (required)</dt>
    <dd>(FREE REVIEW) : Heisig index for start of range (based on user's RTK Edition setting)</dd>
    <dt><code>to</code> (required)</dt>
    <dd>(FREE REVIEW) : Heisig index for end of range (based on user&#39;s RTK Edition setting)</dd>
    <dt><code>shuffle</code> (optional)</dt>
    <dd>(FREE REVIEW) : <var>1</var> to shuffle the selection</dd>

    <dt><code>type</code> (required)</dt>
    <dd>(SRS REVIEW) : <var>due</var> (expired cards), <var>new</var> (untested, blue pile), <var>failed</var> (red pile)</dd>
  </dl>

<h5>URL STRUCTURE</h5>
<p><samp><?php echo $api_url ?>/review/start</samp>?api_key=TESTING&amp;mode=free&amp;from=1&amp;to=10&amp;shuffle=1</p>
<p><samp><?php echo $api_url ?>/review/start</samp>?api_key=TESTING&amp;mode=srs&amp;type=new</p>

<h5>METHOD</h5>
<p><samp>GET</samp></p>

<h5>SAMPLE JSON RESPONSE</h5>
  <p><var>items</var> is an array of flashcard ids. Since Kanji Koohii only deals in kanji flashcards, 
   the unicode value is used as a unique id. Approximately 20,000 kanji and hanzi that are supported on the website
   all have a UCS code that fits in 16 bit storage (ie. "smallint" in MySQL).
  <p><var>limit_fetch</var> and <var>limit_sync</var> are the maximum number of items that can be handled
  by /review/fetch and /review/sync. These may change at a later time if the complexity of the flashcard data
  increases.
<pre><code class="json">{
  "stat":        "ok",
  "card_count":  10,
  "items":       [20108,20845,19968,20843,19977,21313,22235,19971,20061,20116],
  "limit_fetch": 10,
  "limit_sync":  50
} 
</code></pre>
</div>


<div class="doc-m">
  <?php echo method_header('/review/sync') ?>

<p>Send flashcard answers back to the server. Use this <strong>only for SRS reviews</strong>, where the user has created flashcards.
The server takes the answers and will update the card's "due" time, last review timestamp, etc. Note that the "DELETE" answer
will actually delete the flashcard!

<p>There is a built in limit of N cards (see <var>limit_sync</var> in /review/start response).

<p>This can be used in two ways:

<ul>
<li>To rate all the cards <em>at the end of a review session</em> (not recommended), you may call this multiple times
   (with a min. 1s pause in between
   requests).</li>
<li>You can also sync flashcard answers <em>while a review is in progress</em>, as the
   user advances through the cards. For example if user is at position P in the array of flashcards, send all
   the answers up to P - 10 (leaving some room for a "Undo" functionality). Repeat every ten cards or so.
   This ensures the user can never lose too much progress if the app inadvertently closes.
</li>
</ul>

<p>Please do NOT sync one card at a time! It is easier on the server, and causes less delay in the app, to sync flashcard
answers in batches.

<h5>URL STRUCTURE</h5>
<p><samp><?php echo $api_url ?>/review/sync</samp></p>

<h5>METHOD</h5>
<p><samp>POST</samp> : <span style="color:red">this method requires the data to be sent in JSON format</span> with <samp>Content-Type: application/json</samp> HTTP header.

<p><var>time</var> is currently ignored, and should be set to 0.

<p><var>sync</var> is an array of objects, each object contains update information for a unique flashcard. <var>id</var> needs
  to be a unique identifier (here, the UCS code of the kanji). <var>r</var> is the SRS answer (see below).

<pre><code class="json">{
  "time": 54812541,
  "sync": [
    { "id": 20108, "r": 1 },
    { "id": 20845, "r": 5 },
    (...)
  ]
} 
</code></pre>

<p>The flashcard ratings for the SRS are:
<pre>NO      = 1
YES     = 2
EASY    = 3
DELETE  = 4
SKIP    = 5
</pre>

<h5>SAMPLE JSON RESPONSE</h5>
<p><var>put</var>: is an array that confirms each item that has been succesfully updated (or deleted).
<p><var>ignored</var> (optional): if this is returned, it means the items have already been handled during this session and
   the card status has not been updated. A session is reset with /review/start . This is to avoid rating cards multiple
   times in case of an API request being sent twice.

<pre><code class="json">{
  "stat":    "ok",
  "put":     [22244,22242,22241,22240],
  "ignored": [22244]
} 
</code></pre>
</div>



<!--
<div class="doc-m">
  <?php echo method_header('/flashcards/update') ?>

  <p>An API call to start a review, that returns all the flashcard ids

  <dl>
    <dt><code>api_key</code> (required)</dt>
    <dd>Your API application key.</dd>
    <dt><code>mode</code> (required)</dt>
    <dd><samp>due</samp> for due cards (ie. expired cards due for review)</dd>
    <dd><samp>due</samp> for due cards (ie. expired cards due for review)</dd>
  </dl>
</div>
-->

  <h3>SRS (Spaced Repetition System)</h3>

<div class="doc-m">
  <?php echo method_header('/srs/info') ?>

  <p>Returns SRS status information for the signed in user, as seen in the <?php echo link_to('review', '@review') ?> page.

  <p>Note that unlike the Leitner chart seen on the website, this method does not filter between RTK1, RTK3, and non-RTK cards.
  
  <p>Information returned: total count of new (blue), due (orange) and failed/restudy (red) cards.

<h5>URL STRUCTURE</h5>
<p><samp><?php echo $api_url ?>/srs/info</samp>?api_key=TESTING</p>

<h5>METHOD</h5>
<p><samp>GET</samp></p>

  <h5>SAMPLE JSON RESPONSE</h5>
<pre><code class="json">{
  "stat":           "ok",
  "new_cards":      20,
  "due_cards":      15,
  "relearn_cards":  5
}
</code></pre>
</div>


<!--


  <h3>Stories</h3>

<div class="doc-m">
  <?php echo method_header('/stories/list') ?>

  <p>TODO</p>

</div>
-->


<?php decorate_end() ?>

<?php koohii_onload_slot() ?>
  hljs.initHighlightingOnLoad();
<?php end_slot() ?>
