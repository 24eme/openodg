<?php
class ConditionnementValidation extends DeclarationLotsValidation
{

    protected $etablissement = null;
    protected $produit_revendication_rendement = array();

    public function __construct($document, $options = null)
    {
        $this->etablissement = $document->getEtablissementObject();
        parent::__construct($document, $options);
        $this->noticeVigilance = true;
    }

    public function configure()
    {
        $this->configureLots();
    }

    public function controle()
    {
        $this->controleLotsGenerique('conditionnement_lots');
    }

}
