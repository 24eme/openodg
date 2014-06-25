<?php
/**
 * Model for DRevLotCepage
 *
 */

class DRevLotCepage extends BaseDRevLotCepage
{

    public function hasVtsgn() {

        return !$this->exist('no_vtsgn') || !$this->get('no_vtsgn');
    }

}