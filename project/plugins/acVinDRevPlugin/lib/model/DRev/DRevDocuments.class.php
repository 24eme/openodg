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
		self::DOC_MUTAGE_DECLARATION => 'Déclaration de mutage'
	);

	private static $_statut_libelles = array(
		self::STATUT_EN_ATTENTE => 'En attente de réception',
		self::STATUT_RECU => 'Reçu'
	);

	public static function getDocumentLibelle($doc)
	{
		$libelles = self::$_document_libelles;
		return (isset($libelles[$doc])) ? $libelles[$doc] : '';
	}

	public static function getStatutLibelle($statut)
	{
		$libelles = self::$_statut_libelles;
		return (isset($libelles[$statut])) ? $libelles[$statut] : '';
	}

}
