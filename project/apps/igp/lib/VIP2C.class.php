<?php

class VIP2C
{
    public function getContratsAPIURL($cvi)
    {
        $api_link = sfConfig::get('app_api_contrats_link');
        $secret = sfConfig::get('app_api_contrats_secret');
        if (!$api_link || !$secret) {
            return array();
        }

        $millesime = DRevConfiguration::getInstance()->getMillesime();
        $epoch = (string) time();

        $md5 = md5($secret."/".$cvi."/".$millesime."/".$epoch);
        return $api_link."/".$cvi."/".$millesime."/".$epoch."/".$md5;
    }

    public function getContratsFromAPI($cvi)
    {
        $url = $this->getContratsAPIURL($cvi);
        if (!$url) {
            return array();
        }
        $content = file_get_contents($url);

        $result = json_decode($content,true);

        return($result);
    }
}
