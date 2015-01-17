=== BotScout Comment Protection ===
Tags: botscout, antispam, comment
Requires at least: 4.0
Tested up to: 4.1
Contributors: jp2112
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=7EX9NB9TLFHVW
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Pass comments through the <a href="http://www.botscout.com/">BotScout</a> API and flag spam comments where appropriate.

== Description ==

BotScout is a third-party antispam service. It maintains a database of known spammer bots which can be checked to determine if a given IP or email has been flagged as a spammer according to their service.

This plugin checks the BotScout database every time someone tries to leave a comment. If the IP and/or email are found in BotScout's database, the comment is flagged as spam. Use this in conjunction with other plugins such as <a href="https://wordpress.org/plugins/nospamnx/">NoSpamNX</a> and <a href="https://wordpress.org/plugins/akismet/">Akismet</a>.

Requires BotScout API key which you can acquire <a href="http://www.botscout.com/getkey.htm">here</a>.

Disclaimer: This plugin is not affiliated with or endorsed by BotScout.

Update: we are listed on the BotScout <a href="http://www.botscout.com/code.htm">code page</a>!

<h3>If you need help with this plugin</h3>

If this plugin breaks your site or just flat out does not work, create a thread in the <a href="http://wordpress.org/support/plugin/botscout-comment-protection">Support</a> forum with a description of the issue. Make sure you are using the latest version of WordPress and the plugin before reporting issues, to be sure that the issue is with the current version and not with an older version where the issue may have already been fixed.

<strong>Please do not use the <a href="http://wordpress.org/support/view/plugin-reviews/botscout-comment-protection">Reviews</a> section to report issues or request new features.</strong>

== Installation ==

1. Upload plugin file through the WordPress interface.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to the plugin settings page and enter your BotScout API key. Click <a href="http://www.botscout.com/getkey.htm">here</a> to get one.
4. Log out of WordPress, go to botscout.com and grab the email of a known bot, and try to post a comment on your own site. Then log in and check your comment spam queue. It will probably be cleared by Akismet but stopped by this plugin.

== Frequently Asked Questions ==

= How does this plugin work? =

Every time someone tries to leave a comment, this plugin takes their IP and email address and passes it to the BotScout API. If the API returns a 'Y' then the commenter is in the BotScout database. The comment is flagged as spam and put into the comment spam queue. You will have to moderate it.

Note that even though BotScout is able to check name, email and IP address (3 fields), this plugin only checks IP and email (2 fields). Typically, the name field is unreliable as an indicator of a spammer.

= How do I use the plugin? =

Simply install and activate the plugin, then enter your API key on the plugin settings page. It starts working immediately to protect your comments form.

= I activated the plugin but it's not working. =

Make sure you entered your API key. Click <a href="http://www.botscout.com/getkey.htm">here</a> to get one.

= Can I use this plugin without an API key? =

Technically, you can make 20 API calls per day to the BotScout API without a key, but this plugin requires it anyway. Its just easier than writing code to track the number of API calls, or catching API errors when the limit is exceeded. It's easy and free to sign up for a key.

= Can I submit new spammers to BotScout using this plugin? =

At this time there is no way to programmatically submit spambots to BotScout, so no plugin can offer this functionality. You have to manually go to botscout.com and fill out the CAPTCHA form to submit a spammer to their database. Once you do, however, it will be picked up by this plugin after that (assuming the submission is accepted).

== Screenshots ==

1. Plugin settings page

== Changelog ==

= 0.0.6 =
- confirmed compatibility with WordPress 4.1
- added uninstall.php

= 0.0.5 =
- updated readme

= 0.0.4 =
- updated .pot file and readme

= 0.0.3 =
- use wp_remote_get instead of cURL, let WP decide
- check for real IP when using Cloudflare

= 0.0.2 =
- switch to using email and IP instead of email and name
- added cURL check

= 0.0.1 =
- created

== Upgrade Notice ==

= 0.0.6 =
- confirmed compatibility with WordPress 4.1, added uninstall.php

= 0.0.5 =
- updated readme

= 0.0.4 =
- updated .pot file and readme

= 0.0.3 =
- use wp_remote_get instead of cURL, let WP decide; check for real IP when using Cloudflare

= 0.0.2 =
- switch to using email and IP instead of email and name; added cURL check

= 0.0.1 =
created