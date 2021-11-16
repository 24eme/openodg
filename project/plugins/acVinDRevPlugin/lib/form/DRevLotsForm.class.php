<?php
class DRevLotsForm extends acCouchdbForm
{

	public function __construct(acCouchdbDocument $doc, $defaults = array(), $options = array(), $CSRFSecret = null) {
      parent::__construct($doc, $defaults, $options, $CSRFSecret);
	  $doc->add('lots');
    }

	public function configure()
    {
        $formLots = new BaseForm();

        foreach($this->getDocument()->lots as $lot) {
            if($lot->hasBeenEdited()){
                continue;
            }
            $formLots->embedForm($lot->getKey(), new DRevLotForm($lot));
        }

        $this->embedForm('lots', $formLots);

        $this->widgetSchema->setNameFormat('drev_lots[%s]');
    }

	public function save() {
		$values = $this->getValues();
		foreach ($this->getEmbeddedForm('lots')->getEmbeddedForms() as $key => $embedForm) {
			$embedForm->doUpdateObject($values['lots'][$key]);
        }
        $this->getDocument()->cleanLots();
		$this->getDocument()->lotsImpactRevendication();
		$this->getDocument()->save();
	}

}
