<?php
class CotisationsCollectionCondition extends CotisationsCollection
{

    public function getDetails()
    {
        $callback = $this->config->callback;
        if(!$this->doc->$callback()) {

            return array();
        }

        return parent::getDetails();
    }

}
