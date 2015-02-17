<?php
class ParcellaireValidation extends DocumentValidation
{
    const TYPE_ERROR = 'erreur';
    const TYPE_WARNING = 'vigilance';
	
	public function __construct($document, $options = null)
    {
        parent::__construct($document, $options);
        $this->noticeVigilance = false;
    }
    
  	public function configure() 
  	{
  		/*
  		 * Warning
  		 */
        $this->addControle(self::TYPE_WARNING, 'parcellaire_complantation', 'Parcelle complantée');
        $this->addControle(self::TYPE_ERROR, 'surface_vide', 'Superficie nulle (0 are)');


        /*
  		 * Error
  		 */  
//        $this->addControle(self::TYPE_ERROR, 'parcellaire_invalidproduct', "Ce cépage non autorisé");
      
  	}

    public function controle()
    {
        $parcelles = array();
        foreach($this->document->declaration->getProduitsCepageDetails() as $detailk => $detailv) {
            $pid = preg_replace('/.*\//', '', $detailk);
            if (! isset($parcelles[$pid])) {
                $parcelles[$pid] = array();
            }
            array_push($parcelles[$pid], $detailk);
            if (!$detailv->superficie) {
                $this->addPoint(self::TYPE_ERROR, 'surface_vide', 'parcelle n°'.$detailv->section.' '.$detailv->numero_parcelle.' à '.$detailv->commune.' contenant '.$detailv->getLibelleComplet(), $this->generateUrl('parcellaire_parcelles', array('id' => $this->document->_id, 'appellation' => preg_replace('/appellation_/', '', $detailv->getAppellation()->getKey()))));
            }
        }
        foreach($parcelles as $pid => $phashes) {
            if (count($phashes) > 1) {
                $detail = $this->document->get($phashes[0]);
                $this->addPoint(self::TYPE_WARNING, 'parcellaire_complantation', 'La parcelle '.$detail->section.' '.$detail->numero_parcelle.' à '.$detail->commune.' est déclarée en '.$detail->getLibelleComplet().' et '.$this->document->get($phashes[1])->getLibelleComplet(), $this->generateUrl('parcellaire_parcelles', array('id' => $this->document->_id, 'appellation' => preg_replace('/appellation_/', '', $detail->getAppellation()->getKey()))));
                $detail->numero_parcelle .=  ' (complantation)';
                $this->document->get($phashes[1])->numero_parcelle .= ' (complantation)';
            }
        }
    }
  	
  

}