<?php use_helper('Widgets', 'SimpleDate', 'Form', 'CJK', 'Links'); ?>

<?php slot('inline_styles'); ?>
#fm-summary { margin:10px 0 5px; overflow:auto; color:#716e57; }
#fm-summary p { margin:0 0 20px; }
#fm-summary ul { margin:0 -6px; list-style:none; padding:0; }
#fm-summary .card {
  position:relative; float:left; padding:10px 10px; width:109px; height:150px; margin:0 10px 23px;
  border-bottom:1px solid #cfcdbb; text-align:center;
  /* http://www.colorzilla.com/gradient-editor/ */
  background: #fff; /* Old browsers */
  background: -moz-linear-gradient(315deg, #ffffff 0%, #f8f8f8 80%, #FFFFFF 93%, #EAEAEA 100%); /* FF3.6+ */
  background: -webkit-gradient(linear, left top, right bottom, color-stop(0%,#ffffff), color-stop(80%,#f8f8f8), color-stop(93%,#ffffff), color-stop(100%,#eaeaea)); /* Chrome,Safari4+ */
}
#fm-summary li a  { text-decoration:none; display:block; color:#000; }
#fm-summary li a:hover { color:#005CB1; }
  
#fm-summary .rbn  { width:30px; height:28px; position:absolute; left:-3px; top:9px; background:url(/images/2.0/review/review-summary-sprites.gif) no-repeat 0 0; }
#fm-summary li.fail .rbn { background-position:0 -29px; }
#fm-summary .chr  { padding:22px 0 10px; font-size:70px; line-height:1em; }
#fm-summary .inf  { color:#888; font-size:12px; line-height:1.2em; }
#fm-summary .inf em { display:block; font-family:Georgia, serif; font-size:15px; padding:6px 0 0; }

/*#fm-summary li.card:hover { background-color:blue; }*/
<?php end_slot(); ?>

<?php //DBG::request()?>

<?php //echo DBG::printr($cards);?>

<div id="fm-summary" class="ko-Box">
<?php if (count($cards) > 0) { ?>

  <p><strong>Hint!</strong>  Click the <?= _CJ('kanji'); ?> to open the Study page</p>
  <ul>
  <?php foreach ($cards as $c) { ?>
    <li class="card<?= $c->pass ? '' : ' fail'; ?>">
      <div class="rbn"></div>
      <div class="chr"><?= link_to(cjk_lang_ja($c->kanji), '@study_edit?id='.$c->framenum, ['target' => '_blank']); ?></div>
      <div class="inf">#<?= $c->framenum.'<em>'.$c->keyword; ?></em></div>
    </li>
  <?php } ?>
  </ul>

<?php }
else
{ ?>
  <p style="color:red;">Woops, the session may have expired. The review summary is no longer available.</p>
<?php } ?>
</div>

