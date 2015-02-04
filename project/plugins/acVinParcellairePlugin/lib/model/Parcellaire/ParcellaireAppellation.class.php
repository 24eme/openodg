<?php

/**
 * Model for ParcellaireAppellation
 *
 */
class ParcellaireAppellation extends BaseParcellaireAppellation {

    public function getGenre() {
        return $this->getParent();
    }

    public function getChildrenNode() {
        return $this->getMentions();
    }

    public function getMentions() {
        return $this->filter('^mention');
    }

}
