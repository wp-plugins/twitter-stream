<?php
/*
Plugin Name: Twitter Stream
Plugin URI: http://return-true.com/
Description: A simple Twitter plugin designed to show the provided username's Twitter updates. Includes file caching to prevent API overuse.
Version: 2.0.1
Author: Paul Robinson
Author URI: http://return-true.com

	Copyright (c) 2009, 2010 Paul Robinson (http://return-true.com)
	Twitter Stream is released under the GNU General Public License (GPL)
	http://www.gnu.org/licenses/gpl.txt

	This is a WordPress 2 plugin (http://wordpress.org).
	Plugin is well documented for those who wish to learn.
*/

/*
	There is no CSS included with this plugin to keep it simple and in one file.
	If you wish to customize things here are the CSS commands available.
	.at-reply is for @replys, .hash-tag is for #tags, finally a.twitter-link and
	a:hover.twitter-link are for autolinked URLs within the twitter stream.
	a.twitter-date & a:hover.twitter-date is for the date's permalink.
*/

//Setup oAuth data such as Twitter Streams Consumer Key etc.
//define('CONSUMER_KEY', 'NOATAREALKEY');
//define('CONSUMER_SECRET', 'NOTAREALSECRETEITHER');
//define('OAUTH_CALLBACK', 'http://' . $_SERVER['HTTP_HOST'] . preg_replace('/&wptwit-page=[^&]*/', '', $_SERVER['REQUEST_URI']) . '&wptwit-page=callback');

//include TwitteroAuthFile
//require_once('twitteroauth/twitteroauth.php');

//If we are authenticating execute the redirection to Twitter...
//if(isset($_GET['wptwit-page']) && $_GET['wptwit-page'] == 'redirect') {
//	require_once('redirect.php'); //Load redirect to auth
//} elseif(isset($_GET['wptwit-page']) && $_GET['wptwit-page'] == 'callback') {
//	require_once('callback.php'); //Load callback to create tokens
//}

//Add our new page so users can authorize the plugin with Twitter... Yes, a page for 1 button...
//add_action('admin_menu', 'twitter_stream_add_options');
//add our page to the settings sub menu
//function twitter_stream_add_options() {
//	add_options_page('Twitter Stream Authorize Page', 'Twitter Stream', 8, 'twitterstreamauth', 'twitter_stream_options_page');	
//}

//Create the page...
function twitter_stream_options_page() {
?>
<div class="wrap">
   	<h2><?php _e( 'Twitter Stream Authorize Page', 'twit_sream' ); ?></h2>
   	Created by <strong>Paul Robinson</strong>.
	<small style="margin-bottom: 25px; display: block;">Confused? Unsure what to do? Check the <a href="http://return-true.com/2009/12/wordpress-plugin-twitter-stream/">documentation</a></small>
	<?php
	//Have we already been authorized?
	$token = get_option('twitter_stream_token');
	if(!is_array($token) && !isset($token['oauth_token'])) {
	?>
		<p>Due to the discontinuation of basic authentication by Twitter you now have to authenticate Twitter Stream with your Twitter account. This just means you must give Twitter Stream <strong>Read-Only</strong> permission to access your tweets. To do this click the 'Sign in with Twitter' button just below and follow the instructions. You may notice the name given for the application is 'Twit Stream' instead of 'Twitter Stream'. This is because Twitter do not allow applications to be registered with Twitter in their name... Slightly annoying, but very true.</p>
		<div style="margin: 15px 0 0 0;"><a href="<?php echo preg_replace('/&wptwit-page=[^&]*/', '', $_SERVER['REQUEST_URI']) . '&wptwit-page=redirect'; ?>"><img src="<?php echo WP_PLUGIN_URL; ?>/twitter-stream/darker.png" alt="Sign in with Twitter"/></a></div>
	<?php
	} else {
	?>
		<p>You have authorized Twitter Stream. Great job. ;)</p>
		<p>If you ever wish to revoke Twitter Stream's access to your twitter account just go to <a href="http://twitter.com">Twitter</a>, login, then go to <strong>settings->connections</strong>. Find 'Twit Stream' and press 'Revoke Access'. Remember that doing this will obviously stop Twitter Stream from working.</p>
	<?php
	}
	?>
	<p><small>Huge thanks to <a href="http://twitteroauth.labs.poseurtech.com/">Abraham Williams</a> for creating TwitterOAuth which is used to connect Twitter Stream to Twitter via oAuth.</small></p>
</div>
<?php
}

//Init for translations
add_action( 'init', 'twitter_stream_init' );

// Initialize the text domain for translations.
function twitter_stream_init() {
	
	$plugin_dir = basename(dirname(__FILE__));
	load_plugin_textdomain( 'twit_stream', 'wp-content/plugins/' . $plugin_dir, $plugin_dir );
	
}

//Setup the notice just in case a PHP version less than 5 is installed.
add_action('admin_head', 'twitter_stream_activation_notice');

//Check the version of PHP using the version constant. If the version is less than 5 run the notification.
function twitter_stream_activation_notice() {
	
	if(version_compare(PHP_VERSION, '5.0.0', '<')) {
		add_action('admin_notices', 'twitter_stream_show_notice');
	}
		
}

//Define the notification function for the check above. Advise the user to upgrade their PHP version or uninstall & consider an alternative plugin.
function twitter_stream_show_notice() {
		echo '<div class="error fade"><p><strong>';
		_e('You appear to be using a version of PHP lower than version 5. As noted in the description this plugin uses SimpleXML which was not available in PHP 4. Please either contact your host &amp; ask for your version of PHP to be upgraded or uninstall this plugin and consider an alternative. Sorry for the inconvenience.', 'twit_stream');
		echo '</strong></p></div>';
}

//Shell for new array/query string argument system... Redundant since intro of oAuth...
function twitter_stream_args($args) {
	twitter_stream($args);
}
//Old system, kept for backwards compat...
function twitter_stream($args = FALSE) {

	if(is_array($args)) { //Is it an array?
		$r = &$args; //Good, reference out arguments into our options array.
	} else {
		parse_str($args, $r); //It's a query string, parse out our values into our options array.
	}
	
	if(!isset($r)) {
		$r = array();
	}
	
	$defaults = array( //Set some defaults
					'username' => '',
					'count' => '10',
					'date' => FALSE,
					'profile_link' => 'Visit My Profile',
					'retweets' => 'FALSE',
					'show_followers' => FALSE
					);
					
	$r = array_merge($defaults, $r); //Merge our defaults array onto our options array to fill in any missing values with defaults.
	
	if($r['retweets'] == "on")
		$r['retweets'] = "true";

	if(version_compare(PHP_VERSION, '5.0.0', '<')) { //Checked before hand, but if the user didn't listen tell them off & refuse to run.
		_e('You must have PHP5 or higher for this plugin to work.', 'twit_stream');
		return FALSE;
	}
	if(empty($r['username'])) {
		_e('You must provide a username', 'twit_stream'); //Must have a username our it's pointless even trying to run.
		return FALSE;
	}
	
	$cache_path = dirname(__FILE__).'/'.$r['username'].'.cache'; //Set our cache path. Can be changed if you feel the need.
	
	//Caching is used to help prevent us from hitting the 150 per hour (20,000 if whilelisted) TwitterAPI request limit.
	//Being on a shared server can negate the effects of caching, but it still helps you not get blacklisted.
	//First we need to check to see if a cache file has already been made.
	if(file_exists($cache_path)) {
		$modtime = filemtime($cache_path); //Get the time the file was last modified.
		$content = twitter_stream_cache($modtime, $cache_path); //Hand it to the cache function & get the data
		if($content !== FALSE) {
			$cache = TRUE; //Cache is still valid
		} else {
			$cache = FALSE; //Cache too old invalidate it
			unset($content); //Delete the content variable to force the script to connect to twitter & renew the cache.
			if( function_exists('wp_cache_clear_cache') ) {
				wp_cache_clear_cache();
            } elseif ( function_exists('prune_super_cache') ) {
                prune_super_cache(WP_CONTENT_DIR.'/cache/', true );
            }
		}
	} else {
		$cache = FALSE; //This is probably first run so set the cache to false so it can be created.
		if( function_exists('wp_cache_clear_cache') ) {
			wp_cache_clear_cache();
        } elseif ( function_exists('prune_super_cache') ) {
            prune_super_cache(WP_CONTENT_DIR.'/cache/', true );
        }
	}
	
	//No content is set so we either need to create the cache or it has been invalidated and we need to renew it.
	if(!isset($content)) {
		/* Get user access tokens out of the session. */
		//$access_token = get_option('twitter_stream_token');
		//if(empty($access_token) || $access_token === FALSE) {
		//	_e('You need to go to the Twitter Stream Authorization page in the WordPress Admin (under settings) before I can show your tweets');
		//}
		/* Create a TwitterOauth object with consumer/user tokens. */
		//$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);
		//$content = $connection->get('statuses/user_timeline', array('screen_name' => $r['username'], 'count' => $r['count'], 'include_rts' => $r['retweets']));
		$content = twitter_stream_connect('http://api.twitter.com/1/statuses/user_timeline.xml?screen_name='.$r['username'].'&count='.$r['count'].'&include_rts='.$r['retweets'], false);
	}
	
	if($cache === FALSE) {
		//If cache was set to false we need to update the cache;
		$fp = fopen($cache_path, 'w');
		if(flock($fp, LOCK_EX)) {
			fwrite($fp, $content);
			flock($fp, LOCK_UN);	
		}
		fclose($fp);
	}
	
	$tweetfollow = twitter_stream_parse_tweets($content, $r);
	
	$output = $tweetfollow[0];
	
	//Now let's do some highlighting & auto linking.
	//Find all the @replies and place them in a span so CSS can be used to highlight them.
	$output = preg_replace('~(\@[a-z0-9_]+)~ise', "'<span class=\"at-reply\"><a href=\"http://twitter.com/'.substr('$1', 1).'\" title=\"View '.substr('$1', 1).'\'s profile\">$1</a></span>'", $output);
	//Find all the #tags and place them in a span so CSS can be used to highlight them.
	$output = preg_replace('~(\#[a-z0-9_]+)~ise', "'<span class=\"hash-tag\"><a href=\"http://twitter.com/search?q='.urlencode('$1').'\" title=\"Search for $1 on Twitter\">$1</a></span>'", $output);
	
	//Show follower count
	if($r['show_followers']) {
		$output .= '<div class="follower-count">'.$tweetfollow[1].' followers</div>';
	}
	
	//Link to users profile. Can be customized via the profile_link parameter & via CSS targeting.
	$output .= '<div class="profile-link"><a href="http://twitter.com/'.$r['username'].'" title="'.$r['profile_link'].'">'.$r['profile_link'].'</a></div>';
	
	
	echo '<div class="twitter-stream">'.$output.'</div>';
	
}

function twitter_stream_cache($modtime, $cache_path) {
	
	$thirtyago = time() - 1800; //the timestamp thirty minutes ago
	
	if($modtime < $thirtyago) {
		//our cache is older than 30 minutes return FALSE so the script will run the cache updater.
		return FALSE;
	}
	
	//We have already checked that the file exists. So we can assume it exsits here.
	$data = file_get_contents($cache_path);
	
	if($data !== FALSE) {
		return $data; //return our data if there wasn't a problem
	}
	
}

//parse tweets
function twitter_stream_parse_tweets($content, $r) {
				
	$twitxml = twitter_stream_convert_to_xml($content);
	if($twitxml === FALSE || isset($twitxml->error)) {
		return FALSE;
	}
	if(empty($twitxml)) {
		return FALSE;
	}
	$followers = $twitxml->status[0]->user->followers_count;
	$o = '';
	foreach($twitxml->status as $tweet) {
	
		//Find all URL's mentioned and store them in $matches. 
		$pattern = "/(http:\/\/|https:\/\/)?(?(1)(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}|([-a-z0-9_]+\.)?[a-z][-a-z0-9]+\.[a-z]+(\.[a-z]{2,2})?)|(www\.[a-z][-a-z0-9]+\.[a-z]+(\.[a-z]{2,2})?))\/?[a-z0-9._\/~#&=;%+?-]+[a-z0-9\/#=?]{1,1}/is";
		$out_count = preg_match_all($pattern, $tweet->text, $matches);
		
		//If there were any matches
		if($out_count > 0) {
			//Loop through all the full matches
			foreach($matches[0] as $match) {
				//Use a simple string replace to replace each URL with a HTML <a href>.
				$tweet->text = str_replace($match, '<a href="'.$match.'" target="_blank" class="twitter-link">'.$match.'</a>', $tweet->text);	
			}
		}
					
		$o .= "<p>".$tweet->text;
		
		if($r['date'] !== FALSE) {
			$tweet->created_at = strtotime($tweet->created_at);
				
			if($r['date'] === TRUE || $r['date'] == 'true' || $r['date'] == 'TRUE' || $r['date'] == '1') {
				$o .= ' - ';
			} else {
				$r['date'] = trim($r['date']);
				$o .= " {$r['date']} ";	
			}
			$o .= "<a href=\"http://twitter.com/{$r['username']}/statuses/{$tweet->id}/\" title=\"Permalink to this tweet\" target=\"_blank\" class=\"twitter-date\">".twitter_stream_time_ago($tweet->created_at)."</a>";
		}
				
		$o .= "</p>";
			
	}
		
	return array($o,$followers);

}

function twitter_stream_connect($twitter_url, $auth = FALSE) {
	
	if($auth === FALSE) {
		unset($auth);
	}

	$method = 'curl';
	if(!function_exists('curl_init')) {
		$method = 'fopen';
		//if CURL isn't installed assume fopen has URL access enabled, then check.
	}
	if(ini_get('allow_url_fopen') == '0') {
		$method = 'socket';
		//as fopen doesn't have URL access enabled drop back to custom socket access. See function twit_getRemoteFile()
	}

	if($method == 'curl') {
			
		//initialize a new curl resource
		$ch = curl_init();	
		//Fetch the timeline
		curl_setopt($ch, CURLOPT_URL, $twitter_url);
		//For Debug purposes turn this to 1, delete cache & echo the contents before parsed by SimpleXML.
		curl_setopt($ch, CURLOPT_HEADER, 0);
		if(isset($auth)) {
			//Authentication
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			//Set user and pass
			curl_setopt($ch, CURLOPT_USERPWD, "{$auth['username']}:{$auth['password']}");
		}
		//Live on the edge & trust Twitters SSL Certificate
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		//Give me the data back as a string... Don't echo it.
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		//Warp 9, Engage!
		$content = curl_exec($ch);
		//Close CURL connection & free the used memory.
		curl_close($ch); 
		
		//Check for failure. If cURL failed report to the user.
		if($content === FALSE) {
			echo '<p>';
			_e('cURL failed to retrieve any results.', 'twit_stream');
			echo '</p>';
			return FALSE;
		}
	
	} elseif($method == 'fopen') {
		
		if(isset($auth)) {
		
			$ctx = stream_context_create(array(
    											'http' => array(
        										'header'  => "Authorization: Basic " . base64_encode("{$auth['username']}:{$auth['password']}")
    													)
												)
						);
		}
		//Now let's get the twitter stream
		$content = file_get_contents($twitter_url, FALSE, $ctx);
		
		//Check for failure. If fopen failed report to the user.
		if($content === FALSE) {
			echo '<p>';
			_e('fopen failed to retrieve any results.', 'twit_stream');
			echo '</p>';
			return FALSE;
		}
	
	} elseif($method == 'socket') {
		
		if(!isset($auth)) {
			$auth = FALSE;	
		}
		
		//Run the custom socket function to get the twitter stream
		$content = twitter_stream_getRemoteFile($twitter_url, $auth);
	
	}
	
	return $content;
}



function twitter_stream_convert_to_xml($content) {

	//Some sort of strange fix for unterminated entities in XML. Possibly related to PHP bug #36795.
	$content = str_replace('&amp;', '&amp;amp;', $content);
	//Convert the string recieved from twitter into a simple XML object.
	$twitxml = simplexml_load_string($content); //Supress errors as we check for any next anyway.
	
	if($twitxml === FALSE) {
		//Twitter was unable to provide the stream requested. Let's notify the user.
		echo '<p>';
		_e('Your Twitter stream could not be collected. Normally this is caused by no XML feed being returned. Why this happens is still unclear.', 'twit_stream');
		echo '</p>';
		return FALSE;
	}
	if(isset($twitxml->error)) {
		//Check for an error such as API overuse and display it.
		echo '<p>'.$twitxml->error.'</p>';
		return FALSE;
	}
	
	return $twitxml;

}

//Work out the time in the AGO tense. Thanks to http://css-tricks.com for this snippet...
function twitter_stream_time_ago($time)
{
   $singular = array(__("second", 'twit_stream'), __("minute", 'twit_stream'), __("hour", 'twit_stream'), __("day", 'twit_stream'), __("week", 'twit_stream'), __("month", 'twit_stream'), __("year", 'twit_stream'), __("decade", 'twit_stream'));
   $plural = array(__("seconds", 'twit_stream'), __("minutes", 'twit_stream'), __("hours", 'twit_stream'), __("days", 'twit_stream'), __("weeks", 'twit_stream'), __("months", 'twit_stream'), __("years", 'twit_stream'), __("decades", 'twit_stream'));
   $lengths = array("60","60","24","7","4.35","12","10");

   $now = time();

       $difference     = $now - $time;
       $tense         = __("ago", 'twit_stream');

   for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
       $difference /= $lengths[$j];
   }

   $difference = round($difference);

   if($difference != 1) {
	    $period = $plural[$j];
   } else {
		$period = $singular[$j];    
   }
   
   //French translation fix
   if(strcasecmp(get_bloginfo('language'), 'fr-FR') === 0) {
    return "{$tense} {$difference} {$period}";
   } else {
	return "{$difference} {$period} {$tense}";
   }
}


//For the widget to work you must have WP 2.8 or higher.
if(get_bloginfo('version') >= '2.8') {

	class TwitterStreamWidget extends WP_Widget {
	 
		function TwitterStreamWidget() {
			parent::WP_Widget(FALSE, $name = 'Twitter Stream');    
		}
	 
		function widget($args, $instance) {        
			extract( $args );
			if(empty($instance['count']))
				$instance['count'] = 10;
			if(empty($instance['username']))
				$instance['username'] = '';
			if(empty($instance['date']))
				$instance['date'] = FALSE;
			if(empty($instance['profile_link']))
				$instance['profile_link'] = 'Visit My Profile';
			if(empty($instance['retweets']))
				$instance['retweets'] = FALSE;
			if(empty($instance['show_followers']))
				$instance['show_followers'] = FALSE;
			?>
				  <?php echo $before_widget; ?>
					  <?php echo $before_title . $instance['title'] . $after_title; ?>
	 					
						  <?php 
						  unset($instance['title']);
						  twitter_stream_args($instance); 
						  
						  ?>
	 
				  <?php echo $after_widget; ?>
			<?php
	
		}
	 
	
		function update($new_instance, $old_instance) {                
			return $new_instance;
		}
	 
		function form($instance) {                
			$title = esc_attr($instance['title']);
			$username = esc_attr($instance['username']);
			$count = esc_attr($instance['count']);
			$date = esc_attr($instance['date']);
			$profile_link = esc_attr($instance['profile_link']);
			$retweets = esc_attr($instance['retweets']);
			$show_followers = esc_attr($instance['show_followers']);
			?>
				<p>
                  <label for="<?php echo $this->get_field_id('title'); ?>">
				    <?php _e('Title:', 'twit_stream'); ?>
                    <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
                  </label>
                </p>
                <p>
                  <label for="<?php echo $this->get_field_id('username'); ?>">
				    <?php _e('Twitter Username:', 'twit_stream'); ?>
                  <input class="widefat" id="<?php echo $this->get_field_id('username'); ?>" name="<?php echo $this->get_field_name('username'); ?>" type="text" value="<?php echo $username; ?>" /></label>
                </p>
				<p>
                  <label for="<?php echo $this->get_field_id('count'); ?>">
				    <?php _e('How Many Twitter Updates To Show:', 'twit_stream'); ?>
                    <input class="widefat" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" type="text" value="<?php echo $count; ?>" />
                  </label>
                </p>
                <p>
                  <label for="<?php echo $this->get_field_id('date'); ?>">
				    <?php _e('Show The Date:', 'twit_stream'); ?>
                    <br />
                    <small>
					  <?php _e('(Leave blank to turn off, type a separator, true or 1 will show the date)', 'twit_stream'); ?>
                    </small>
                    <input class="widefat" id="<?php echo $this->get_field_id('date'); ?>" name="<?php echo $this->get_field_name('date'); ?>" type="text" value="<?php echo $date; ?>" />
                  </label>
                </p>
                <p>
                  <label for="<?php echo $this->get_field_id('profile_link'); ?>">
				    <?php _e('Profile Link Text:', 'twit_stream'); ?>
                    <br />
                    <small>
					  <?php _e('(What the link to your Twitter profile should say)', 'twit_stream'); ?>
                    </small>
                    <input class="widefat" id="<?php echo $this->get_field_id('profile_link'); ?>" name="<?php echo $this->get_field_name('profile_link'); ?>" type="text" value="<?php echo $profile_link; ?>" />
                  </label>
                </p>
				<p>
                  <label for="<?php echo $this->get_field_id('retweets'); ?>">
				    <?php _e('Show Retweets:', 'twit_stream'); ?>
                    <br />
                    <small>
					  <?php _e('(Warning: Uses 2 API requests.)', 'twit_stream'); ?>
                    </small>
                    <input class="widefat" id="<?php echo $this->get_field_id('retweets'); ?>" name="<?php echo $this->get_field_name('retweets'); ?>" type="checkbox" <?php if($retweets == TRUE) echo 'checked="checked"'; ?> />
                  </label>
                </p>
				<p>
                  <label for="<?php echo $this->get_field_id('show_followers'); ?>">
				    <?php _e('Show Followers:', 'twit_stream'); ?>
                    <br />
                    <small>
					  <?php _e('(Shows your follower count.)', 'twit_stream'); ?>
                    </small>
                    <input class="widefat" id="<?php echo $this->get_field_id('show_followers'); ?>" name="<?php echo $this->get_field_name('show_followers'); ?>" type="checkbox" <?php if($show_followers == TRUE) echo 'checked="checked"'; ?> />
                  </label>
                </p>
			<?php 
	
		}
	}
	
	add_action('widgets_init', create_function('', 'return register_widget("TwitterStreamWidget");'));

}


?>