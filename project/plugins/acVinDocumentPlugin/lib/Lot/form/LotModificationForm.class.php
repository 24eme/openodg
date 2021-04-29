<?php

class LotModificationForm extends LotForm
{
    protected function doSave($con = NULL) {
        $this->updateObject();
        LotsClient::getInstance()->modifyAndSave($this->getObject());
    }
}