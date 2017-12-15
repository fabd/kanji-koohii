
<div id="hero" class="k_bg_head">
<header class="container">
  <div class="row">
    <h1>Remember the&nbsp;kanji</h1>
    <div id="hero_display">
      <img src="/koohii/landing/hero_screen4.png" width="687" />
    </div>
  </div>
</header>
</div>

<div class="k_bg_odd">
  <div class="container">
<section class="row m_top_x">
  <div class="col-md-6 col-md-push-6">
    <div class="f_text" style="margin-bottom:2.5em">
      <h2>Kanji mnemonics</h2>
      <p>
<span class="break">Create your mnemonics or pick one from the community.</span>
We recommend <em>Remembering the Kanji</em> by James Heisig to study with the website.
<!-- <a href="https://en.wikipedia.org/wiki/Remembering_the_Kanji_and_Remembering_the_Hanzi"> -->
      </p>
    </div>
  </div>
  <div class="col-md-6 col-md-pull-6">
    <div class="f_box f_stories">
      <div class="f_stories_clip">
        <!-- yeah, it's blurry on mobile, this will do for now... -->
        <img src="/koohii/landing/f_stories3.png" />
      </div>
    </div>
  </div>
</section>
  </div>
</div>

<div class="k_bg_even">
  <div class="container">
<section class="row">
  <div class="col-md-6">
    <div class="f_text">
      <h2>Smart dictionary</h2>
      <p>
Koohii's dictionary highlights words <em>which uses only the kanji
that you have learned</em>. This helps you acquire words 
gradually, building from your previous knowledge.
      </p>
    </div>
  </div>
  <div class="col-md-6">
    <div class="f_box f_dict f_shadow">
      <div class="f_dict_clip">
        <!-- img src="/koohii/landing/f_dict.png" /></div -->
      </div>
  </div>
</section>
  </div>
</div>

<div class="k_bg_odd">
  <div class="container">
<section class="row">
  <div class="col-md-6 col-md-push-6">
    <div class="f_text">
<h2>Beautiful kanji flashcards</h2>
<p>
<span class="break">Kanji Koohii comes with built in flashcards for <em>Remembering the Kanji</em>.</span> Show example words and readings (optional). Review on desktop and mobile.
</p>
    </div>
  </div>
  <div class="col-md-6 col-md-pull-6">
    <div class="f_box">
      <div class="f_fc_layers">
        <img class="f_fc_desktop" src="/koohii/landing/f_flashcard_desktop.png" />
        <img class="f_fc_mobile" src="/koohii/landing/f_flashcard_mobile.png" />
      </div>
    </div>
  </div>
</section>
  </div>
</div>

<div class="k_bg_even">
  <div class="container">
<section class="row">
  <div class="col-md-6">
    <div class="f_text">
      <h2>Spaced repetition</h2>
      <p>
Kanji Koohii uses a friendly spaced repetition system: flashcard reviews are scheduled at increasing intervals to stimulate long term memory.
      </p>
    </div>
  </div>
  <div class="col-md-6">
    <div class="f_box f_srs"><img src="/koohii/landing/f_srs.png" /></div>
  </div
</section>
  </div>
</div>

<div id="its-free">
  <div class="transition"></div>
  <div class="k_bg_free">
    <div class="container">
<section>
  <h2>It’s free!</h2>
  <p>Kanji Koohii is handcrafted from&nbsp;Belgium by&nbsp;Fabrice since&nbsp;2006!</p>
<?php if ($sf_user->isAuthenticated()): ?>
  <p>The website is supported by <?php echo link_to('donations', 'about/support') ?>, and affiliate <a href="http://www.japanesepod101.com/member/go.php?r=12933&amp;i=b3">JapanesePod101.com</a>.</p>
<?php endif ?>
  <div class="action row">
    <div class="col-md-6 col-l">
      <?php echo _bs_button('Register', 'account/create', array('class' => 'btn btn-signup')) ?>
    </div>
    <div class="col-md-6 col-r">
      <?php echo link_to('Learn more', '@learnmore', array('class' => 'learnmore')) ?>
    </div>
  </div>
</section>
    </div>
  </div>
</div>

<footer id="footer" class="k_bg_foot">
  <ul>
    <li><?php echo link_to('<i class="fa fa-comment"></i>Blog','news/index') ?></li>
    <li><?php echo link_to('<i class="fa fa-envelope"></i>Contact', '@contact') ?></li>
    <li class="ne"><?php echo link_to('<i class="fa fa-bar-chart"></i>About', 'about/index') ?></li>
  </ul>
</footer>

