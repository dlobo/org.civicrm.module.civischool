<?php

// $Id: page.tpl.php,v 1.10.2.4 2009/02/13 17:30:22 johnalbin Exp $

/**
 * @file page.tpl.php
 *
 * Theme implementation to display a single Drupal page.
 *
 * Available variables:
 *
 * General utility variables:
 * - $base_path: The base URL path of the Drupal installation. At the very
 *   least, this will always default to /.
 * - $css: An array of CSS files for the current page.
 * - $directory: The directory the theme is located in, e.g. themes/garland or
 *   themes/garland/minelli.
 * - $is_front: TRUE if the current page is the front page. Used to toggle the mission statement.
 * - $logged_in: TRUE if the user is registered and signed in.
 * - $is_admin: TRUE if the user has permission to access administration pages.
 *
 * Page metadata:
 * - $language: (object) The language the site is being displayed in.
 *   $language->language contains its textual representation.
 *   $language->dir contains the language direction. It will either be 'ltr' or 'rtl'.
 * - $head_title: A modified version of the page title, for use in the TITLE tag.
 * - $head: Markup for the HEAD section (including meta tags, keyword tags, and
 *   so on).
 * - $styles: Style tags necessary to import all CSS files for the page.
 * - $scripts: Script tags necessary to load the JavaScript files and settings
 *   for the page.
 * - $body_classes: A set of CSS classes for the BODY tag. This contains flags
 *   indicating the current layout (multiple columns, single column), the current
 *   path, whether the user is logged in, and so on.
 *
 * Site identity:
 * - $front_page: The URL of the front page. Use this instead of $base_path,
 *   when linking to the front page. This includes the language domain or prefix.
 * - $logo: The path to the logo image, as defined in theme configuration.
 * - $site_name: The name of the site, empty when display has been disabled
 *   in theme settings.
 * - $site_slogan: The slogan of the site, empty when display has been disabled
 *   in theme settings.
 * - $mission: The text of the site mission, empty when display has been disabled
 *   in theme settings.
 *
 * Navigation:
 * - $search_box: HTML to display the search box, empty if search has been disabled.
 * - $primary_links (array): An array containing primary navigation links for the
 *   site, if they have been configured.
 * - $secondary_links (array): An array containing secondary navigation links for
 *   the site, if they have been configured.
 *
 * Page content (in order of occurrance in the default page.tpl.php):
 * - $left: The HTML for the left sidebar.
 *
 * - $breadcrumb: The breadcrumb trail for the current page.
 * - $title: The page title, for use in the actual HTML content.
 * - $help: Dynamic help text, mostly for admin pages.
 * - $messages: HTML for status and error messages. Should be displayed prominently.
 * - $tabs: Tabs linking to any sub-pages beneath the current page (e.g., the view
 *   and edit tabs when displaying a node).
 *
 * - $content: The main content of the current Drupal page.
 *
 * - $right: The HTML for the right sidebar.
 *
 * Footer/closing data:
 * - $feed_icons: A string of all feed icons for the current page.
 * - $footer_message: The footer message as defined in the admin settings.
 * - $footer : The footer region.
 * - $closure: Final closing markup from any modules that have altered the page.
 *   This variable should always be output last, after all other dynamic content.
 *
 * @see template_preprocess()
 * @see template_preprocess_page()
 */
?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php print $language->language; ?>" lang="<?php print $language->language; ?>" dir="<?php print $language->dir; ?>">

<head>
  <title><?php print $head_title; ?></title>
  <?php print $head; ?>
  <?php print $styles; ?>
  <?php print $scripts; ?>

<!--[if lte IE 6]>
<link rel="stylesheet" href="<?php echo $base_path . $directory; ?>/ie.css" type="text/css" />
<![endif]-->
<!--[if lte IE 7]>
<link rel="stylesheet" href="<?php echo $base_path . $directory; ?>/ie7.css" type="text/css" />
<![endif]-->
<!--[if lte IE 8]>
<link rel="stylesheet" href="<?php echo $base_path . $directory; ?>/ie8.css" type="text/css" />
<![endif]-->

</head>
<body onload="loadPage()" class="<?php print $body_classes; ?>">
<table align="center">
<tr>
<td>
    <div id="page">
    <table><tr><td>

    <div id="header">
      <table cellspacing="0" cellpadding="0">
      <tr><td colspan="2">
      <div id="skip-nav"><a href="#content"><?php print t('Skip to Main Content'); ?></a></div>

      <div id="navigation" class="menu<?php if ($primary_links) { print " withprimary"; } if ($secondary_links) { print " withsecondary"; } ?> ">
        <?php if (!empty($primary_links)): ?>
          <div id="primary" class="clear-block">
            <?php print theme('links', $primary_links); ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($secondary_links)): ?>
          <div id="secondary" class="clear-block">
            <?php print theme('links', $secondary_links); ?>
          </div>
        <?php endif; ?>
      </div> <!-- /navigation -->
      </td></tr>
      
      <tr><td><?php  global $base_url; ?><img src="<?php echo $base_path . $directory; ?>/families.jpg">
      </td><td>
      <div id="logo-title">

        <?php print $search_box; ?>
        <?php if (!empty($logo)): ?>
          <a href="<?php print $front_page; ?>" title="<?php print t('Home'); ?>" rel="home">
            <img src="<?php print $logo; ?>" alt="<?php print t('Home'); ?>" id="logo" />           
          </a> 
        <?php endif; ?>

        <div id="name-and-slogan">

        <?php if (!empty($site_name)): ?>
          <div id="site-name"><strong>
            <a href="<?php print $front_page; ?>" title="<?php print t('Home'); ?>" rel="home">
              <?php print $site_name; ?>
            </a>
          </strong></div>
        <?php endif; ?>

        <?php if (!empty($site_slogan)): ?>
          <div id="site-slogan">
            <?php print $site_slogan; ?>
          </div>
        <?php endif; ?>

        </div> <!-- /name-and-slogan -->

      </div> <!-- /logo-title -->      
      </td></tr>
      </table>
    </div> <!-- /header -->
   </td></tr></table>

   <table><tr><td colspan="2" valign="top" >
    <div id="container" class="clear-block">
     <table><tr>
      <?php if (!empty($left)): ?>
        <td valign="top" width="184px" bgcolor="#f9f3d9">
        <div id="sidebar-left" class="column sidebar">
          <?php print $left; ?>
        </div> <!-- /sidebar-left -->
        </td>
      <?php endif; ?>
        
      <td>  
      <div id="main" class="column">

      <?php if (!empty($right)): ?>    

      <table><tr><td width="665px" valign="top">
      <?php endif; if(empty($right)): ?>
     
       <table width="100%"><tr><td valign="top">
      <?php endif; ?>
                                     
      <div id="squeeze" class="clear-block">
        <?php if (!empty($mission)): ?>
          <div id="mission"><?php print $mission; ?></div>
        <?php endif; ?>
        <?php if (!empty($content_top)): ?>
          <div id="content-top"><?php print $content_top; ?></div>
        <?php endif; ?>
        <div id="content">
          <?php if (!empty($title)): ?>
            <h1 class="title"><?php print $title; ?></h1>
          <?php endif; ?>
          <?php if (!empty($tabs)): ?>
            <div class="tabs"><?php print $tabs; ?></div>
          <?php endif; ?>
          <?php print $help; ?>
          <?php print $messages; ?>
          <?php print $content; ?>
          <?php if (!empty($feed_icons)): ?>
            <div class="feed-icons"><?php print $feed_icons; ?></div>
          <?php endif; ?>
        </div> <!-- /content -->
        <?php if (!empty($content_bottom)): ?>
          <div id="content-bottom"><?php print $content_bottom; ?></div>
        <?php endif; ?>
      </div>
      </td>
       <!-- /squeeze -->

      <?php if (!empty($right)): ?>
      <td valign="top">
        <div id="sidebar-right" class="column sidebar">
          <?php print $right; ?>
        </div></td></tr></table> <!-- /sidebar-right -->
      <?php endif; ?>
    </div></td></tr></table> <!-- /main -->
    </div> <!-- /container -->
    </td></tr></table>

    <table><tr><td>

    <?php if ($footer || $footer_message): ?>
      <div id="footer-wrapper"><div id="footer">

        <?php if ($footer_message): ?>
          <div id="footer-message"><?php print $footer_message; ?></div>
        <?php endif; ?>

        <?php print $footer; ?>

      </div></div> <!-- /#footer, /#footer-wrapper -->
    <?php endif; ?>

  </td></tr></table>
  </div> <!-- /page -->


</td>
</tr>
</table>


  <?php if ($closure_region): ?>
    <div id="closure-blocks"><?php print $closure_region; ?></div>
  <?php endif; ?>

  <?php print $closure; ?>

</body>
</html>