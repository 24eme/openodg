<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TourneesRecapDateForm
 *
 * @author mathurin
 */
class RedirectAgentForm extends sfForm {

    public function __construct($defaults = array(), $options = array(), $CSRFSecret = null) {
        parent::__construct($defaults, $options, $CSRFSecret);
    }

    public function configure() {


        $this->setWidget('agent', new sfWidgetFormChoice(array('expanded' => false, 'multiple' => false, 'choices' => $this->getAllAgents())));

        $this->widgetSchema->setLabel('agent', 'Saisissez votre nom');

        $this->setValidator('agent', new sfValidatorChoice(array('required' => true, 'choices' => array_keys($this->getAllAgents())), array('required' => "Aucun agent n'a été choisi.")));


        $this->widgetSchema->setNameFormat('redirectAgentForm[%s]');
    }

    public function getAllAgents() {
        $agents = TourneeClient::getInstance()->getAgents();

        $result = array("" => "");

        foreach ($agents as $agent) {
            $result[$agent->identifiant] = $agent->nom_a_afficher;
        }
        return $result;
    }

}
