<?php

class ParcellaireCalculPPForm extends BaseForm
{
    public function __construct()
    {
        parent::__construct();
    }
    public function configure() {
        $dgc_array = array('CDP' =>"Pas de DGC (AOC Côte de Provence)",'SVI'=>"Sainte-Victoire",'FRE'=>"Féjus",'PIE'=>"Pierrefeu",'LLE'=>"La Londe",'NDA'=>"Notre-Dame-des-Anges");
        $this->setWidget('dgc' ,new bsWidgetFormChoice(array('choices' => $dgc_array )) );
        $this->setValidator('dgc', new sfValidatorPass());

        foreach ($this->getCepages() as $cepage) {
            $name = $this->getCepageKey($cepage);
            $this->setWidget($name , new sfWidgetFormInput([], ['required' => false]));
            $this->setValidator($name, new sfValidatorNumber(['required' => false]));
        }

        //$this->validatorSchema->setPostValidator(new DegustationAffectationValidator($this));

        $this->widgetSchema->setNameFormat('calcul_pp[%s]');
    }

    public function getCepages() {
        return [
            'GRENACHE N',
            'SYRAH N',
            'MOURVEDRE N',
            'TIBOUREN N',
            'CINSAUT N',
            'CARIGNAN N',
            'CABERNET SAUVIGNON N',
            'CALITOR NOIR N',
            'BARBAROUX RS',
            'ROUSSELI RS',
            'CALADOC N',
            'AGIORGITIKO N',
            'CALABRESE N',
            'MOSCHOFILERO RS',
            'XINOMAVRO N',
            'VERDEJO B',
            'VERMENTINO B',
            'UGNI BLANC B',
            'CLAIRETTE B',
            'SEMILLON B',
        ];
    }

    public function getCepageKey($cepage) {
        return str_replace(' ', '_', strtoupper($cepage));
    }
}
