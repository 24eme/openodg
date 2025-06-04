<?php

class PMCLotsForm extends acCouchdbForm
{
	private $addrequest;
	public function __construct(acCouchdbDocument $doc, $defaults = array(), $options = array(), $CSRFSecret = null) {
      parent::__construct($doc, $defaults, $options, $CSRFSecret);
	  $this->addrequest = isset($options['addrequest']) && $options['addrequest'];
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
                $formLots->embedForm($lot->getKey(), new PMCLotForm($lot));
            }
        }

        $this->embedForm('lots', $formLots);

        $this->widgetSchema->setNameFormat('pmc_lots[%s]');
    }

	public function save() {
		$values = $this->getValues();

		foreach ($this->getEmbeddedForm('lots')->getEmbeddedForms() as $key => $embedForm) {
			$embedForm->doUpdateObject($values['lots'][$key]);
			if (!$this->addrequest && !$values['lots'][$key]['volume'] && !$values['lots'][$key]['numero_logement_operateur']) {
					$this->getDocument()->lots->remove($key);
			}
		}

		$this->getDocument()->save();
	}

}
