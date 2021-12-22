<?php
/**
 * The uiSelectPager class applies paging to a coreDatabaseSelect query
 * and offers getters for the partial template to render the pager.
 * 
 * Constructor options:
 * 
 *   select          The coreDatabaseSelect object
 *   internal_uri    Url as used by link_to() helper (internal or absolute)
 * 
 * Constructor OPTIONAL:
 *
 *   query_params    Associative array of query parameters to add to each paging link
 *   max_per_page    Set rows per page (optional, defaults to 10)
 *   page            Set current page number (optional, defaults to 1)
 *
 *  
 * Methods:
 * 
 *  init()
 *  setSelect(coreDatabaseSelect $select)
 *  setPage($page)
 *  setMaxPerPage($max)
 *  setUrl($internal_uri)
 *  setQueryParams(array $query_params)
 *  
 *  getNbResults()
 *  getMaxPerPage()
 *  getNumPages()
 *  getPage()
 *  getPreviousPage()
 *  getNextPage()
 *  getSelect()                  Returns Select object with paging applied (if pagenum is >= 1)
 *  getResults($fetchMode)
 *  getLinks()                   Return an array of page numbers to be displayed in the pager.
 *  getPageLink($page, $label)   Returns html link for given page, using internal_uri and query_params
 *  getMaxPerPageLinks()         Returns values to display as rows per page options
 *  getMaxPerPageLink()          Returns HTML link tag for rows per page link
 *  
 * 
 * @package    UiParts
 * @author     Fabrice Denis
 */

class uiSelectPager
{
  const
    QUERY_PAGENUM     = 'page',
    QUERY_ROWSPERPAGE = 'rows';

  protected
    $page            = 1,
    $maxPerPage      = 10,
    $maxPerPageLinks = [10, 20, 50],
    $nbResults       = 0,
    $select          = null,
    $countQuery      = null,
    $db              = null,
    $internal_uri    = '',
    $query_params    = [];

  public function __construct(array $options)
  {
    $this->db = sfProjectConfiguration::getActive()->getDatabase();

    if (!isset($options['internal_uri']))
    {
      throw new sfException("Must set 'internal_uri' in uiSelectPager options.");
    }
    $this->setUrl($options['internal_uri']);

    if (isset($options['select']))
    {
      $this->setSelect($options['select']);
    }
    
    if (isset($options['query_params']))
    {
      $this->setQueryParams($options['query_params']);
    }

    if (isset($options['max_per_page']))
    {
      $this->setMaxPerPage((int)$options['max_per_page']);
    }
    
    if (isset($options['page']))
    {
      $this->setPage($options['page']);
    }
  }

  /**
   * Set the url for the paging links.
   * 
   * @param  string  $internal_uri   Internal uri or absolute url
   */
  public function setUrl($internal_uri)
  {
    $this->internal_uri = $internal_uri;
  }
  
  /**
   * Set any number of extra query parameters to append to the paging links.
   * For example, to maintain sort column and sort order when paging through a data table.
   * 
   * @param array  $query_params   Associative array of query variable name and values
   */
  public function setQueryParams(array $query_params)
  {
    $this->query_params = $query_params;
  }

  public function setSelect(coreDatabaseSelect $select)
  {
    $this->select = $select;
  }

  public function setPage($page)
  {
    $this->page = intval($page);
    if ($this->page <= 0)
    {
      //set first page, which depends on a maximum set
      $this->page = $this->getMaxPerPage() ? 1 : 0;
    }
  }
  
  public function setMaxPerPage($max)
  {
    $this->maxPerPage = $max;
  }

  public function init()
  {
    // count the select results
    $this->getCountSelect()->query();
    $result = $this->db->fetchObject();
    $this->setNbResults($result->count);
  }

  protected function getCountSelect()
  {
    // use custom count select if provided (solves odd cases where MySQL doesn't ignore unnecessary JOINs)
    $s = null!== $this->countQuery ? $this->countQuery : clone $this->select;

    $s->reset(coreDatabaseSelect::COLUMNS);
    $s->reset(coreDatabaseSelect::LIMIT_COUNT);
    $s->reset(coreDatabaseSelect::LIMIT_OFFSET);
    $s->reset(coreDatabaseSelect::ORDER);
    $s->columns(['count' => 'COUNT(*)']);
    return $s;
  }

  public function getNbResults()
  {
    return $this->nbResults;
  }
  
  protected function setNbResults($nb)
  {
    $this->nbResults = $nb;
  }

  public function getMaxPerPage()
  {
    return $this->maxPerPage;
  }

  public function getNumPages()
  {
    return ceil($this->getNbResults() / $this->getMaxPerPage());
  }

  public function getPage()
  {
    return $this->page;
  }

  public function getPreviousPage()
  {
    return $this->getPage() > 1 ? $this->page - 1 : false;
  }

  public function getNextPage()
  {
    return $this->getPage() < $this->getNumPages() ? $this->page + 1 : false;
  }

  /**
   * Returns Select object with paging applied (if pagenum is >= 1).
   * 
   * If page number is 0, no paging is applied.
   * 
   * @return coreDatabaseSelect
   */
  public function getSelect()
  {
    return $this->applyPaging($this->select);
  }

  /**
   * Returns Select object with the current paging applied.
   *
   * Use with a custom select when the select passed to constructor was
   * optimized for COUNT(*). Otherwise use getSelect().
   *
   * @return   coreDatabaseSelect
   */
  public function applyPaging(coreDatabaseSelect $select)
  {
    if ($this->page > 0)
    {
      return $select->limitPage($this->page-1, $this->maxPerPage);
    }

    return $select;
  }

  /**
   * Query the Select object, return results according to $fetchMode
   * 
   * @return 
   * @param object $fetchMode
   */
  public function getResults($fetchMode)
  {
    // apply paging
    $pagedSelect = $this->getSelect();

    $prevmode = $this->db->setFetchMode($fetchMode);
    $pagedSelect->query();
    $rows = $this->db->fetchAll();
    $this->db->setFetchMode($prevmode);
    return $rows;
  }

  /**
   * Return an array of page numbers to display in the pager.
   * A false value also means to insert a "..." element in the pager display.
   * 
   * Logic from PunBB paginate() function.
   * 
   * @return array  Array of page indices as appearing in the query string
   */
  public function getLinks()
  {
    $num_pages = $this->getNumPages();
    $cur_page  = $this->getPage();

    $pages = [];
  
    if ($num_pages <= 1)
    {
      $pages[] = 1;
    }
    else
    {
      if ($cur_page > 3)
      {
        $pages[] = 1;
  
        if ($cur_page != 4)
        {
          // "..."
          $pages[] = false;  
        }
      }
  
      // Don't ask me how the following works. It just does, OK? :-)
      for ($current = $cur_page - 2, $stop = $cur_page + 3; $current < $stop; ++$current)
      {
        if ($current < 1 || $current > $num_pages)
          continue;

        $pages[] = $current;
      }
  
      if ($cur_page <= ($num_pages-3))
      {
        if ($cur_page != ($num_pages-3))
        {
          // "..."
          $pages[] = false;
        }
  
        $pages[] = $num_pages;
      }
    }
    
    return $pages;
  }

  /**
   * Return a html link for the given page number.
   * 
   * @param  integer $page   Page number
   * @param  string  $label  Label (eg. "Previous page"), defaults to the page number
   * 
   * @return string  HTML link
   */  
  public function getPageLink($page, $label = null)
  {
    if (is_null($label))
    {
      $label = $page;
    }

    $options = [
      'query_string' => http_build_query(array_merge($this->query_params, [self::QUERY_PAGENUM => $page, self::QUERY_ROWSPERPAGE => $this->getMaxPerPage()])),
      'class'        => 'JSPagerLink'
    ];
    
    return link_to($label, $this->internal_uri, $options);
  }

  /**
   * Returns values to display in the rows per page selection.
   * 
   * @return  array
   */
  public function getMaxPerPageLinks()
  {
    return $this->maxPerPageLinks;
  }

  /**
   * Returns information to build a rows per page link.
   * 
   * @param  int   $n   Max per page value for this link
   * 
   * @return array  Array of values as used by link_to() helper (name, internal_uri, options)
   */
  public function getMaxPerPageUrl($n)
  {
    return [
      (string)$n,
      $this->internal_uri,
      ['query_string' => http_build_query(array_merge($this->query_params, [self::QUERY_ROWSPERPAGE => $n]))]
    ];
  }
}
