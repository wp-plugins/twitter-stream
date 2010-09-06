=== Twitter Stream ===
Contributors: veneficusunus
Donate link: http://return-true.com/donations/
Tags: twitter
Requires at least: 2.8
Tested up to: 3.0.1
Stable tag: 2.1.1

Twitter Stream is a very simple Twitter plugin designed to show a users Twitter timeline. Also includes file caching to stop API overuse.

== Description ==

**IMPORTANT** due to an oversight on Twitters part all features that require oAuth are suspended until Twitter figure out a way to authenticate plugins without making your API keys public... **Do Not Worry** the plugin should work as normal showing tweets, retweets etc. The only thing that will not work is if your twitter account is private as it cannot log in to authenticate you.

Twitter Stream is a simple plugin designed to simply show a users Twitter timeline. It includes file caching to stop overuse of Twitter's API. You can also choose how many updates to return (maximum of 200). It also includes autolinking for URL's found within the timeline. Also includes a date ago feature, showing the time the tweet was posted in xx ago format. Also has a permalink pointing to the tweet.

A widget is included, but you must have WordPress version 2.8 or higher for it to work, however standard useage should work down to version 2.5 although it has not been tested.

Twitter Stream requires PHP5 due to the use of SimpleXML. If you do not have PHP5 installed you will not be able to use this plugin.

Twitter Stream is also designed to be very lightweight & use the smallest amount of resources possible, ideal for shared or low memory servers.

Here is a quick run down of the features available in Twitter Stream.

1. Show the twitter timeline for any public username.
1. Uses oAuth to connect to Twitter without ever using your password.
1. Choose how many tweets to show.
1. A Widget or template function is available.
1. File caching to stop API overuse.
1. Optional date shown in xx ago format, also links to permalink for the tweet. (Requested by Ron)
1. Customizeable via CSS. (see 'Can I Style It?' in the FAQ)
1. Authentication for more accurate API counting & so protected users can show their tweets.
1. Translation files for different languages are now available to download on my [blog post](http://return-true.com/2009/12/wordpress-plugin-twitter-stream/ "Check here for translation files.").
1. @replies now link to the user profile of the user you are replying to.
1. #tags now link to the Twitter search page for that hash tag.
1. Link to user's profile, customizable via CSS & via function parameter.
1. Optional display of follower count.
1. Retweets can now be shown. Used 2 API requests due to Twitter API limitations.

A big thank you to all the people who have translated Twitter Stream into different languages.

1. [Fatcow](http://www.fatcow.com/)
1. [Tolingo.com](http://tolingo.com)
1. [Albert Johansson](http://twitter.com/albertjohansson)


== Installation ==

Download & install via the WordPress plugin repository in the admin of your blog, or you can do it the manual way as follows:

1. Unzip the zip file.
1. place the folder into the `wp-content/plugins` folder.
1. To use the Widget. Go to appearance, click widgets & drag it to a widgetized area of your choice & fill in the two fields required.
1. Go [here](http://return-true.com/2009/12/wordpress-plugin-twitter-stream/ "PHP function call") for info on how to use Twitter Stream in your template.

== Frequently Asked Questions ==

= Can I Style It? =
You can. I haven't added any styles so I could keep the plugin on one file & keep it free of clutter. The available CSS classes are:

1. .at-reply for @replys.
1. .hash-tag for #tags.
1. a.twitter-link for autolinked URL's within the timeline.
1. a:hover.twitter-link for autolinked URL's within the timeline when they are hovered over.
1. a.twitter-date</code> for the date permalink.
1. a:hover.twitter-date</code> for the date permalink when it's hovered over.
1. .profile-link for the newly added link to user profile.
1. .follower-count for the newly added follower count.

= I Have Some More Questions! =
To make it easier for me to answer questions & to keep everything in one place, please go to the [blog post](http://return-true.com/2009/12/wordpress-plugin-twitter-stream/ "Check here for answers to any questions.") for Twitter Stream on my website. If you have any requests or problems please leave a comment there or drop me an email via the contact form also available there. Thanks.

== Changelog ==

= 2.1.1 =
* Correct an annoying SVN problem now have to create new version number to force update in WP admin for users of plugin.

= 2.1 =
* Reintroduced oAuth using user application registration method for consumer keys...

= 2.0.2 =
* Fixed cURL bug. curlopt_get is not needed as is set to get by default & it is curlopt_httpget...

= 2.0.1 =
* Removed oAuth until key exchange system does not make keys public...

= 2.0 =
* Version number change

= 2.0-beta =
* Changed authentication system to oAuth & made general improvements to the entire code.

= 1.9.6 =
* Once more, trying to fix ugly errors.

= 1.9.5 =
* Tried to fix ugly errors.

= 1.9.4 =
* Added TRUE to array_slice() to preserve the keys. How my localhost was preserving them without this option I'll never know. Thanks to Bryon Powell for the heads up.

= 1.9.3 =
* fixed an exceptionally stupid spelling mistake in a variable effecting the widgets. Thanks to fruityoaty for spotting the bug.

= 1.9.2 =
* Added back support for parameters, please use twitter_stream_args for array/query string support.

= 1.9.1 =
* Minor bug fix. Showed more tweets than specified count.

= 1.9 =
* Stopped function parameter support, now only support array or query based options. Also added follower count & retweet support.

= 1.8 =
* fixed some small errors.

= 1.7 =
* Fixed stupid widget HTML error that caused WP's text to go tiny.

= 1.6 =
* Added i18n pluralization support for xx ago time system.

= 1.5 =
* Added i18n support & Swedish .po file.

= 1.4 =
* Added a bug fix for some strange PHP bug. Bug number #36795...

= 1.3 =
* Added htmlentities() for escaping special characters such as ampersands etc..

= 1.2 =
* Added user authentication & more advanced error checking.

= 1.1 =
* Added date ago feature. Also added some minor fixes including PHP version checking & Username checking.

= 1.0 =
* Initial Release.