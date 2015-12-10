<?php

/*
 * Main template file for laliste theme
 *
**/

/*
 * Removing unecassry css files bloating our site
 **/
function laliste_css_alter(&$css) {
    unset($css[drupal_get_path('module','system').'/system.theme.css']);
    unset($css[drupal_get_path('module','system').'/system.base.css']);
    unset($css[drupal_get_path('module','system').'/system.menus.css']);
    unset($css[drupal_get_path('module','system').'/system.messages.css']);
    unset($css[drupal_get_path('module','system').'/system.admin.css']);
    unset($css[drupal_get_path('module','comment').'/comment.css']);
    unset($css[drupal_get_path('module','field').'/theme/field.css']);
    unset($css[drupal_get_path('module','search').'/search.css']);
    unset($css[drupal_get_path('module','user').'/user.css']);
    unset($css[drupal_get_path('module','node').'/node.css']);
    unset($css[drupal_get_path('module','views').'/css/views.css']);
    unset($css[drupal_get_path('module','ctools').'/css/ctools.css']);
    unset($css[drupal_get_path('module','addressfield').'/addressfield.css']);
}

/*
 * from https://www.drupal.org/node/1167712#comment-5080586
 */
function laliste_theme(&$existing, $type, $theme, $path) {
   $hooks['user_login_block'] = array(
     'template' => 'templates/user-login-block',
     'render element' => 'form',
   );
   return $hooks;
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
}

/**
* hook_form_FORM_ID_alter
*/
function laliste_form_search_block_form_alter(&$form, &$form_state) {
  $form['search_block_form']['#title'] = ''; // Change the text on the label element
  $form['search_block_form']['#title_display'] = 'invisible'; // Toggle label visibilty
  $form['search_block_form']['#size'] = 40;  // define size of the textfield
   // Set a default value for the textfield
  $form['actions']['submit']['#value'] = ''; // Change the text on the submit button
  // Prevent user from searching the default text
  $form['#attributes']['onsubmit'] = "if(this.search_block_form.value=='Search'){ alert('Please enter a search'); return false; }";
  // we customize the search appearance on the homepage
  if(drupal_is_front_page()) {
    $form['actions']['submit']['#attributes']['class'][] = 'fpage-search-icon';
    $form['search_block_form']['#attributes']['class'][] = 'fpage-search-box';
    // Add extra attributes to the text box
    $form['search_block_form']['#default_value'] = t('Search a restaurant, a city, a country...');
    $form['search_block_form']['#attributes']['onblur'] = "if (this.value == '') {this.value = '".t('Search a restaurant, a city, a country...')."';}";
    $form['search_block_form']['#attributes']['onfocus'] = "if (this.value == '".t('Search a restaurant, a city, a country...')."') {this.value = '';}";
      // Alternative (HTML5) placeholder attribute instead of using the javascript
    $form['search_block_form']['#attributes']['placeholder'] = t('Search a restaurant, a city, a country...');
  }
  else {
    $form['actions']['submit']['#attributes']['class'][] = 'other-page-search-icon';
    $form['search_block_form']['#attributes']['class'][] = 'other-page-search-box';
    // Add extra attributes to the text box
    $form['search_block_form']['#default_value'] = t('Search');
    $form['search_block_form']['#attributes']['onblur'] = "if (this.value == '') {this.value = '".t('Search')."';}";
    $form['search_block_form']['#attributes']['onfocus'] = "if (this.value == '".t('Search')."') {this.value = '';}";
      // Alternative (HTML5) placeholder attribute instead of using the javascript
  $form['search_block_form']['#attributes']['placeholder'] = t('Search');
  }
}

function laliste_preprocess_page(&$vars) {
  if (drupal_is_front_page()) {
    unset($vars['page']['content']['system_main']['default_message']); // removes "No front page content has been created yet."
    drupal_set_title(''); //removes welcome message (page title)
  }
}

function laliste_preprocess_node(&$variables) {
  if($variables['type'] == 'restaurant') {
    // getting restaurant rank & score
     $ranking = db_query("
      SELECT rank, ROUND(score_laliste,2) as score_laliste FROM {restaurant_stats}
      WHERE restaurant_id = ".$variables['nid'])->fetchAssoc();
     if(isset($ranking['rank'])) {
       if($bool = ( !is_int($ranking['rank']) ? (ctype_digit($ranking['rank'])) : true )) {
        $variables['rank'] = t(ordinal($ranking['rank']));
       }
       $variables['score'] = $ranking['score_laliste'] . '<sup>' . t('SCORE') . '/100' . '</sup>';
    }
    // Addresses
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
      include_once DRUPAL_ROOT . '/includes/locale.inc';
      $country_code = $variables['field_address'][0]['country'];
      $countries = country_get_list();
      $variables['country_name'] = $countries[$country_code];
      $variables['country_icon'] = theme('countryicons_icon', array('code' =>  $country_code, 'iconset' =>  'gosquared_flat_large'));
    }
    // full address
    $variables['address_full'] = (isset($variables['address1']) ? $variables['address1'] : null);
    $variables['address_full'] .= (isset($variables['address2']) ? ', '.$variables['address2'] : null);
    $variables['address_full'] .= (isset($variables['postal_code']) ? ', '.$variables['postal_code'] : null);
    $variables['address_full'] .= (isset($variables['city']) ? ', '.$variables['city'] : null);
    // Other infos
    if(!empty($variables['field_cooking_type'][0]['url'])) {
      $variables['cooking_type'] = $variables['field_cooking_type'][0]['value'];
    }
    if(!empty($variables['field_website'][0]['url'])) {
      $variables['website'] = $variables['field_website'][0]['url'];
    }
    if(!empty($variables['field_phone'][0]['value'])) {
      $variables['phone'] = $variables['field_phone'][0]['value'];
    }
    // extracting tags
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
    foreach ($links as $tid => $link) {
      $variables['guides'][$terms[$tid]->name] = $link;
    }
  }
  elseif($variables['type'] == 'liste') {
    // let's get the name of the Liste author via a fast SQL all.
    if(!empty($variables['field_liste_author'][0]['target_id'])) {
      $variables['liste_author'] = db_query("SELECT name FROM {users} WHERE uid = :uid",
        array(":uid"=>$variables['field_liste_author'][0]['target_id']))->fetchField();
    }
  }
}

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
