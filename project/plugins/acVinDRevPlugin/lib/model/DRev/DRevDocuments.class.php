<?php

/**
 * Model for DRevDocuments
 *
 */
class DRevDocuments extends BaseDRevDocuments 
{
	const DOC_DR = 'dr';
	const DOC_SV = 'sv';
	const DOC_PRESSOIR = 'pressoir';
	
	const STATUT_EN_ATTENTE = 'EN ATTENTE';
	const STATUT_RECU = 'RECU';
	
	private static $_document_libelles = array(
		self::DOC_DR => 'Déclaration de récolte (DR)',
		self::DOC_SV => 'Déclarations SV11 / SV12',
		self::DOC_PRESSOIR => 'Carnet de pressoir'
	);
	
	private static $_statut_libelles = array(
		self::STATUT_EN_ATTENTE => 'En attente de reception',
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
