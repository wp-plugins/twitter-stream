=== Twitter Stream ===
Contributors: veneficusunus
Donate link: http://return-true.com/donations/
Tags: twitter
Requires at least: 2.8
Tested up to: 2.9
Stable tag: 1.2

Twitter Stream is a very simple Twitter plugin designed to show a users Twitter timeline. Also includes file caching to stop API overuse.

== Description ==

Twitter Stream is a simple plugin designed to simply show a users Twitter timeline. It includes file caching to stop overuse of Twitter's API, and three different connection types (CURL, fopen, socket). You can also choose how many updates to return (maximum of 200). It also includes autolinking for URL's found within the timeline. Also includes a date ago feature, showing the time the tweet was posted in xx ago format. Also has a permalink pointing to the tweet.

A widget is included, but you must have WordPress version 2.8 or higher for it to work, however standard useage should work down to version 2.5 although it has not been tested.

Twitter Stream requires PHP5 due to the use of SimpleXML. If you do not have PHP5 installed you will not be able to use this plugin.

Twitter Stream is also designed to be very lightweight & use the smallest amount of resources possible, ideal for shared or low memory servers.

Here is a quick run down of the features available in Twitter Stream.


1. Show the twitter timeline for any public username.
1. Choose how many tweets to show.
1. A Widget or template function is available.
1. File caching to stop API overuse.
1. Optional date shown in xx ago format, also links to permalink for the tweet. (Requested by Ron)
1. Customizeable via CSS. (see 'Can I Style It?' in the FAQ)
1. Authentication for more accurate API counting & so protected users can show their tweets.


== Installation ==

Download & install via the WordPress plugin repository in the admin of your blog, or you can do it the manual way as follows:

1. Unzip the zip file.
1. place the folder into the `wp-content/plugins` folder.
1. Place `<?php twitter_stream('username', '10'); ?>` in your template. 'Username' must be the Twitter username for the timeline you wish to show & '10' is the amount of tweets to show, or
1. If you wish you may use the Widget instead. Go to appearance, click widgets & drag it to a widgetized area of your choice & fill in the two fields required.

== Frequently Asked Questions ==

= Can I Style It? =
You can. I haven't added any styles so I could keep the plugin on one file & keep it free of clutter. The available CSS classes are:

1. .at-reply for @replys.
1. .hash-tag for #tags.
1. a.twitter-link for autolinked URL's within the timeline.
1. a:hover.twitter-link for autolinked URL's within the timeline when they are hovered over.
1. a.twitter-date</code> for the date permalink.
1. a:hover.twitter-date</code> for the date permalink when it's hovered over.

= I Have Some More Questions! =
To make it easier for me to answer questions & to keep everything in one place, please go to the [blog post](http://return-true.com/2009/12/wordpress-plugin-twitter-stream/ "Check here for answers to any questions.") for Twitter Stream on my website. If you have any requests or problems please leave a comment there or drop me an email via the contact form also available there. Thanks.

== Changelog ==

= 1.2 =
* Added user authentication & more advanced error checking.

= 1.1 =
* Added date ago feature. Also added some minor fixes including PHP version checking & Username checking.

= 1.0 =
* Initial Release.