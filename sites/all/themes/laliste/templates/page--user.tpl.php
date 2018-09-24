<?php

/**
 * @file
 * Default theme implementation to display a single Drupal page.
 *
 * The doctype, html, head and body tags are not in this template. Instead they
 * can be found in the html.tpl.php template in this directory.
 *
 * Available variables:
 *
 * General utility variables:
 * - $base_path: The base URL path of the Drupal installation. At the very
 *   least, this will always default to /.
 * - $directory: The directory the template is located in, e.g. modules/system
 *   or themes/bartik.
 * - $is_front: TRUE if the current page is the front page.
 * - $logged_in: TRUE if the user is registered and signed in.
 * - $is_admin: TRUE if the user has permission to access administration pages.
 *
 * Site identity:
 * - $front_page: The URL of the front page. Use this instead of $base_path,
 *   when linking to the front page. This includes the language domain or
 *   prefix.
 * - $logo: The path to the logo image, as defined in theme configuration.
 * - $site_name: The name of the site, empty when display has been disabled
 *   in theme settings.
 * - $site_slogan: The slogan of the site, empty when display has been disabled
 *   in theme settings.
 *
 * Navigation:
 * - $main_menu (array): An array containing the Main menu links for the
 *   site, if they have been configured.
 * - $secondary_menu (array): An array containing the Secondary menu links for
 *   the site, if they have been configured.
 * - $breadcrumb: The breadcrumb trail for the current page.
 *
 * Page content (in order of occurrence in the default page.tpl.php):
 * - $title_prefix (array): An array containing additional output populated by
 *   modules, intended to be displayed in front of the main title tag that
 *   appears in the template.
 * - $title: The page title, for use in the actual HTML content.
 * - $title_suffix (array): An array containing additional output populated by
 *   modules, intended to be displayed after the main title tag that appears in
 *   the template.
 * - $messages: HTML for status and error messages. Should be displayed
 *   prominently.
 * - $tabs (array): Tabs linking to any sub-pages beneath the current page
 *   (e.g., the view and edit tabs when displaying a node).
 * - $action_links (array): Actions local to the page, such as 'Add menu' on the
 *   menu administration interface.
 * - $feed_icons: A string of all feed icons for the current page.
 * - $node: The node object, if there is an automatically-loaded node
 *   associated with the page, and the node ID is the second argument
 *   in the page's path (e.g. node/12345 and node/12345/revisions, but not
 *   comment/reply/12345).
 *
 * Regions:
 * - $page['help']: Dynamic help text, mostly for admin pages.
 * - $page['highlighted']: Items for the highlighted content region.
 * - $page['content']: The main content of the current page.
 * - $page['sidebar_first']: Items for the first sidebar.
 * - $page['sidebar_second']: Items for the second sidebar.
 * - $page['header']: Items for the header region.
 * - $page['footer']: Items for the footer region.
 *
 * @see template_preprocess()
 * @see template_preprocess_page()
 * @see template_process()
 * @see html.tpl.php
 *
 * @ingroup themeable
 */
global $user;
?>
<header class="header-main header-user">
  <div class="header-branding">
    <a class="logo" href="<?php print url('<front>'); ?>"><img src="<?php print base_path() . path_to_theme() . '/img/logo_bheader.jpg'; ?>" alt="LA LISTE logo branding"><br/><span>OBJECTIVELY DELICIOUS &middot; DELICIOUSLY OBJECTIVE</span></a>
  </div>
  <div class="header-utils">
    <?php if (user_is_logged_in() && !isset($user->roles[6]) && ((isset($node) && $node->type =='restaurant'))): ?><div class="tabs"><?php print render($tabs); ?></div><?php endif; ?>
    <?php print render($page['header']); ?>
  </div>
</header>
<?php if (isset($language->language) && ($language->language == 'fr')): ?>
<nav class="nav-main">
  <div class="nav-container">
    <a class="nav-link" href="/fr/méthode"><?php print t('MÉTHODE'); ?></a>
    <a class="nav-link" href="/fr/équipe"><?php print t("L'ÉQUIPE"); ?></a>
    <a class="nav-link" href="/fr/palmarès"><?php print t('PALMARÈS'); ?></a>
    <a class="nav-link" href="/fr/sponsors"><?php print t('SPONSORS'); ?></a>
    <a class="nav-link" href="/fr/news"><?php print t('NEWS'); ?></a>
  </div>
</nav>
<?php else: ?>
  <nav class="nav-main">
  <div class="nav-container">
    <a class="nav-link" href="/about"><?php print t('METHOD'); ?></a>
    <a class="nav-link" href="/our-team"><?php print t('TEAM'); ?></a>
    <a class="nav-link" href="/winners"><?php print t('WINNERS'); ?></a>
    <a class="nav-link" href="/sponsors"><?php print t('SPONSORS'); ?></a>
    <a class="nav-link" href="/news"><?php print t('NEWS'); ?></a>
  </div>
</nav>
<?php endif; ?>
<main class="content-body content-user">
  <div class="content-container<?php if((isset($node) && $node->type =='page')): print ' page'; endif; ?>">
    <?php if ((isset($node) && $node->type =='page') || (strpos(current_path(),'country/') !== FALSE)): ?><h1 class="title" id="page-title"><?php print $title; ?></h1><?php endif; ?>
    <?php if (false): ?><ul class="action-links"><?php print render($action_links); ?></ul><?php endif; ?>
    <?php if ($page['content']): ?>
      <article class="content">
        <?php print $messages; ?>
        <?php print render($page['content']); ?>
        <div>
            <!-- Displays the LISTES of the user found in the path or the current logged in userid by default  -->
            <?php
              if (strpos(current_path(),'user') !== FALSE) {
                $tokens = explode('/', current_path());
                $userid = $tokens[sizeof($tokens)-1];
              }
            ?>
          <?php if(isset($userid)): ?>
          <div class="liste-title-container">
            <h2>
            <?php
            if(user_is_logged_in() && $user->uid == $userid) {
              print t('My Listes');
            } else {
              print t('Chosen Listes');
            }
            ?></h2>
          </div>
          <div class="listes-container">
            <?php print views_embed_view('laliste_listes_view', 'user_page_listes', $userid); ?>
            <?php
              if(user_is_logged_in() && $user->uid == $userid) {
                print "<a href='/node/add/liste' class='add-liste-link'>Add your own Liste</a>";
              }
            ?>
          </div>
        <?php endif; ?>
        </div>
      </article>
    <?php endif; ?>
    <?php print $feed_icons; ?>
    <?php if ($page['sidebar_first']): ?>
      <nav class="laliste-nav">
        <?php print render($page['sidebar_first']); ?>
        <strong>Navigation</strong>
        <?php if ($main_menu || $secondary_menu): ?>
          <?php print theme('links__system_main_menu', array('links' => $main_menu, 'attributes' => array('id' => 'main-menu', 'class' => array('links', 'inline', 'clearfix')), 'heading' => t('Main menu'))); ?>
        <?php endif; ?>
      </nav>
    <?php endif; ?>
    <?php if ($page['sidebar_second']): ?>
      <aside class="laliste-ads">
        <?php print render($page['sidebar_second']); ?>
        <strong>Advertisements</strong>
      </aside>
    <?php endif; ?>
  </div>
</main>
<?php if (isset($language->language) && ($language->language == 'fr')): ?>
<footer class="footer-main">
  <div class="footer-container">
    <a class="footer-link" href="/fr/méthode"><?php print t('MÉTHODE'); ?></a>
    <a class="footer-link" href="/fr/équipe"><?php print t("L'ÉQUIPE"); ?></a>
    <a class="footer-link" href="/fr/palmarès"><?php print t('PALMARÈS'); ?></a>
    <a class="footer-link" href="/fr/sponsors"><?php print t('SPONSORS'); ?></a>
    <a class="footer-link" href="/fr/news"><?php print t('NEWS'); ?></a>
  </div>
  <div class="sub-footer">
    <a class="sub-footer-link" href="/fr/mentions-légales"><?php print t('LEGAL NOTICE'); ?></a>
    <a class="sub-footer-link" href="/fr/conditions"><?php print t('CONDITIONS OF USE'); ?></a>
    <a class="sub-footer-link" href="mailto:contact@laliste.com?subject=Contact%20from%20laliste.com"><?php print t('CONTACT'); ?></a>
  </div>
</footer>
<?php else: ?>
  <footer class="footer-main">
  <div class="footer-container">
    <a class="footer-link" href="/about"><?php print t('METHOD'); ?></a>
    <a class="footer-link" href="/our-team"><?php print t('TEAM'); ?></a>
    <a class="footer-link" href="/winners"><?php print t('WINNERS'); ?></a>
    <a class="footer-link" href="/sponsors"><?php print t('SPONSORS'); ?></a>
    <a class="footer-link" href="/news"><?php print t('NEWS'); ?></a>
  </div>
  <div class="sub-footer">
    <a class="sub-footer-link" href="/legal"><?php print t('LEGAL NOTICE'); ?></a>
    <a class="sub-footer-link" href="/terms"><?php print t('CONDITIONS OF USE'); ?></a>
    <a class="sub-footer-link" href="mailto:contact@laliste.com?subject=Contact%20from%20laliste.com"><?php print t('CONTACT'); ?></a>
  </div>
</footer>
<?php endif; ?>