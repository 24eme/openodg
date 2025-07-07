
<?php

class ParcellaireManquantValidation extends DocumentValidation {

    public function __construct($document, $options = null) {
        parent::__construct($document, $options);
    }

    public function configure()
    {
        $this->addControle(self::TYPE_WARNING, 'pourcentage_inf_min', "Le pourcentage de pied manquant est inférieur à ".ParcellaireConfiguration::getInstance()->getManquantPCMin()."%");
        $this->addControle(self::TYPE_ERROR, 'pourcentage_nul', "Le pourcentage de pied manquant ne peut pas être nul ou à zéro");
        $this->addControle(self::TYPE_ENGAGEMENT, 'engagement_rendement_maximal', "Ne pas produire le rendement maximal sur les parcelles déclarées ayant des pieds manquants");
        $this->addControle(self::TYPE_ENGAGEMENT, 'engagement_vci', "Ne pas faire de VCI sur les parcelles déclarées ayant des pieds manquants");
        $this->addControle(self::TYPE_ENGAGEMENT, 'engagement_transmission', "À envoyer ce document à mes unités de vinification pour les coopérateurs et les vendeurs de vendange fraîche");
    }

    public function controle()
    {
        foreach ($this->document->getParcelles() as $parcelle) {
            if(!$parcelle->pourcentage || $parcelle->pourcentage === 0){
                $this->addPoint(self::TYPE_ERROR, 'pourcentage_nul', "Parcelle n° {$parcelle->section} {$parcelle->numero_parcelle} - Le pourcentage de pied mort n'est pas conforme", $this->generateUrl('parcellairemanquant_manquants', $this->document));
                continue;
            }

            if (!ParcellaireConfiguration::getInstance()->isManquantAllPourcentageAllowed() && $parcelle->pourcentage < ParcellaireConfiguration::getInstance()->getManquantPCMin()) {
                $this->addPoint(self::TYPE_WARNING, 'pourcentage_inf_min', "Parcelle n° {$parcelle->section} {$parcelle->numero_parcelle} - Le pourcentage de pied mort est de {$parcelle->pourcentage}%", $this->generateUrl('parcellairemanquant_manquants', $this->document));
            }
        }

        $this->addPoint(self::TYPE_ENGAGEMENT, 'engagement_rendement_maximal', null);
        $this->addPoint(self::TYPE_ENGAGEMENT, 'engagement_vci', null);
        $this->addPoint(self::TYPE_ENGAGEMENT, 'engagement_transmission', null);
    }
}
