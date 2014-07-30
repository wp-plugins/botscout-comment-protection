=== BotScout Comment Protection ===
Tags: botscout, antispam, comment
Requires at least: 3.5
Tested up to: 3.9
Contributors: jp2112
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=7EX9NB9TLFHVW
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Pass comments through the <a href="http://www.botscout.com/">BotScout</a> API and flag spam comments where appropriate.

== Description ==

BotScout is a third-party antispam service. It maintains a database of known spammer bots which can be checked to determine if a given name or email has been flagged as a spammer according to their service.

This plugin checks the BotScout database every time someone tries to leave a comment. If the IP and/or email are found in BotScout's database, the comment is flagged as spam. Use this in conjunction with other plugins such as <a href="https://wordpress.org/plugins/nospamnx/">NoSpamNX</a> and <a href="https://wordpress.org/plugins/akismet/">Akismet</a>.

Requires BotScout API key which you can acquire <a href="http://www.botscout.com/getkey.htm">here</a>.

Disclaimer: This plugin is not affiliated with or endorsed by BotScout.

== Installation ==

1. Upload plugin file through the WordPress interface.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to the plugin settings page and enter your BotScout API key. Click <a href="http://www.botscout.com/getkey.htm">here</a> to get one.
4. Log out of WordPress, go to botscout.com and grab the email of a known bot, and try to post a comment on your own site. Then log in and check your comment spam queue. It will probably be cleared by Akismet but stopped by this plugin.

== Frequently Asked Questions ==

= How does this plugin work? =

Every time someone tries to leave a comment, this plugin takes their IP and email address and passes it to the BotScout API. If the API returns a 'Y' then the commenter is a known spambot. The comment is flagged as spam and put into the comment spam queue.

Note that even though BotScout is able to check name, email and IP address (3 fields), this plugin only checks IP and email (2 fields). Typically, the name field is unreliable as an indicator of a spammer.

= How do I use the plugin? =

Simply install and activate the plugin, then enter your API key on the plugin settings page. It starts working immediately to protect your comments form.

= I activated the plugin but it's not working. =

Make sure you entered your API key. Click <a href="http://www.botscout.com/getkey.htm">here</a> to get one.

Also, cURL is required. If you do not have cURL installed on your server you cannot use this plugin.

= Can I use this plugin without an API key? =

Technically, you can make 20 API calls per day to the BotScout API without a key, but this plugin requires it anyway. Its just easier than writing code to track the number of API calls, or catching API errors when the limit is exceeded. It's easy to sign up for a key.

== Screenshots ==

1. Plugin settings page

== Changelog ==

= 0.0.2 =
- switch to using email and IP instead of email and name
- added cURL check

= 0.0.1 =
- created

== Upgrade Notice ==

= 0.0.2 =
- switch to using email and IP instead of email and name; added cURL check

= 0.0.1 =
created