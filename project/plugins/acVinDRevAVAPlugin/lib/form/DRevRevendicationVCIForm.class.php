<?php
class DRevRevendicationVCIForm extends acCouchdbObjectForm
{
	public function configure()
    {
        $this->embedForm('cepages', new DRevRevendicationCepagesVCIForm($this->getObject()->declaration->getProduitsVCI()));
        $this->widgetSchema->setNameFormat('drev_vci[%s]');
    }

    protected function doUpdateObject($values)
    {
        parent::doUpdateObject($values);
        foreach ($this->getEmbeddedForms() as $key => $embedForm) {
        	$embedForm->doUpdateObject($values[$key]);
        }
        $this->getObject()->calculateVolumeRevendiqueVCI();
    }
}
