<?php
class memberActions extends sfActions
{
  public function executeIndex()
  {
  }

  public function executeProgress()
  {
    $curIndex = rtkIndex::inst();

    define('RTK_VOLUME_3_LESSON', $curIndex->getNumLessonsVol1() + 1);

    // find the success/fail flashcard count per lesson
    $card_data = ReviewsPeer::getProgressChartData($this->getUser()->getUserId());
    if (isset($card_data[0])) {
      $this->extraFlashcards = $card_data[0];
    }

    $rtkLessons = rtkIndex::getLessons();

    //  lessons always show even if empty
    $lessons = [];
    for ($i = 1, $from = 1; $i <= RTK_VOLUME_3_LESSON; $i++)
    {
      $lessons[$i] = [
        'label'      => $i < RTK_VOLUME_3_LESSON ? '<span class="visible-md-lg">Lesson </span>'.$i : 'RTK Vol.3',
        'index'      => $i,
        'passValue'  => 0,
        'failValue'  => 0,
        'testedCards'=> 0,
        'totalCards' => 0,
        'maxValue'   => $rtkLessons[$i],
        'from'       => $from              // for Review lesson link
      ];
      $from += $rtkLessons[$i];
    }
   
    $this->rtk1NotStarted = true;


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
          if ($p->lessonId === RTK_VOLUME_3_LESSON)
          {
            // $lesson['label'] = 'RTK Vol.3';
            // $lesson['from']  = $lessons[$curIndex->getNumLessonsVol1()]['from'] + $lessons[$curIndex->getNumLessonsVol1()]['maxValue'];
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
    
    // get progress data for last completed frame number in order
    $this->progress = rtkIndex::getProgressSummary();

    $this->lessons = $lessons;    
  }

}
