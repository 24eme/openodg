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
	
	const STATUT_EN_ATTENTE = 'EN ATTENTE';
	const STATUT_RECU = 'RECU';
	
	private static $_document_libelles = array(
		self::DOC_DR => 'Déclaration de Récolte',
		self::DOC_SV11 => 'SV11',
		self::DOC_SV12 => 'SV12',
		self::DOC_SV => 'SV11 / SV12',
		self::DOC_PRESSOIR => 'Carnet de Pressoir'
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
