<?php

class GenerationExportComptableSage extends GenerationAbstract
{


    public function generate() {
        $this->generation->setStatut(GenerationClient::GENERATION_STATUT_ENCOURS);
        $facturesfile = "generation/".$this->generation->date_emission."_factures.csv";
        $paiementsfile = "generation/".$this->generation->date_emission."_paiements.csv";
        $clientsfile = "generation/".$this->generation->date_emission."_clients.csv";

        $facturescomptafile = "generation/".$this->generation->date_emission."_factures_sage.txt";
        $paiementscomptafile = "generation/".$this->generation->date_emission."_paiements_sage.txt";
        $clientscomptafile = '';

        $date_facturation = $this->generation->arguments->exist('date_facturation') ? Date::getIsoDateFromFrenchDate($this->generation->arguments->get('date_facturation')) : null;

        $handle_factures = fopen(sfConfig::get('sf_web_dir')."/".$facturesfile.".tmp", 'a');
        $handle_clients = fopen(sfConfig::get('sf_web_dir')."/".$clientsfile, 'a');

        $with_headers = !count($this->generation->documents);
        if ($with_headers) {
            fwrite($handle_factures, ExportFactureCSV4Sage::getHeaderCsv());
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
                $export = new ExportFactureCSV4Sage($facture, false);
                fwrite($handle_factures, $export->export());
                $this->generation->documents->add(null, $facture->_id);
                if (!FactureConfiguration::getInstance()->hasDonotSaveExportFacture()){
                    $facture->versement_comptable = 1;
                    $facture->versement_comptable_paiement = 1;
                    $facture->save();
                }
            }

            $compte = $facture->getCompte();
            if(!$compte) {
                throw new sfException(sprintf("Document COMPTE-%s introuvable", $facture->identifiant));
            }

            $compte_export = new ExportCompteCsv($compte);
            fwrite($handle_clients, $compte_export->export());

        }

        $this->generation->save();
        fclose($handle_factures);
        fclose($handle_clients);

        shell_exec(sprintf("cat %s | iconv -f UTF8 -t ISO-8859-1//TRANSLIT > %s", sfConfig::get('sf_web_dir')."/".$facturesfile.".tmp", sfConfig::get('sf_web_dir')."/".$facturesfile));
        file_put_contents(sfConfig::get('sf_web_dir')."/".$facturescomptafile, shell_exec(sprintf("bash %s/bin/facture/csv2sage.sh %s %s", sfConfig::get('sf_root_dir'), sfConfig::get('sf_web_dir')."/".$clientsfile, sfConfig::get('sf_web_dir')."/".$facturesfile)));

        if(count($this->generation->documents)) {
            if (filesize(sfConfig::get('sf_web_dir')."/".$facturescomptafile)) {
                $this->generation->add('fichiers')->add(urlencode("/".$facturescomptafile), 'Export Comptable des factures');
            }
            if (filesize(sfConfig::get('sf_web_dir')."/".$clientsfile)) {
                $this->generation->add('fichiers')->add(urlencode("/".$clientsfile), 'Export CSV des societes');
            }
            if (filesize(sfConfig::get('sf_web_dir')."/".$facturesfile)) {
                $this->generation->add('fichiers')->add(urlencode("/".$facturesfile), 'Export CSV des factures');
            }
        }
        $this->generation->save();


        $date_mouvement = $this->generation->arguments->exist('date_mouvement') ? Date::getIsoDateFromFrenchDate($this->generation->arguments->get('date_mouvement')) : null;


        if(!class_exists("ExportFacturePaiementsCSV")){

            throw new sfException("La classe ExportFacturePaiementsCSV n'existe pas");
        }

        $paiements_buffer = '';
        foreach(FactureEtablissementView::getInstance()->getPaiementNonVerseeEnCompta() as $vfacture) {
            $facture = FactureClient::getInstance()->find($vfacture->key[FactureEtablissementView::KEYS_FACTURE_ID]);

            if(!$facture) {
                throw new sfException(sprintf("Document %s introuvable", $vfacture->key[FactureEtablissementView::KEYS_FACTURE_ID]));
            }

            if(!$facture->versement_comptable_paiement) {
                $export = new ExportFacturePaiementsCSV($facture, false, true);
                $csvPaiement = $export->exportFacturePaiements($date_mouvement, true);
                $paiements_buffer .= $csvPaiement;
                if($csvPaiement) {
                    $this->generation->documents->add(null, $facture->_id);
                }
                if (!sfConfig::has('configuration_facture_export_donotsave') || !sfConfig::get('configuration_facture_export_donotsave')) {
                    $facture->save();
                }
            }

        }

        $this->generation->save();

        if ($paiements_buffer) {
            $handle_paiements = fopen(sfConfig::get('sf_web_dir')."/".$paiementsfile.".tmp", 'a');
            if ($with_headers) {
                fwrite($handle_paiements, ExportFacturePaiementsCSV::getHeaderCsv());
            }
            fwrite($handle_paiements, $paiements_buffer);
            fclose($handle_paiements);

            shell_exec(sprintf("cat %s | iconv -f UTF8 -t ISO-8859-1//TRANSLIT > %s", sfConfig::get('sf_web_dir')."/".$paiementsfile.".tmp", sfConfig::get('sf_web_dir')."/".$paiementsfile));


            if(count($this->generation->documents)) {
                if (filesize(sfConfig::get('sf_web_dir')."/".$paiementscomptafile)) {
                    $this->generation->add('fichiers')->add(urlencode("/".$paiementscomptafile), 'Export Comptable des paiements');
                }
                if (filesize(sfConfig::get('sf_web_dir')."/".$paiementsfile)) {
                    $this->generation->add('fichiers')->add(urlencode("/".$paiementsfile), 'Export CSV des paiements');
                }
            }
        }
        $this->generation->setStatut(GenerationClient::GENERATION_STATUT_GENERE);
        $this->generation->save();

    }

    public function getDocumentName() {

        return 'ExportComptable';
    }

}
