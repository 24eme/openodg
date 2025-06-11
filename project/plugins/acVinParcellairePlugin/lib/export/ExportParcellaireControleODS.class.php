<?php

/**
 * Crée le fichier ODS de contrôle des parcelles 
 * en remplaçant dans le fichier ODS /modules/parcellaire/templates/extraction.ods 
 * les clés (%%UNECLE) par les valeurs qui vont bien.
 */
class ExportParcellaireControleODS extends ExportGenericParcellaireODS {

    private $appellation_filter = null;
    public function __construct($parcellaire, $appellation_filter = null) {
        $this->appellation_filter = $appellation_filter;
        parent::__construct($parcellaire, 'extraction.ods');
    }

    protected function parseDocument() {
        $parcellaire = $this->getParcellaire();

        $keys_vals = [];
        $i = 1;
        foreach ($parcellaire->declaration->getParcellesByCommune() as $parcelles) {
            foreach ($parcelles as $parcelle_id => $detail) {
                $hash_produit = $detail->produit_hash;
                if ($this->appellation_filter && strpos($hash_produit, '/'.$this->appellation_filter.'/') === false) {
                    continue;
                }
                $ecart_rang = intval(($detail->exist('ecart_rang')) ? $detail->get('ecart_rang') : 0);
                $ecart_pieds = intval(($detail->exist('ecart_pieds')) ? $detail->get('ecart_pieds') : 0);
                $id_pcv = $detail->section.sprintf("%04d",$detail->numero_parcelle);
                $id_pcv_ys = "$detail->code_commune $id_pcv";
                // Intitialise les valeurs des clés pour les cepages CDP et DGC
                $keys_vals[] = [
                    '%%NUM_OP' => 1,
                    '%%NUM_ORDRE' => $i++,
                    '%%ID_PCV_YS_IT' => $id_pcv_ys . ' ' . ($detail->numero_ordre ? ($detail->numero_ordre+1) : 1),
                    '%%ID_PCV_YS' => $id_pcv_ys,
                    '%%EVV' => $parcellaire->declarant['cvi'],
                    '%%OPERATEUR' => $parcellaire->declarant['raison_sociale'],
                    '%%SIRET' => $parcellaire->declarant['siret'],
                    '%%COMMUNE' => $detail->commune,
                    '%%LIEU_DIT' => "<![CDATA[".$detail->lieu."]]>",
                    '%%ID_PCV' =>  $id_pcv,
                    '%%CONTENANCE_CADASTRALE' => $detail->getSuperficieCadastrale(),
                    '%%SUPERFICIE' => $detail->getSuperficie(),
                    '%%ECART_RANGS' => $ecart_rang,
                    '%%ECART_PIEDS' => $ecart_pieds,
                    '%%INAO' => ConfigurationClient::getInstance()->getCurrent()->get(preg_replace('#/detail/.*$#', '', $hash_produit))->getCodeDouane(),
                    '%%ABBR_PRODUIT' => str_replace(['rouge', 'rose', 'blanc', 'DEFAUT ', 'SVI', 'LLO', 'PIE', 'FRE'], ['RG', 'RS', 'BL', '', 'SV', 'LL', 'PF', 'FR'], preg_replace('#^.*/appellations/([^/]+)/.*/lieux/([^/]+)/couleurs/([^/]+)/.*$#', '$1 $2 $3', $hash_produit)),
                    '%%MODE_FAIRE_VALOIR' => ($detail->exist('mode_savoirfaire')) ? $detail->mode_savoirfaire : '' ,
                    '%%CDP' => substr($parcellaire->identifiant, 0, 8),
                    '%%ANNEE_CAMPAGNE' => substr($detail->campagne_plantation, -4),
                    '%%CEPAGE' => $detail->cepage,
                ];
            }
        }

        $this->create_rows($keys_vals);
    }
}