<?php
/**
 * Model for DegustationNote
 *
 */

class DegustationNote extends BaseDegustationNote {

    public function isMauvaiseNote() {

        return in_array($this->note, array("0", "1", "D"));
    }

    public function getLibelle() {
        if(!isset(DegustationClient::$note_type_notes[$this->getKey()][$this->note])) {

            return null;
        }

        return preg_replace("/^.+\- /", "", DegustationClient::$note_type_notes[$this->getKey()][$this->note]);
    }
}
