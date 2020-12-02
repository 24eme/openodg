<?php
class DegustationValidation extends DocumentValidation
{
    const TYPE_ERROR = 'erreur';
    const TYPE_WARNING = 'vigilance';
    const TYPE_ENGAGEMENT = 'engagement';

    public function __construct($document, $options = null)
    {
        parent::__construct($document, $options);
    }

    public function configure()
    {
        /*
         * Warning
         */
        $this->addControle(self::TYPE_ERROR, 'degustateurs_choisi_multiple', 'Vous avez choisi ce dégustateur plusieurs fois.');

        /*
         * Error
         */


        /*
         * Engagement
         */
    }

    public function controle()
    {
        $degustateurs = array();
        foreach ($this->document->getDegustateurs() as $college => $degustateursCollege) {
          foreach ($degustateursCollege as $id_compte => $degustateur) {
            if (!array_key_exists($id_compte, $degustateurs)) {
    			      $degustateurs[$id_compte] = array();
    		    }
            $degustateurs[$id_compte][$college] = $degustateur;
          }
        }
        foreach ($degustateurs as $id_compte => $degustateurArray) {
          if(count($degustateurArray) > 1){
            $collegesCompte = array_keys($degustateurArray);
            $college = end($collegesCompte);
            $degNode = array_shift($degustateurArray);
            $url = $this->generateUrl('degustation_selection_degustateurs', array('id' => $this->document->_id, 'college' => $college));
            $this->addPoint(self::TYPE_ERROR, 'degustateurs_choisi_multiple', $degNode->libelle , $url);
          }
        }
    }


}
