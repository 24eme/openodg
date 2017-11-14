<?php
class HabilitationEditionForm extends acCouchdbForm
{
    protected $produits;

    public function __construct(acCouchdbDocument $doc, $defaults = array(), $options = array(), $CSRFSecret = null)
    {
        $this->produits = $doc->getProduits();
        foreach ($this->produits as $key => $produit) {
          foreach ($produit->activites as $keyActivite => $activite) {
            $idWidgets = $activite->getHashForKey();
            $date = Date::francizeDate($activite->date);
            if(!$activite->date){
              $date = Date::francizeDate($doc->getDate());
            }
            $defaults['statut_'.$idWidgets] = $activite->statut;
            $defaults['date_'.$idWidgets] = $date;
            $defaults['commentaire'.$idWidgets] = $activite->commentaire;
          }
        }
        parent::__construct($doc, $defaults, $options, $CSRFSecret);
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

        return array_merge(array("" => ""), HabilitationClient::$statuts_libelles);
    }

    public function save()
    {
        $values = $this->getValues();
        foreach ($this->produits as $key => $produit) {
            foreach ($produit->activites as $keyActivite => $activite) {
                $idWidgets = $activite->getHashForKey();
                if (!isset($values['statut_'.$idWidgets]) || empty($values['statut_'.$idWidgets]) || !isset($values['date_'.$idWidgets]) || empty($values['date_'.$idWidgets])) {
                  continue;
                }

                $hash = str_replace('-','/',$idWidgets);
                $values = $this->getValues();
                $activite = $this->getDocument()->get($hash);
                HabilitationClient::getInstance()->updateAndSaveHabilitation($this->getDocument()->identifiant,
                                                                    $activite->getProduitHash(),
                                                                    $values['date_'.$idWidgets],
                                                                    array($activite->getKey()),
                                                                    $values['statut_'.$idWidgets],
                                                                    $values['commentaire_'.$idWidgets]);
            }
        }
    }
}
