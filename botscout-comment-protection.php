<?php
/*
Plugin Name: BotScout Comment Protection
Plugin URI: http://www.jimmyscode.com/wordpress/botscout-comment-protection/
Description: Pass comments through the <a href="http://www.botscout.com/">BotScout</a> API and flag spam comments where appropriate.
Version: 0.0.6
Author: Jimmy Pe&ntilde;a
Author URI: http://www.jimmyscode.com/
License: GPLv2 or later
*/

if (!defined('BSCP_PLUGIN_NAME')) {
	// plugin constants
	define('BSCP_MIN_WP_VERSION', '4.0');
	define('BSCP_MIN_PHP_VERSION', '5.2');
	define('BSCP_VERSION', '0.0.6');
	define('BSCP_PLUGIN_NAME', 'BotScout Comment Protection');
	define('BSCP_SLUG', 'botscout-comment-protection');
	define('BSCP_LOCAL', 'bscp');
	define('BSCP_OPTION', 'bscp');
	define('BSCP_OPTIONS_NAME', 'bscp_options');
	define('BSCP_PERMISSIONS_LEVEL', 'manage_options');
	define('BSCP_PATH', plugin_basename(dirname(__FILE__)));
	/* default values */
	define('BSCP_DEFAULT_ENABLED', true);
	define('BSCP_DEFAULT_APIKEY', '');
	/* option array member names */
	define('BSCP_DEFAULT_ENABLED_NAME', 'enabled');
	define('BSCP_DEFAULT_APIKEY_NAME', 'bs_apikey');
}
// oh no you don't
if (!defined('ABSPATH')) {
	wp_die(__('Do not access this file directly.', bscp_get_local()));
}

// localization to allow for translations
add_action('init', 'bscp_translation_file');
function bscp_translation_file() {
	$plugin_path = plugin_basename(dirname(__FILE__) . '/translations');
	load_plugin_textdomain(bscp_get_local(), '', $plugin_path);
}
// tell WP that we are going to use new options
// also, register the admin CSS file for later inclusion
add_action('admin_init', 'bscp_options_init');
function bscp_options_init() {
	register_setting(BSCP_OPTIONS_NAME, bscp_get_option(), 'bscp_validation');
	register_bscp_admin_style();
}
// do version checks
// http://pento.net/2014/02/18/dont-let-your-plugin-be-activated-on-incompatible-sites/
add_action('admin_init', 'bscp_check_versions');
register_activation_hook(__FILE__, 'bscp_check_version_on_activation');
function bscp_check_versions() {
	// check for minimum WP and PHP versions
	if (is_plugin_active(plugin_basename(__FILE__))) {
		if (bscp_compare_versions(get_bloginfo('version'), BSCP_MIN_WP_VERSION)) { // WP version too low
			deactivate_plugins(plugin_basename(__FILE__));
			add_action('admin_notices', 'bscp_show_wp_notice');
			if (isset($_GET['activate'])) {
				unset($_GET['activate']);
			}
		} elseif (bscp_compare_versions(phpversion(), BSCP_MIN_PHP_VERSION)) { // PHP version too low
			deactivate_plugins(plugin_basename(__FILE__));
			add_action('admin_notices', 'bscp_show_php_notice');
			if (isset($_GET['activate'])) {
				unset($_GET['activate']);
			}
		}
	}
}
function bscp_check_version_on_activation() {
	// check for minimum WP and PHP versions
	if (bscp_compare_versions(get_bloginfo('version'), BSCP_MIN_WP_VERSION)) { // WP version too low
		deactivate_plugins(plugin_basename(__FILE__));
		add_action('admin_notices', 'bscp_show_wp_notice');
	} elseif (bscp_compare_versions(phpversion(), BSCP_MIN_PHP_VERSION)) { // PHP version too low
		deactivate_plugins(plugin_basename(__FILE__));
		add_action('admin_notices', 'bscp_show_php_notice');
	}
}
function bscp_compare_versions($versiontobechecked, $minversion) {
	if (version_compare($versiontobechecked, $minversion, '<')) {
		return true;
	}
}
function bscp_show_wp_notice() {
	echo '<div id="message" class="error">' . BSCP_PLUGIN_NAME . ' ' . sprintf(__('requires WordPress version %s or higher. You must update WordPress before you can use this plugin.', bscp_get_local()), BSCP_MIN_WP_VERSION) . '</div>';
}
function bscp_show_php_notice() {
	echo '<div id="message" class="error">' . BSCP_PLUGIN_NAME . ' ' . sprintf(__('requires PHP version %s or higher. You must update WordPress before you can use this plugin.', bscp_get_local()), BSCP_MIN_PHP_VERSION) . '</div>';
}
// validation function
function bscp_validation($input) {
// validate all form fields
	if (!empty($input)) {
		$input[BSCP_DEFAULT_ENABLED_NAME] = (bool)$input[BSCP_DEFAULT_ENABLED_NAME];
		$input[BSCP_DEFAULT_APIKEY_NAME] = sanitize_text_field($input[BSCP_DEFAULT_APIKEY_NAME]);
	}
	return $input;
}
// add Settings sub-menu
add_action('admin_menu', 'bscp_plugin_menu');
function bscp_plugin_menu() {
	if (is_plugin_active(plugin_basename(__FILE__))) {
		add_options_page(BSCP_PLUGIN_NAME, BSCP_PLUGIN_NAME, BSCP_PERMISSIONS_LEVEL, bscp_get_slug(), 'bscp_page');
	}
}
	// plugin settings page
	// http://planetozh.com/blog/2009/05/handling-plugins-options-in-wordpress-28-with-register_setting/
	function bscp_page() {
		// check perms
		if (!current_user_can(BSCP_PERMISSIONS_LEVEL)) {
			wp_die(__('You do not have sufficient permission to access this page', bscp_get_local()));
		}
		?>
		<div class="wrap">
			<h2 id="plugintitle"><img src="<?php echo bscp_getimagefilename('prot.png'); ?>" title="" alt="" height="64" width="64" align="absmiddle" /> <?php echo BSCP_PLUGIN_NAME; _e(' by ', bscp_get_local()); ?><a href="http://www.jimmyscode.com/">Jimmy Pe&ntilde;a</a></h2>
			<div><?php _e('You are running plugin version', bscp_get_local()); ?> <strong><?php echo BSCP_VERSION; ?></strong>.</div>

			<?php /* http://code.tutsplus.com/tutorials/the-complete-guide-to-the-wordpress-settings-api-part-5-tabbed-navigation-for-your-settings-page--wp-24971 */ ?>
			<?php $active_tab = (isset($_GET['tab']) ? $_GET['tab'] : 'settings'); ?>
			<h2 class="nav-tab-wrapper">
			  <a href="?page=<?php echo bscp_get_slug(); ?>&tab=settings" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>"><?php _e('Settings', bscp_get_local()); ?></a>
				<a href="?page=<?php echo bscp_get_slug(); ?>&tab=support" class="nav-tab <?php echo $active_tab == 'support' ? 'nav-tab-active' : ''; ?>"><?php _e('Support', bscp_get_local()); ?></a>
			</h2>
			
			<form method="post" action="options.php">
				<?php settings_fields(BSCP_OPTIONS_NAME); ?>
				<?php $options = bscp_getpluginoptions(); ?>
				<?php update_option(bscp_get_option(), $options); ?>
				<?php if ($active_tab == 'settings') { ?>
					<h3 id="settings"><img src="<?php echo bscp_getimagefilename('settings.png'); ?>" title="" alt="" height="61" width="64" align="absmiddle" /> <?php _e('Plugin Settings', bscp_get_local()); ?></h3>
					<table class="form-table" id="theme-options-wrap">
						<tr valign="top"><th scope="row"><strong><label title="<?php _e('Is plugin enabled? Uncheck this to turn it off temporarily.', bscp_get_local()); ?>" for="<?php echo bscp_get_option(); ?>[<?php echo BSCP_DEFAULT_ENABLED_NAME; ?>]"><?php _e('Plugin enabled?', bscp_get_local()); ?></label></strong></th>
							<td><input type="checkbox" id="<?php echo bscp_get_option(); ?>[<?php echo BSCP_DEFAULT_ENABLED_NAME; ?>]" name="<?php echo bscp_get_option(); ?>[<?php echo BSCP_DEFAULT_ENABLED_NAME; ?>]" value="1" <?php checked('1', bscp_checkifset(BSCP_DEFAULT_ENABLED_NAME, BSCP_DEFAULT_ENABLED, $options)); ?> /></td>
						</tr>
						<?php bscp_explanationrow(__('Is plugin enabled? Uncheck this to turn it off temporarily.', bscp_get_local())); ?>
						<?php bscp_getlinebreak(); ?>
						<tr valign="top"><th scope="row"><strong><label title="<?php _e('Enter your BotScout API key here.', bscp_get_local()); ?>" for="<?php echo bscp_get_option(); ?>[<?php echo BSCP_DEFAULT_APIKEY_NAME; ?>]"><?php _e('Enter your BotScout API key here.', bscp_get_local()); ?></label></strong></th>
							<td><input type="text" id="<?php echo bscp_get_option(); ?>[<?php echo BSCP_DEFAULT_APIKEY_NAME; ?>]" name="<?php echo bscp_get_option(); ?>[<?php echo BSCP_DEFAULT_APIKEY_NAME; ?>]" value="<?php echo bscp_checkifset(BSCP_DEFAULT_APIKEY_NAME, BSCP_DEFAULT_APIKEY, $options); ?>" /></td>								  
						</tr>
						<?php bscp_explanationrow(__('Put your BotScout API key here. <a href="http://www.botscout.com/getkey.htm">Register</a> if you do not have one.', bscp_get_local())); ?>
					</table>
					<?php submit_button(); ?>
				<?php } else { ?>
					<h3 id="support"><img src="<?php echo bscp_getimagefilename('support.png'); ?>" title="" alt="" height="64" width="64" align="absmiddle" /> <?php _e('Support', bscp_get_local()); ?></h3>
					<div class="support">
						<?php echo bscp_getsupportinfo(bscp_get_slug(), bscp_get_local()); ?>
						<small><?php _e('Disclaimer: This plugin is not affiliated with or endorsed by BotScout. If you use this plugin please send them a <a href="http://www.botscout.com/donate.htm">donation<a>.', bscp_get_local()); ?></small>
					</div>
				<?php } ?>
			</form>
		</div>
		<?php }
	
// main function based on:
// http://www.jimmyscode.com/code/botscout-api/
// http://codex.wordpress.org/Plugin_API/Filter_Reference/preprocess_comment
// http://blog.datumbox.com/how-to-build-an-intelligent-antispam-wordpress-plugin/ 
// http://marketpress.com/2013/mini-plugin-blocking-known-spam-ips-in-wordpress/
// http://stackoverflow.com/questions/14985518/cloudflare-and-logging-visitor-ips-via-in-php
add_action('preprocess_comment', 'bscp_check_comment');
function bscp_check_comment($commentdata) {
	$options = bscp_getpluginoptions();
	$enabled = (bool)$options[BSCP_DEFAULT_ENABLED_NAME];

	if ($enabled) {
		$bs_apikey = sanitize_text_field($options[BSCP_DEFAULT_APIKEY_NAME]);
		if ($bs_apikey) {
		  $ip = '';
			if ($_SERVER['REMOTE_ADDR']) {
				$ip = preg_replace( '/[^0-9a-fA-F:., ]/', '', (isset($_SERVER['HTTP_CF_CONNECTING_IP']) ? $_SERVER['HTTP_CF_CONNECTING_IP'] : $_SERVER['REMOTE_ADDR'] ));
			}
			$isspam = bscp_isspam(bscp_checkifset('comment_author_email', '', $commentdata), $ip, $bs_apikey);
			if ($isspam) {
				add_filter('pre_comment_approved', 'bscp_return_spam');
			}
		}
	}
	return $commentdata;
}

function bscp_return_spam() {
	return 'spam';
}
function bscp_isspam($email, $ip, $apiKey) {
  // BotScout API base url
  $baseURL = 'http://botscout.com/test/?';
	// default value so no PHP complaining
	$multicheck = false;
   // are we checking multiple parameters?
  if ((strlen($email) > 0 && strlen($name) > 0)) {
    $multicheck = true;
  }
  // start with botscout URL, then append other values
  $apiquery = $baseURL;
  if ($multicheck) { // must use 'multi' keyword
    $apiquery .= 'multi';
  }
  if (strlen($email) > 0) {
    $apiquery .= ($multicheck ? '&' : '') . 'mail=' . $email;
  }
  if (strlen($ip) > 0) {
    $apiquery .= ($multicheck ? '&' : '') . 'ip=' . $name;
  }
	$apiquery .= '&key=' . $apiKey;
	
  // call API
  $returned_data = bscp_checkBotScout($apiquery);
  // if API returns a 'Y' as first char, we found a spammer
	return (bool)(substr($returned_data, 0, 1) === 'Y');
} // end botscout function

function bscp_checkBotScout($url) {
	// http://planetozh.com/blog/2009/08/how-to-make-http-requests-with-wordpress/
	$resp = wp_remote_get($url);
	if ($resp['response']['message'] === "OK") {
		return $resp['body'];
	}
}

	// show admin messages to plugin user
	add_action('admin_notices', 'bscp_showAdminMessages');
	function bscp_showAdminMessages() {
		// http://wptheming.com/2011/08/admin-notices-in-wordpress/
		global $pagenow;
		if (current_user_can(BSCP_PERMISSIONS_LEVEL)) { // user has privilege
			if ($pagenow == 'options-general.php') { // we are on Settings menu
				if (isset($_GET['page'])) {
					if ($_GET['page'] == bscp_get_slug()) { // we are on this plugin's settings page
						$options = bscp_getpluginoptions();
						if (!empty($options)) {
							$enabled = (bool)$options[BSCP_DEFAULT_ENABLED_NAME];
							$hasapikey = (bool)$options[BSCP_DEFAULT_APIKEY_NAME];
							if (!$enabled) {
								echo '<div id="message" class="error">' . BSCP_PLUGIN_NAME . ' ' . __('is currently disabled.', bscp_get_local()) . '</div>';
							}
							if (!$hasapikey) {
								echo '<div id="message" class="error">' . __('API Key is missing. Please enter yours or <a href="http://www.botscout.com/getkey.htm">register</a> for one.', bscp_get_local()) . '</div>';
							}
						}
					}
				}
			} // end page check
		} // end privilege check
	} // end admin msgs function
	// enqueue admin CSS if we are on the plugin options page
	add_action('admin_head', 'insert_bscp_admin_css');
	function insert_bscp_admin_css() {
		global $pagenow;
		if (current_user_can(BSCP_PERMISSIONS_LEVEL)) { // user has privilege
			if ($pagenow == 'options-general.php') { // we are on Settings menu
				if (isset($_GET['page'])) {
					if ($_GET['page'] == bscp_get_slug()) { // we are on this plugin's settings page
						bscp_admin_styles();
					}
				}
			}
		}
	}
	// add helpful links to plugin page next to plugin name
	// http://bavotasan.com/2009/a-settings-link-for-your-wordpress-plugins/
	// http://wpengineer.com/1295/meta-links-for-wordpress-plugins/
	add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'bscp_plugin_settings_link');
	add_filter('plugin_row_meta', 'bscp_meta_links', 10, 2);
	
function bscp_plugin_settings_link($links) {
	return (is_plugin_active(plugin_basename(__FILE__)) ? bscp_settingslink($links, bscp_get_slug(), bscp_get_local()) : $links);
/*	if (is_plugin_active(plugin_basename(__FILE__))) {
		return bscp_settingslink($links, bscp_get_slug(), bscp_get_local());
	} else {
		return $links;
	}
*/
}
function bscp_meta_links($links, $file) {
	if (is_plugin_active(plugin_basename(__FILE__))) {
		if ($file == plugin_basename(__FILE__)) {
			$links = array_merge($links,
			array(
				sprintf(__('<a href="http://wordpress.org/support/plugin/%s">Support</a>', bscp_get_local()), bscp_get_slug()),
				sprintf(__('<a href="http://wordpress.org/extend/plugins/%s/">Documentation</a>', bscp_get_local()), bscp_get_slug()),
				sprintf(__('<a href="http://wordpress.org/plugins/%s/faq/">FAQ</a>', bscp_get_local()), bscp_get_slug())
			));
		}
	}
	return $links;	
}
	// enqueue/register the admin CSS file
	function bscp_admin_styles() {
		wp_enqueue_style('bscp_admin_style');
	}
	function register_bscp_admin_style() {
		wp_register_style('bscp_admin_style',
			plugins_url(bscp_get_path() . '/css/admin.css'),
			array(),
			BSCP_VERSION . "_" . date('njYHis', filemtime(dirname(__FILE__) . '/css/admin.css')),
			'all');
	}
	// when plugin is activated, create options array and populate with defaults
	register_activation_hook(__FILE__, 'bscp_activate');
	function bscp_activate() {
		$options = bscp_getpluginoptions();
		update_option(bscp_get_option(), $options);
		
		// delete option when plugin is uninstalled
		register_uninstall_hook(__FILE__, 'uninstall_bscp_plugin');
	}
	function uninstall_bscp_plugin() {
		delete_option(bscp_get_option());
	}

	// generic function that returns plugin options from DB
	// if option does not exist, returns plugin defaults
	function bscp_getpluginoptions() {
		return get_option(bscp_get_option(), 
			array(
				BSCP_DEFAULT_ENABLED_NAME => BSCP_DEFAULT_ENABLED, 
				BSCP_DEFAULT_APIKEY_NAME => BSCP_DEFAULT_APIKEY
			));
	}
	
// encapsulate these and call them throughout the plugin instead of hardcoding the constants everywhere
	function bscp_get_slug() { return BSCP_SLUG; }
	function bscp_get_local() { return BSCP_LOCAL; }
	function bscp_get_option() { return BSCP_OPTION; }
	function bscp_get_path() { return BSCP_PATH; }

	function bscp_settingslink($linklist, $slugname = '', $localname = '') {
		$settings_link = sprintf( __('<a href="options-general.php?page=%s">Settings</a>', $localname), $slugname);
		array_unshift($linklist, $settings_link);
		return $linklist;
	}
	function bscp_getsupportinfo($slugname = '', $localname = '') {
		$output = __('Do you need help with this plugin? Check out the following resources:', $localname);
		$output .= '<ol>';
		$output .= '<li>' . sprintf( __('<a href="http://wordpress.org/extend/plugins/%s/">Documentation</a>', $localname), $slugname) . '</li>';
		$output .= '<li>' . sprintf( __('<a href="http://wordpress.org/plugins/%s/faq/">FAQ</a><br />', $localname), $slugname) . '</li>';
		$output .= '<li>' . sprintf( __('<a href="http://wordpress.org/support/plugin/%s">Support Forum</a><br />', $localname), $slugname) . '</li>';
		$output .= '<li>' . sprintf( __('<a href="http://www.jimmyscode.com/wordpress/%s">Plugin Homepage / Demo</a><br />', $localname), $slugname) . '</li>';
		$output .= '<li>' . sprintf( __('<a href="http://wordpress.org/extend/plugins/%s/developers/">Development</a><br />', $localname), $slugname) . '</li>';
		$output .= '<li>' . sprintf( __('<a href="http://wordpress.org/plugins/%s/changelog/">Changelog</a><br />', $localname), $slugname) . '</li>';
		$output .= '</ol>';
		
		$output .= sprintf( __('If you like this plugin, please <a href="http://wordpress.org/support/view/plugin-reviews/%s/">rate it on WordPress.org</a>', $localname), $slugname);
		$output .= sprintf( __(' and click the <a href="http://wordpress.org/plugins/%s/#compatibility">Works</a> button. ', $localname), $slugname);
		$output .= '<br /><br /><br />';
		$output .= __('Your donations encourage further development and support. ', $localname);
		$output .= '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=7EX9NB9TLFHVW"><img src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" alt="Donate with PayPal" title="Support this plugin" width="92" height="26" /></a>';
		$output .= '<br /><br />';
		return $output;		
	}
	function bscp_checkifset($optionname, $optiondefault, $optionsarr) {
		return (isset($optionsarr[$optionname]) ? $optionsarr[$optionname] : $optiondefault);
	}
	function bscp_getlinebreak() {
	  echo '<tr valign="top"><td colspan="2"></td></tr>';
	}
	function bscp_explanationrow($msg = '') {
		echo '<tr valign="top"><td></td><td><em>' . $msg . '</em></td></tr>';
	}
	function bscp_getimagefilename($fname = '') {
		return plugins_url(bscp_get_path() . '/images/' . $fname);
	}
?>