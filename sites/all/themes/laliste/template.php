<?php

/*
 * Main template file for laliste theme
 *
**/

/*
 * Removing unecassry css files bloating our site
 **/
function laliste_css_alter(&$css) {
/*    unset($css[drupal_get_path('module','system').'/system.theme.css']);*/
/*    unset($css[drupal_get_path('module','system').'/system.base.css']);*/
/*    unset($css[drupal_get_path('module','system').'/system.menus.css']);
    unset($css[drupal_get_path('module','system').'/system.messages.css']);
    unset($css[drupal_get_path('module','system').'/system.admin.css']);
    unset($css[drupal_get_path('module','comment').'/comment.css']);
    unset($css[drupal_get_path('module','field').'/theme/field.css']);
    unset($css[drupal_get_path('module','search').'/search.css']);
 /*   unset($css[drupal_get_path('module','user').'/user.css']);*/
    unset($css[drupal_get_path('module','node').'/node.css']);
    unset($css[drupal_get_path('module','views').'/css/views.css']);
    unset($css[drupal_get_path('module','ctools').'/css/ctools.css']);
/*    unset($css[drupal_get_path('module','addressfield').'/addressfield.css']);*/
}

/**
 * Implements theme_html_head_alter().
 * Removes the Generator tag from the head for Drupal 7
 */
function laliste_html_head_alter(&$head_elements) {
  unset($head_elements['system_meta_generator']);
}

/**
 * Prioritizes js scripts
 * Implements hook_js_alter()
 */
function laliste_js_alter(&$javascript) {
  // Collect the scripts we want in to remain in the header scope.
  $header_scripts = array(
    //'sites/all/libraries/modernizr/modernizr.min.js', example of putting js in header
  );

  // Change the default scope of all other scripts to footer.
  // We assume if the script is scoped to header it was done so by default.
  foreach ($javascript as $key => &$script) {
    if ($script['scope'] == 'header' && !in_array($script['data'], $header_scripts)) {
      $script['scope'] = 'footer';
    }
  }
}

/*
 * from https://www.drupal.org/node/1167712#comment-5080586
 */
function laliste_theme(&$existing, $type, $theme, $path) {
  // user login block
  $hooks['user_login_block'] = array(
    'template' => 'templates/user-login-block',
    'render element' => 'form',
  );
  // registration form page
  //
  $hooks['laliste_registration_form'] = array(
    'template' => 'templates/laliste-registration-form',
    'render element' => 'form',
  );
  // user login form page
  $hooks['laliste_login_form'] = array(
    'template' => 'templates/laliste-login-form',
    'render element' => 'form',
  );
  // user edit page
  $hooks['user_profile_form'] = array(
    'template' => 'templates/laliste-user-profile-edit',
    'render element' => 'form',
  );
  return $hooks;
}

/*
 * HOOK_form_alter().
 */
function laliste_form_alter(&$form, &$form_state, $form_id)
{
  if ('user_register_form' == $form_id) {
    // we set the user's language
    global $language_content;
    $language_current = $language_content->language;
    $form['locale']['#value'] = $language_current;
    $form['#theme'] = 'laliste_registration_form'; // @todo get the password field to show up!
  }
  elseif('user_login' == $form_id) {
    $form['#theme'] = 'laliste_login_form';
  }
}

/*
 * Theming the uer login block
 * from https://www.drupal.org/node/1167712#comment-9654249
 */
function laliste_preprocess_user_login_block(&$vars) {
  $vars['name'] = render($vars['form']['name']);
  $vars['pass'] = render($vars['form']['pass']);
  $vars['links'] = render($vars['form']['links']);
  $vars['submit'] = render($vars['form']['actions']['submit']);
  $vars['rendered'] = drupal_render_children($vars['form']);
}

function laliste_form_user_login_block_alter(&$form, &$form_state, $form_id) {
  $items = array();
  if (variable_get('user_register', USER_REGISTER_VISITORS_ADMINISTRATIVE_APPROVAL)) {
    $items[] = l(t('Create Account'), 'user/register', array('attributes' => array('title' => t('Create an account.'))));
  }
  $items[] = l(t('Request Password'), 'user/password', array('attributes' => array('title' => t('Request password reset.'))));
  $form['links'] = array('#markup' => theme('item_list', array('items' => $items)));
  return $form;
}

/*
 * Remove block title
 */
function laliste_preprocess_block(&$vars) {
  if ($vars['block']->delta == 'login') {
    $vars['theme_hook_suggestions'][] = 'block__no_title';
  }
  if ($vars['elements']['#block']->delta == 'language') {
    $vars['elements']['#block']->subject = NULL;
  }
  if ($vars['block']->module == 'system'
   && $vars['block']->delta == 'user-menu') {
      $vars['block']->subject = null;
  }
}

/**
* hook_form_FORM_ID_alter
*/
function laliste_form_views_exposed_form_alter(&$form, &$form_state) {
  $form['search_api_views_fulltext']['#size'] = 40;  // define size of the textfield
   // Set a default value for the textfield
  $form['submit']['#value'] = ''; // Change the text on the submit button
  // we customize the search appearance on the homepage
  if(drupal_is_front_page()) {
    $form['submit']['#attributes']['class'][] = 'fpage-search-icon';
    $form['search_api_views_fulltext']['#attributes']['class'][] = 'fpage-search-box';
    // Add extra attributes to the text box
    $form['search_api_views_fulltext']['#default_value'] = t('Search a restaurant, a city, a country...');
    $form['search_api_views_fulltext']['#attributes']['onblur'] = "if (this.value == '') {this.value = '".t('Search a restaurant, a city, a country...')."';}";
    $form['search_api_views_fulltext']['#attributes']['onfocus'] = "if (this.value == '".t('Search a restaurant, a city, a country...')."') {this.value = '';}";
      // Alternative (HTML5) placeholder attribute instead of using the javascript
    $form['search_api_views_fulltext']['#attributes']['placeholder'] = t('Search a restaurant, a city, a country...');
    // Prevent user from searching the default text
    $form['#attributes']['onsubmit'] = "if(this.search_api_views_fulltext.value=='Search a restaurant, a city, a country...'){ alert('Please enter a search'); return false; }";
  }
  else {
    $form['submit']['#attributes']['class'][] = 'other-page-search-icon';
    $form['search_api_views_fulltext']['#attributes']['class'][] = 'other-page-search-box';
    // Add extra attributes to the text box
    $form['search_api_views_fulltext']['#default_value'] = t('Search');
    $form['search_api_views_fulltext']['#attributes']['onblur'] = "if (this.value == '') {this.value = '".t('Search')."';}";
    //$form['search_api_views_fulltext']['#attributes']['onfocus'] = "if (this.value == '".t('Search')."') {this.value = '';}";
    $form['search_api_views_fulltext']['#attributes']['onfocus'] = "this.value = '';";
    // Alternative (HTML5) placeholder attribute instead of using the javascript
    $form['search_api_views_fulltext']['#attributes']['placeholder'] = t('Search');
    // Prevent user from searching the default text
    $form['#attributes']['onsubmit'] = "if(this.search_api_views_fulltext.value=='Search'){ alert('Please enter a search'); return false; }";
  }
}

/**
 * Customize panel output (removing panel separators)
 */
function laliste_panels_default_style_render_region($vars) {
  $output = '';
  $output .= implode('', $vars['panes']);
  return $output;
}

function laliste_preprocess_page(&$vars) {
  // front page
  if (drupal_is_front_page()) {
    unset($vars['page']['content']['system_main']['default_message']); // removes "No front page content has been created yet."
    drupal_set_title(''); //removes welcome message (page title)
  }
  // user page
  if($panel_page = page_manager_get_current_page()) {
    $suggestions[] = 'page-panel';
    $suggestions[] = 'page-' . $panel_page['name'];
    $variables['theme_hook_suggestions'] = array_merge($vars['theme_hook_suggestions'], $suggestions);
  }
  elseif($vars['theme_hook_suggestions'][0] == 'page__user') {
    unset($vars['theme_hook_suggestions'][0]);
  }
}

function laliste_preprocess_node(&$variables) {
  if($variables['type'] == 'restaurant') {
    global $language_content;
    $language_current = $language_content->language;
    // getting restaurant rank & score
     $ranking = db_query("
      SELECT rank, ROUND(score_laliste,2) as score_laliste FROM {restaurant_stats}
      WHERE restaurant_id = ".$variables['nid'])->fetchAssoc();
     if(isset($ranking['rank'])) {
       if($bool = ( !is_int($ranking['rank']) ? (ctype_digit($ranking['rank'])) : true )) {
        $variables['rank'] = t(ordinal($ranking['rank']));
       }
       $variables['score'] = $ranking['score_laliste'] . '<sup>' . t('SCORE') . '/100' . '</sup>';
       // let's populate the previous/next restaurant arrows if current got rank and in the LISTE
       $prev_next = db_query("
        SELECT restaurant_id as rid, rank from {restaurant_stats}
        WHERE rank = " . ($ranking['rank']-1) . " OR rank = " . ($ranking['rank']+1) . " ORDER BY rank ASC")->fetchAll();

       // first condition
       if((isset($prev_next[0]->rid)) && (isset($prev_next[1]->rank)) && (!empty($prev_next[0]->rid))) {
         $variables['prev_link'] = $GLOBALS['base_url'].'/node/'.$prev_next[0]->rid;
       }
       // special case 1
       elseif($ranking['rank'] == 1) {
          $variables['prev_link'] = null;
          $variables['next_link'] = $GLOBALS['base_url'].'/node/'.$prev_next[0]->rid;
       }
       // special case 2
       elseif($ranking['rank'] == 1000) {
          $variables['prev_link'] = $GLOBALS['base_url'].'/node/'.$prev_next[0]->rid;
          $variables['next_link'] = null;
       }
       // second condition
       if((isset($prev_next[0]->rid)) && (isset($prev_next[1]->rid)) && (!empty($prev_next[1]->rid))) {
         $variables['next_link'] = $GLOBALS['base_url'].'/node/'.$prev_next[1]->rid;
       }

    }
    // Addresses for search
    if(!empty($variables['field_address'][$language_current][0]['thoroughfare'])) {
      $variables['address1'] = $variables['field_address'][$language_current][0]['thoroughfare'];
    }
    if(!empty($variables['field_address'][$language_current][0]['premise'])) {
      $variables['address2'] = $variables['field_address'][$language_current][0]['premise'];
    }
    if(!empty($variables['field_address'][$language_current][0]['postal_code'])) {
      $variables['postal_code'] = $variables['field_address'][$language_current][0]['postal_code'];
    }
    if(!empty($variables['field_address'][$language_current][0]['locality'])) {
      $variables['city'] = $variables['field_address'][$language_current][0]['locality'];
    }
    if(!empty($variables['field_address'][$language_current][0]['country'])) {
      $variables['country_icon'] = base_path() . path_to_theme() . '/img/flat-large-countries.png';
      $variables['country_code'] = strtolower($variables['field_address'][$language_current][0]['country']);
      // let's translate the 2 letter ISO code into the full country name
      include_once DRUPAL_ROOT . '/includes/locale.inc';
      $country_list = country_get_list();
      $variables['country'] = $country_list[strtoupper($variables['country_code'])];
      // we create the proper country view link according to current language
      if($language_current != 'en') {
        $variables['country_view_url'] = $GLOBALS['base_url'].'/'.$language_current.'/country/'.$variables['country_code'].'/laliste/view';
      }
      else {
        $variables['country_view_url'] = $GLOBALS['base_url'].'/country/'.$variables['country_code'].'/laliste/view';
      }
    }
    // Addresses for node view
    if(!empty($variables['field_address'][0]['thoroughfare'])) {
      $variables['address1'] = $variables['field_address'][0]['thoroughfare'];
    }
    if(!empty($variables['field_address'][0]['premise'])) {
      $variables['address2'] = $variables['field_address'][0]['premise'];
    }
    if(!empty($variables['field_address'][0]['postal_code'])) {
      $variables['postal_code'] = $variables['field_address'][0]['postal_code'];
    }
    if(!empty($variables['field_address'][0]['locality'])) {
      $variables['city'] = $variables['field_address'][0]['locality'];
    }
    if(!empty($variables['field_address'][0]['country'])) {
      $variables['country_icon'] = base_path() . path_to_theme() . '/img/flat-large-countries.png';
      $variables['country_code'] = strtolower($variables['field_address'][0]['country']);
      if($language_current != 'en') {
        $variables['country_view_url'] = $GLOBALS['base_url'].'/'.$language_current.'/country/'.$variables['country_code'].'/laliste/view';
      }
      else {
        $variables['country_view_url'] = $GLOBALS['base_url'].'/country/'.$variables['country_code'].'/laliste/view';
      }
    }
    // full address
    $variables['address_full'] = (isset($variables['address1']) ? $variables['address1'] : null);
    $variables['address_full'] .= (isset($variables['address2']) ? ', '.$variables['address2'] : null);
    $variables['address_full'] .= (isset($variables['postal_code']) ? ', '.$variables['postal_code'] : null);
    $variables['address_full'] .= (isset($variables['city']) ? ', '.$variables['city'] : null);
    $variables['address_full'] .= (isset($variables['country']) ? ' - '.$variables['country'] : null);
    // Other infos search
    if(!empty($variables['field_cooking_type'][$language_current][0]['url'])) {
      $variables['cooking_type'] = $variables['field_cooking_type'][$language_current][0]['value'];
    }
    if(!empty($variables['field_website'][$language_current][0]['url'])) {
      $variables['website'] = $variables['field_website'][$language_current][0]['url'];
    }
    if(!empty($variables['field_phone'][$language_current][0]['value'])) {
      $variables['phone'] = $variables['field_phone'][$language_current][0]['value'];
    }
    // Other infos node view
    if(!empty($variables['field_cooking_type'][0]['url'])) {
      $variables['cooking_type'] = $variables['field_cooking_type'][0]['value'];
    }
    if(!empty($variables['field_website'][0]['url'])) {
      $url = $variables['field_website'][0]['url'];
      $scheme = parse_url($url, PHP_URL_SCHEME);
      //if(isset($scheme)) @TODO
      $host = parse_url($url, PHP_URL_HOST);
      $variables['website_url'] = $url;
      $variables['website_scheme'] = $scheme;
      $variables['website_host'] = $host;
    }
    if(!empty($variables['field_phone'][0]['value'])) {
      $variables['phone'] = $variables['field_phone'][0]['value'];
    }
    // extracting tags for node
    if(!empty($variables['field_restaurant_tags'][0])) {
      foreach($variables['field_restaurant_tags'] as $key => $tag) {
        $tids[] = $tag['tid'];
      }
      $terms = taxonomy_term_load_multiple($tids);
      foreach ($terms as $term) {
        $variables['tags'][] = $term->name;
      }
    }
    // extracting food guides
    // let's get all the guide_ids and url for this restaurant
    $links = db_query("
      SELECT guide_id, link FROM ranking r LEFT JOIN restaurantguideranking rgr
      ON rgr.ranking_id=r.ranking_id WHERE restaurant_id=".$variables['nid']."
      ORDER BY guide_id")->fetchAllKeyed();
    // we now get the taxonomy term names
    $terms = taxonomy_term_load_multiple(array_keys($links));
    // we load everything together : the terms and the url in one variable
    $array  = array( 'Lo Mejor de la Gastronomia', 'Identita Golose', 'Touring', 'Guide bleu' );
    foreach ($links as $tid => $link) {
      if(!in_array($terms[$tid]->name, $array)) {
        $variables['guides'][$terms[$tid]->name] = $link;
      }
    }
    // if not geocoder location exists, we put a default map instead
  }
  elseif($variables['type'] == 'liste') {
    //var_dump($variables);
    // let's get the name of the Liste author via a fast SQL call.
    if(!empty($variables['nid'])) {
      $variables['liste_author'] = db_query("SELECT name FROM {users} LEFT JOIN node ON users.uid=node.uid WHERE node.nid = :nid",
        array(":nid"=>$variables['nid']))->fetchField();
    }
  }
}

/*
 * Remove the worldwide map from the LA LISTE 1000 page view.
 */
function laliste_preprocess_views_view(&$vars) {
  // we are hidding the world view map header when showing LA LISTE 1000 page(s) view
  if (($vars['view']->name == "laliste_rr_restaurants_country_winners_view")
    && (strpos(current_path(),'country/world') !== FALSE)) {
    $vars['header'] = array();
  }
}

/*
 * Add the th / ème to the restaurant rank
 */
function laliste_preprocess_views_view_field(&$vars){
     $view = $vars['view'];
     $output = $vars['output'];
     $bool = ( !is_int($output) ? (ctype_digit($output)) : true );
    if((($view->name == 'laliste_rr_restaurants_country_winners_view') ||
        ($view->name == 'laliste_liste_restaurants_view')) && $bool) {
      $vars['output'] = t(ordinal($output));
    }
}

function ordinal($number) {
    $ends = array(t('th'),t('st'),t('nd'),t('rd'),t('th'),t('th'),t('th'),t('th'),t('th'),t('th'));
    if ((($number % 100) >= 11) && (($number%100) <= 13))
        return $number. '<sup>' . t('th') . '</sup>';
    else
        return $number. '<sup>' . $ends[$number % 10] . '</sup>';
}

function laliste_pager($variables) {
  // only customize pager for country page view
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
          // Only for LA LISTE custom pager
          if(strpos(current_path(),'country/world') !== FALSE) {
            // we customize the current pager
            switch($i) {
              case '1': $j = '1-250'; break;
              case '2': $j = '251-500'; break;
              case '3': $j = '501-750'; break;
              case '4': $j = '750-1000'; break;
            }
            $items[] = array(
              'class' => array('pager-current'),
              'data' => $j,
            );
          }
          // normal views pager
          else {
            $items[] = array(
            'class' => array('pager-current'),
            'data' => $i,
            );
          }
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
    return '<h2 class="element-invisible">' . t('Pages') . '</h2>' . theme('item_list', array(
      'items' => $items,
      'attributes' => array('class' => array('pager')),
    ));

    if(strpos(current_path(),'country/world') !== FALSE) {
      // @todo l() cannot be used here, since it adds an 'active' class based on the
      //   path only (which is always the current path for pager links). Apparently,
      //   none of the pager links is active at any time - but it should still be
      //   possible to use l() here.
      // @see http://drupal.org/node/1410574
      $attributes['href'] = url($_GET['q'], array('query' => $query));
      return '<a' . drupal_attributes($attributes) . '>' . check_plain($text) . '</a>';
    }
  }
}


function laliste_pager_link($variables) {
  // only customize pager for country page view
  if(strpos(current_path(),'country/world') !== FALSE) {
    $text = $variables['text'];
    // we customize the view pager output
    switch ($text) {
      case '1': $text = '1-250'; break;
      case '2': $text = '251-500' ;break;
      case '3': $text = '501-750' ;break;
      case '4': $text = '751-1000' ;break;
    }
    $variables['text'] = $text;
  }
  return theme_pager_link($variables);
}

/**
 * Implements hook_views_pre_view().
 */
function laliste_views_pre_render($view) {
  if ($view->name == 'laliste_rr_restaurants_country_winners_view') {
    if ($view->current_display == 'page_1') {
      if(isset($view->args[0])) {
        if($view->args[0] != 'world') {
          include_once DRUPAL_ROOT . '/includes/locale.inc';
          $country_names = country_get_list();
          $country = $country_names[strtoupper($view->args[0])];
          $view->set_title(t('Restaurants') . ' - ' . t($country));
        }
        else {
          $view->set_title(t('Restaurants') . ' - ' . t('Worldwide'));
        }
      }
    }
  }
}

/**
 * Language switch menu block
 * 2 letters language instead of full language name.
 * Implements hook_language_switch_links_alter().
 */
function laliste_language_switch_links_alter(array &$links, $type, $path) {
  foreach($links as $key => $link) {
    $links[$key]['title'] = strtoupper($link['language']->language);
  }
}

/*
 * Customize the Search Facet display.
 */
function laliste_facet_items_alter(&$build, &$settings) {
  if ($settings->facet == "field_address:country") {
    // let's translate the 2 letter ISO code into the full country name
    include_once DRUPAL_ROOT . '/includes/locale.inc';
    $country_list = country_get_list();
    foreach($build as $key => $item) {
      if(strlen($item["#markup"])==2) {
        $markup = strtoupper(trim($item["#markup"]));
        if(isset($country_list[$markup])) {
          $build[$key]["#markup"] = $country_list[$markup];
        }
        else {
          unset($build[$key]);
        }
      }
      else {
        unset($build[$key]);
      }
    }
  }
  /*
  elseif($settings->facet == "field_address:locality") {
    foreach($build as $key => $item) {
      //var_dump($item);
      //$build[$key]["#markup"] = drupal_strtoupper($item["#markup"]);
    }
  }*/
}
