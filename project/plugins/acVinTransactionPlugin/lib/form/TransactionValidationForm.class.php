<?php

class TransactionValidationForm extends ConditionnementValidationForm
{
    public function configure() {
        parent::configure();
        unset($this['date_degustation_voulue']);
    }
}
