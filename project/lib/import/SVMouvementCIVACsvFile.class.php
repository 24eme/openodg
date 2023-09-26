<?php

class SVMouvementCIVACsvFile extends CIVACsvFile
{

    const CSV_TYPE = 0;
    const CSV_ANNÉE = 1;
    const CSV_ACHETEUR_IDENTIFIANT = 2;
    const CSV_ACHETEUR_CVI = 3;
    const CSV_ACHETEUR_NOM = 4;
    const CSV_APPELLATION = 5;
    const CSV_LIEU = 6;
    const CSV_CEPAGE = 7;
    const CSV_VTSGN = 8;
    const CSV_LIEUDIT = 9;
    const CSV_DENOMINATION = 10;
    const CSV_TYPE_MOUVEMENT = 11;
    const CSV_QUANTITE = 12;
    const CSV_VENDEUR_IDENTIFIANT = 13;
    const CSV_VENDEUR_CVI = 14;
    const CSV_VENDEUR_NOM = 15;
    const CSV_RECOLTANT_IDENTIFIANT = 13;
    const CSV_RECOLTANT_CVI = 14;
    const CSV_RECOLTANT_NOM = 15;
    const CSV_HASH_PRODUIT = 16;
    const CSV_DOC_ID = 17;
    const CSV_FAMILLE_CALCULEE = 18;

    const TYPE_MOUVEMENT_SUPERFICIE = "superficie";
    const TYPE_MOUVEMENT_QUANTITE = "quantite";
    const TYPE_MOUVEMENT_VOLUME = "volume";
    const TYPE_MOUVEMENT_VCI = "vci";
    const TYPE_MOUVEMENT_VOLUME_DETRUIT = "volume_detruit";
    const TYPE_MOUVEMENT_VOLUME_REVENDIQUE = "volume_revendique";


    public function getCsvRecoltant($cvi) {
        $lignes = array();
        foreach ($this->getCsv() as $line) {
            if ($line[self::CSV_RECOLTANT_CVI] == $cvi)
            $lignes[] = $line;
        }
        return $lignes;
    }

    public function getCsvAcheteur($cvi) {
        $lignes = array();
        foreach ($this->getCsv() as $line) {
            if ($line[self::CSV_ACHETEUR_CVI] == $cvi)
            $lignes[] = $line;
        }
        return $lignes;
    }

    public static function getHashProduitByLine(array $line) {
        $hashProduit = $line[self::CSV_HASH_PRODUIT];
        $hashProduit = preg_replace("/(mentions.VT|mentions.SGN)/", "mention", $hashProduit);
        $hashProduit = preg_replace('|/DEFAUT$|', '', $hashProduit);
        return '/declaration'.HashMapper::inverse($hashProduit);
    }

    public function updateDRevProduitDetail(DRev $drev) {
        foreach ($this->getCsvAcheteur($drev->identifiant) as $line) {
            $hashProduit = self::getHashProduitByLine($line);
            if (!$drev->getConfiguration()->exist($hashProduit)) {
                continue;
            }

            $config = $drev->getConfiguration()->get($hashProduit)->getNodeRelation('revendication');

            $produit = $drev->addProduit($config->getHash());
            $produitDetail = $produit->detail;
            if($line[self::CSV_VTSGN]) {
                $produitDetail = $produit->detail_vtsgn;
            }

            switch ($line[self::CSV_TYPE_MOUVEMENT]) {

                case self::TYPE_MOUVEMENT_SUPERFICIE:
                    $produitDetail->superficie_total += (float) $line[self::CSV_QUANTITE];
                    if (!$produitDetail->exist('superficie_vinifiee')) {
                        $produitDetail->add('superficie_vinifiee');
                    }
                    $produitDetail->superficie_vinifiee += (float) $line[self::CSV_QUANTITE];
                    break;

                case self::TYPE_MOUVEMENT_VCI:
                    if(preg_match("/^[0-9\.,]+$/", $line[self::CSV_QUANTITE]) && ((float) $line[self::CSV_QUANTITE]) > 0) {
                        $produitDetail->vci_total += (float) $line[self::CSV_QUANTITE];
                        $produitDetail->vci_sur_place += (float) $line[self::CSV_QUANTITE];
                    }
                    break;

                case self::TYPE_MOUVEMENT_VOLUME_REVENDIQUE:
                    $produitDetail->volume_total += (float) $line[self::CSV_QUANTITE];
                    break;

                case self::TYPE_MOUVEMENT_VOLUME_DETRUIT:
                /*
                $produitDetail->usages_industriels_total += (float) $line[self::CSV_QUANTITE];
                $produitDetail->usages_industriels_sur_place += (float) $line[self::CSV_QUANTITE];
                break;
                */
                case self::TYPE_MOUVEMENT_QUANTITE:
                case self::TYPE_MOUVEMENT_VOLUME:
                    break;

                default:
                    throw new sfException('Type de mouvement inconnu : '.$line[self::CSV_TYPE_MOUVEMENT]);
            }
        }
    }

    public function updateDRevCepage(DRev $drev) {

        foreach ($this->getCsvAcheteur($drev->identifiant) as $line) {
            $hash = self::getHashProduitByLine($line);
            if (!$drev->getConfiguration()->exist($hash)) {
                continue;
            }

            $config = $drev->getConfiguration()->get($hash);
            $detail = $drev->getOrAdd($config->getHash())->addDetailNode($line[self::CSV_LIEUDIT]);
            switch ($line[self::CSV_TYPE_MOUVEMENT]) {
                case self::TYPE_MOUVEMENT_SUPERFICIE:
                    if ($line[self::CSV_VTSGN] == "VT") {
                        $detail->superficie_revendique_vt += (float) $line[self::CSV_QUANTITE];
                        $detail->superficie_vinifiee_vt += (float) $line[self::CSV_QUANTITE];
                    } elseif ($line[self::CSV_VTSGN] == "SGN") {
                        $detail->superficie_revendique_sgn += (float) $line[self::CSV_QUANTITE];
                        $detail->superficie_vinifiee_sgn += (float) $line[self::CSV_QUANTITE];
                    } else {
                        $detail->superficie_revendique += (float) $line[self::CSV_QUANTITE];
                        $detail->superficie_vinifiee += (float) $line[self::CSV_QUANTITE];
                    }
                    break;

                case self::TYPE_MOUVEMENT_VOLUME_REVENDIQUE:
                    if ($line[self::CSV_VTSGN] == "VT") {
                        $detail->volume_revendique_vt += (float) $line[self::CSV_QUANTITE];
                    } elseif ($line[self::CSV_VTSGN] == "SGN") {
                        $detail->volume_revendique_sgn += (float) $line[self::CSV_QUANTITE];
                    } else {
                        $detail->volume_revendique += (float) $line[self::CSV_QUANTITE];
                    }
                    break;
                case self::TYPE_MOUVEMENT_VCI:
                    if (!$detail->exist('vci_constitue')) {
                        $detail->add('vci_constitue');
                    }
                    $detail->vci_constitue += (float) $line[self::CSV_QUANTITE];
                    break;

            }
            $detail->updateTotal();
            $detail->getLibelle();
        }
    }
}
