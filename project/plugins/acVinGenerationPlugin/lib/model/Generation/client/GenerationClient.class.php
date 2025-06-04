<?php

class GenerationClient extends acCouchdbClient {

    const TYPE_DOCUMENT_FACTURES = 'FACTURE';
    const TYPE_DOCUMENT_DS = 'DS';
    const TYPE_DOCUMENT_RELANCE = 'RELANCE';
    const TYPE_DOCUMENT_EXPORT_CSV = 'EXPORT';
    const TYPE_DOCUMENT_EXPORT_SAGE = 'SAGE';
    const TYPE_DOCUMENT_EXPORT_COMPTABLE = 'COMPTABLE';
    const TYPE_DOCUMENT_EXPORT_PARCELLAIRE = 'PARCELLAIRE';
    const TYPE_DOCUMENT_FACTURES_MAILS = 'FACTUREMAIL';
    const TYPE_DOCUMENT_FACTURES_PAPIER = 'FACTUREPAPIER';
    const TYPE_DOCUMENT_EXPORT_XML_SEPA = 'SEPA';
    const TYPE_DOCUMENT_SHELL = 'SHELL';

    const HISTORY_KEYS_TYPE_DOCUMENT = 0;
    const HISTORY_KEYS_TYPE_DATE_EMISSION = 1;
    const HISTORY_KEYS_DOCUMENT_ID = 1;

    const HISTORY_VALUES_NBDOC = 0;
    const HISTORY_VALUES_DOCUMENTS = 1;
    const HISTORY_VALUES_SOMME = 2;
    const HISTORY_VALUES_STATUT = 3;
    const HISTORY_VALUES_LIBELLE = 4;
    const HISTORY_VALUES_REGION = 5;
    const GENERATION_STATUT_ENATTENTE = "EN ATTENTE";
    const GENERATION_STATUT_ENCOURS = "EN COURS";
    const GENERATION_STATUT_GENERE = "GENERE";
    const GENERATION_STATUT_ENERREUR = "EN ERREUR";
    const GENERATION_STATUT_RELANCABLE = "RELANCABLE";

    const GENERATION_EXPORT_COMPTABLE_TYPE_ISA = 'isa';
    const GENERATION_EXPORT_COMPTABLE_TYPE_CUSTOM = 'custom';
    const GENERATION_EXPORT_COMPTABLE_TYPE_SAGE = 'sage';

    public static function getInstance() {
        return acCouchdbManager::getClient("Generation");
    }

    public function getId($type_document, $date) {
        return 'GENERATION-' . $type_document . '-' . $date;
    }

    public function findHistory($limit = 10, $region = null) {
        $rows = acCouchdbManager::getClient()
        ->limit($limit)
        ->descending(true)
        ->getView("generation", "history")
        ->rows;

        uasort($rows, "GenerationClient::sortHistory");

        return $rows;
    }

    public function findHistoryWithType($types, $limit = 100, $region = null) {
        if(!is_array($types)) {
            $types = array($types);
        }

        $rows = array();
        foreach($types as $type) {
            foreach( acCouchdbManager::getClient()
                        ->startkey(array($type, array()))
                        ->endkey(array($type))
                        ->descending(true)
                        ->limit($limit * 10)
                        ->getView("generation", "history")
                        ->rows as $r ) {
                            if ($region && $r->value[self::HISTORY_VALUES_REGION] != $region) {
                                continue;
                            }
                            $rows[] = $r;
                            if (count($rows) > $limit) {
                                break;
                            }
                        }
        }

        uasort($rows, "GenerationClient::sortHistoryByDate");

        return array_slice($rows, 0, $limit);
    }

    public function findSubGeneration($idGeneration) {
        return acCouchdbManager::getClient()
                ->startkey_docid($idGeneration."-")
                ->endkey_docid($idGeneration."-Z")
                ->execute();
    }

    public static function sortHistory($a, $b) {

        return strcmp($b->key[self::HISTORY_KEYS_TYPE_DATE_EMISSION], $a->key[self::HISTORY_KEYS_TYPE_DATE_EMISSION]);
    }

    public static function sortHistoryByDate($a, $b) {

        return strcmp($b->key[self::HISTORY_KEYS_TYPE_DATE_EMISSION], $a->key[self::HISTORY_KEYS_TYPE_DATE_EMISSION]);
    }

    public function getGenerationIdEnAttente() {
        $rows = acCouchdbManager::getClient()
                        ->startkey(array(self::GENERATION_STATUT_ENATTENTE))
                        ->endkey(array(self::GENERATION_STATUT_ENATTENTE, array()))
                        ->getView("generation", "creation")
                ->rows;
        $ids = array();
        foreach ($rows as $row) {
            $ids[] = $row->id;
        }
        return $ids;
    }

    public function getDateFromIdGeneration($date) {
        $annee = substr($date, 0, 4);
        $mois = substr($date, 4, 2);
        $jour = substr($date, 6, 2);
        $heure = substr($date, 8, 2);
        $minute = substr($date, 10, 2);
        $seconde = substr($date, 12, 2);
        return $jour . '/' . $mois . '/' . $annee . ' ' . $heure . ':' . $minute . ':' . $seconde;
    }

    public function getAllStatus() {
        return array(self::GENERATION_STATUT_ENCOURS, self::GENERATION_STATUT_GENERE);
    }

    public function getClassForGeneration($generation) {
        switch ($generation->type_document) {
            case GenerationClient::TYPE_DOCUMENT_FACTURES:

                return 'GenerationFacturePDF';

            case GenerationClient::TYPE_DOCUMENT_FACTURES_MAILS:

                return 'GenerationFactureMail';

            case GenerationClient::TYPE_DOCUMENT_FACTURES_PAPIER:

                return 'GenerationFacturePapier';

            case GenerationClient::TYPE_DOCUMENT_DS:

                return 'GenerationDSPDF';

            case GenerationClient::TYPE_DOCUMENT_RELANCE:

                return 'GenerationRelancePDF';

            case GenerationClient::TYPE_DOCUMENT_EXPORT_CSV:

                return 'GenerationExportCSV';

            case GenerationClient::TYPE_DOCUMENT_EXPORT_SAGE:

                return 'GenerationExportSage';

            case GenerationClient::TYPE_DOCUMENT_EXPORT_COMPTABLE:
                switch (FactureConfiguration::getInstance()->getExportType()) {
                    case self::GENERATION_EXPORT_COMPTABLE_TYPE_ISA:
                        return 'GenerationExportComptableIsa';
                    case self::GENERATION_EXPORT_COMPTABLE_TYPE_CUSTOM:
                        return 'GenerationExportComptableCustom';
                    case self::GENERATION_EXPORT_COMPTABLE_TYPE_SAGE:
                    default:
                        return 'GenerationExportComptableSage';
                }

            case GenerationClient::TYPE_DOCUMENT_EXPORT_PARCELLAIRE:

                return 'GenerationExportParcellaire';

            case GenerationClient::TYPE_DOCUMENT_EXPORT_XML_SEPA:

                return 'GenerationExportXmlSepa';

            case GenerationClient::TYPE_DOCUMENT_SHELL:
                return 'GenerationShell';
        }
        throw new sfException($generation->type_document." n'est pas un type supporté");
    }

    public function getGenerator($generation, $configuration, $options) {
        $class = $this->getClassForGeneration($generation);

        return new $class($generation, $configuration, $options);
    }

    public function isRegenerable($generation) {
        $class = $this->getClassForGeneration($generation);

        return $class::isRegenerable();
    }

}
