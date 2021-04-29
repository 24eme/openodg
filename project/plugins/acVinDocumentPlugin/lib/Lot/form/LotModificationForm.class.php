<?php

class LotModificationForm extends LotForm
{
    public function configure() {
        parent::configure();

        $this->widgetSchema->setNameFormat('lot_modification[%s]');
    }

    protected function doSave($con = NULL) {
        $this->updateObject();
        LotsClient::getInstance()->modifyAndSave($this->getObject());
    }
}