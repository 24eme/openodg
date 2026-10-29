<?php
/**
 * Model for ParcellaireIrrigableDocuments
 *
 */

 class ParcellaireIrrigableDocuments extends BaseParcellaireIrrigableDocuments {
     
     const STATUT_RECU = "RECU";
     const ENGAGEMENT_A_NE_PAS_IRRIGUER = "ENGAGEMENT_NON_IRRIGATION";


     private static $_document_libelles = array(
        self::ENGAGEMENT_A_NE_PAS_IRRIGUER => "Engagement à ne pas irriguer les lots de l'affectation parcellaire"
     );
     
     private static $_statut_libelles = array(
        self::STATUT_RECU => 'Reçu'
     );
     
     public static function getDocumentLibelle($doc) {
         $libelles = self::$_document_libelles;
         return (isset($libelles[$doc])) ? $libelles[$doc] : '';
     }

     public static function getStatutLibelle($statut) {
         $libelles = self::$_statut_libelles;
         return (isset($libelles[$statut])) ? $libelles[$statut] : '';
     }

 }