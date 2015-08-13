<?php

class CompteFormationsForm extends acCouchdbObjectForm {
    public function configure() {
        $this->getObject()->add();
        foreach ($this->getObject() as $formation) {
            $this->embedForm($formation->getKey(), new CompteFormationForm($formation));
        }
    }
}


