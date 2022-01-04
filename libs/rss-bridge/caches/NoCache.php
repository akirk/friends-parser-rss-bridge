<?php
namespace RSS_Bridge;

class NoCache implements CacheInterface {
	public function loadData() {
		return null;
	}

	public function saveData($data){
		return $this;
	}

	public function getTime(){
		return null;
	}

	public function purgeCache($seconds){
	}

	public function setScope($scope){
		return $this;
	}

	public function setKey($key){
		$this->key = $key;
		return $this;
	}

	private function getPath(){
		return '';
	}
	private function getCacheFile(){
		return '';
	}

	private function getCacheName(){
		if(is_null($this->key)) {
			throw new \Exception('Call "setKey" first!');
		}

		return hash('md5', $this->key) . '.cache';
	}
}
