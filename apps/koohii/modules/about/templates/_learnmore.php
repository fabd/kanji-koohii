<?php 
  use_helper('Markdown');

  $filename = dirname(__FILE__).'/learnmore.md';
  $html = markdown_parsedown_extra($filename);

?>
<div id="learn-more">

  <div class="row">

    <div class="col-md-3 mb-2">
      <div id="learn-more-menu" class="side-menu">
        <h2>Contents</h2>
        <ul>
          <li><a href="#help-rtk">Remembering the Kanji</a></li>
          <li><a href="#help-study">Study and Share Stories</a></li>
          <li><a href="#help-dictionary">The Dictionary</a></li>

          <li><a href="#help-manage-cards">Adding Flashcards</a></li>

          <li><a href="#help-srs">Spaced Repetition</a></li>
          <li><a href="#help-scheduling">Review Scheduling</a></li>
          <li><a href="#help-reviewing">Reviewing</a></li>
          <li><a href="#help-benefits">Benefits</a></li>
          <li><a href="#help-faq">FAQ</a></li>
        </ul>
      </div>
    </div>

    <div class="col-md-9 markdown">
<?php echo $html ?>
    </div><!-- /col -->

  </div><!-- /row -->

</div>
