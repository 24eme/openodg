<?php

class ParcellaireDouaneNatifCsvFile extends ParcellaireCsvFile
{


    const CSV_NUMERO_LEXPLOITATION = 0;
    const CSV_RAISON_SOCIALE_DE_LEXPLOITATION = 1;
    const CSV_SIRET_DE_LEXPLOITATION = 2;
    const CSV_REFERENCE_CADASTRALE_DE_LA_PARCELLE = 3;
    const CSV_CONTENANCE_CADASTRALE_DE_LA_PARCELLE = 4;
    const CSV_CODE_INSEE_DE_LA_COMMUNE = 5;
    const CSV_LIBELLE_DE_LA_COMMUNE = 6;
    const CSV_LIEUDIT = 7;
    const CSV_NUMERO_DORDRE_DE_LA_PLANTATION_SUR_LA_PARCELLE = 8;
    const CSV_SUPERFICIE_DE_LA_PLANTATION = 9;
    const CSV_DATE_DE_PLANTATION_OU_SURGREFFAGE = 10;
    const CSV_CAMPAGNE_DE_PLANTATION = 11;
    const CSV_CODE_DU_CEPAGE = 12;
    const CSV_LIBELLE_CEPAGE = 13;
    const CSV_COULEUR_DU_CEPAGE = 14;
    const CSV_CODE_DU_PORTEGREFFE = 15;
    const CSV_LIBELLE_DU_PORTEGREFFE = 16;
    const CSV_CODE_DU_PRODUIT = 17;
    const CSV_LIBELLE_DU_PRODUIT = 18;
    const CSV_ECART_ENTRE_LES_PIEDS_DE_VIGNE = 19;
    const CSV_ECART_ENTRE_LES_RANGS_DE_VIGNE = 20;
    const CSV_MOTIF_DENCEPAGEMENT = 21;
    const CSV_SERVICE_GESTIONNAIRE = 22;

    public function convert($verbose = false)
    {
        $configuration = ConfigurationClient::getInstance()->getCurrent();

        if (!$this->file) {
            throw new sfException('no csv loaded');
        }
        foreach ($this->file->getLignes() as $parcelle) {
            $produit = $configuration->identifyProductByCodeDouane($parcelle[self::CSV_CODE_DU_PRODUIT]);
            if (!$produit) {
                $libelle = $parcelle[self::CSV_LIBELLE_DU_PRODUIT];
                $produit = $configuration->identifyProductByLibelle($libelle);
            }
            if (!$produit) {
                $cepage = strtoupper($parcelle[self::CSV_LIBELLE_CEPAGE]);
                $cepage = str_replace(array('CEPAGE INCONNU', 'CEPAGE NON RENSEIGNE'), '', $cepage);
                $cepage = str_replace(array('MUSCAT A PETITS GRAINS', 'MUSCATS A PETITS GRAINS'), 'MUSCAT', $cepage);
                $libelle .= " ".$cepage;
                $libelle = str_replace('GEWURZTRAMINER', 'GEWURZT', $libelle);
                $libelle = preg_replace('/ ?\.?(B|RS|R|N|G)$/', '', $libelle);
                $produit = $configuration->identifyProductByLibelle($libelle);
            }
            $hash = ($produit) ? $produit->getHash() : null ;

            $idu = Parcellaire::computeIDU(
                $parcelle[self::CSV_CODE_INSEE_DE_LA_COMMUNE],
                str_replace(' ', '', substr($parcelle[self::CSV_REFERENCE_CADASTRALE_DE_LA_PARCELLE], 6, 3)), //prefix
                str_replace(' ', '', substr($parcelle[self::CSV_REFERENCE_CADASTRALE_DE_LA_PARCELLE], 9, 2)), //section
                substr($parcelle[self::CSV_REFERENCE_CADASTRALE_DE_LA_PARCELLE], 11),   //numero parcelle
            );

            $new_parcelle = $this->parcellaire->addParcelle(
                $idu,
                $libelle,
                $parcelle[self::CSV_LIBELLE_CEPAGE],
                $parcelle[self::CSV_CAMPAGNE_DE_PLANTATION],
                $parcelle[self::CSV_LIBELLE_DE_LA_COMMUNE],
                $parcelle[self::CSV_LIEUDIT]
            );

            $new_parcelle->ecart_rang = (float) $parcelle[self::CSV_ECART_ENTRE_LES_RANGS_DE_VIGNE];
            $new_parcelle->ecart_pieds = (float) $parcelle[self::CSV_ECART_ENTRE_LES_PIEDS_DE_VIGNE];
            $new_parcelle->superficie = (float) str_replace(',', '.', $parcelle[self::CSV_SUPERFICIE_DE_LA_PLANTATION]);
            $new_parcelle->superficie_cadastrale = (float) str_replace(',', '.', $parcelle[self::CSV_SUPERFICIE_DE_LA_PLANTATION]);
            if ($hash && $new_parcelle->produit_hash && strpos($new_parcelle->produit_hash, ParcellaireClient::PARCELLAIRE_DEFAUT_PRODUIT_HASH) === false) {
                $new_parcelle = $this->parcellaire->affecteParcelleToHashProduit($hash, $new_parcelle);
            }

            if (! $this->check($new_parcelle) && $this->contextInstance) {
                $this->contextInstance->getLogger()->info("La parcelle ".$new_parcelle->getKey()." n'est pas conforme");
            }
            if ($this->contextInstance) {
                $this->contextInstance->getLogger()->info("Parcelle de ".$new_parcelle->getKey()." ajouté");
            }
        }
    }

}
