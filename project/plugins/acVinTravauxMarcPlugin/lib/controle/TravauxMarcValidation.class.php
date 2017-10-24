<?php
class TravauxMarcValidation extends DocumentValidation
{
    const TYPE_ERROR = 'erreur';
    const TYPE_WARNING = 'vigilance';

	public function __construct($document, $options = null)
    {
        parent::__construct($document, $options);
    }

    public function configure()
    {
        $this->addControle(self::TYPE_ERROR, 'fournisseurs_imcomplet', "Le tableau de déclaration de vos fournisseurs n'est pas complet");
        $this->addControle(self::TYPE_ERROR, 'distillation_date', "La date de distillation n'a pas été complétée");
        $this->addControle(self::TYPE_ERROR, 'distillation_adresse', "L'adresse de distillation n'est pas complète");

        $this->addControle(self::TYPE_WARNING, 'distillation_date_trop_ancienne', "Selon le cahier des charges, les opérations de distillation doivent avoir lieu au plus tard le 30 avril de l’année qui suit celle de la récolte");
    }

    public function controle()
    {
        $fournisseurComplet = true;
        foreach($this->document->fournisseurs as $fournisseur) {
            if($fournisseur->etablissement_id && $fournisseur->date_livraison && $fournisseur->quantite) {
                continue;
            }
            $fournisseurComplet = false;
        }

        if(!$fournisseurComplet) {
            $this->addPoint(self::TYPE_ERROR, 'fournisseurs_imcomplet', "Fournisseurs de marcs", $this->generateUrl('travauxmarc_fournisseurs', $this->document));
        }

        if(!$this->document->date_distillation) {
            $this->addPoint(self::TYPE_ERROR, 'distillation_date', "Distillation", $this->generateUrl('travauxmarc_distillation', $this->document));
        }

        if($this->document->date_distillation && $this->document->date_distillation > ($this->document->campagne + 1) . '-04-30') {
            $this->addPoint(self::TYPE_WARNING, 'distillation_date_trop_ancienne', "Distillation", $this->generateUrl('travauxmarc_distillation', $this->document));
        }

        if(!$this->document->adresse_distillation->adresse || !$this->document->adresse_distillation->code_postal || !$this->document->adresse_distillation->commune) {
            $this->addPoint(self::TYPE_ERROR, 'distillation_adresse', "Distillation", $this->generateUrl('travauxmarc_distillation', $this->document));
        }
    }

}
