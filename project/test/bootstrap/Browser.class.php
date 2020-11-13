<?php

class Browser extends sfBrowser
{
    public function getContext($forceReload = false)
    {
        parent::getContext($forceReload);

        if(getenv("COUCHURL")) {
            $db = sfContext::getInstance()->getDatabaseManager();
            $db->setDatabase('default', new acCouchdbDatabase(array('dsn' => preg_replace('|[^/]+$|', '', getenv("COUCHURL")), 'dbname' => preg_replace('|^.+/([^/]+$)|', '\1', getenv("COUCHURL")))));
        }

        return $this->context;
    }
}
