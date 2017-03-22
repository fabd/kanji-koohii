<?php use_helper('Widgets', 'CJK') ?>

<?php if (count($rows) > 0): ?>

<dl class="dict-list">
<?php foreach ($rows as $r): ?>
  <dt><?php echo cjk_lang_ja($r['compound'], array('c')) ?><?php echo cjk_lang_ja($r['reading'], array('r')) ?></dt>
  <dd><?php echo $r['glossary'] ?></dd>
<?php endforeach ?>
</dl>

<?php else: ?>

  <div class="body info">
    <p>There are no common words using this character.</p>
  </div>

<?php endif ?>
