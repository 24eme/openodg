<?php

/**
 * Model for DRevDocuments
 *
 */
class DRevDocuments extends BaseDRevDocuments
{
	const DOC_SV11 = 'SV11';
	const DOC_SV12 = 'SV12';
	const DOC_VCI = 'VCI';
	const DOC_MUTAGE_DECLARATION = 'MUTAGE_DECLARATION';
	const DOC_PARCELLES_MANQUANTES_15_OUEX_INF = 'PARCELLES_MANQUANTES_15_OUEX_INF';
	const DOC_PARCELLES_MANQUANTES_15_OUEX_SUP = 'PARCELLES_MANQUANTES_15_OUEX_SUP';	
	const DOC_PARCELLES_MANQUANTES_20_OUEX_INF = 'PARCELLES_MANQUANTES_20_OUEX_INF';
	const DOC_PARCELLES_MANQUANTES_20_OUEX_SUP = 'PARCELLES_MANQUANTES_20_OUEX_SUP';
	const DOC_DEPASSEMENT_CONSEIL = 'DEPASSEMENT_CONSEIL';
	const DOC_ELEVAGE_CONTACT_SYNDICAT = 'ELEVAGE_CONTACT_SYNDICAT';
	const DOC_REVENDICATION_SUPERFICIE_DAE = 'REVENDICATION_SUPERFICIE_DAE';

	const STATUT_EN_ATTENTE = 'EN ATTENTE';
	const STATUT_RECU = 'RECU';

	private static $_document_libelles = array(
		self::DOC_SV11 => 'SV11',
		self::DOC_SV12 => 'SV12',
		self::DOC_VCI => 'Justificatif de destruction de VCI',
		self::DOC_PARCELLES_MANQUANTES_15_OUEX_SUP => 'Liste des parcelles manquantes de VDN > 15%',
		self::DOC_PARCELLES_MANQUANTES_20_OUEX_SUP => 'Liste des parcelles manquantes de VDN > 20%',
		self::DOC_MUTAGE_DECLARATION => 'Déclaration de mutage',
		self::DOC_REVENDICATION_SUPERFICIE_DAE => 'DAE justificatif du transfert de récolte',
	);

	private static $_document_statuts_initiaux = array(
		self::DOC_SV11 => self::STATUT_EN_ATTENTE,
		self::DOC_SV12 => self::STATUT_EN_ATTENTE,
		self::DOC_VCI => self::STATUT_EN_ATTENTE,
		self::DOC_PARCELLES_MANQUANTES_15_OUEX_INF => self::STATUT_RECU,
		self::DOC_PARCELLES_MANQUANTES_15_OUEX_SUP => self::STATUT_EN_ATTENTE,
		self::DOC_PARCELLES_MANQUANTES_20_OUEX_INF => self::STATUT_RECU,
		self::DOC_PARCELLES_MANQUANTES_20_OUEX_SUP => self::STATUT_EN_ATTENTE,
		self::DOC_MUTAGE_DECLARATION => self::STATUT_EN_ATTENTE,
		self::DOC_DEPASSEMENT_CONSEIL => self::STATUT_RECU,
		self::DOC_REVENDICATION_SUPERFICIE_DAE => self::STATUT_RECU
	);

	private static $_engagement_libelles = array(
		DRevDocuments::DOC_REVENDICATION_SUPERFICIE_DAE => 'Je m\'engage à transmettre le DAE justifiant le transfert de récolte vers ce chais',
		DRevDocuments::DOC_SV11 => 'Je m\'engage à joindre une copie de la SV11',
		DRevDocuments::DOC_SV11 => 'Je m\'engage à joindre une copie de la SV11',
		DRevDocuments::DOC_SV12 => 'Je m\'engage à joidre une copie de la SV12',
		DRevDocuments::DOC_VCI => 'Je m\'engage à transmettre le justificatif de destruction de VCI',
		DRevDocuments::DOC_MUTAGE_DECLARATION => "Je m'engage à transmettre la déclaration de mutage à mon ODG",
		DRevDocuments::DOC_PARCELLES_MANQUANTES_15_OUEX_INF => "Je n'ai aucune parcelle avec un % de manquants > à 15%",
		DRevDocuments::DOC_PARCELLES_MANQUANTES_15_OUEX_SUP => "Je m'engage à transmettre à mon ODG la liste de mes parcelles avec un % de manquants > à 15%",
		DRevDocuments::DOC_PARCELLES_MANQUANTES_20_OUEX_INF => "Je n'ai aucune parcelle avec un % de manquants > à 20%",
		DRevDocuments::DOC_PARCELLES_MANQUANTES_20_OUEX_SUP => "Je m'engage à transmettre à mon ODG la liste de mes parcelles avec un % de manquants > à 20%",
		DRevDocuments::DOC_DEPASSEMENT_CONSEIL => "Je dispose de la dérogation qui m'autorise à dépasser le rendement conseil",
		DRevDocuments::DOC_ELEVAGE_CONTACT_SYNDICAT => "Je m'engage à contacter le syndicat quand le vin sera prêt");

	private static $_statut_libelles = array(
		self::STATUT_EN_ATTENTE => 'En attente de réception',
		self::STATUT_RECU => 'Reçu'
	);

	public static function getDocumentLibelle($doc)
	{
		$libelles = self::$_document_libelles;
		return (isset($libelles[$doc])) ? $libelles[$doc] : '';
	}

	public static function getEngagementLibelle($doc)
	{
		$libelles = self::$_engagement_libelles;
		return (isset($libelles[$doc])) ? $libelles[$doc] : '';
	}

	public static function getStatutLibelle($statut)
	{
		$libelles = self::$_statut_libelles;
		return (isset($libelles[$statut])) ? $libelles[$statut] : '';
	}

	public static function getStatutInital($doc)
	{
		$statuts = self::$_document_statuts_initiaux;
		return (isset($statuts[$doc])) ? $statuts[$doc] : self::STATUT_RECU;
	}

}
