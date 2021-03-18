<?php
class ProdouaneScrappyClient {

    public static function getDocumentPath($contextInstance = null) {
        $contextInstance = ($contextInstance)? $contextInstance : sfContext::getInstance();
        return sfConfig::get('app_scrapy_documents');
    }

    public static function exec($scriptname, $arguments, &$output, $contextInstance = null) {
        $contextInstance = ($contextInstance)? $contextInstance : sfContext::getInstance();
        $scrapybin = sfConfig::get('app_scrapy_bin');
        $scrapyconfigfilename = sfConfig::get('app_scrapy_configfilename');
        if ($scrapyconfigfilename) {
            $scrapybin = "PRODOUANE_CONFIG_FILENAME=".$scrapyconfigfilename." bash ".$scrapybin;
        }else{
            $scrapybin = "bash ".$scrapybin;
        }

        $contextInstance->getLogger()->info("PrdouaneScrappyClient: ".$scrapybin."/$scriptname $arguments RUNNING");
        exec($scrapybin."/$scriptname $arguments", $output, $status);
        $contextInstance->getLogger()->info("PrdouaneScrappyClient: ".$scrapybin."/$scriptname $arguments".implode(' - ', $output));
        return $status;
    }


}
