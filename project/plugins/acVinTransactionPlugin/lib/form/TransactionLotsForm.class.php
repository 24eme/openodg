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
				$this->getDocument()->lots[$key]->getOrAdd("degustable");

				if(($this->getDocument()->etape == TransactionEtapes::ETAPE_VALIDATION || $this->getDocument()->etape == TransactionEtapes::ETAPE_LOTS)
				 && $values['lots'][$key]['produit_hash']){
						$embedForm->doUpdateObject($values['lots'][$key]);
						continue;
				}else{
					$this->getDocument()->lots[$key]->set("degustable", $values['lots'][$key]['degustable']);
					TransactionLotForm::setLotStatut($this->getDocument()->lots[$key], $values['lots'][$key]);
				}
			}
		$this->getDocument()->save();
	}

}
