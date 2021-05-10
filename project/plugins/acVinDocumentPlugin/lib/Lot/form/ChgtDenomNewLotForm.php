<?php

class ChgtDenomNewLotForm extends LotModificationForm
{
    public function configure() {
        parent::configure();

        $this->widgetSchema->setNameFormat('chgtdenom_newlot_[%s]');
    }

    protected function doSave($con = NULL) {
        $this->updateObject();
        LotsClient::getInstance()->modifyAndSave($this->getObject());
    }
}
