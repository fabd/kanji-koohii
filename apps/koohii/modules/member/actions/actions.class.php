<?php
class memberActions extends sfActions
{
  public function executeIndex()
  {
  }

  public function executeProgress()
  {
    // get progress data for last completed frame number in order
    $this->progress = rtkIndex::getProgressSummary();

    // find the success/fail flashcard count per lesson
    $card_data = ReviewsPeer::getProgressChartData($this->getUser()->getUserId());
    if (isset($card_data[0])) {
      $this->extraFlashcards = $card_data[0];
    }

    $rtkLessons = rtkIndex::getLessons();

    //  lessons always show even if empty
    $lessons = array();
    for ($i = 1, $from = 1; $i <= rtkIndex::inst()->getNumLessonsVol1(); $i++)
    {
      $lessons[$i] = array(
        'label'      => '<span class="visible-md-lg">Lesson </span>'.$i,
        'index'      => $i,
        'passValue'  => 0,
        'failValue'  => 0,
        'testedCards'=> 0,
        'totalCards' => 0,
        'maxValue'   => $rtkLessons[$i],
        'from'       => $from              // for Review lesson link
      );
      $from += $rtkLessons[$i];
    }
   
    $this->rtk1NotStarted = true;

    $curIndex = rtkIndex::inst();

    foreach ($card_data as $lessNr => $p)
    {
      if ($lessNr === 0)
      {
        continue;
      }

//DBG::printr($p);exit;
      $lesson =& $lessons[$p->lessonId];

      $lesson['passValue']  = $p->pass;
      $lesson['failValue']  = $p->fail;
      $lesson['testedCards']= $p->pass + $p->fail;
      $lesson['totalCards'] = $p->total;
      $lesson['maxValue']   = $rtkLessons[$p->lessonId];
      
      // special lessons
      if ($p->lessonId > $curIndex->getNumLessonsVol1())
      {
          // RTK Vol.3
          if ($p->lessonId === $curIndex->getNumLessonsVol1() + 1)
          {
            $lesson['label'] = 'RTK Vol.3';
            $lesson['from']  = $lessons[$curIndex->getNumLessonsVol1()]['from'] + $lessons[$curIndex->getNumLessonsVol1()]['maxValue'];
          }
          // RTK Supplement
          elseif ($p->lessonId === 58)
          {
            $lesson['label'] = 'RTK Suppl.';
            $lesson['from']  = $curIndex->getNumCharactersVol3() + 1;
          }

      }

      if ($p->lessonId <= $curIndex->getNumLessonsVol1() && $p->total > 0)
      {
        $this->rtk1NotStarted = false;
      }
    }

    $this->lessons = $lessons;    
  }

}
