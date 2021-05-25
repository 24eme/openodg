<?php

class GenerationExportComptable extends GenerationAbstract
{
    public function generate() {
        $this->generation->setStatut(GenerationClient::GENERATION_STATUT_ENCOURS);
        $facturesfile = "generation/".$this->generation->date_emission."_factures.csv";
        $facturesisafile = "generation/".$this->generation->date_emission."_factures_isa.txt";
        $paiementsfile = "generation/".$this->generation->date_emission."_paiements.csv";
        $paiementsisafile = "generation/".$this->generation->date_emission."_paiements_isa.txt";

        $date_facturation = $this->generation->arguments->exist('date_facturation') ? Date::getIsoDateFromFrenchDate($this->generation->arguments->get('date_facturation')) : null;

        $handle_factures = fopen(sfConfig::get('sf_web_dir')."/".$facturesfile.".tmp", 'a');

        if(!class_exists("ExportFactureCSV")){

            throw new sfException("La classe ExportFactureCSV n'existe pas");
        }

        $with_headers = !count($this->generation->documents);
        if ($with_headers)) {
            fwrite($handle_factures, ExportFactureCSV::getHeaderCsv());
        }

        foreach(FactureEtablissementView::getInstance()->getFactureNonVerseeEnCompta() as $vfacture) {
            $facture = FactureClient::getInstance()->find($vfacture->key[FactureEtablissementView::KEYS_FACTURE_ID]);

            if(!$facture) {
                throw new sfException(sprintf("Document %s introuvable", $vfacture->key[FactureEtablissementView::KEYS_FACTURE_ID]));
            }

            if ($facture->date_facturation > $date_facturation) {
                continue;
            }

            if(!$facture->versement_comptable) {
                $export = new ExportFactureCSV($facture, false);
                fwrite($handle_factures, $export->exportFacture());
                $this->generation->documents->add(null, $facture->_id);
                $facture->versement_comptable = 1;
                $facture->save();
            }

        }

        $this->generation->save();
        fclose($handle_factures);

        shell_exec(sprintf("cat %s | iconv -f UTF8 -t ISO-8859-1//TRANSLIT > %s", sfConfig::get('sf_web_dir')."/".$facturesfile.".tmp", sfConfig::get('sf_web_dir')."/".$facturesfile));

        file_put_contents(sfConfig::get('sf_web_dir')."/".$facturesisafile, shell_exec(sprintf("bash %s/bin/facture/csvfacture2isacompta.sh %s", sfConfig::get('sf_root_dir'), sfConfig::get('sf_web_dir')."/".$facturesfile)));

        if(count($this->generation->documents)) {
            if (filesize(sfConfig::get('sf_web_dir')."/".$facturesisafile)) {
                $this->generation->add('fichiers')->add(urlencode("/".$facturesisafile), 'Export Comptable des factures');
            }
            if (filesize(sfConfig::get('sf_web_dir')."/".$facturesfile)) {
                $this->generation->add('fichiers')->add(urlencode("/".$facturesfile), 'Export CSV des factures');
            }
        }
        $this->generation->save();


        $date_mouvement = $this->generation->arguments->exist('date_mouvement') ? Date::getIsoDateFromFrenchDate($this->generation->arguments->get('date_mouvement')) : null;

        $handle_paiements = fopen(sfConfig::get('sf_web_dir')."/".$paiementsfile.".tmp", 'a');

        if(!class_exists("ExportFacturePaiementsCSV")){

            throw new sfException("La classe ExportFacturePaiementsCSV n'existe pas");
        }

        if ($with_headers)) {
            fwrite($handle_paiements, ExportFacturePaiementsCSV::getHeaderCsv());
        }

        foreach(FactureEtablissementView::getInstance()->getPaiementNonVerseeEnCompta() as $vfacture) {
            $facture = FactureClient::getInstance()->find($vfacture->key[FactureEtablissementView::KEYS_FACTURE_ID]);

            if(!$facture) {
                throw new sfException(sprintf("Document %s introuvable", $vfacture->key[FactureEtablissementView::KEYS_FACTURE_ID]));
            }

            if(!$facture->versement_comptable_paiement) {
                $export = new ExportFacturePaiementsCSV($facture, false, true);
                fwrite($handle_paiements, $export->exportFacturePaiements($date_mouvement, true));
                $this->generation->documents->add(null, $facture->_id);
                $facture->save();
            }

        }

        $this->generation->save();
        fclose($handle_paiements);

        shell_exec(sprintf("cat %s | iconv -f UTF8 -t ISO-8859-1//TRANSLIT > %s", sfConfig::get('sf_web_dir')."/".$paiementsfile.".tmp", sfConfig::get('sf_web_dir')."/".$paiementsfile));

        file_put_contents(sfConfig::get('sf_web_dir')."/".$paiementsisafile, shell_exec(sprintf("bash %s/bin/facture/csvpaiement2isacompta.sh %s", sfConfig::get('sf_root_dir'), sfConfig::get('sf_web_dir')."/".$paiementsfile)));

        if(count($this->generation->documents)) {
            if (filesize(sfConfig::get('sf_web_dir')."/".$paiementsisafile)) {
                $this->generation->add('fichiers')->add(urlencode("/".$paiementsisafile), 'Export Comptable des paiements');
            }
            if (filesize(sfConfig::get('sf_web_dir')."/".$paiementsfile)) {
                $this->generation->add('fichiers')->add(urlencode("/".$paiementsfile), 'Export CSV des paiements');
            }
        }
        $this->generation->setStatut(GenerationClient::GENERATION_STATUT_GENERE);
        $this->generation->save();



    }

    public function getDocumentName() {

        return 'ExportComptable';
    }

}
