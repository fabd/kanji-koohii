<?php
/**
 * uiWidgets.FilterStd template (a widget to switch between multiple options).
 *
 * @uses  css/ui/widgets.css
 *
 * @see   widgets.js (uiWidgets.FilterStd)
 */
?>
<?= tag('div', $options, true)."\n"; ?>
<?php if (!empty($label)): ?>
  <span class="lbl"><?= $label; ?></span>
<?php endif; ?>
  <span class="tb">
    <span class="lr"><?php
      foreach ($links as $link) {
        $name = $link[0];
        $internal_uri = $link[1];
        $options = $link[2] ?? [];
        echo link_to($name, $internal_uri, $options);
      }
?></span>
  </span>
  <div class="clear-both"></div>
</div>
