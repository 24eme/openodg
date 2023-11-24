<?php

class GenerationImportParcellaire extends GenerationAbstract
{
    public function generate()
    {
        /* $this->generation->setStatut(GenerationClient::GENERATION_STATUT_ENCOURS); */
        $batch_size = 50;
        $batch_i = 1;

        $etablissements = EtablissementClient::getInstance()->findAll()->rows;
        foreach ($etablissements as $etablissement) {
            if ($etablissement->key[0] !== EtablissementFamilles::FAMILLE_PRODUCTEUR_VINIFICATEUR) {
                continue;
            }

            if ($etablissement->key[7] !== "CDP0446301") {
                continue;
            }

            $etablissement = EtablissementClient::getInstance()->find($etablissement->id);

            $errors = [];
            ParcellaireClient::getInstance()->saveParcellaire($etablissement, $errors);
            $this->generation->documents->add(null, ParcellaireClient::getInstance()->getLast($etablissement->identifiant));

            $batch_i++;
            if ($batch_i > $batch_size) {
                $this->generation->save();
            }
        }

        //$this->generation->setStatut(GenerationClient::GENERATION_STATUT_GENERE);
        $this->generation->save();
    }
}
