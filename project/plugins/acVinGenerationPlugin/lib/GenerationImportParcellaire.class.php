<?php

class GenerationImportParcellaire extends GenerationAbstract
{
    public function generate()
    {
        $this->generation->setStatut(GenerationClient::GENERATION_STATUT_ENCOURS);
        $batch_size = 50;
        $batch_i = 1;


        $this->generation->setStatut(GenerationClient::GENERATION_STATUT_GENERE);
        $this->generation->save();
    }
}
