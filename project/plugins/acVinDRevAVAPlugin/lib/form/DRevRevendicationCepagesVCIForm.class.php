<?php
class DRevRevendicationCepagesVCIForm extends sfForm
{
    protected $produits;

	public function __construct($cepages, $defaults = array(), $options = array(), $CSRFSecret = null)
  	{
        $this->cepages = $cepages;
        parent::__construct($defaults, $options, $CSRFSecret);
    }

   	public function configure()
    {
        foreach ($this->cepages as $hash => $cepage) {
            $form = new DRevRevendicationCepageVCIForm($cepage);
            $this->embedForm($hash, $form);
        }

    }

    public function doUpdateObject($values)
    {
        foreach ($this->getEmbeddedForms() as $key => $embedForm) {
            unset($values[$key]['_revision']);
        	$embedForm->doUpdateObject($values[$key]);
        }
    }


}
