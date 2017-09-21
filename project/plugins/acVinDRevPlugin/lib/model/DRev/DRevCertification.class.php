<?php

class DRevCertification extends BaseDRevCertification
{

    public function getChildrenNode()
    {
        return $this->getGenres();
    }

    public function getGenres()
    {
        return $this->filter('^genre');
    }

}
