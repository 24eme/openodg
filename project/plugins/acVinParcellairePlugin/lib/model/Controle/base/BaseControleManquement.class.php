<?php
/**
 * BaseControleManquement
 * 
 * Base model for ControleManquement

 * @property string $constat_date
 * @property string $notification_date
 * @property string $cloture_date
 * @property string $actif
 * @property acCouchdbJson $parcelles_id
 * @property string $libelle_point_de_controle
 * @property string $libelle_manquement
 * @property string $observations
 * @property string $delais

 * @method string getConstatDate()
 * @method string setConstatDate()
 * @method string getNotificationDate()
 * @method string setNotificationDate()
 * @method string getClotureDate()
 * @method string setClotureDate()
 * @method string getActif()
 * @method string setActif()
 * @method acCouchdbJson getParcellesId()
 * @method acCouchdbJson setParcellesId()
 * @method string getLibellePointDeControle()
 * @method string setLibellePointDeControle()
 * @method string getLibelleManquement()
 * @method string setLibelleManquement()
 * @method string getObservations()
 * @method string setObservations()
 * @method string getDelais()
 * @method string setDelais()
 
 */

abstract class BaseControleManquement extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'Controle';
       $this->_tree_class_name = 'ControleManquement';
    }
                
}