<?php
/**
 * BaseConfigurationCepage
 * 
 * Base model for ConfigurationCepage

 * @property string $no_vtsgn
 * @property ConfigurationDouane $douane
 * @property acCouchdbJson $relations
 * @property string $libelle
 * @property string $libelle_long
 * @property float $rendement
 * @property float $min_quantite
 * @property float $max_quantite
 * @property integer $exclude_total
 * @property integer $superficie_optionnelle
 * @property integer $no_negociant
 * @property integer $no_cooperative
 * @property integer $no_mout
 * @property integer $no_motif_non_recolte
 * @property integer $no_dr
 * @property integer $no_ds

 * @method string getNoVtsgn()
 * @method string setNoVtsgn()
 * @method ConfigurationDouane getDouane()
 * @method ConfigurationDouane setDouane()
 * @method acCouchdbJson getRelations()
 * @method acCouchdbJson setRelations()
 * @method string getLibelle()
 * @method string setLibelle()
 * @method string getLibelleLong()
 * @method string setLibelleLong()
 * @method float getRendement()
 * @method float setRendement()
 * @method float getMinQuantite()
 * @method float setMinQuantite()
 * @method float getMaxQuantite()
 * @method float setMaxQuantite()
 * @method integer getExcludeTotal()
 * @method integer setExcludeTotal()
 * @method integer getSuperficieOptionnelle()
 * @method integer setSuperficieOptionnelle()
 * @method integer getNoNegociant()
 * @method integer setNoNegociant()
 * @method integer getNoCooperative()
 * @method integer setNoCooperative()
 * @method integer getNoMout()
 * @method integer setNoMout()
 * @method integer getNoMotifNonRecolte()
 * @method integer setNoMotifNonRecolte()
 * @method integer getNoDr()
 * @method integer setNoDr()
 * @method integer getNoDs()
 * @method integer setNoDs()
 
 */

abstract class BaseConfigurationCepage extends _ConfigurationDeclaration {
                
    public function configureTree() {
       $this->_root_class_name = 'Configuration';
       $this->_tree_class_name = 'ConfigurationCepage';
    }
                
}