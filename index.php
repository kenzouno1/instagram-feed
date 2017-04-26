<?php
$accessToken = '2945299519.3a81a9f.5da8322d63a142a4a043d37f28365dbe';
$userId = array('1361626572');
$tags = array('handmade');
$instagram = new InstagramFeed($accessToken,$userId,$tags,10);

$images = $instagram->getImages();
var_dump($images);


class InstagramFeed {

	protected $accessToken, $userId, $tags, $max;

	function __construct($token, $userId, $tags, $max) {
		$this->accessToken = $token;
		$this->userId = $userId;
		$this->tags = $tags;
		$this->max = $max;
	}


	public function getImages($size=''){
		$imagesData = $this->getImagesData();
		$lst = array();
	
		foreach ($imagesData['images'] as $key => $image) {
			$lst[]= $image['images'];
		}
		return $lst;
	}

	public function getImagesData($next = false, $result = array('next' => array(), 'images' => array())) {
		foreach ($this->userId as $key => $id) {
			if ($next == false) {
				$lst = $this->getData($id);
			} else {
				$lst = $this->getData($id,$result['next'][$key]);
			}
			$result['next'][$key] = $lst['next'];
			foreach ($lst['images'] as $key => $item) {
				$result['images'][] = $item;
				if (count($result['images']) == $this->max) {
					return $result;
				}
			}
		}

		if (count($result['images'] < $this->max)) {
			$result = $this->getImagesData(true, $result);
		}
		return $result;
	}
	private function getData($userId,$next = false) {

		if ($next == false) {
			$url = 'https://api.instagram.com/v1/users/' . $userId . '/media/recent/?access_token=' . $this->accessToken.'&count=50';
		} else {
			$url = $next;
		}

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$data = curl_exec($ch);
		$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if (curl_errno($ch) == 0 AND $http == 200) {
			$decode = json_decode($data, true);
			if (isset($decode['data'])) {
				if (empty($this->tags)) {
					$lst['images'] = $decode['data'];
				} else {
					$tags = $this->tags;
					$lst['images'] = array_filter($decode['data'], function ($item) use ($tags) {
						return count(array_intersect($item['tags'], $tags)) > 0;
					});
				}
				$lst['next'] = $decode['pagination']['next_url'];
				return $lst;
			}
		}
		return null;
	}
}
