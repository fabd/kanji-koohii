<?php
  use_helper('Gadgets');
  $sf_request->setParameter('_homeFooter', true);

  // set flag : RTK3 complete
  $rtk3_less =& $lessons[ rtkIndex::inst()->getNumLessonsVol1() + 1 ];
  $is_rtk3_complete = $rtk3_less['totalCards'] === $rtk3_less['maxValue'];

// DBG::printr($lessons)

  // test links code (obsolete)
  // echo link_to('Test', 'review/free', array(
  //   'query_string' => 'from='.$less['from'].'&to='.($less['from'] + $less['maxValue'] - 1).'&shuffle=1'))
?>
<?php slot('inline_styles') ?>
#progress-chart { margin:2em 0 0; }
#progress-chart .active td { background-color:#E5F4A3; color:#000; }
<?php end_slot() ?>

    <h2>Check your progress</h2>
  
    <p> The chart below represents your progress through <strong><?php echo rtkIndex::inst()->getSequenceName() ?></strong>
        (<?php echo link_to('change', 'account/sequence') ?>).
    </p>

    <p>
    The chart is based on your <strong>flashcards</strong>. A card must be reviewed at least once succesfully to appear in the progress chart.
    </p>

    <?php if ($rtk1NotStarted === true): ?>
      <div class="confirmwhatwasdone">
        <?php echo link_to('Add flashcards', '@manage') ?> for <?php echo _CJ('Remembering the Kanji') ?>.
      </div>
    <?php endif; ?>

    <?php if (isset($extraFlashcards)): ?>
      <div class="warningmessagebox">
        Note: <?php echo $extraFlashcards->total ?> flashcards in your deck which are not part of <strong><?php echo rtkIndex::inst()->getSequenceName() ?></strong>
        are ignored in the chart.
      </div>
    <?php endif; ?>

<?php if ($progress->heisignum === rtkIndex::inst()->getNumCharactersVol1()): ?>
      <p class="confirmwhatwasdone" style="color:#318c1c;">
        <strong>RTK Volume 1 complete!</strong> Congratulations!</p>
      </p>
<?php endif ?>

<?php if ($is_rtk3_complete): ?>
      <p class="confirmwhatwasdone" style="color:#318c1c;">
        <strong>RTK Volume 3 complete!</strong>
      </p>
<?php endif ?>


    <?php if ($progress->curlesson !== false): ?>
      <p> <strong>Your current goal is to complete lesson <?php echo $progress->curlesson ?>.</strong></p>
    <?php endif; ?>
 
    <?php #progress chart table ?>

<div class="no-gutter-xs-sm">
    <table id="progress-chart" class="uiTabular" cellspacing="0">
     <thead>
      <tr>
        <th style="width:15%;"><span class="hd">Lesson</span></th>
        <th><span class="hd">Progress</span></th>
        <th style="width:15%;text-align:center">Flashcards&nbsp;</th>
      </tr>
     </thead>
     <tbody>
     <?php $i=1; foreach ($lessons as $lkey => $less): ?>
      <tr<?php $cssClass = (($i++ % 2) ? 'odd' : '') . ($lkey === $progress->curlesson ? ' active' : ''); 
        if ($cssClass !== '') echo ' class="'.$cssClass.'"';
       ?>>
        <td><?php echo $less['label'] ?></td>
        <td><?php
        if ($less['totalCards'] > 0) {
          # Show failed cards also:
          # array('value' => $less['testedCards'], 'label' => $less['passValue'].' passed card(s)' , 'class' => 'g'),
          # array('value' => $less['failValue'], 'label' => $less['failValue'].' failed card(s)', 'class' => 'r')
          # Show just progress (reduce info overload, focus on progres)
          echo ui_progress_bar(array(
            array('value' => $less['totalCards'], 'label' => $less['totalCards'].' flashcard(s)' , 'class' => 'g')
            #array('value' => $less['failValue'], 'label' => $less['failValue'].' failed card(s)', 'class' => 'r'),
          ), $less['maxValue']);
        }
        else {
          echo 'Not yet started';
        }?></td>
        <td class="center ws-nw"><?php echo $less['totalCards'].' / '.$less['maxValue'] ?></td>
        
      </tr>
     <?php endforeach; ?>
     </tbody>
    </table>
</div>