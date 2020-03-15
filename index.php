<?php
require './simple-cache.php';
header('Content-type: application/json; charset=utf-8');

if (!empty($_GET['username'])) {
	$user = $_GET['username'];
	
	$cacheFolder = 'ig-cache';
	if (!file_exists($cacheFolder)) {
		mkdir($cacheFolder, 0777, true);
	}
	
	//fetch Instagram raw content
	$cache = new Gilbitron\Util\SimpleCache();
	$cache->cache_path = $cacheFolder . '/';
	$cache->cache_time = 3600;
	$scraped_website = $cache->get_data("user-$user", "https://www.instagram.com/$user/");
	$document = new DOMDocument();
	$document->loadHTML($scraped_website);
	$selector = new DOMXPath($document);
	$anchors = $selector->query('/html/body//script');
	$text = $anchors[0]->nodeValue;
	preg_match('/window._sharedData = {(.*?)};/', $text, $matches);
	
	$json = json_decode('{' . $matches[1] . '}', true);
	
	//fetching basic info
	$followers = $json['entry_data']['ProfilePage'][0]['graphql']['user']['edge_followed_by']['count'];
	$following = $json['entry_data']['ProfilePage'][0]['graphql']['user']['edge_follow']['count'];
	$totalPost = $json['entry_data']['ProfilePage'][0]['graphql']['user']['edge_owner_to_timeline_media']['count'];
	
	//fetching post and put into array
	$posts = $json['entry_data']['ProfilePage'][0]['graphql']['user']['edge_owner_to_timeline_media']['edges'];
	if (!is_null($followers)) {
		if (!empty($posts)) {
			$postEntries = array();
			
			//limit to only 9 post
			$limit = count($posts);
			if (count($posts) > 9) $limit = 9;
			
			//fetch from newest post
			for ($i = 0; $i < $limit; $i++) {
				$post = $posts[$i];
				$caption = trim(($post['node']['edge_media_to_caption']['edges'][0]['node']['text']));
				$title = strtok($caption, "\n");
				$thumbnail = $post['node']['thumbnail_resources'][2]['src'];
				$date = date('l, j F Y', $post['node']['taken_at_timestamp']);
				$link = 'https://www.instagram.com/p/' . $post['node']['shortcode'] . "/";
				array_push($postEntries, array(
					'title' => $title,
					'link' => $link,
					'date' => $date,
					'thumbnail' => $thumbnail,
					'caption' => $caption
				));
			}
			http_response_code(200);
			echo json_encode(array(
				'status' => 1,
				'followers' => $followers,
				'following' => $following,
				'totalPost' => $totalPost,
				'posts' => $postEntries
			));
		}
	} else {
		http_response_code(404);
		echo json_encode(array(
			'status' => 0,
			'message' => 'Username not found or website broken'
		));
	}
} else {
	http_response_code(401);
	echo json_encode(array(
		'status' => 0,
		'message' => 'Parameter or method not allowed'
	));
}