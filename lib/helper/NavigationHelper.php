<?php
/**
 * These helpers allow to define the active primary and secondary navigation items
 * from the view template. Then from the site layout, to highlight the corresponding
 * item with a css class.
 * 
 * 
 * @package  Helpers
 * @author   Fabrice Denis
 */

/**
 * Set both primary and secondary navigation highlights at once.
 * 
 * @param string $nav_id
 */
function set_nav($primaryNavId, $secondaryNavId = '')
{
  set_primary_nav($primaryNavId);
  set_secondary_nav($secondaryNavId);
}

/**
 * Set primary navigation highlight.
 * 
 */
function set_primary_nav($nav_id)
{
  sfConfig::set('layout.primarynav.current', $nav_id);
}

function get_primary_nav()
{
  return sfConfig::get('layout.primarynav.current', '');
}

/**
 * Set secondary navigation highlight.
 * 
 */
function set_secondary_nav($nav_id)
{
  sfConfig::set('layout.secondarynav.current', $nav_id);
}

/**
 * Returns the css active class for a link or element, if the given id corresponds
 * to the currently defined PRIMARY navigation (set by set_secondary_nav() in view template).
 * 
 * The class is returned as html attribute array, as used by the Tag and Url helpers.
 * 
 * Example:
 * 
 *   In the view template:
 *   
 *     <%php set_nav('home') %>
 *     
 *   In the layout template:
 *   
 *     The active link will have class="active" set.
 * 
 *     ...
 *     <li><%php echo link_to('Home', '@homepage', nav_pri('homepage')) %></li>
 *     <li><%php echo link_to('Help', '@helppage', nav_pri('helppage')) %></li>
 *     ...
 * 
 * 
 * @return array  Options to pass to the url link helpers
 * @param  string Navigation id of link
 */
function nav_pri($nav_id)
{
  return strcasecmp($nav_id, sfConfig::get('layout.primarynav.current'))==0 ? array('class' => 'active') : array();
}

/**
 * Returns the css active class for a link or element, if the given id corresponds
 * to the currently defined SECONDARY navigation (set by set_secondary_nav() in view template).
 * 
 * The class is returned as html attribute array, as used by the Tag and Url helpers.
 * 
 */
function nav_sec($nav_id)
{
  return strcasecmp($nav_id, sfConfig::get('layout.secondarynav.current'))==0 ? array('class' => 'active') : array();
}
