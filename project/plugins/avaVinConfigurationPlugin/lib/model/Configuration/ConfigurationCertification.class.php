<?php

class ConfigurationCertification extends BaseConfigurationCertification {

    public function getGenres() {

        return $this->filter('^genre');
    }

    public function getAppellations() {

        return $this->getChildrenNodeDeep();
    }

    public function getChildrenNode() {

        return $this->getGenres();
    }

    public function hasManyGenre() {

        return $this->getChildrenNodeDeep()->hasManyNoeuds();
    }

}
