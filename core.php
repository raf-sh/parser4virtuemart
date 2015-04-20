<?php
function request($url,$post = 0){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url ); // url
	curl_setopt($ch, CURLOPT_HEADER, 0); // empty headers
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // follow redirects 
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);// timeout
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)');
	curl_setopt($ch, CURLOPT_COOKIEJAR, dirname(__FILE__).'/cookie.txt'); // file to save cookies
	curl_setopt($ch, CURLOPT_COOKIEFILE, dirname(__FILE__).'/cookie.txt');
	curl_setopt($ch, CURLOPT_POST, $post!==0 ); // use post
	if($post)
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}

function outputVar($mixed) {
	echo '<pre>'.print_r($mixed,1).'</pre>';
}
?>