<?php 
$all= array('next'=>array(),'images'=>array());
$tags = array('handmade','dulich','mywork');
$userId = array('1361626572');
$all = loopUser($all,$userId,$tags,10);
var_dump($all['images']);

function loopUser($all,$userId,$tags,$max=10,$next=false){
	foreach ($userId as $key => $id) {
		if ($next==false) {
			$lst = getImage($id,$tags);
		}else{
			$lst = getImage($id,$tags,$all['next'][$key]);
		}

		$all['next'][$key]= $lst['next'];
		foreach ($lst['images'] as $key => $item) {
			$all['images'][]= $item;
			if (count($all['images'])==$max) {
				return $all;
			}
		}
	}

	if (count($all['images']<$max)) {
		 $all = loopUser($all,$userId,$tags,$max,true);
	}
	return $all;
}

function getImage($userId,$tags,$next=false){
	$accessToken = '2945299519.3a81a9f.5da8322d63a142a4a043d37f28365dbe';
	if ($next==false) {
		$url = 'https://api.instagram.com/v1/users/'.$userId.'/media/recent/?access_token='.$accessToken;
	}else{
		$url=$next;
	}

	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$data = curl_exec($ch);
	$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	if(curl_errno($ch) == 0 AND $http == 200) {
	    $decode = json_decode($data, true);
	   	if (isset($decode['data'])) {
	   		$lst['images'] = array_filter($decode['data'],function($item) use ($tags){
	   			return count(array_intersect($item['tags'], $tags)) > 0;
	   		});
	   		$lst['next'] = $decode['pagination']['next_url'];
	   		return $lst;
	   	}
	}
	return null;
}

