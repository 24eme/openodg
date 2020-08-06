<?php
class DegustationSelectionDegustateurForm extends acCouchdbForm
{
    public function configure() {
        $this->setWidgets(array(
	       'selectionne' => new WidgetFormInputCheckbox(),
	    ));

    	$this->setValidators(array(
    	   'selectionne' => new ValidatorBoolean(),
    	));
        $this->widgetSchema->setNameFormat('[%s]');
    }
}