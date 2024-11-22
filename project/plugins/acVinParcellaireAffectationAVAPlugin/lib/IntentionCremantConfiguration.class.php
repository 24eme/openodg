<?php

class IntentionCremantConfiguration extends ParcellaireAffectationConfiguration {

    private static $_instance = null;

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new IntentionCremantConfiguration();
        }
        return self::$_instance;
    }

    public function getModuleName() {
        return 'intentionCremant';
    }

    public function getDateOuvertureConfigName() {

        return 'intention_cremant';
    }

}
