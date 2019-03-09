<?php use_helper('Widgets') ?>
<?php #DBG::warn($sf_user->getLocalPrefs()->get('review.graph.filter')) ?>
<?php $todayCount = ReviewsPeer::getTodayCount($sf_user->getUserId()); ?>

  <h2>Spaced Repetition</h2>

  <div id="srs-summary" class="txt-lg mb-p50">
    <span class="total"><strong><?php echo $flashcard_count ?></strong> flashcards</span>
    <span class="today"><strong><?php echo $todayCount ?></strong> reviews today</span>
  </div>

  <div class="mb-2">

    <div id="view-pane-all" class="rtk-filter-pane" style="<?php ui_display($filter==='all') ?>">
      
        <?php if ($filter==='all'): ?>
          <?php include_component('review', 'LeitnerChart') ?>
        <?php else: ?>
          <div class="not-loaded-yet"></div>
        <?php endif ?>
     
      <!--p> php echo link_to('Review fullscreen (dev)','review/fullscreen') ?-->
    </div>
    <div id="view-pane-rtk1" class="rtk-filter-pane" style="<?php ui_display($filter==='rtk1') ?>">
    
        <?php if ($filter==='rtk1'): ?>
          <?php include_component('review', 'LeitnerChart') ?>
        <?php else: ?>
          <div class="not-loaded-yet"></div>
        <?php endif ?>
   
    </div>
    <div id="view-pane-rtk3" class="rtk-filter-pane" style="<?php ui_display($filter==='rtk3') ?>">
  
        <?php if ($filter==='rtk3'): ?>
          <?php include_component('review', 'LeitnerChart') ?>
        <?php else: ?>
          <div class="not-loaded-yet"></div>
        <?php endif ?>
 
    </div>

  </div><!--/section-->

  <h3>Due cards next week</h3>
  <p>The following bar chart represents how many cards are scheduled for review over the next week.</p>
  <?php include_component('review', 'DueCardsGraph') ?>
