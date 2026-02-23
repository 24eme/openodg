<?php

class Browser extends sfBrowser
{
    $additionnalsConfig = array();

    public function getContext($forceReload = false)
    {
        parent::getContext($forceReload);

        if(getenv("COUCHURL")) {
            $db = sfContext::getInstance()->getDatabaseManager();
            $db->setDatabase('default', new acCouchdbDatabase(array('dsn' => preg_replace('|[^/]+$|', '', getenv("COUCHURL")), 'dbname' => preg_replace('|^.+/([^/]+$)|', '\1', getenv("COUCHURL")))));
        }

        return $this->context;
    }

    public function setAdditionnalsConfig($additionnalsConfig) {
        $this->additionnalsConfig = $additionnalsConfig;
    }

    protected function doCall()
    {
        foreach($this->additionnalsConfig as $keyConfig => $valueConfig) {
            sfConfig::set($keyConfig, $valueConfig);
        }

        parent::doCall();
    }
}
