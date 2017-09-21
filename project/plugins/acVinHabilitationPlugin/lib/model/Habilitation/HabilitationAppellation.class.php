<?php
/**
 * Model for HabilitationAppellation
 *
 */

class HabilitationAppellation extends BaseHabilitationAppellation {

    public function getGenre()
    {
        return $this->getParent();
    }

    public function getChildrenNode()
    {
        return $this->getMentions();
    }

    public function getMentions()
    {
        return $this->filter('^mention');
    }

    public function getLibelleComplet()
    {
        return $this->libelle;
    }

}
