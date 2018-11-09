<?php
class DRevDenominationAutoForm extends acCouchdbObjectForm
{

    public function configure()
    {
        $denominationAutoChoices = $this->getDenominationAutoChoices();
        $this->setWidgets(array(
            'denomination_auto' => new sfWidgetFormChoice(array('multiple' => false, 'expanded' => true,'choices' => $denominationAutoChoices, 'renderer_options' => array('formatter' => array($this, 'formatter'))))
        ));
        $this->widgetSchema->setLabels(array(
            'denomination_auto' => 'Pour vous faciliter la saisie de cette Drev, merci de nous indiquer si vous revendiquez du bio :'
        ));

        $this->setValidators(array(
            'denomination_auto' => new sfValidatorChoice(array('multiple' => false, 'required' => false, 'choices' => array_keys($denominationAutoChoices)),array('required' => "Il faut choisir un des choix ci dessus."))
        ));
        $this->widgetSchema->setNameFormat('drev_denomination_auto[%s]');
    }

    public function getDenominationAutoChoices()
    {
        return array_merge(array("" => "Je n'ai pas de volume certifié en Bio"),DrevClient::$denominationsAuto);
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
