<?php
/**
 * BaseHabilitationMouvement
 * 
 * Base model for HabilitationMouvement

 * @property integer $facture
 * @property integer $facturable
 * @property string $produit_hash
 * @property string $produit_libelle
 * @property string $type_hash
 * @property string $type_libelle
 * @property string $detail_identifiant
 * @property string $detail_libelle
 * @property float $quantite
 * @property string $date
 * @property string $date_version
 * @property string $version

 * @method integer getFacture()
 * @method integer setFacture()
 * @method integer getFacturable()
 * @method integer setFacturable()
 * @method string getProduitHash()
 * @method string setProduitHash()
 * @method string getProduitLibelle()
 * @method string setProduitLibelle()
 * @method string getTypeHash()
 * @method string setTypeHash()
 * @method string getTypeLibelle()
 * @method string setTypeLibelle()
 * @method string getDetailIdentifiant()
 * @method string setDetailIdentifiant()
 * @method string getDetailLibelle()
 * @method string setDetailLibelle()
 * @method float getQuantite()
 * @method float setQuantite()
 * @method string getDate()
 * @method string setDate()
 * @method string getDateVersion()
 * @method string setDateVersion()
 * @method string getVersion()
 * @method string setVersion()
 
 */

abstract class BaseHabilitationMouvement extends Mouvement {
                
    public function configureTree() {
       $this->_root_class_name = 'Habilitation';
       $this->_tree_class_name = 'HabilitationMouvement';
    }
                
}