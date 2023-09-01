<?php

class PMCRegionForm extends acCouchdbObjectForm
{
    public function configure()
    {
        parent::configure();

        $this->setWidget('region', new sfWidgetFormChoice(['expanded' => true, 'choices' => array_combine($this->getChoices(), $this->getChoices())]));
        $this->setValidator('region', new sfValidatorChoice(['choices' => $this->getChoices()]));

        $this->widgetSchema->setNameFormat('pmc_region[%s]');
    }

    public function getChoices()
    {
        return RegionConfiguration::getInstance()->getOdgRegions();
    }
}
