<?php
/**
 * BaseControleParcelle
 *
 * Base model for ControleParcelle

 * @property float $superficie
 * @property float $superficie_cadastrale
 * @property string $commune
 * @property string $code_postal
 * @property string $code_commune
 * @property string $section
 * @property string $numero_parcelle
 * @property string $idu
 * @property string $lieu
 * @property string $cepage
 * @property string $campagne_plantation
 * @property string $numero_ordre
 * @property string $departement
 * @property string $code_insee
 * @property float $ecart_rang
 * @property float $ecart_pieds
 * @property string $mode_savoirfaire
 * @property string $porte_greffe
 * @property string $prefix
 * @property string $produit_hash
 * @property string $source_produit_libelle
 * @property string $parcelle_id
 * @property acCouchdbJson $controle

 * @method float getSuperficie()
 * @method float setSuperficie()
 * @method float getSuperficieCadastrale()
 * @method float setSuperficieCadastrale()
 * @method string getCommune()
 * @method string setCommune()
 * @method string getCodePostal()
 * @method string setCodePostal()
 * @method string getCodeCommune()
 * @method string setCodeCommune()
 * @method string getSection()
 * @method string setSection()
 * @method string getNumeroParcelle()
 * @method string setNumeroParcelle()
 * @method string getIdu()
 * @method string setIdu()
 * @method string getLieu()
 * @method string setLieu()
 * @method string getCepage()
 * @method string setCepage()
 * @method string getCampagnePlantation()
 * @method string setCampagnePlantation()
 * @method string getNumeroOrdre()
 * @method string setNumeroOrdre()
 * @method string getDepartement()
 * @method string setDepartement()
 * @method string getCodeInsee()
 * @method string setCodeInsee()
 * @method float getEcartRang()
 * @method float setEcartRang()
 * @method float getEcartPieds()
 * @method float setEcartPieds()
 * @method string getModeSavoirfaire()
 * @method string setModeSavoirfaire()
 * @method string getPorteGreffe()
 * @method string setPorteGreffe()
 * @method string getPrefix()
 * @method string setPrefix()
 * @method string getProduitHash()
 * @method string setProduitHash()
 * @method string getSourceProduitLibelle()
 * @method string setSourceProduitLibelle()
 * @method string getParcelleId()
 * @method string setParcelleId()
 * @method acCouchdbJson getControle()
 * @method acCouchdbJson setControle()

 */

abstract class BaseControleParcelle extends ParcellaireParcelle {

    public function configureTree() {
       $this->_root_class_name = 'Controle';
       $this->_tree_class_name = 'ControleParcelle';
    }

}
