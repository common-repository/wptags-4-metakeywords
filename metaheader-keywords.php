<?php
/*
Plugin Name: Metaheader Keywords
Plugin URI: http://www.weinschenker.name/wptags-4-metakeywords/
Description: This plugin lets you embed tags for a post as keywords into the meta-header of your theme.
Version:  0.6.4
Author: Jan Weinschenker
Author URI: http://www.weinschenker.name


   $Id: pandorafeeds.php,v 1.5 2007/02/23 13:13:45 acubens Exp $

   
    Plugin: Copyright 2006  Jan Weinschenker  (email: pandorafeeds@weinschenker.name)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

add_option('metaheader_keywords_settings', $data, 'WPtags 4 MetaKeywords');
add_action('admin_menu', 'metaheader_register_with_options');
add_action('admin_init', 'metaheader_register_setting');

/**
 * Register uninstall-hook
 * @since WordPress 2.7
 *  */
if (function_exists('register_uninstall_hook'))
register_uninstall_hook(__FILE__, 'metaheader_uninstall_hook');


/*
 * Register Settings and options
 * @since WordPress 2.7
 */
function metaheader_register_setting(){
    if (function_exists('register_setting')) {
        register_setting('metaheader_keywords_settings_group', 'metaheader_keywords_settings');
    }
}


/**
 * Register with options-page
 */
function metaheader_register_with_options() {
	if (function_exists('add_options_page')) {
		add_options_page('WPtags 4 MetaKeywords', 'WPtags 4 MetaKeywords', 8, basename(__FILE__), 'metaheader_keywords_options_subpanel');
	}
}

/**
 * The uninstall-hook for the plugin
 * @since WordPress 2.7
 */
function metaheader_uninstall_hook() {
	delete_option('metaheader_keywords_settings');
}

/*
 * Returns true, if new values are contained in POST-request, else false
 */
function metaheader_vars_are_set(){
	if (isset ($_POST['home-meta-keywords'])){
		return TRUE;
	} else {
		return FALSE;
	}
}

/**
 * This function renders the options-subpanel of this plugin. The form is used to store
 * the user's pandora-account-data in the database.
 */
function metaheader_keywords_options_subpanel() {
	if (function_exists('wp_nonce_field') and !function_exists('settings_fields')) {
		if (metaheader_vars_are_set())
			check_admin_referer('metaheader_keywords_action_option_panel');
	} else if (function_exists('settings_fields')) {
		if (metaheader_vars_are_set())
			check_admin_referer('metaheader_keywords_settings_group-options');
	}
	
	//global $_POST;
	
	/* This array contains all settings for this plugin. */
	//$data = array (
	//	'home-meta-keywords' => ''
	//);
	/* Get settings from database via WordPress framework */
	$metaheader_keywords_settings = get_option('metaheader_keywords_settings');
	$metaheader_keywords_flash = "";

	if (metaheader_user_is_authorized()) {
		if (metaheader_vars_are_set()) {
			$metaheader_keywords_settings['home-meta-keywords'] = attribute_escape($_POST['home-meta-keywords']);
			update_option('metaheader_keywords_settings', $metaheader_keywords_settings);
			$metaheader_keywords_flash = "Your settings have been saved.";
		}
	} else {
		$metaheader_keywords_flash = "You don't have enough access rights.";
	}

	if ($metaheader_keywords_flash != '') { ?>
		<div id="message"class="updated fade"><p><?php echo $metaheader_keywords_flash ?></p></div>
    <?php } // end if ?>

	<div class="wrap">
	<h2>WPtags 4 MetaKeywords</h2>
	<form id="metaheader_keywords_options_form" action="" method="post"><?php
	if (function_exists('wp_nonce_field') and !function_exists('settings_fields')) {
	    wp_nonce_field('metaheader_keywords_action_option_panel');
	} else if (function_exists('settings_fields')) {
	    settings_fields('metaheader_keywords_settings_group');
	}
	?>
            <p><b>This plugin does not require any changes to your theme. The meta-tag is added automatically to the html-header of your blog regardless of the currently chosen theme.</b></p>
            <p>This plugin sets keywords automatically for articles, pages, archives, author-, tag- and category-pages. On all other pages (e.g. the homepage), the default keywords are used.</p>
            <p>Enter your default keywords below:</p>
			<p><label for="id"></label><input type="text" id="home-meta-keywords" name="home-meta-keywords" value="<?php echo htmlentities($metaheader_keywords_settings['home-meta-keywords']) ?>" size="150" /></p>
            <p class="submit"><input name="submit"
	value="<?php _e('Save Changes'); ?>" type="submit" /></p>
    </form>
    <h2>Example</h2>
            <p>Example of the meta-tag with your your default-tags:</p>
            <?php 
                $metaheader_options = get_option('metaheader_keywords_settings');
                $default_metakeywords = $metaheader_options['home-meta-keywords'];
            ?>
            <p><pre><code>&lt;meta name="keywords" content="<?php echo $default_metakeywords?>" /&gt;</code></pre>
    <h2>Documentation and Support for this Plugin</h2>
    <p>Can be found at the <a href="http://www.weinschenker.name/wptags-4-metakeywords/" title="go to http://www.weinschenker.name/wptags-4-metakeywords/">Plugin-Homepage</a>.</p>
	</div> <?php
}

/**
 * Check if the current user is allowed to activate plugins.
 */
function metaheader_user_is_authorized() {
	global $user_level;
	if (function_exists("current_user_can")) {
		return current_user_can('activate_plugins');
	} else {
		return $user_level > 5;
	}
}

function metahead_keywords() {
    global $wp_query, $wpdb;
    if(is_single() || is_page()) {
        while (have_posts()) : the_post();
            $keywords = get_the_tags();
            if (empty( $keywords )) {
                $metaheader_options = get_option('metaheader_keywords_settings');
                $default_metakeywords = $metaheader_options['home-meta-keywords'];
                ?><meta name="keywords" content="<?php echo $default_metakeywords ?>" /><?php
            } else {
                $keywords_list = '';
                foreach ( $keywords as $keyword ) {
                    $keywords_list[] = $keyword->name;
                }
                $sep = ',';
                $keywords_list = join( $sep, $keywords_list );
                ?><meta name="keywords" content="<?php echo $keywords_list ?>" /><?php
            }
        endwhile;
        rewind_posts();
        
	} elseif (is_category()) {
        ?><meta name="keywords" content="<?php echo single_cat_title() ?>" /><?php
    } elseif (is_day()) {
        ?><meta name="keywords" content="<?php echo get_the_time(__('F jS, Y','redo_domain')) ?>" /><?php
    } elseif (is_month()) {
        ?><meta name="keywords" content="<?php echo get_the_time(__('F, Y','redo_domain')) ?>" /><?php
    } elseif (is_year()) {
        ?><meta name="keywords" content="<?php echo get_the_time(__('Y','redo_domain')) ?>" /><?php    
    } elseif (function_exists('is_tag') and is_tag()) {
        ?><meta name="keywords" content="<?php echo get_query_var('tag') ?>" /><?php
    } elseif (is_author()) {
        $post = $wp_query->post; $the_author = $wpdb->get_var("SELECT meta_value FROM $wpdb->usermeta WHERE user_id = '$post->post_author' AND meta_key = 'nickname'");
        ?><meta name="keywords" content="author,<?php echo $the_author ?>" /><?php
    } else {
        $metaheader_options = get_option('metaheader_keywords_settings');
        $default_metakeywords = $metaheader_options['home-meta-keywords'];
        ?><meta name="keywords" content="<?php echo $default_metakeywords ?>" /><?php
    }
}

add_action('wp_head', 'metahead_keywords');

?>
