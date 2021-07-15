<?php

class CertipaqService
{
    private static $_instances = [];
    protected $configuration;
    const TOKEN_CACHE_FILENAME = 'certipaq_access_token';
    const TOKEN_TIME_VALIDITY = 2700;

    public static function hasAppConfig() {
        return (sfConfig::get('app_certipaq_oauth'));
    }

    private function __construct()
    {
        $this->configuration = sfConfig::get('app_certipaq_oauth');
        if (!$this->configuration) {
            throw new sfException('CertipaqService Error : Yml configuration not found for Certipaq');
        }
    }

    final public static function getInstance()
    {
        $class = get_called_class();

        if(! isset(self::$_instances[$class])) {
            self::$_instances[$class] = new $class();
        }
        return self::$_instances[$class];
    }

    public function getToken()
    {
        if ($this->needNewToken()) {
            $token = $this->getNewToken();
            $this->setTokenCache($token);
        } else {
            $file = $this->getTokenCacheFilename();
            $token = file_get_contents($file);
            if ($token === false) {
                throw new sfException('CertipaqService Error : cannot read '.$this->getTokenCacheFilename());
            }
        }
        return $token;
    }

    public function getNewToken()
    {
        $payload = [
            'username' => $this->configuration['username'],
            'password' => $this->configuration['password']
        ];

        $result = json_decode($this->httpQuery(
            $this->configuration['urltoken'],
            ['http' => $this->getJWTHttpRequest($payload)]
        ), true);

        if (isset($result['error'])) {
            throw new sfException('CertipaqService Error : '.$result['error'].' : '.$result['message']);
        }

        if (! isset($result['token'])) {
            throw new sfException('CertipaqService Error : '.json_encode($result));
        }

        return $result['token'];
    }

    protected function needNewToken()
    {
        $file = $this->getTokenCacheFilename();
        if (file_exists($file)) {
            $timestamp = filemtime($file);
            if (($timestamp + self::TOKEN_TIME_VALIDITY) >= time()) {
                return false;
            }
        }
        return true;
    }

    protected function setTokenCache($token)
    {
        $file = $this->getTokenCacheFilename();
        $result = file_put_contents($file, $token, LOCK_EX);
        if ($result === false) {
            throw new sfException('CertipaqService Error : cannot write in '.$file);
        }
    }

    protected function getTokenCacheFilename()
    {
        return sfConfig::get('sf_cache_dir').'/'.self::TOKEN_CACHE_FILENAME;
    }

    protected function getJWTHttpRequest($content)
    {
        return array(
            'headers'  => array(
                "Host: ".$this->configuration['host'],
                "Content-Type: application/json"
            ),
            'method'  => 'GET',
            'content' => json_encode($content)
        );
    }

    protected function getQueryHttpRequest($token, $method = 'GET', $payload = null)
    {
        return array(
            'headers'  => array(
                "Host: ".$this->configuration['host'],
                "Content-Type: application/json",
                "Authorization: Bearer $token"
            ),
            'method'  => $method,
            'content' => ($payload) ? json_encode($payload) : null
        );
    }

    protected function httpQuery($url, $options)
    {
        if (extension_loaded('curl')) {
            return $this->httpQueryCurl($url, $options);
        }
        return $this->httpQueryFgc($url, $options);
    }

    protected function httpQueryFgc($url, $options)
    {
        if (isset($options['http']['headers'])) {
            $options['http']['header'] = join('\n', $options['http']['header']);
            unset($options['http']['headers']);
        }
        $context  = stream_context_create($options);
        return file_get_contents($url, false, $context);
    }

    protected function httpQueryCurl($url, $options)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        if (isset($options['http']['method'])) {
            if ($options['http']['method'] == "GET") {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            } else {
                curl_setopt($ch, CURLOPT_POST, 1);
            }
        }

        if (isset($options['http']['content'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $options['http']['content']);
        }

        if (isset($options['http']['headers'])) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $options['http']['headers']);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec ($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close ($ch);

        if ($httpCode < 200 || $httpCode >= 300 ) {
            throw new sfException('HTTP Error '.$httpCode.' : '.$server_output);
        }

        return $server_output;
    }
}
