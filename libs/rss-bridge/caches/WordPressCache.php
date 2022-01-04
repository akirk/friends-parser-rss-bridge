<?php
namespace RSS_Bridge;

class WordPressCache implements CacheInterface
{

    private $scope;

    private $key;

    private $expiration = 0;

    private $time = false;

    private $data = null;


    public function loadData() {
        if ($this->data) {
            return $this->data;
        }

        $result = \get_site_transient($this->getCacheKey());
        if ($result === false) {
            return null;
        }

        $this->time = $result['time'];
        $this->data = $result['data'];
        return $result['data'];

    }


    public function saveData($datas)
    {
        $time           = time();
        $object_to_save = [
            'data' => $datas,
            'time' => $time,
        ];
        $result         = \set_site_transient($this->getCacheKey(), $object_to_save, $this->expiration);

        if ($result === false) {
            returnServerError('Cannot set site transient '.$this->getCacheKey());
        }

        $this->time = $time;

        return $this;

    }//end saveData()


    public function getTime()
    {
        if ($this->time === false) {
            $this->loadData();
        }

        return $this->time;

    }//end getTime()


    public function purgeCache($duration)
    {
        // Note: does not purges cache right now
        // Just sets cache expiration and leave cache purging for memcached itself
        $this->expiration = $duration;

    }//end purgeCache()


    /**
     * Set scope
     *
     * @return self
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
        return $this;

    }//end setScope()


    /**
     * Set key
     *
     * @return self
     */
    public function setKey($key)
    {
        if (!empty($key) && is_array($key)) {
            $key = array_map('strtolower', $key);
        }

        $key = json_encode($key);

        if (!is_string($key)) {
            throw new \Exception('The given key is invalid!');
        }

        $this->key = $key;
        return $this;

    }//end setKey()


    private function getCacheKey()
    {
        if (is_null($this->key)) {
            returnServerError('Call "setKey" first!');
        }

        return 'rss_bridge_cache_'.md5( $this->scope.$this->key.'A');

    }//end getCacheKey()


}//end class
