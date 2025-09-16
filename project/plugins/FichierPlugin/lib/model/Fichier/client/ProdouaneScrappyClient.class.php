<?php
class ProdouaneScrappyClient {

    const SCRAPING_SUCCESS = 0;

    public static function getUrl($action, $type, $millesime, $cvi, $json = false, $filename = null) {
        $conf = sfConfig::get('app_scrapy_api_url');
        $conf = str_replace('%app%', sfConfig::get('sf_app'), $conf);
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
        return self::SCRAPING_SUCCESS;
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
        $urlfiles = self::list($type, $millesime, $cvi);
        foreach($urlfiles as $f) {
            $ftmp = self::saveFile($type, $millesime, $cvi, $f, "/tmp");
            if ($ftmp) {
                $files[] = $ftmp;
            }
        }
        return $files;
    }

}
