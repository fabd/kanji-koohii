<?php

class LeitnerChartComponent extends sfComponent
{
  public function execute($request)
  {
    $user_id = sfContext::getInstance()->getUser()->getUserId();

    // we phased this out (if we do it again, using Vue)
    $filter = '';

    $carddata = ReviewsPeer::getLeitnerBoxCounts($filter);

    $this->restudy_cards = $carddata[0]['expired_cards'];

    // count untested cards and add to graph
    $this->untested_cards = ReviewsPeer::getCountUntested($user_id, $filter);

    $carddata[0]['fresh_cards'] = $this->untested_cards;
    $carddata[0]['total_cards'] += $this->untested_cards;

    // count totals (save a database query)
    //$this->total_cards = 0;
    $this->expired_total = 0;
    for ($i = 0; $i < count($carddata); ++$i)
    {
      $box = &$carddata[$i];
      //$this->total_cards += $box['total_cards'];

      // count expired cards, EXCEPT the red stack
      if ($i > 0)
      {
        $this->expired_total += $box['expired_cards'];
      }
    }

    //DBG::printr($this->chart_data);exit;

    $this->leitner_chart_data = $this->makeChartData($carddata, $filter);
    $this->me = $this;

    return sfView::SUCCESS;
  }

  // Format data for the VueJS bar chart component
  protected function makeChartData($carddata, string $filter)
  {
    $data = new stdClass();
    $boxes = [];

    $numDisplayBoxes = max(5, $this->getUser()->getUserSetting('OPT_SRS_MAX_BOX') + 1);

    for ($i = 0; $i < count($carddata); ++$i)
    {
      if ($i < $numDisplayBoxes)
      {
        $boxes[] = [
          ['value' => $carddata[$i]['expired_cards']],
          ['value' => $carddata[$i]['fresh_cards']],
        ];
      }
      else
      {
        $boxes[$numDisplayBoxes - 1][0]['value'] += $carddata[$i]['expired_cards'];
        $boxes[$numDisplayBoxes - 1][1]['value'] += $carddata[$i]['fresh_cards'];
      }
    }

    // initialize remaining empty boxes
    for ($i = count($boxes); $i < $numDisplayBoxes; ++$i)
    {
      $boxes[] = [['value' => 0], ['value' => 0]];
    }

    $data->boxes = $boxes;

    // links used in the chart (obsolete?  Jan 2017)
    sfProjectConfiguration::getActive()->loadHelpers(['Url']);
    $data->urls = [
      'restudy' => url_for('study/failedlist', ['absolute' => true]),
      'new' => url_for_review(['type' => 'untested']),
      'due' => url_for_review(['type' => 'expired']),
    ];

    return $data;
  }
}
