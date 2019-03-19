<?php
/**
 * Model for CompteChai
 *
 */

class CompteChai extends BaseCompteChai {
    public function getAdresse() {

        return Anonymization::hideIfNeeded($this->_get('adresse'));
    }
}
