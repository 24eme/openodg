<?php

class ParcellaireValidation extends DocumentValidation {

    const TYPE_ERROR = 'erreur';
    const TYPE_WARNING = 'vigilance';

    public function __construct($document, $options = null) {
        parent::__construct($document, $options);
        $this->noticeVigilance = false;
    }

    public function configure() {
        /*
         * Warning
         */
        $this->addControle(self::TYPE_WARNING, 'parcellaire_complantation', 'Attention');
        $this->addControle(self::TYPE_ERROR, 'surface_vide', 'Superficie nulle (0 are)');
        $this->addControle(self::TYPE_ERROR, 'parcelle_doublon', 'Parcelle doublonnée');


        /*
         * Error
         */
//        $this->addControle(self::TYPE_ERROR, 'parcellaire_invalidproduct', "Ce cépage non autorisé");
    }

    public function controle() {
        $parcelles = array();
        foreach ($this->document->declaration->getProduitsCepageDetails() as $detailk => $detailv) {
            if(!$detailv->getActive()) {
                continue;
            }
            $pid = preg_replace('/.*\//', '', $detailk);
            if (!isset($parcelles[$pid])) {
                $parcelles[$pid] = array();
            }
            array_push($parcelles[$pid], $detailk);
            if (!$detailv->superficie) {
                $this->addPoint(self::TYPE_ERROR, 'surface_vide', 'parcelle n°' . $detailv->section . ' ' . $detailv->numero_parcelle . ' à ' . $detailv->commune . ' déclarée en ' . $detailv->getLibelleComplet(), $this->generateUrl('parcellaire_parcelles', array('id' => $this->document->_id,
                            'appellation' => preg_replace('/appellation_/', '', $detailv->getAppellation()->getKey()),
                            'erreur' => $detailv->getHashForKey())));
            }
        }
        foreach ($parcelles as $pid => $phashes) {
            if (count($phashes) > 1) {
                $detail = $this->document->get($phashes[0]);
                $this->addPoint(self::TYPE_WARNING, 'parcellaire_complantation', '<a href="' . $this->generateUrl('parcellaire_parcelles', array(
                            'id' => $this->document->_id,
                            'appellation' => preg_replace('/appellation_/', '', $detail->getAppellation()->getKey()),
                            'attention' => $detail->getHashForKey())) . "\" class='alert-link' >La parcelle " . $detail->section . ' ' . $detail->numero_parcelle . ' à ' . $detail->commune . " a été déclarée avec plusieurs cépages. </a>"
                        . "&nbsp;S’il ne s’agit pas d’une erreur de saisie de votre part, ne tenez pas compte de ce point de vigilance.", '');
            }
        }
        $uniqParcelles = array();
        foreach ($this->document->declaration->getProduitsCepageDetails() as $pid => $detail) {
            if(!$detail->getActive()) {
                continue;
            }
            $keyParcelle = $detail->getCepage()->getHash() . '/' . $detail->getCommune() . '-' . $detail->getSection() . '-' . $detail->getNumeroParcelle();
            if (array_key_exists($keyParcelle, $uniqParcelles)) {
                $this->addPoint(self::TYPE_ERROR, 'parcelle_doublon', 'parcelle n°' . $detail->getSection() . ' ' . $detail->getNumeroParcelle() . ' à ' . $detail->getCommune() . ' déclarée en ' . $detail->getLibelleComplet(), $this->generateUrl('parcellaire_parcelles', array('id' => $this->document->_id,
                            'appellation' => preg_replace('/appellation_/', '', $detailv->getAppellation()->getKey()),
                            'erreur' => $detail->getHashForKey())));
            } else {
                $uniqParcelles[$keyParcelle] = $keyParcelle;
            }
        }
    }

}
