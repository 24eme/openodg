<?php
class ProdouaneScrappyClient {

    const SCRAPING_SUCCESS = 0;

    public static function getDocumentPath($contextInstance = null) {
        $contextInstance = ($contextInstance)? $contextInstance : sfContext::getInstance();
        return sfConfig::get('app_scrapy_documents');
    }

    public static function getScrapyBin($contextInstance = null) {
        $contextInstance = ($contextInstance)? $contextInstance : sfContext::getInstance();
        return sfConfig::get('app_scrapy_bin');;
    }

    public static function exec($scriptname, $arguments, &$output, $contextInstance = null) {
        $contextInstance = ($contextInstance)? $contextInstance : sfContext::getInstance();
        $scrapybin = self::getScrapyBin($contextInstance);
        $scrapyconfigfilename = sfConfig::get('app_scrapy_configfilename');
        if ($scrapyconfigfilename) {
            $scrapyconfigfilename = preg_replace('/%app%/', sfConfig::get('sf_app'), $scrapyconfigfilename);
            $scrapybin = "PRODOUANE_CONFIG_FILENAME=".$scrapyconfigfilename." bash ".$scrapybin;
        }else{
            $scrapybin = "bash ".$scrapybin;
        }

        $contextInstance->getLogger()->info("PrdouaneScrappyClient: ".$scrapybin."/$scriptname $arguments RUNNING");
        exec($scrapybin."/$scriptname $arguments", $output, $status);
        $contextInstance->getLogger()->info("PrdouaneScrappyClient: ".$scrapybin."/$scriptname $arguments FIN ($status) ".implode(' - ', $output));
        return $status;
    }

    public static function getUrl($action, $type, $millesime, $cvi, $json = false, $filename = null) {
        $conf = sfConfig::get('app_scrapy_api_url');
        if (!$conf) {
            throw new sfException('no configuration for scrapy api url');
        }
        $type = strtolower($type);
        $extra = '';
        if ($json) {
            $extra = '&format=json';
        }
        if ($filename) {
            $extra .= '&filename='.$filename;
        }
        return $conf.'?action='.$action.'&type='.$type.'&millesime='.$millesime.'&cvi='.$cvi.$extra;
    }

    public static function scrape($type, $millesime, $cvi) {
        $url = self::getUrl('scrape', $type, $millesime, $cvi, true);
        $response = file_get_contents($url);
        $res = json_decode($response);
        if (!isset($res->error_code) || $res->error_code) {
            return $res->error_code;
        }
        return 0;
    }

    public static function list($type, $millesime, $cvi) {
        $url = self::getUrl('list', $type, $millesime, $cvi, true);
        $response = file_get_contents($url);
        $res = json_decode($response);
        if (isset($res->error_code) && $res->error_code) {
            return [];
        }
        return $res->files;
    }

    public static function saveFile($type, $millesime, $cvi, $filename, $dest_dir_path) {
        $url = self::getUrl('file', $type, $millesime, $cvi, true, $filename);
        $response = file_get_contents($url);
        $json = json_decode(substr($response, 0, 100));
        if ($json && isset($json->error_code) && $json->error_code) {
            return false;
        }
        file_put_contents($dest_dir_path.'/'.$filename, $response);
        return $dest_dir_path.'/'.$filename;
    }

    public static function listAndSaveInTmp($type, $millesime, $cvi) {
        $files = [];
        foreach(self::list($type, $millesime, $cvi) as $f) {
            $ftmp = self::saveFile($type, $millesime, $cvi, $f, "/tmp/");
            if ($fp) {
                $files[] = $ftmp;
            }
        }
        return $files;
    }

}
