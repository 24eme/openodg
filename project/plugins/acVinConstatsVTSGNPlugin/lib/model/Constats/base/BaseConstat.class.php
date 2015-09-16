<?php
/**
 * BaseConstat
 * 
 * Base model for Constat

 * @property string $produit
 * @property string $produit_libelle
 * @property string $denomination_lieu_dit
 * @property string $nb_botiche
 * @property string $nb_contenant
 * @property string $contenant
 * @property string $contenant_libelle
 * @property string $type_botiche
 * @property string $degre_potentiel_raisin
 * @property string $degre_potentiel_volume
 * @property string $volume_obtenu
 * @property string $type_vtsgn
 * @property string $statut_raisin
 * @property string $raison_refus
 * @property string $raison_refus_libelle
 * @property string $statut_volume
 * @property string $date_signature
 * @property string $date_raisin
 * @property string $date_volume
 * @property string $rendezvous_raisin
 * @property string $rendezvous_volume
 * @property string $rendezvous_report
 * @property string $send_mail_required
 * @property string $mail_sended
 * @property string $signature_base64

 * @method string getProduit()
 * @method string setProduit()
 * @method string getProduitLibelle()
 * @method string setProduitLibelle()
 * @method string getDenominationLieuDit()
 * @method string setDenominationLieuDit()
 * @method string getNbBotiche()
 * @method string setNbBotiche()
 * @method string getNbContenant()
 * @method string setNbContenant()
 * @method string getContenant()
 * @method string setContenant()
 * @method string getContenantLibelle()
 * @method string setContenantLibelle()
 * @method string getTypeBotiche()
 * @method string setTypeBotiche()
 * @method string getDegrePotentielRaisin()
 * @method string setDegrePotentielRaisin()
 * @method string getDegrePotentielVolume()
 * @method string setDegrePotentielVolume()
 * @method string getVolumeObtenu()
 * @method string setVolumeObtenu()
 * @method string getTypeVtsgn()
 * @method string setTypeVtsgn()
 * @method string getStatutRaisin()
 * @method string setStatutRaisin()
 * @method string getRaisonRefus()
 * @method string setRaisonRefus()
 * @method string getRaisonRefusLibelle()
 * @method string setRaisonRefusLibelle()
 * @method string getStatutVolume()
 * @method string setStatutVolume()
 * @method string getDateSignature()
 * @method string setDateSignature()
 * @method string getDateRaisin()
 * @method string setDateRaisin()
 * @method string getDateVolume()
 * @method string setDateVolume()
 * @method string getRendezvousRaisin()
 * @method string setRendezvousRaisin()
 * @method string getRendezvousVolume()
 * @method string setRendezvousVolume()
 * @method string getRendezvousReport()
 * @method string setRendezvousReport()
 * @method string getSendMailRequired()
 * @method string setSendMailRequired()
 * @method string getMailSended()
 * @method string setMailSended()
 * @method string getSignatureBase64()
 * @method string setSignatureBase64()
 
 */

abstract class BaseConstat extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'Constats';
       $this->_tree_class_name = 'Constat';
    }
                
}