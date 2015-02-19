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
        $this->addControle(self::TYPE_WARNING, 'parcellaire_complantation', '');
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
                $this->addPoint(self::TYPE_ERROR,
                        'surface_vide',
                        'parcelle n°'.$detailv->section.' '.$detailv->numero_parcelle.' à '.$detailv->commune.' contenant '.$detailv->getLibelleComplet(),
                        $this->generateUrl('parcellaire_parcelles',
                                array('id' => $this->document->_id,
                                    'appellation' => preg_replace('/appellation_/', '', $detailv->getAppellation()->getKey()),
                                    'erreur' => $detailv->getHashForKey())));
            }
        }
        foreach($parcelles as $pid => $phashes) {
            if (count($phashes) > 1) {
                $detail = $this->document->get($phashes[0]);
                $this->addPoint(self::TYPE_WARNING,
                        'parcellaire_complantation',
                        $this->generateUrl('parcellaire_parcelles', array(
                            'id' => $this->document->_id,
                            'appellation' => 'La parcelle '.$detail->section.' '.$detail->numero_parcelle.' à '.$detail->commune.' a été déclarée avec plusieurs cépages. ',
                            'attention' => $detail->getHashForKey())
                                ." S’il ne s’agit pas d’une erreur de saisie de votre part, ne tenez pas compte de ce point de vigilance."));
                $detail->numero_parcelle .=  ' (complantation)';
                $this->document->get($phashes[1])->numero_parcelle .= ' (complantation)';
            }
        }
    }
  	
  

}