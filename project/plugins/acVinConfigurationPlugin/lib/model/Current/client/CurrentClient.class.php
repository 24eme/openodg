<?php

class CurrentClient extends acCouchdbClient {
  private static $current = array();

  /**
   *
   * @return CurrentClient
   */
  public static function getInstance() {
      return acCouchdbManager::getClient("Current");
  }

  /**
   *
   * @return Current
   */
  public static function getCurrent() {
    if (self::$current == null) {
        self::$current = CacheFunction::cache('model', array(CurrentClient::getInstance(), 'retrieveCurrent'), array());
    }
    return self::$current;
  }

  public function cacheResetConfiguration() {
        CacheFunction::remove('model');
  }

  /**
   *
   * @return Current
   */
  public function retrieveCurrent() {
    return parent::retrieveDocumentById('CURRENT');
  }

  public function getCurrentFromTheFuture() {

      return sfContext::getInstance()->getUser()->getAttribute('back_to_the_future');
  }

  public function hasCurrentFromTheFuture() {

      return sfContext::getInstance()->getUser()->hasAttribute('back_to_the_future');
  }

}
