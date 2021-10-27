<?php
class DRevDenominationAutoForm extends acCouchdbObjectForm
{

    public function configure()
    {
        $denominationAutoChoices = $this->getDenominationAutoChoices();
        $this->setWidgets(array(
            'denomination_auto' => new sfWidgetFormChoice(array('multiple' => true, 'expanded' => true,'choices' => $denominationAutoChoices, 'renderer_options' => array('formatter' => array($this, 'formatter'))))
        ));

        $this->setValidators(array(
            'denomination_auto' => new sfValidatorChoice(array('multiple' => true, 'required' => false, 'choices' => array_keys($denominationAutoChoices)),array('required' => "Il faut choisir un des choix ci dessus."))
        ));
        $this->widgetSchema->setNameFormat('drev_denomination_auto[%s]');
    }

    public function getDenominationAutoChoices()
    {
        return DrevClient::$denominationsAuto;
    }

    public function formatter($widget, $inputs)
	{
	    $rows = array();
	    foreach ($inputs as $input)
	    {
	      $rows[] = $widget->renderContentTag('div', $input['input']."&nbsp;&nbsp;".$this->getOption('label_separator').$input['label'], array('class' => ''));
	    }

	    return !$rows ? '' : implode($widget->getOption('separator'), $rows);
  	}

}
