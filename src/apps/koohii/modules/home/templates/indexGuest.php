<div id="hero" class="k_bg_head text-center">
  <header class="ko-Container">

    <h1>Remember the&nbsp;kanji</h1>
    <div id="hero_display" class="mx-auto">
      <img class="block"  src="/koohii/landing/hero_screen4.png" width="687" />
    </div>

  </header>
</div>

<div class="k_bg_odd">
  <div class="ko-Container">
    <section class="row">
      <div class="col-lg-6 lg:order-1">
        <div class="f_text">
          <h2>Kanji mnemonics</h2>
          <p>
<span class="break">Create your mnemonics or pick one from the community.</span>
We recommend <em>Remembering the Kanji</em> by James Heisig to study with the website.
          </p>
        </div>
      </div>
      <div class="col-lg-6 pt-4">
        <div class="f_box mt-[-25px]">
          <div class="">
            <!-- FIXME : blurry on mobile (use markup instead?) -->
            <img src="/koohii/landing/f_stories3.png" />
          </div>
        </div>
      </div>
    </section>
  </div>
</div>

<div class="k_bg_even">
  <div class="ko-Container">
    <section class="row">
      <div class="col-lg-6">
        <div class="f_text">
          <h2>Smart dictionary</h2>
          <p>
    Koohii's dictionary highlights words <em>which uses only the kanji
    that you have learned</em>. This helps you acquire words 
    gradually, building from your previous knowledge.
          </p>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="f_box f_dict f_shadow">
          <div class="f_dict_clip">
            <!-- img src="/koohii/landing/f_dict.png" /></div -->
          </div>
      </div>
    </section>
  </div>
</div>

<div class="k_bg_odd">
  <div class="ko-Container">
    <section class="row">
      <div class="col-lg-6 lg:order-1">
        <div class="f_text">
    <h2>Beautiful kanji flashcards</h2>
    <p>
    <span class="break">Kanji Koohii comes with built in flashcards for <em>Remembering the Kanji</em>.</span> Show example words and readings (optional). Review on desktop and mobile.
    </p>
        </div>
      </div>
      <div class="col-lg-6">
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
  <div class="ko-Container">
    <section class="row">
      <div class="col-lg-6">
        <div class="f_text">
          <h2>Spaced repetition</h2>
          <p>
    Kanji Koohii uses a friendly spaced repetition system: flashcard reviews are scheduled at increasing intervals to stimulate long term memory.
          </p>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="f_box f_srs"><img src="/koohii/landing/f_srs.png" /></div>
      </div>
    </section>
  </div>
</div>

<div id="its-free">
  <div class="transition"></div>
  <div class="k_bg_free">
    <div class="ko-Container">
      <section class="text-center">
        
        <h2>It’s free!</h2>

        <p>Kanji Koohii is handcrafted from&nbsp;Belgium by&nbsp;Fabrice since&nbsp;2006!</p>

<?php if ($sf_user->isAuthenticated()): ?>
        <p>The website is supported by <?= link_to('donations', 'about/support'); ?>, and affiliate <a href="http://www.japanesepod101.com/member/go.php?r=12933&amp;i=b3">JapanesePod101.com</a>.</p>
<?php endif; ?>

        <div class="max-w-[540px] mx-auto md:flex md:items-center pt-8">
          <div class="mb-6 md:w-1/2 md:mb-0">
            <?= _bs_button_to('Register', 'account/create', ['class' => 'ko-ButtonSignup']); ?>
          </div>
          <div class="md:w-1/2">
            <?= link_to('Learn more', '@learnmore', ['class' => 'learnmore whitespace-nowrap']); ?>
          </div>
        </div>
      </section>
    </div>
  </div>
</div>

<footer id="footer-landing">
  <ul>
    <li><?= link_to('<i class="fa fa-comment"></i>Blog', 'news/index'); ?></li>
    <li><?= link_to('<i class="fa fa-envelope"></i>Contact', '@contact'); ?></li>
    <li class="ne"><?= link_to('<i class="fa fa-question"></i>About', 'about/index'); ?></li>
  </ul>
</footer>
