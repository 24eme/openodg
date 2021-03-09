<?php
class TransactionLotsForm extends acCouchdbForm
{
	public function __construct(acCouchdbDocument $doc, $defaults = array(), $options = array(), $CSRFSecret = null) {
    parent::__construct($doc, $defaults, $options, $CSRFSecret);
	  $doc->add('lots');
    }

	public function configure()
    {
        $formLots = new BaseForm();

        foreach($this->getDocument()->getLotsByCouleur(false) as $couleur => $lots) {
            foreach ($lots as $lot) {
                if($lot->hasBeenEdited()){
                    continue;
                }
								$formLots->embedForm($lot->getKey(), new TransactionLotForm($lot));
            }
        }

        $this->embedForm('lots', $formLots);

        $this->widgetSchema->setNameFormat('transaction_lots[%s]');
    }

	public function save() {
		$values = $this->getValues();

			foreach ($this->getEmbeddedForm('lots')->getEmbeddedForms() as $key => $embedForm) {				
				$embedForm->doUpdateObject($values['lots'][$key]);
			}
		$this->getDocument()->save();
	}

}
