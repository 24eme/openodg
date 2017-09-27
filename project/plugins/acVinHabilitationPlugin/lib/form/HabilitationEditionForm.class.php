<?php
class HabilitationEditionForm extends acCouchdbObjectForm
{
    protected $produits;

    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null)
    {
        $this->produits = $object->getProduits();
        parent::__construct($object, $options, $CSRFSecret);
    }

    public function configure()
    {
      foreach ($this->produits as $key => $produit) {
        foreach ($produit->activites as $keyActivite => $activite) {
          $idWidgets = $activite->getHashForKey();
          $this->setWidget('statut_'.$idWidgets, new bsWidgetFormChoice(array('choices' => $this->getStatuts()), array("class"=>"select2 form-control")));
          $this->setWidget('date_'.$idWidgets, new sfWidgetFormInput(array(), array()));
          $this->setWidget('commentaire_'.$idWidgets, new sfWidgetFormInput());

          $this->setValidator('commentaire_'.$idWidgets, new sfValidatorString(array("required" => false)));
          $this->setValidator('statut_'.$idWidgets, new sfValidatorChoice(array('required' => false, 'choices' => array_keys($this->getStatuts()))));
          $this->setValidator('date_'.$idWidgets, new sfValidatorDate(
                  array('date_output' => 'Y-m-d',
              'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~',
              'required' => false)));
          $this->validatorSchema['date_'.$idWidgets]->setMessage('bad_format', "La date n'est pas au bon format, le format accepté est dd/mm/YYYY");

        }
      }
      $this->widgetSchema->setNameFormat('habilitation_edition_form[%s]');
    }

    public function getStatuts(){
      return array_merge( array("" => ""), HabilitationClient::$statuts_libelles );
    }

    protected function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();
        foreach ($this->produits as $key => $produit) {
          foreach ($produit->activites as $keyActivite => $activite) {
            $idWidgets = $activite->getHashForKey();
            $date = Date::francizeDate($activite->date);
            if(!$activite->date){
              $date = Date::francizeDate($this->getObject()->getDate());
            }
            $this->setDefault('statut_'.$idWidgets, $activite->statut);
            $this->setDefault('date_'.$idWidgets, $date);
            $this->setDefault('commentaire_'.$idWidgets, $activite->commentaire);
          }
        }
    }


    protected function doUpdateObject($values)
    {
      foreach ($this->produits as $key => $produit) {
        foreach ($produit->activites as $keyActivite => $activite) {
          $idWidgets = $activite->getHashForKey();
          if (isset($values['statut_'.$idWidgets]) && !empty($values['statut_'.$idWidgets]) && isset($values['date_'.$idWidgets]) && !empty($values['date_'.$idWidgets])) {
            $hash = str_replace('-','/',$idWidgets);
            $this->getObject()->get($hash)->updateHabilitation($values['date_'.$idWidgets],$values['statut_'.$idWidgets],$values['commentaire_'.$idWidgets]);
          }
        }
      }
    }
}
