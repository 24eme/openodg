<?php

class LotModificationForm extends LotForm
{
    public function configure() {
        parent::configure();

        $this->widgetSchema->setNameFormat('lot_modification[%s]');
    }

    protected function isModified() {
        foreach($this->getValues() as $key => $value) {
            if($this->getObject()->exist($key) && $this->getObject()->get($key) != $value) {
                return true;

                break;
            }

            if($this->getObject()->exist($key)) {
                continue;
            }

            if($this->getDefault($key) != $value) {

                return true;
            }
        }

        return false;
    }

    protected function doSave($con = NULL) {
        if(!$this->isModified()) {
            return;
        }

        $this->updateObject();
        LotsClient::getInstance()->modifyAndSave($this->getObject());
    }
}