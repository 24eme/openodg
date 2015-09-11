<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RendezvousDeclarantForm
 *
 * @author mathurin
 */
class RendezvousDeclarantForm extends acCouchdbObjectForm {

    private $rendezvous = null;
    private $oldDate = null;
    private $oldHeure = null;

    public function __construct(Rendezvous $rendezvous = null, $options = array(), $CSRFSecret = null) {
        $this->rendezvous = $rendezvous;
        sfContext::getInstance()->getConfiguration()->loadHelpers(array('Date'));
        parent::__construct($rendezvous, $options, $CSRFSecret);
        $this->oldDate = $this->rendezvous->date;
        $this->oldHeure = $this->rendezvous->heure;
        if ($this->rendezvous->date) {
            $this->setDefault('date', format_date($this->rendezvous->date, 'dd/MM/yyyy'));
        }
        if ($this->rendezvous->heure) {
            $this->setDefault('heure', format_date($this->rendezvous->heure, 'HH:mm'));
        }
        if ($this->rendezvous->commentaire) {
            $this->setDefault('commentaire', $this->rendezvous->commentaire);
        }
    }

    public function configure() {
        $this->setWidget('date', new sfWidgetFormInput(array(), array()));
        $this->setWidget('heure', new sfWidgetFormInput(array(), array()));
        $this->setWidget('commentaire', new sfWidgetFormInput());

        $this->widgetSchema->setLabel('date', 'Date');
        $this->widgetSchema->setLabel('heure', 'Heure');
        $this->widgetSchema->setLabel('commentaire', 'Commentaire');


        $this->setValidator('date', new sfValidatorDate(
                array('date_output' => 'Y-m-d',
            'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~',
            'required' => true)));
        $this->setValidator('heure', new sfValidatorTime(
                array('time_output' => 'H:i',
            'time_format' => '~(?P<hour>\d{2}):(?P<minute>\d{2})~',
            'required' => true)
        ));
        $this->setValidator('commentaire', new sfValidatorString(array("required" => false)));


        $this->widgetSchema->setNameFormat('rendezvous_declarant_' . $this->rendezvous->idchai . '[%s]');
    }

    public function updateObject($values = null) {
        parent::updateObject($values);
        if ($this->getObject()->heure != $this->oldHeure) {
            $tournees = TourneeClient::getInstance()->getTourneesByDate($this->getObject()->date);
            foreach ($tournees as $tournee) {
                if($tournee->getRendezvous()->exist($this->getObject()->_id)){
                    $tournee->getRendezvous()->get($this->getObject()->_id)->setHeure($this->getObject()->heure);
                    $tournee->save();
                }
            }
        }
    }

}
