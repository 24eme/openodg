<?php
class ProdouaneScrappyClient {

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


}
