<?php
/**
 * LeitnerChart Component.
 * 
 * Prepare the data for the front end Leitner var chart (Vue)
 *
 * The data is passed as a JSON string in a <input type="hidden" ...>
 */

class LeitnerChartComponent extends sfComponent
{
  public function execute($request)
  {
    $user_id = sfContext::getInstance()->getUser()->getUserId();

    $this->filter = ''; //$this->getUser()->getLocalPrefs()->get('review.graph.filter', '');

    $carddata = ReviewsPeer::getLeitnerBoxCounts($this->filter);

    $this->restudy_cards = $carddata[0]['expired_cards'];

    // count untested cards and add to graph
    $this->untested_cards = ReviewsPeer::getCountUntested($user_id, $this->filter);

    $carddata[0]['fresh_cards'] = $this->untested_cards;
    $carddata[0]['total_cards'] += $this->untested_cards;
    
    // count totals (save a database query)
    //$this->total_cards = 0;
    $this->expired_total = 0;
    for ($i = 0; $i < count($carddata); $i++)
    {
      $box =& $carddata[$i];
      //$this->total_cards += $box['total_cards'];
      
      // count expired cards, EXCEPT the red stack
      if ($i > 0)
      {
        $this->expired_total += $box['expired_cards'];
      }
    }

    $this->leitner_chart_data = $this->makeChartData($carddata);

    
//DBG::printr($this->chart_data);exit;

    $this->me = $this;

    return sfView::SUCCESS;
  }

  // Format data for the VueJS bar chart component
  protected function makeChartData($carddata)
  {
    $data  = new stdClass();
    $boxes = array();

    for ($i = 0; $i < count($carddata); $i++) {
      $boxes[] = array(
        // left stack, right stack
        array( 'value' => $carddata[$i]['expired_cards'] ),
        array( 'value' => $carddata[$i]['fresh_cards']   )
      );
    }
    
    $data->boxes = $boxes;

    /* links used in the chart (obsolete?  Jan 2017) */
    sfProjectConfiguration::getActive()->loadHelpers(array('Url'));
    $data->urls = array(
      'restudy'  => url_for('study/failedlist', array('absolute' => true)),
      'new'      => $this->getReviewUrl(array('type' => 'untested')),
      'due'      => $this->getReviewUrl(array('type' => 'expired'))
    );

    return $data;
  }

  /**
   * Returns url for the review page with given query parameters.
   * 
   * @param  array   $params    Query parameters
   * 
   * @return string  HTML for link tag
   */
  public function getReviewUrl($query_params)
  {
    return url_for('@review', array('absolute' => true)).'?'.http_build_query($this->addFilterParam($query_params));
  }
  
  // Add the current view filter param ('all', 'rtk1', 'rtk3', ...)
  protected function addFilterParam($query_params)
  {
    if ($this->filter !== '') {
      $query_params['filt'] = $this->filter;
    }
    return $query_params;
  }

  // Returns query_string for eview buttons given parameters + current filter.
  public function getQueryString($query_params)
  {
    return http_build_query($this->addFilterParam($query_params));
  }
}


