<?php
/**
 * BaseConstat
 * 
 * Base model for Constat

 * @property string $produit
 * @property string $nb_botiche
 * @property string $degre_potentiel_raisin
 * @property string $degre_potentiel_volume
 * @property string $type_vtsgn
 * @property string $statut_raisin
 * @property string $statut_volume
 * @property string $date_signature
 * @property string $date_raisin
 * @property string $date_volume
 * @property string $rendezvous_report
 * @property string $constat

 * @method string getProduit()
 * @method string setProduit()
 * @method string getNbBotiche()
 * @method string setNbBotiche()
 * @method string getDegrePotentielRaisin()
 * @method string setDegrePotentielRaisin()
 * @method string getDegrePotentielVolume()
 * @method string setDegrePotentielVolume()
 * @method string getTypeVtsgn()
 * @method string setTypeVtsgn()
 * @method string getStatutRaisin()
 * @method string setStatutRaisin()
 * @method string getStatutVolume()
 * @method string setStatutVolume()
 * @method string getDateSignature()
 * @method string setDateSignature()
 * @method string getDateRaisin()
 * @method string setDateRaisin()
 * @method string getDateVolume()
 * @method string setDateVolume()
 * @method string getRendezvousReport()
 * @method string setRendezvousReport()
 * @method string getConstat()
 * @method string setConstat()
 
 */

abstract class BaseConstat extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'Constats';
       $this->_tree_class_name = 'Constat';
    }
                
}