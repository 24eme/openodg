<?php

/**
 * Model for DRevDocuments
 *
 */
class DRevDocuments extends BaseDRevDocuments 
{
	const DOC_DR = 'DR';
	const DOC_SV11 = 'SV11';
	const DOC_SV12 = 'SV12';
	const DOC_SV = 'SV';
	const DOC_PRESSOIR = 'PRESSOIR';
	const DOC_VCI = 'VCI';
	const DOC_MUTAGE_DECLARATION = 'DOC_MUTAGE_DECLARATION';
	const DOC_MUTAGE_MANQUANTS_OUEX_INF = 'MUTAGE_MANQUANTS_OUEX_INF';
	const DOC_MUTAGE_MANQUANTS_OUEX_SUP = 'MUTAGE_MANQUANTS_OUEX_SUP';
	const DOC_DEPASSEMENT_CONSEIL = 'DOC_DEPASSEMENT_CONSEIL';

	const STATUT_EN_ATTENTE = 'EN ATTENTE';
	const STATUT_RECU = 'RECU';

	private static $_document_libelles = array(
		self::DOC_DR => 'Déclaration de Récolte',
		self::DOC_SV11 => 'SV11',
		self::DOC_SV12 => 'SV12',
		self::DOC_SV => 'SV11 / SV12',
		self::DOC_PRESSOIR => 'Carnet de Pressoir',
		self::DOC_VCI => 'Justificatif de destruction de VCI',
		self::DOC_MUTAGE_MANQUANTS_OUEX_INF => 'Déclaration de manquants VDN < 20%',
		self::DOC_MUTAGE_MANQUANTS_OUEX_SUP => 'Déclaration de manquants VDN > 20%',
		self::DOC_MUTAGE_DECLARATION => 'Déclaration de mutage',
		self::DOC_DEPASSEMENT_CONSEIL => 'Autorisation de dépassement de rendement conseil'
	);

	private static $_engagement_libelles = array(
		'revendication_superficie_dae' => 'Je m\'engage à transmettre le DAE justifiant le transfert de récolte vers ce chais',
		DRevDocuments::DOC_SV11 => 'Joindre une copie de votre SV11',
		DRevDocuments::DOC_SV12 => 'Joindre une copie de votre SV12',
		DRevDocuments::DOC_VCI => 'Je m\'engage à transmettre le justificatif de destruction de VCI',
		DRevDocuments::DOC_MUTAGE_DECLARATION => 'Je m\'engage à transmettre la déclaration de mutage',
		DRevDocuments::DOC_MUTAGE_MANQUANTS_OUEX_INF => "Je n'ai aucune parcelle de VDN avec un % de manquants > à 20%",
		DRevDocuments::DOC_MUTAGE_MANQUANTS_OUEX_SUP => "Je m'engage à transmettre la liste de mes parcelles de VDN avec un % de manquants > à 20%",
		DRevDocuments::DOC_DEPASSEMENT_CONSEIL => "Je dispose de la dérogation qui m'autorise à dépasser le rendement conseil",
		'elevage_contact_syndicat' => "Je m'engage à contacter le syndicat quand le vin sera prêt");

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

}
