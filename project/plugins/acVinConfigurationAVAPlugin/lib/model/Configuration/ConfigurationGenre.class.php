<?php

class ConfigurationGenre extends BaseConfigurationGenre {

    public function getAppellations() {

        return $this->filter('^appellation');
    }

    public function getMentions() {

        return $this->getChildrenNodeDeep();
    }

    public function getChildrenNode() {

        return $this->getAppellations();
    }

    public function hasManyAppellations() {

        return $this->getChildrenNodeDeep()->hasManyNoeuds();
    }


}
