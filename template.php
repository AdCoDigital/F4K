<?php
/**
 * @file
 * Contains the theme's functions to manipulate Drupal's default markup.
 *
 * Complete documentation for this file is available online.
 * @see https://drupal.org/node/1728096
 */


/**                        **
 ** Page Related Functions **
 **                        **/

function fun4kids_preprocess_page(&$variables) {

  //get the newsletter subscibe block based on what region you have selected
  if(global_filter_get_session_value('field_region')=='All'){
    $variables['newsletter_subscribe_block'] = module_invoke('simplenews', 'block_view','37');
  }
   else if(global_filter_get_session_value('field_region')=='Brainerd Lakes Area'){
    $variables['newsletter_subscribe_block'] = module_invoke('simplenews', 'block_view','53');
  }
  else if(global_filter_get_session_value('field_region')=='Fargo / Moorehead'){
    $variables['newsletter_subscribe_block'] = module_invoke('simplenews', 'block_view','54');
  }
  else if(global_filter_get_session_value('field_region')=='Minneapolis'){
    $variables['newsletter_subscribe_block'] = module_invoke('simplenews', 'block_view','55');
  }
  else if(global_filter_get_session_value('field_region')=='NE Metro'){
    $variables['newsletter_subscribe_block'] = module_invoke('simplenews', 'block_view','56');
  }
  else if(global_filter_get_session_value('field_region')=='Northeast MN'){
    $variables['newsletter_subscribe_block'] = module_invoke('simplenews', 'block_view','57');
  }
  else if(global_filter_get_session_value('field_region')=='NW Metro'){
    $variables['newsletter_subscribe_block'] = module_invoke('simplenews', 'block_view','58');
  }
  else if(global_filter_get_session_value('field_region')=='NW Suburban'){
    $variables['newsletter_subscribe_block'] = module_invoke('simplenews', 'block_view','59');
  }
  else if(global_filter_get_session_value('field_region')=='SE Metro'){
    $variables['newsletter_subscribe_block'] = module_invoke('simplenews', 'block_view','60');
  }
  else if(global_filter_get_session_value('field_region')=='Southern MN'){
    $variables['newsletter_subscribe_block'] = module_invoke('simplenews', 'block_view','61');
  }
  else if(global_filter_get_session_value('field_region')=='St. Cloud Area'){
    $variables['newsletter_subscribe_block'] = module_invoke('simplenews', 'block_view','62');
  }
  else if(global_filter_get_session_value('field_region')=='St. Paul'){
    $variables['newsletter_subscribe_block'] = module_invoke('simplenews', 'block_view','63');
  }
  else if(global_filter_get_session_value('field_region')=='SW Metro'){
    $variables['newsletter_subscribe_block'] = module_invoke('simplenews', 'block_view','64');
  }


  /* check page to see if various pages (e.g. front page, taxonomy page, specific node pages, etc.)
     and add css files that are specific to those pages to them */
  if ($variables['is_front']) { /* on the home page */
    drupal_add_css(path_to_theme() . '/css/home-main-view-panes.css', array('group' => CSS_THEME));
    drupal_add_css(path_to_theme() . '/css/page--home.css', array('group' => CSS_THEME));
    drupal_add_css(path_to_theme() . '/css/parents-corner-pane.css', array('group' => CSS_THEME));
  } else if (menu_get_object('taxonomy_term', 2) /* menu_get_object('taxonomy_term', 2) will exist if page is a taxonomy page */
          || (isset($variables['node']) && ($variables['node']->type == 'business_listing'))
          || ((drupal_get_path_alias($_GET['q']) == 'fun-by-category') || (drupal_get_path_alias($_GET['q']) == 'services'))) {
    drupal_add_css(path_to_theme() . '/css/businesses.css', array('group' => CSS_THEME));
    drupal_add_js(path_to_theme() . '/js/businesses.js');
  } else if (isset($variables['node']) && ($variables['node']->type == 'fun_4_kids_event')) { /* an events page */
    drupal_add_css(path_to_theme() . '/css/events.css', array('group' => CSS_THEME));
  } else if ((drupal_get_path_alias($_GET['q']) == 'calendar') || (drupal_get_path_alias($_GET['q']) == 'monthly-calendar')) { /* a calendar page */
    drupal_add_css(path_to_theme() . '/css/calendar-overrides.css', array('group' => CSS_THEME));
  } else if (drupal_get_path_alias($_GET['q']) == 'parents-corner') { /* parents corner page */
    drupal_add_css(path_to_theme() . '/css/page--parents-corner.css', array('group' => CSS_THEME));
    drupal_add_css(path_to_theme() . '/css/parents--quick-links-pane.css', array('group' => CSS_THEME));
  } else if (drupal_get_path_alias($_GET['q']) == 'crafts-4-kids') {
    drupal_add_css(path_to_theme() . '/css/rainy-day-and-blog-pages.css', array('group' => CSS_THEME));
  } else if (drupal_get_path_alias($_GET['q']) == 'daily-deals') { /* submit-event page */
    drupal_add_css(path_to_theme() . '/css/deals-and-more.css', array('group' => CSS_THEME));
  } else if (drupal_get_path_alias($_GET['q']) == 'garage_sales') { /* garage_sales page */
    drupal_add_css(path_to_theme() . '/css/deals-and-more.css', array('group' => CSS_THEME));
  } else if (drupal_get_path_alias($_GET['q']) == 'confirmation-page') { /* confirmation-page */
    drupal_add_css(path_to_theme() . '/css/confirmation-page.css', array('group' => CSS_THEME));
  } else if (drupal_get_path_alias($_GET['q']) == 'find-your-region') { /* find-your-region info page */
    drupal_add_css(path_to_theme() . '/css/find-your-region.css', array('group' => CSS_THEME));
  }
}


/*
 * For the "search form"
*/
function fun4kids_form_search_block_form_alter(&$form, &$form_state, $form_id) {
     $form['search_block_form']['#title'] = t('Search for fun');
     $form['search_block_form']['#title_display'] = 'before';
}


/*
 * Set variables so HTML for flags for Add / Remove items from Toybox are as desired 
*/
function fun4kids_preprocess_flag(&$variables) {
  switch ($variables['flag']->name) {
    case 'blogs':
    case 'garage_sale':
    case 'event':
    case 'coupons':
    case 'bookmarks': // flag is of "Add To Toybox" variety
      $variables['flag_marker'] = "<img class='toybox-marker rainy-day-view--link-marker link-marker' src='/fun4kids/images/toy_box_add_lg.png' />";
      $variables['flag_wrapper_classes_array'][] = 'flag-add-toybox';
      break;
    default:
      $variables['flag_marker'] = '';
      break;
  }
}



/**                                          **
 ** Business Listing Field Related Functions **
 **                                          **/



// remove excess wrapper divs for (most) business listing fields
function fun4kids_field__business_listing($variables) {
  $output = drupal_render($variables['items'][0]);
  $output = '<span class="' . $variables ['classes'] . '"' . $variables ['attributes'] . '>' . $output . '</span>';

  return $output;
}





/**                                   **
 ** Date / Calendar Related Functions **
 **                                   **/



/*                                                        
 * Override of theme_date_nav_title() from the Date module 
 */                                                          

// Theme the calendar title
function fun4kids_date_nav_title($params) {
  $granularity = $params['granularity'];
  $view = $params['view'];
  $date_info = $view->date_info;
  $link = !empty($params['link']) ? $params['link'] : FALSE;
  $format = !empty($params['format']) ? $params['format'] : NULL;
  $format_with_year = variable_get('date_views_' . $granularity . 'format_with_year', 'l, F j, Y');
  $format_without_year = variable_get('date_views_' . $granularity . 'format_without_year', 'l, F j');
  switch ($granularity) {
    case 'year':
      $title = $date_info->year;
      $date_arg = $date_info->year;
      break;
    case 'month':
      // $format = !empty($format) ? $format : (empty($date_info->mini) ? $format_with_year : $format_without_year);
      $format = 'F Y'; /* e.g. Want date title on monthly calendars to show as August 2015 instead of Saturday, August 1, 2015 */
      if ($view->current_display == 'f4k_calendar__month_for_week') {
        $title = 'This Week\'s Events';
      } else {
        $title = date_format_date($date_info->min_date, 'custom', $format);
      }
      $date_arg = $date_info->year . '-' . date_pad($date_info->month);
      break;
    case 'day':
      $format = !empty($format) ? $format : (empty($date_info->mini) ? $format_with_year : $format_without_year);
      $title = date_format_date($date_info->min_date, 'custom', $format);
      $date_arg = $date_info->year . '-' . date_pad($date_info->month) . '-' . date_pad($date_info->day);
      break;
    case 'week':
      // $format = !empty($format) ? $format : (empty($date_info->mini) ? $format_with_year : $format_without_year);
      // $title = t('Week of @date', array('@date' => date_format_date($date_info->min_date, 'custom', $format)));
      $title = t("This Week's Events");
      $date_arg = $date_info->year . '-W' . date_pad($date_info->week);
      break;
  }
  if (!empty($date_info->mini) || $link) {
    // Month navigation titles are used as links in the mini view.
    $attributes = array('title' => t('View full page month'));
    $url = date_pager_url($view, $granularity, $date_arg, TRUE);
    return l($title, $url, array('attributes' => $attributes));
  }
  else {
    return $title;
  }
}


/*                                                        
 * Add html class (to help with CSS) to each item designating whether day of month is even or odd
 */    
function fun4kids_preprocess_calendar_month_col(&$variables) {
  $variables['item']['class'] .= ($variables['item']['day_of_month'] % 2 == 0 ? ' even' : ' odd');
}


/*                                                        
 * Override theme_date_display_range so dates are separated by '-', not 'to'
 */    
function fun4kids_date_display_range($variables) {
  $date1 = $variables['date1'];
  $date2 = $variables['date2'];
  $timezone = $variables['timezone'];
  $attributes_start = $variables['attributes_start'];
  $attributes_end = $variables['attributes_end'];

  $start_date = '<span class="date-display-start"' . drupal_attributes($attributes_start) . '>' . $date1 . '</span>';
  $end_date = '<span class="date-display-end"' . drupal_attributes($attributes_end) . '>' . $date2 . $timezone . '</span>';

  // If microdata attributes for the start date property have been passed in,
  // add the microdata in meta tags.
  if (!empty($variables['add_microdata'])) {
    $start_date .= '<meta' . drupal_attributes($variables['microdata']['value']['#attributes']) . '/>';
    $end_date .= '<meta' . drupal_attributes($variables['microdata']['value2']['#attributes']) . '/>';
  }

  /* EDITED: I added a wrapper and changed the separator to a '-' (itself wrapped in a span) */
  $output = '<span class="date-range-wrapper">' . $start_date . '<span class="date-range-separator">-</span>' . $end_date . '</span>';
  return $output;

  // Wrap the result with the attributes.

/* return t('!start-date to !end-date', array(
    '!start-date' => $start_date,
    '!end-date' => $end_date,
  )); */
}


function fun4kids_preprocess_date_views_pager(&$vars) {
  if ($vars['plugin']->display->id != 'f4k_calendar__month_for_week') {
    $vars['nav_button_1'] = array(
        'class-name' => 'date-prev',
        'value'      => '<img src="/fun4kids/images/full_calendar_left.png" />',
        'url'        => $vars['prev_url'],
        'options'    => $vars['prev_options'],
      );
    $vars['nav_button_2'] = array(
        'class-name' => 'date-prev',
        'value'      => '<img src="/fun4kids/images/full_calendar_right.png" />',
        'url'        => $vars['next_url'],
        'options'    => $vars['next_options'],
      );
  } else  {
     $vars['nav_button_1'] = array(
        'class-name' => 'view-full-calendar',
        'value'      => 'View Full Calendar',
        'url'        => 'monthly-calendar',
        'options'    => array(
          'attributes' => array(
            'title' => 'View full calendar'),
          'html' => true)
      );
      $vars['nav_button_2'] = array(
        'class-name' => 'add-event',
        'value'      => 'Add an Event',
        'url'        => 'submit-event',
        'options'    => array(
          'attributes' => array(
            'title' => 'Add an event'),
          'html' => true)
      );
  }
}








/**                        **
 ** Other / Misc Functions **
 **                        **/


function fun4kids_item_list($variables) {
  $items = $variables['items'];
  $title = $variables['title'];
  $type = $variables['type'];
  $attributes = $variables['attributes'];

  // Only output the list container and title, if there are any list items.
  // Check to see whether the block title exists before adding a header.
  // Empty headers are not semantic and present accessibility challenges.
  $output = '<div class="item-list">';
  if (isset($title) && $title !== '') {
    $output .= '<h3>' . $title . '</h3>';
  }

  if (!empty($items)) {
    $output .= "<$type" . drupal_attributes($attributes) . '>';
    $num_items = count($items);
    $i = 0;
    foreach ($items as $item) {
      $attributes = array();
      $children = array();
      $data = '';
      $i++;
      if (is_array($item)) {
        foreach ($item as $key => $value) {
          if ($key == 'data') {
            $data = $value;
          }
          elseif ($key == 'children') {
            $children = $value;
          }
          else {
            $attributes[$key] = $value;
          }
        }
      }
      else {
        $data = $item;
      }
      if (count($children) > 0) {
        // Render nested list.
        $data .= theme_item_list(array('items' => $children, 'title' => NULL, 'type' => $type, 'attributes' => $attributes));
      }
      if ($i == 1) {
        $attributes['class'][] = 'first';
      }
      if ($i == $num_items) {
        $attributes['class'][] = 'last';
      }
      $output .= '<li' . drupal_attributes($attributes) . '>' . $data . "</li>\n";
    }
    $output .= "</$type>";
  }
  $output .= '</div>';
  return $output;
}




/* The head_title variable doesn't seem to pass correctly to the template? (e.g. it'll just show | Fun 4 Kids, instead of
  Calendar | Fun 4 Kids), but works when I put the preprocess function (unedited) here */
function fun4kids_preprocess_html(&$variables) {
  // Compile a list of classes that are going to be applied to the body element.
  // This allows advanced theming based on context (home page, node of certain type, etc.).
  // Add a class that tells us whether we're on the front page or not.
  $variables ['classes_array'][] = $variables ['is_front'] ? 'front' : 'not-front';
  // Add a class that tells us whether the page is viewed by an authenticated user or not.
  $variables ['classes_array'][] = $variables ['logged_in'] ? 'logged-in' : 'not-logged-in';

  // Add information about the number of sidebars.
  if (!empty($variables ['page']['sidebar_first']) && !empty($variables ['page']['sidebar_second'])) {
    $variables ['classes_array'][] = 'two-sidebars';
  }
  elseif (!empty($variables ['page']['sidebar_first'])) {
    $variables ['classes_array'][] = 'one-sidebar sidebar-first';
  }
  elseif (!empty($variables ['page']['sidebar_second'])) {
    $variables ['classes_array'][] = 'one-sidebar sidebar-second';
  }
  else {
    $variables ['classes_array'][] = 'no-sidebars';
  }

  // Populate the body classes.
  if ($suggestions = theme_get_suggestions(arg(), 'page', '-')) {
    foreach ($suggestions as $suggestion) {
      if ($suggestion != 'page-front') {
        // Add current suggestion to page classes to make it possible to theme
        // the page depending on the current page type (e.g. node, admin, user,
        // etc.) as well as more specific data like node-12 or node-edit.
        $variables ['classes_array'][] = drupal_html_class($suggestion);
      }
    }
  }

  // If on an individual node page, add the node type to body classes.
  if ($node = menu_get_object()) {
    $variables ['classes_array'][] = drupal_html_class('node-type-' . $node->type);
  }

  // RDFa allows annotation of XHTML pages with RDF data, while GRDDL provides
  // mechanisms for extraction of this RDF content via XSLT transformation
  // using an associated GRDDL profile.
  $variables ['rdf_namespaces'] = drupal_get_rdf_namespaces();
  $variables ['grddl_profile'] = 'http://www.w3.org/1999/xhtml/vocab';
  $variables ['language'] = $GLOBALS ['language'];
  $variables ['language']->dir = $GLOBALS ['language']->direction ? 'rtl' : 'ltr';

  // Add favicon.
  if (theme_get_setting('toggle_favicon')) {
    $favicon = theme_get_setting('favicon');
    $type = theme_get_setting('favicon_mimetype');
    drupal_add_html_head_link(array('rel' => 'shortcut icon', 'href' => drupal_strip_dangerous_protocols($favicon), 'type' => $type));
  }

  // Construct page title.
  if (drupal_get_title()) {
    $head_title = array(
      'title' => strip_tags(drupal_get_title()),
      'name' => check_plain(variable_get('site_name', 'Drupal')),
    );
  }
  else {
    $head_title = array('name' => check_plain(variable_get('site_name', 'Drupal')));
    if (variable_get('site_slogan', '')) {
      $head_title ['slogan'] = filter_xss_admin(variable_get('site_slogan', ''));
    }
  }
  $variables ['head_title_array'] = $head_title;
  $variables ['head_title'] = implode(' | ', $head_title);

  // Populate the page template suggestions.
  if ($suggestions = theme_get_suggestions(arg(), 'html')) {
    $variables ['theme_hook_suggestions'] = $suggestions;
  }
}




/* Override of theme_pager() for blog listings views */

function fun4kids_pager__blog_listings($variables) {
  $tags = $variables['tags'];
  $element = $variables['element'];
  $parameters = $variables['parameters'];
  $quantity = $variables['quantity'];
  global $pager_page_array, $pager_total;

  // Calculate various markers within this pager piece:
  // Middle is used to "center" pages around the current page.
  $pager_middle = ceil($quantity / 2);
  // current is the page we are currently paged to
  $pager_current = $pager_page_array[$element] + 1;
  // first is the first page listed by this pager piece (re quantity)
  $pager_first = $pager_current - $pager_middle + 1;
  // last is the last page listed by this pager piece (re quantity)
  $pager_last = $pager_current + $quantity - $pager_middle;
  // max is the maximum page number
  $pager_max = $pager_total[$element];
  // End of marker calculations.

  // Prepare for generation loop.
  $i = $pager_first;
  if ($pager_last > $pager_max) {
    // Adjust "center" if at end of query.
    $i = $i + ($pager_max - $pager_last);
    $pager_last = $pager_max;
  }
  if ($i <= 0) {
    // Adjust "center" if at start of query.
    $pager_last = $pager_last + (1 - $i);
    $i = 1;
  }
  // End of generation loop preparation.

  $li_first = theme('pager_first', array('text' => (isset($tags[0]) ? $tags[0] : t('« first')), 'element' => $element, 'parameters' => $parameters));
  $li_previous = theme('pager_previous', array('text' => (isset($tags[1]) ? $tags[1] : t('‹ previous')), 'element' => $element, 'interval' => 1, 'parameters' => $parameters));
  $li_next = theme('pager_next', array('text' => (isset($tags[3]) ? $tags[3] : t('next ›')), 'element' => $element, 'interval' => 1, 'parameters' => $parameters));
  $li_last = theme('pager_last', array('text' => (isset($tags[4]) ? $tags[4] : t('last »')), 'element' => $element, 'parameters' => $parameters));

  if ($pager_total[$element] > 1) {
    if ($li_first) {
      $items[] = array(
        'class' => array('pager-first'),
        'data' => $li_first,
      );
    }
    if ($li_previous) {
      $items[] = array(
        'class' => array('pager-previous'),
        'data' => $li_previous,
      );
    }

    // When there is more than one page, create the pager list.
    if ($i != $pager_max) {
      if ($i > 1) {
        $items[] = array(
          'class' => array('pager-ellipsis'),
          'data' => '…',
        );
      }
      // Now generate the actual pager piece.
      for (; $i <= $pager_last && $i <= $pager_max; $i++) {
        if ($i < $pager_current) {
          $items[] = array(
            'class' => array('pager-item'),
            'data' => theme('pager_previous', array('text' => $i, 'element' => $element, 'interval' => ($pager_current - $i), 'parameters' => $parameters)),
          );
        }
        if ($i == $pager_current) {
          $items[] = array(
            'class' => array('pager-current'),
            'data' => $i,
          );
        }
        if ($i > $pager_current) {
          $items[] = array(
            'class' => array('pager-item'),
            'data' => theme('pager_next', array('text' => $i, 'element' => $element, 'interval' => ($i - $pager_current), 'parameters' => $parameters)),
          );
        }
      }
      if ($i < $pager_max) {
        $items[] = array(
          'class' => array('pager-ellipsis'),
          'data' => '…',
        );
      }
    }
    // End generation.
    if ($li_next) {
      $items[] = array(
        'class' => array('pager-next'),
        'data' => $li_next,
      );
    }
    if ($li_last) {
      $items[] = array(
        'class' => array('pager-last'),
        'data' => $li_last,
      );
    }
    /* return '<h2 class="element-invisible">' . t('Pages') … */
    return '<h4 class="pager-title">' . t('More Crafts') . '</h4>' . theme('item_list', array(
      'items' => $items,
      'attributes' => array('class' => array('pager')),
    ));
  }
}