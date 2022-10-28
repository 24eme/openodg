<?php
class HabilitationCertipaqDocumentsForm extends acCouchdbForm
{
    protected $certipaq_demande_id = null;
    protected $demande = null;
    protected $etablissement = null;
    protected $files = null;
    protected $query_document = null;

    public function __construct($demande, $certipaq_demande_id, $defaults = array(), $options = array(), $CSRFSecret = null) {
        $this->demande = $demande;
        $this->etablissement = $demande->getDocument()->getEtablissementObject();
        $this->certipaq_demande_id = $certipaq_demande_id;
        parent::__construct($demande->getDocument(), $defaults, $options, $CSRFSecret);
    }

    public function configure() {
        foreach($this->getFichiersAttendus() as $fichier) {
            $this->setWidget('fichier_'.$fichier->id, new sfWidgetFormChoice(array('choices' => $this->getFichiersDispos())));
            $this->setValidator('fichier_'.$fichier->id, new sfValidatorChoice(array('required' => ($fichier->required), 'choices' => array_keys($this->getFichiersDispos()))));
            $this->widgetSchema->setLabel('fichier_'.$fichier->id, $fichier->libelle);
        }

        $this->widgetSchema->setNameFormat('habilitation_certipaq_demande_documents[%s]');
    }

    public function getFichiersAttendus() {
        if (!is_array($this->query_document)) {
            $this->query_document = CertipaqDI::getInstance()->getDocumentForDemandeIdentification($this->demande);
        }
        if (!isset($this->query_document[0])) {
            return array();
        }
        return $this->query_document[0]->types_document;
    }

    public function getCDCFamilleId() {
        return $this->query_document[0]->dr_cdc_famille_id;
    }

    public function getFichierTypeId($k) {
        return str_replace('fichier_', '', $k);
    }

    public function getFichiersDispos() {
        if (!$this->files) {
            $this->files = array('' => '');
            foreach ( PieceAllView::getInstance()->getPiecesByEtablissement($this->etablissement->identifiant, true, null, null, array(FichierClient::CATEGORIE_FICHIER, FichierClient::CATEGORIE_IDENTIFICATION, FichierClient::CATEGORIE_OI)) as $row) {
                $this->files[$row->id] = $row->key[PieceAllView::KEYS_LIBELLE]." (".$row->key[PieceAllView::KEYS_DATE_DEPOT].')';
            }
        }
        return $this->files;
    }

    public function save()
    {
    }
}
