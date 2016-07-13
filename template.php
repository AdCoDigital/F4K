<?php



function fun4kids_preprocess_views_view(&$vars) {
  global $base_path;

  $view = $vars['view'];

  $vars['rows']       = (!empty($view->result) || $view->style_plugin->even_empty()) ? $view->style_plugin->render($view->result) : '';

  $vars['css_name']   = drupal_clean_css_identifier($view->name);
  $vars['name']       = $view->name;
  $vars['display_id'] = $view->current_display;

  // Basic classes
  $vars['css_class'] = '';

  $vars['classes_array'] = array();
  $vars['classes_array'][] = 'view';
  $vars['classes_array'][] = 'view-' . drupal_clean_css_identifier($vars['name']);
  $vars['classes_array'][] = 'view-id-' . $vars['name'];
  $vars['classes_array'][] = 'view-display-id-' . $vars['display_id'];

  $css_class = $view->display_handler->get_option('css_class');
  if (!empty($css_class)) {
    $vars['css_class'] = preg_replace('/[^a-zA-Z0-9- ]/', '-', $css_class);
    $vars['classes_array'][] = $vars['css_class'];
  }

  $empty = empty($vars['rows']);

  $vars['header'] = $view->display_handler->render_area('header', $empty);
  $vars['footer'] = $view->display_handler->render_area('footer', $empty);
  if ($empty) {
    $vars['empty'] = $view->display_handler->render_area('empty', $empty);
  }

  $vars['exposed']    = !empty($view->exposed_widgets) ? $view->exposed_widgets : '';
  $vars['more']       = $view->display_handler->render_more_link();
  $vars['feed_icon']  = !empty($view->feed_icon) ? $view->feed_icon : '';

  $vars['pager']      = '';

  // @todo: Figure out whether this belongs into views_ui_preprocess_views_view.
  // Render title for the admin preview.
  $vars['title'] = !empty($view->views_ui_context) ? filter_xss_admin($view->get_title()) : '';

  if ($view->display_handler->render_pager()) {
    $exposed_input = isset($view->exposed_raw_input) ? $view->exposed_raw_input : NULL;
    $vars['pager']      = $view->query->render_pager($exposed_input);
  }

  $vars['attachment_before'] = !empty($view->attachment_before) ? $view->attachment_before : '';
  $vars['attachment_after'] = !empty($view->attachment_after) ? $view->attachment_after : '';

  // Add contextual links to the view. We need to attach them to the dummy
  // $view_array variable, since contextual_preprocess() requires that they be
  // attached to an array (not an object) in order to process them. For our
  // purposes, it doesn't matter what we attach them to, since once they are
  // processed by contextual_preprocess() they will appear in the $title_suffix
  // variable (which we will then render in views-view.tpl.php).
  views_add_contextual_links($vars['view_array'], 'view', $view, $view->current_display);

  // Attachments are always updated with the outer view, never by themselves,
  // so they do not have dom ids.
  if (empty($view->is_attachment)) {
    // Our JavaScript needs to have some means to find the HTML belonging to this
    // view.
    //
    // It is true that the DIV wrapper has classes denoting the name of the view
    // and its display ID, but this is not enough to unequivocally match a view
    // with its HTML, because one view may appear several times on the page. So
    // we set up a hash with the current time, $dom_id, to issue a "unique" identifier for
    // each view. This identifier is written to both Drupal.settings and the DIV
    // wrapper.
    $vars['dom_id'] = $view->dom_id;
    $vars['classes_array'][] = 'view-dom-id-' . $vars['dom_id'];
  }

  // If using AJAX, send identifying data about this view.
  if ($view->use_ajax && empty($view->is_attachment) && empty($view->live_preview)) {

    /*  It seems to me that the 'view_args' => check_plain(implode('/', $view->args)) line messes up the views when using taxonomy terms
        with special characters as arguments—i.e. 'Camps & Clubs' will be passed as 'Camps &amp; Clubs', thus no business listings will show, 
        so don't run the argument through check_plain filter if it matches a taxonmy term */

    if ( (taxonomy_get_term_by_name($view->args[0], 'business_categories_fun')) ||
         (taxonomy_get_term_by_name($view->args[0], 'business_categories_services')) ) {
      $new_view_args = $view->args[0];
      $new_view_args2 = check_plain(implode('/', array_slice($view->args, 1)));

      $new_view_args .= ($new_view_args2 ? '/' . $new_view_args2 : '');
    } else {
      $new_view_args = check_plain(implode('/', $view->args));
    }


    $settings = array(
      'views' => array(
        'ajax_path' => url('views/ajax'),
        'ajaxViews' => array(
          'views_dom_id:' . $vars['dom_id'] => array(
            'view_name' => $view->name,
            'view_display_id' => $view->current_display,
            'view_args' => $new_view_args,
            'view_path' => check_plain($_GET['q']),
            // Pass through URL to ensure we get e.g. language prefixes.
//            'view_base_path' => isset($view->display['page']) ? substr(url($view->display['page']->display_options['path']), strlen($base_path)) : '',
            'view_base_path' => $view->get_path(),
            'view_dom_id' => $vars['dom_id'],
            // To fit multiple views on a page, the programmer may have
            // overridden the display's pager_element.
            'pager_element' => isset($view->query->pager) ? $view->query->pager->get_pager_id() : 0,
          ),
        ),
      ),
    );

    drupal_add_js($settings, 'setting');
    views_add_js('ajax_view');
  }

  // If form fields were found in the View, reformat the View output as a form.
  if (views_view_has_form_elements($view)) {
    $output = !empty($vars['rows']) ? $vars['rows'] : $vars['empty'];
    $form = drupal_get_form(views_form_id($view), $view, $output);
    // The form is requesting that all non-essential views elements be hidden,
    // usually because the rendered step is not a view result.
    if ($form['show_view_elements']['#value'] == FALSE) {
      $vars['header'] = '';
      $vars['exposed'] = '';
      $vars['pager'] = '';
      $vars['footer'] = '';
      $vars['more'] = '';
      $vars['feed_icon'] = '';
    }
    $vars['rows'] = $form;
  }
}







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


/* function fun4kids_preprocess_page(&$variables) {

  //get the newsletter subscibe block based on what region you have selected

  if(global_filter_get_session_value('field_region')=='All'){
    $variables['newsletter_subscribe_block'] = module_invoke('simplenews', 'block_view','37');
  } else if(global_filter_get_session_value('field_region')=='Brainerd Lakes Area'){
    $variables['newsletter_subscribe_block'] = module_invoke('simplenews', 'block_view','53');
  } else if(global_filter_get_session_value('field_region')=='East Central MN'){
    $variables['newsletter_subscribe_block'] = module_invoke('simplenews', 'block_view','681');
  } else if(global_filter_get_session_value('field_region')=='Fargo / Moorehead'){
    $variables['newsletter_subscribe_block'] = module_invoke('simplenews', 'block_view','54');
  } else if(global_filter_get_session_value('field_region')=='Minneapolis'){
    $variables['newsletter_subscribe_block'] = module_invoke('simplenews', 'block_view','55');
  } else if(global_filter_get_session_value('field_region')=='NE Metro'){
    $variables['newsletter_subscribe_block'] = module_invoke('simplenews', 'block_view','56');
  } else if(global_filter_get_session_value('field_region')=='Northeast MN'){
    $variables['newsletter_subscribe_block'] = module_invoke('simplenews', 'block_view','57');
  } else if(global_filter_get_session_value('field_region')=='NW Metro'){
    $variables['newsletter_subscribe_block'] = module_invoke('simplenews', 'block_view','58');
  } else if(global_filter_get_session_value('field_region')=='NW Suburban'){
    $variables['newsletter_subscribe_block'] = module_invoke('simplenews', 'block_view','59');
  } else if(global_filter_get_session_value('field_region')=='SE Metro'){
    $variables['newsletter_subscribe_block'] = module_invoke('simplenews', 'block_view','60');
  } else if(global_filter_get_session_value('field_region')=='Southern MN'){
    $variables['newsletter_subscribe_block'] = module_invoke('simplenews', 'block_view','61');
  } else if(global_filter_get_session_value('field_region')=='St. Cloud Area'){
    $variables['newsletter_subscribe_block'] = module_invoke('simplenews', 'block_view','62');
  } else if(global_filter_get_session_value('field_region')=='St. Paul'){
    $variables['newsletter_subscribe_block'] = module_invoke('simplenews', 'block_view','63');
  } else if(global_filter_get_session_value('field_region')=='SW Metro'){
    $variables['newsletter_subscribe_block'] = module_invoke('simplenews', 'block_view','64');
  }
} */

function fun4kids_form_element_label($variables) {
  $element = $variables['element'];
  $extra = '';

  // This is also used in the installer, pre-database setup.
  $t = get_t();

  // If title and required marker are both empty, output no label.
  if ((!isset($element['#title']) || $element['#title'] === '') && empty($element['#required'])) {
    return '';
  }

  // If the element is required, a required marker is appended to the label.
  $required = !empty($element['#required']) ? theme('form_required_marker', array('element' => $element)) : '';

  $title = filter_xss_admin($element['#title']);

  $attributes = array();
  // Style the label as class option to display inline with the element.
  if ($element['#title_display'] == 'after') {
    $attributes['class'] = 'option';
  }
  // Show label only to screen readers to avoid disruption in visual flows.
  elseif ($element['#title_display'] == 'invisible') {
    $attributes['class'] = 'element-invisible';
  }

  if (!empty($element['#id'])) {
    $attributes['for'] = $element['#id'];
  }

  if ($element['#id'] == 'edit-field-region') {
    $extra = "<a class='find-region-link' href='/find-your-region'><img src='images/info-find-your-region.png' /></a>";
  }

  // The leading whitespace helps visually separate fields from inline labels.
  return ' <label' . drupal_attributes($attributes) . '>' . $t('!title !required', array('!title' => $title, '!required' => $required)) . "</label>" . $extra . "\n";
}



// Remove height and width inline attributes from images
function fun4kids_preprocess_image(&$variables) {
  foreach (array('width', 'height') as $key) {
    unset($variables[$key]);
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
      $variables['flag_marker'] = "<img class='toybox-marker rainy-day-view--link-marker link-marker' src='images/toy_box_add_lg.png' />";
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
 * Override theme_date_display_range so dates are separated by ' - ', not 'to'
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
  $output = '<span class="date-range-wrapper">' . $start_date . '<span class="date-range-separator"><br />- </span>' . $end_date . '</span>';
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
        'value'      => '<img src="images/full_calendar_left.png" />',
        'url'        => $vars['prev_url'],
        'options'    => $vars['prev_options'],
      );
    $vars['nav_button_2'] = array(
        'class-name' => 'date-prev',
        'value'      => '<img src="images/full_calendar_right.png" />',
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
  Calendar | Fun 4 Kids), but works when I put the preprocess function (unedited) here 
function fun4kids_preprocess_html(&$variables)*/
{
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

  $previous_button = array(
    'path' => 'images/full_calendar_left.png', 
    'alt' => 'Previous',
    'title' => 'Previous',
  );

  $next_button = array(
    'path' => 'images/full_calendar_right.png', 
    'alt' => 'Previous',
    'title' => 'Previous',
  );


  $li_first = theme('pager_first', array('text' => (isset($tags[0]) ? $tags[0] : t('« first')), 'element' => $element, 'parameters' => $parameters));
  $li_previous = theme('pager_previous', array('text' => theme('image', $previous_button), 'element' => $element, 'interval' => 1, 'parameters' => $parameters));
  $li_next = theme('pager_next', array('text' => theme('image', $next_button), 'element' => $element, 'interval' => 1, 'parameters' => $parameters));
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


function fun4kids_pager_link($variables) {
  $text = $variables['text'];
  $page_new = $variables['page_new'];
  $element = $variables['element'];
  $parameters = $variables['parameters'];
  $attributes = $variables['attributes'];

  $page = isset($_GET['page']) ? $_GET['page'] : '';
  if ($new_page = implode(',', pager_load_array($page_new[$element], $element, explode(',', $page)))) {
    $parameters['page'] = $new_page;
  }

  $query = array();
  if (count($parameters)) {
    $query = drupal_get_query_parameters($parameters, array());
  }
  if ($query_pager = pager_get_query_parameters()) {
    $query = array_merge($query, $query_pager);
  }

  // Set each pager link title
  if (!isset($attributes['title'])) {
    static $titles = NULL;
    if (!isset($titles)) {
      $titles = array(
        t('« first') => t('Go to first page'),
        t('‹ previous') => t('Go to previous page'),
        t('next ›') => t('Go to next page'),
        t('last »') => t('Go to last page'),
      );
    }
    if (isset($titles[$text])) {
      $attributes['title'] = $titles[$text];
    }
    elseif (is_numeric($text)) {
      $attributes['title'] = t('Go to page @number', array('@number' => $text));
    }
  }

  // @todo l() cannot be used here, since it adds an 'active' class based on the
  //   path only (which is always the current path for pager links). Apparently,
  //   none of the pager links is active at any time - but it should still be
  //   possible to use l() here.
  // @see http://drupal.org/node/1410574
  $attributes['href'] = url($_GET['q'], array('query' => $query));
  return '<a' . drupal_attributes($attributes) . '>' . $text . '</a>';
  // return '<a' . drupal_attributes($attributes) . '>' . check_plain($text) . '</a>';
}


// Used in conjunction with https://gist.github.com/1417914

/**
* Implements hook_preprocess_html().
*/
function THEMENAME_preprocess_html(&$vars) {
// Move JS files "$scripts" to page bottom for perfs/logic.
// Add JS files that *needs* to be loaded in the head in a new "$head_scripts" scope.
// For instance the Modernizr lib.
$path = drupal_get_path('theme', 'THEMENAME');
drupal_add_js($path . '/js/modernizr.min.js', array('scope' => 'head_scripts', 'weight' => -1, 'preprocess' => FALSE));
}

/**
* Implements hook_process_html().
*/
function THEMENAME_process_html(&$vars) {
$vars['head_scripts'] = drupal_get_js('head_scripts');
}


//Remove Query Strings from CSS filenames (CacheBuster)
function fun4kids_process_html(&$variables) {
$variables['styles'] = preg_replace('/\.css\?[^"]+/','.css', $variables['styles']);
}


