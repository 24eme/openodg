<?php

require_once(dirname(__FILE__).'/vendor/phpCAS/CAS.php');

class acCAS extends phpCAS {

    public static function getConfig($str) {
      if (isset($_SESSION[$str])) {
        return ($_SESSION[$str]);
      }
      return sfConfig::get($str);
    }

    private static function initCasInfo() {
      if (isset($_SESSION['app_cas_domain'])) {
        return ;
      }
      $_SESSION['app_cas_domain'] = sfConfig::get('app_cas_domain');
      $_SESSION['app_cas_port'] = sfConfig::get('app_cas_port');
      $_SESSION['app_cas_path'] = sfConfig::get('app_cas_path');
      $_SESSION['app_cas_url'] = sfConfig::get('app_cas_url');
    }

    public static function processAuth() {
        self::initCasInfo();
        $multidomains = sfConfig::get('app_cas_multidomains', array());
        if (isset($_GET['ticket']) && count($multidomains) && ($postfix = preg_replace('/.*-/', '', $_GET['ticket']))) {
          $_SESSION['app_cas_domain'] = $multidomains[$postfix]['domain'];
          $_SESSION['app_cas_port'] = $multidomains[$postfix]['port'];
          $_SESSION['app_cas_path'] = $multidomains[$postfix]['path'];
          $_SESSION['app_cas_url'] = $multidomains[$postfix]['url'];
        }
        phpCAS::setDebug('/tmp/cas.log');
        @acCAS::client(CAS_VERSION_2_0, $_SESSION['app_cas_domain'], $_SESSION['app_cas_port'], $_SESSION['app_cas_path'], false);
        @acCAS::setNoCasServerValidation();
        @acCAS::forceAuthentication();
    }

    public static function processLogout($url) {
        self::initCasInfo();
        @phpCAS::client(CAS_VERSION_2_0, $_SESSION['app_cas_domain'], $_SESSION['app_cas_port'], $_SESSION['app_cas_path'], false);
        if (@phpCas::isAuthenticated()) {
            @phpCAS::logoutWithRedirectService($url);
        }
    }
}
