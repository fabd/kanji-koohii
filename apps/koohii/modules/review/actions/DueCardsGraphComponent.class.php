<?php
/**
 * Due Cards Graph Component. Shows how many cards are due each day for the next week.
 * 
 */

class DueCardsGraphComponent extends sfComponent
{
  const GRAPH_DAYS = 7;

  public function execute($request)
  {
    $duecards = ReviewsPeer::getDueCardsByDay();

    // fill num days + 1 because we use index 1 for +1 day, and we ignore index 0
    $cardsByDay = array_fill(0, self::GRAPH_DAYS + 1, 0);

    for ($i = 0; $i < count($duecards); $i++)
    {
      $dayDiff = $duecards[$i];
      
      if ($dayDiff > 0)
      {
        $cardsByDay[$dayDiff]++;
      }
    }

//test $cardsByDay = array(0,11,0,23,14,0,20,7);

    $this->cardsByDay = $cardsByDay;
    $this->maxCards = max($cardsByDay);

    return sfView::SUCCESS;
  }
}
