<?php
class EtablissementFamilles
{

    const FAMILLE_PRODUCTEUR = "PRODUCTEUR_RAISINS";
    const FAMILLE_CONDITIONNEUR = "CONDITIONNEUR";
    const FAMILLE_PRODUCTEUR_VINIFICATEUR = "PRODUCTEUR_VINIFICATEUR";
    const FAMILLE_NEGOCIANT_VINIFICATEUR = "NEGOCIANT_VINIFICATEUR";
    const FAMILLE_NEGOCIANT = "NEGOCIANT";
    const FAMILLE_DISTILLATEUR = "DISTILLATEUR";
    const FAMILLE_COOPERATIVE = "COOPERATIVE";
    const FAMILLE_COURTIER = "COURTIER";
    const FAMILLE_REPRESENTANT = "REPRESENTANT";
    const FAMILLE_OPERATEUR = "OPERATEUR";
    const FAMILLE_AUTRE = "AUTRE";

    public static $familles = array(
    	self::FAMILLE_PRODUCTEUR => "Producteur de raisins",
        self::FAMILLE_COOPERATIVE => "Coopérative",
    	self::FAMILLE_NEGOCIANT => "Négociant",
    	self::FAMILLE_NEGOCIANT_VINIFICATEUR => "Négociant Vinificateur",
    	self::FAMILLE_PRODUCTEUR_VINIFICATEUR => "Producteur Vinificateur (Cave Particulière)",
        self::FAMILLE_AUTRE => "Autre etablissement (labo, ...)",
    );

    protected static $type_societe_famille = array(
        SocieteClient::TYPE_OPERATEUR => array(self::FAMILLE_PRODUCTEUR, self::FAMILLE_NEGOCIANT, self::FAMILLE_COOPERATIVE, self::FAMILLE_REPRESENTANT),
        SocieteClient::TYPE_COURTIER => array(self::FAMILLE_COURTIER),
        SocieteClient::TYPE_AUTRE => array(self::FAMILLE_AUTRE),
    );

    public static function getFamilles()
    {
    	return self::$familles;
    }

    public static function getFamilleLibelle($famille = null)
    {
        $familles = self::getFamilles();
    	$famille = str_replace('-', '_', strtoupper(KeyInflector::slugify($famille)));
    	if (!in_array($famille, array_keys($familles))) {
    		throw new sfException('La clé famille "'.$famille.'" n\'existe pas');
    	}
    	return $familles[$famille];
    }

}
