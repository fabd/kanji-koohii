<?php use_helper('CJK', 'SimpleDate', 'Form', 'Validation', 'Widgets') ?>
<?php 
/**
 * uiBMenu documentation:
 *
 * Each menu item element has attribute "data-menuid", value:
 *
 *    "page"    After clicking this menu item, immediately load the page url in "data-uri" attribute.
 *    "close"   This menu item closes the dialog.
 */

/**
 * Output the flashcard information box.
 * 
 * @return string
 */
$menu = array();
$bReviewMode = $sf_params->has('review');

function flashcard_stats($cardData)
{
  // prepare flashcard stats    
  if ($cardData->leitnerbox == 1)
  {
    $whichBox = esc_specialchars($cardData->totalreviews == 0 ? 'New cards (blue pile)' : 'Restudy cards (red pile)');
  }
  else
  {
    $whichBox = esc_specialchars(/*'Stack '.*/$cardData->leitnerbox);
  }
  
  $lastReview = esc_specialchars($cardData->ts_lastreview > 0 ? simple_format_date((int)$cardData->ts_lastreview, rtkLocale::DATE_SHORT) : 'Not tested yet.');

  $html = <<<EOD
<table class="stats" cellspacing="0">
<tr><th>Box</th><td>{$whichBox}</td></tr>
<tr><th>Passed</th><td><strong>{$cardData->successcount}</strong> time(s)</td></tr>
<tr><th>Failed</th><td><strong>{$cardData->failurecount}</strong> time(s)</td></tr>
<tr><th>Last review</th><td>{$lastReview}</td></tr>
</tr>
</table>
EOD;
  return $html;
}

/**
 * add_menu_item 
 * 
 * @param array $menu
 * @param mixed $label  
 * @param mixed $action  Code used in data-menuid attribute, lets client
 *                       identify the menu selected.
 * @param mixed $extra   Additional options to be merged into the menu
 *                       element's attributes (optional)
 */
function add_menu_item(& $menu, $label, $action, $extra = array())
{
  // delete confirm is red
  $classNames = 'uiGUI JsMenuItem ';
  $btnClass   = strpos($action, 'confirm-') === 0 ? 'uiIBtnRed' : 'uiIBtnGreen';

  $attributes = array_merge(array('class' => $classNames.$btnClass, 'data-menuid' => $action), $extra);
  $menu[] = array('label' => $label, 'attributes' => $attributes);
}

function get_dialog_menu($menu)
{
  $numItems = count($menu);

  if ($numItems === 0)
  {
    return '';
  }

  // create menu markup
  $html = '<div class="uiBMenu">';
  foreach ($menu as $item)
  {
    // $html = $html . link_to($item['label'], '#', $item['attributes']);
    $html = $html . '<div class="uiBMenuItem">'.ui_ibtn($item['label'], '', $item['attributes']).'</div>';
  }
  $html .= '</div>';

  return $html;
}
?>
<div id="editflashcarddlg" class="body uiBMenuBody">

<?php
  if ($sf_request->hasErrors())
  {
    echo form_errors();
    add_menu_item($menu, 'Ok', 'close');
  }
  elseif ($confirm !== false)
  {
    // $confirm is the action to confirm, menuid will be "confirm-xyz"
    echo "<p class=\"uiBMenuMsg uiBMenuRed\">$message</p>\n";
    add_menu_item($menu, 'Delete', 'confirm-'.$confirm);
    add_menu_item($menu, 'Cancel', 'close');
  }
  elseif (!empty($message))
  {
?>
    <p class="uiBMenuMsg uiBMenuGreen"><?php echo $message; ?></p>
<?php 
    add_menu_item($menu, 'Ok', 'close'); 
  }
  else
  {
    // build menu
    if (!$cardData)
    {
      add_menu_item($menu, 'Add flashcard', 'add');
    }
    else
    {
      echo flashcard_stats($cardData);
    }

    // Study : "Review new cards (n)" option to start a review of cards added with the menu
    if (!$bReviewMode)
    {
      $countNewCards = ReviewsPeer::getCountUntested($sf_user->getUserId());
      if ($countNewCards)
      {
        add_menu_item($menu, 'Review new cards ('.$countNewCards.')', 'page', array('data-uri' => $sf_context->getController()->genUrl('review/review?type=untested')));
      }
    }

    if ($cardData)
    {
      // Study : "fail" a card
      if (!$bReviewMode && $cardData->leitnerbox > 1 && $cardData->totalreviews > 0)
      {
        add_menu_item($menu, 'Move card to restudy pile', 'fail');
      }

      // Study & Review : delete flashcard for current character
      add_menu_item($menu, 'Delete flashcard', 'delete');

      // Review only : skip this flashcard
      if ($bReviewMode)
      {
        add_menu_item($menu, 'Skip this flashcard', 'skip');
      }
    }
  }

  echo '</div>'; // body

  echo get_dialog_menu($menu);
?>
