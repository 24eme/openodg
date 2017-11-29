<?php

class WidgetCompte extends bsWidgetFormInput
{
    protected $identifiant = null;

    public function __construct($options = array(), $attributes = array())
    {
        parent::__construct($options, $attributes);

        $this->setAttribute('data-url', $this->getUrlAutocomplete());
    }

    protected function configure($options = array(), $attributes = array())
    {
        parent::configure($options, $attributes);

        $this->setAttribute('class', "form-control select2 select2-offscreen select2autocompleteremote");
        $this->addOption('type_compte', array());
    }

    public function getUrlAutocomplete() {

        return sfContext::getInstance()->getRouting()->generate('compte_recherche_json', array('type_compte' => $this->getOption('type_compte', CompteClient::TYPE_COMPTE_ETABLISSEMENT)));
    }

    public function render($name, $value = null, $attributes = array(), $errors = array())
    {
        $identifiant = $value;

        if($identifiant) {
            $compte = CompteClient::getInstance()->find($identifiant, acCouchdbClient::HYDRATE_JSON);
            if(!$compte) {
                $value = null;
            } else {
                $value = $compte->_id.','. CompteClient::getInstance()->makeLibelle($compte);
            }
        }

        return parent::render($name, $value, $attributes, $errors);
    }

}
