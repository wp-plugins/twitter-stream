<?php
/*
Plugin Name: Twitter Stream
Plugin URI: http://return-true.com/
Description: A simple Twitter plugin designed to show the provided username's Twitter updates. Includes file caching to prevent API overuse.
Version: 1.0
Author: Paul Robinson
Author URI: http://return-true.com

	Copyright (c) 2008, 2009 Paul Robinson (http://return-true.com)
	The Attached Image is released under the GNU General Public License (GPL)
	http://www.gnu.org/licenses/gpl.txt

	This is a WordPress 2 plugin (http://wordpress.org).
	Plugin is well documented for those who wish to learn.
*/

/*
	There is no CSS included with this plugin to keep it simple and in one file.
	If you wish to customize things here are the CSS commands available.
	.at-reply is for @replys, .hash-tag is for #tags, finally a.twitter-link and
	a:hover.twitter-link are for autolinked URLs within the twitter stream.
*/
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
		echo '<div class="error fade"><p><strong>You appear to be using a version of PHP lower than version 5. As noted in the description this plugin uses SimpleXML which was not available in PHP 4. Please either contact your host &amp; ask for your version of PHP to be upgraded or uninstall this plugin and consider an alternative. Sorry for the inconvenience.</strong></p></div>';
}

function twitter_stream($username, $count = "10") {
	
	$cache_path = dirname(__FILE__).'/'.$username.'.cache';
	
	//Caching is used to prevent us from hitting the 20,000 per hour TwitterAPI request limit.
	//First we need to check to see if a cache file has already been made.
	if(file_exists($cache_path)) {
		$modtime = filemtime($cache_path); //Get the time the file was last modified.
		$content = twitter_stream_cache($modtime, $cache_path); //Hand it to the cache function & get the data
		if($content !== FALSE) {
			$cache = TRUE; //Cache is still valid
		} else {
			$cache = FALSE; //Cache too old invalidate it
			unset($content); //Delete the content variable to force the script to connect to twitter & renew the cache.
		}
	} else {
		$cache = FALSE; //This is probably first run so set the cache to false so it can be created.
	}
	
	//No content is set so we either need to create the cache or it has been invalidated and we need to renew it.
	if(!isset($content)) {
		//As CURL is the preferable way to collect the info from Twitter let's assume it's installed & then check for it.
		$method = 'curl';
		if(!function_exists('curl_init')) {
			$method = 'fopen';
			//if CURL isn't installed assume fopen has URL access enabled, then check.
		}
		if(ini_get('allow_url_fopen') == '0') {
			$method = 'socket';
			//as fopen doesn't have URL access enabled drop back to custom socket access. See function twit_getRemoteFile()
		}
		
		//Set the twitter URL
		$twitter_url = 'https://twitter.com/statuses/user_timeline/'.$username.'.xml?count='.$count;

		if($method == 'curl') {
				
			//initialize a new curl resource
			$ch = curl_init();	
			//Fetch the timeline
			curl_setopt($ch, CURLOPT_URL, $twitter_url);
			//do it via GET
			curl_setopt($ch, CURLOPT_GET, 1);
			//Live on the edge & trust Twitters SSL Certificate
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			//Give me the data back as a string... Don't echo it.
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
			//Warp 9, Engage!
			$content = curl_exec($ch);
			//Close CURL connection & free the used memory.
			curl_close($ch); 		
		
		} elseif($method == 'fopen') {
					
			//Now let's get the twitter stream
			$content = file_get_contents($twitter_url, FALSE, $ctx);
		
		} elseif($method == 'socket') {
				
			//Run the custom socket function to get the twitter stream
			$content = twitter_stream_getRemoteFile($twitter_url);
		
		}
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
	
	//Convert the string recieved from twitter into a simple XML object.
	@$twitxml = simplexml_load_string($content); //Supress errors as we check for any next anyway.
	if($twitxml === FALSE) {
		//Twitter was unable to provide the stream requested. Let's notify the user.
		echo '<p>Your Twitter stream could not be collected. The most probable reason is that Twitter\'s API is unavailable or your website has exceeded the API limits imposed by Twitter.</p>';
		return FALSE;
	}
	$output = ''; //Create a blank string for concatenation
	
	//For each status update loop through
	foreach($twitxml->status as $tweet) {
		$output .= "<p>".$tweet->text."</p>";	//Concat it's text to the variable inside paragraph's for neatness.
	}
	
	//Now let's do some highlighting & auto linking.
	//Find all the @replys and place them in a span so CSS can be used to highlight them.
	$output = preg_replace('~(\@[a-z0-9_]+)~is', '<span class="at-reply">$1</span>', $output);
	//Find all the #tags and place them in a span so CSS can be used to highlight them.
	$output = preg_replace('~(\#[a-z0-9_]+)~is', '<span class="hash-tag">$1</span>', $output);
	//Find all URL's mentioned and store them in $matches.            
    $pattern = "/(http:\/\/|https:\/\/)?(?(1)(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}|([-a-z0-9_]+\.)?[a-z][-a-z0-9]+\.[a-z]+(\.[a-z]{2,2})?)|(www\.[a-z][-a-z0-9]+\.[a-z]+(\.[a-z]{2,2})?))\/?[a-z0-9._\/~#&=;%+?-]+[a-z0-9\/#=?]{1,1}/is";
   	$out_count = preg_match_all($pattern, $output, $matches);
	//If there were any matches
	if($out_count > 0) {
		//Loop through all the full matches
		foreach($matches[0] as $match) {
			//Use a simple string replace to replace each URL with a HTML <a href>.
			$output = str_replace($match, '<a href="'.$match.'" target="_blank" class="twitter-link">'.$match.'</a>', $output);	
		}
	}
	
	
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

//Custom socket function... Just in case.
function twitter_stream_getRemoteFile($url, $auth = FALSE) {
   // get the host name and url path
   $parsedUrl = parse_url($url);
   $host = $parsedUrl['host'];
   if (isset($parsedUrl['path'])) {
      $path = $parsedUrl['path'];
   } else {
      // the url is pointing to the host like http://www.mysite.com
      $path = '/';
   }

   if (isset($parsedUrl['query'])) {
      $path .= '?' . $parsedUrl['query'];
   }

   if (isset($parsedUrl['port'])) {
      $port = $parsedUrl['port'];
   } else {
      // most sites use port 80
      $port = '80';
   }

   $timeout = 10;
   $response = '';

   // connect to the remote server
   $fp = @fsockopen($host, '80', $errno, $errstr, $timeout );
   //Base encode the username & password for basic auth
   //$auth = base64_encode($auth['username'].':'.$auth['password']);
   if( !$fp ) {
      return FALSE;
   } else {
	   
      // send the necessary headers for Twitter to accept our connection & authorize it.
      fputs($fp, "GET $path HTTP/1.0\r\n" .
                 "Host: $host\r\n" .
                 "User-Agent: Mozilla/5.0 (Windows; U; Windows NT 6.1; en-GB; rv:1.9.1.6) Gecko/20091201 Firefox/3.5.6\r\n" .
                 "Accept: */*\r\n" .
                 "Accept-Language: en-us,en;q=0.5\r\n" .
                 "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7\r\n" .
                 "Keep-Alive: 300\r\n" .
				 //"Authorization: Basic {$auth}\r\n" .
                 "Connection: keep-alive\r\n" .
                 "Referer: http://$host\r\n\r\n");

      // retrieve the response from the remote server
      while ( $line = fread( $fp, 4096 ) ) {
         $response .= $line;
      }

      fclose( $fp );

      // strip the headers
      $pos      = strpos($response, "\r\n\r\n");
      $response = substr($response, $pos + 4);
   }

   // return the file content
   return $response;
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
				$instance['username'] = 'veneficusunus';
			?>
				  <?php echo $before_widget; ?>
					  <?php echo $before_title . $instance['title'] . $after_title; ?>
	 
						  <?php twitter_stream($instance['username'], $instance['count']); ?>
	 
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
			?>
				<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
                <p><label for="<?php echo $this->get_field_id('username'); ?>"><?php _e('Twitter Username:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('username'); ?>" name="<?php echo $this->get_field_name('username'); ?>" type="text" value="<?php echo $username; ?>" /></label></p>
				<p><label for="<?php echo $this->get_field_id('count'); ?>"><?php _e('How many Zazzle update to show:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" type="text" value="<?php echo $count; ?>" /></label></p>
			<?php 
	
		}
	}
	
	add_action('widgets_init', create_function('', 'return register_widget("TwitterStreamWidget");'));

}


?>