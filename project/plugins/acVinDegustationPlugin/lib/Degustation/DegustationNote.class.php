<?php
/**
 * Model for DegustationNote
 *
 */

class DegustationNote extends BaseDegustationNote {

    public function isMauvaiseNote() {

        return in_array($this->note, array("0", "1", "D"));
    }
}