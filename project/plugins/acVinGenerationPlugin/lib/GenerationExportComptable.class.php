<?php

class GenerationExportComptable extends GenerationAbstract
{
    public function generate() {
        $this->generation->setStatut(GenerationClient::GENERATION_STATUT_ENCOURS);
        $facturesfile = "generation/".$this->generation->date_emission."_factures.csv";
        $isafile = "generation/".$this->generation->date_emission."_factures_isa.txt";


        $handle_factures = fopen(sfConfig::get('sf_web_dir')."/".$facturesfile.".tmp", 'a');

        if(!class_exists("ExportFactureCSV")){

            throw new sfException("La classe ExportFactureCSV n'existe pas");
        }

        if(!count($this->generation->documents)) {
            fwrite($handle_factures, ExportFactureCSV::getHeaderCsv());
        }

        foreach(FactureEtablissementView::getInstance()->getFactureNonVerseeEnCompta() as $vfacture) {
            $facture = FactureClient::getInstance()->find($vfacture->key[FactureEtablissementView::KEYS_FACTURE_ID]);

            if(!$facture) {
                throw new sfException(sprintf("Document %s introuvable", $vfacture->key[FactureEtablissementView::KEYS_FACTURE_ID]));
            }

            $export = new ExportFactureCSV($facture, false);

            if(!$facture->versement_comptable) {
                fwrite($handle_factures, $export->exportFacture());
                $this->generation->documents->add(null, $facture->_id);
                $facture->versement_comptable = 1;
                $facture->save();
            }

        }

        $this->generation->save();
        fclose($handle_factures);

        shell_exec(sprintf("cat %s | iconv -f UTF8 -t ISO-8859-1//TRANSLIT > %s", sfConfig::get('sf_web_dir')."/".$facturesfile.".tmp", sfConfig::get('sf_web_dir')."/".$facturesfile));

        file_put_contents(sfConfig::get('sf_web_dir')."/".$isafile, shell_exec(sprintf("bash %s/bin/facture/csvfacture2isacompta.sh %s", sfConfig::get('sf_root_dir'), sfConfig::get('sf_web_dir')."/".$facturesfile)));
        $this->generation->setStatut(GenerationClient::GENERATION_STATUT_GENERE);

        if(count($this->generation->documents)) {
            $this->generation->add('fichiers')->add(urlencode("/".$isafile), 'Export Comptable des factures');
            $this->generation->add('fichiers')->add(urlencode("/".$facturesfile), 'Export CSV des factures');
        }
        $this->generation->save();
    }

    public function getDocumentName() {

        return 'ExportComptable';
    }

}
