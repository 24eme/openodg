<?php
class DRevCommentaireForm extends acCouchdbObjectForm
{
   	public function configure()
    {
      $this->setWidget('Commentaire', new sfWidgetFormTextarea());
  		$this->widgetSchema->setNameFormat('comment[%s]');
    }

    public function doUpdateObject($values) {
        parent::doUpdateObject($values);
    }
}
