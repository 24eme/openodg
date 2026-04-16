<?php use_helper('TemplatingPDF'); ?>
<?php use_helper('Text') ?>
<?php use_helper('Date'); ?>

<style>
table, th, td {
    border: 1px solid black;
    border-collapse: collapse;
}

.center-grey {
    text-align: center;
    background-color: #cccccc;
}

.grey {
    background-color: #eeeeee;
}
</style>

<table>
    <thead>
        <tr>
            <td style="text-align: center;" colspan="3" rowSpan="4"><?php echo tdStart() ?>&nbsp;<img style="height: 66px;" src="<?php echo sfConfig::get('sf_web_dir').'/images/pdf/logo_cotesdeprovence.jpg' ?>" /></td>
            <td class="grey" rowSpan="1">Référence:</td>
        </tr>
        <tr>
            <td class="grey" rowSpan="1"><strong>FO-34</strong></td>
        </tr>
        <tr>
            <td class="grey" rowSpan="1">Révision et date :</td>
        </tr>
        <tr>
            <td class="grey" rowSpan="1"><strong>1 – 13/03/24</strong></td>
        </tr>
        <tr>
            <td style="text-align: center;" rowSpan="1" colspan="3">FICHE DE NOTIFICATION MANQUEMENT OPERATEUR</td>
            <td rowSpan="1" class="grey"><strong>Page 1 sur 1</strong></td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="center-grey" colspan="4"><strong>Identification de l'opérateur</strong></td>
        </tr>
        <tr>
            <td colspan="4"><?php echo $controle->identifiant .' '.$controle->declarant->raison_sociale ?></td>
        </tr>
        <tr>
            <td class="center-grey" colspan="4"><strong>Identification MANQUEMENT</strong></td>
        </tr>
        <tr>
            <td colSpan="2"><strong>Code : </strong><?php echo $manquementId ?></td>
            <td colSpan="2"><strong>N° du manquement : </strong></td>
        </tr>
        <tr>
            <td colSpan="4" style="height: 50px;"><strong>Points de contrôle :</strong> <?php echo $manquement->libelle_manquement; ?></td>
        </tr>
        <tr>
            <td colSpan="4" style="height: 200px;"><strong>Portée du manquement (parcelles, cépages...) :</strong><br/>
                <?php foreach($manquement->parcelles_id as $parcelle_id):
                    echo $controle->getInfoPdf($controle->identifiant, $parcelle_id); ?>
                    <br/>
                <?php endforeach; ?>
            </td>
        </tr>
        <tr>
            <td colSpan="4" style="height: 175px;"><strong>Détails du manquement constaté :</strong>
            <br/>
            <?php echo $manquement->libelle_manquement ?><br/><?php echo $controle->getObservationsFromManquement($manquementId); ?></td>
        </tr>
        <tr>
            <td colspan="2">Date du constat :<br/><?php echo format_date($manquement->constat_date, "dd/MM/yyyy", "fr_FR"); ?></td>
            <td colSpan="2" style="height: 40px;">Visa de l'agent de l'ODG :&nbsp;&nbsp;&nbsp;&nbsp;<?php echo CompteClient::getInstance()->find($controle->agent_identifiant)->getInitiales(); ?></td>
        </tr>
        <tr>
            <td class="center-grey" colspan="4">Mesure ODG</td>
        </tr>
        <tr>
            <td colSpan="4" style="height: 125px;"><?php echo ControleConfiguration::getInstance()->getMesureOdgFromConstatId($manquementId); ?></td>
        </tr>
        <tr>
            <td colSpan="4">Date limite de mise en œuvre des actions correctrices : <?php echo ControleConfiguration::getInstance()->getDelaisConstat($manquementId); ?></td>
        </tr>
        <tr>
            <td class="center-grey" colspan="4">Observations de l'opérateur</td>
        </tr>
        <tr>
            <td colSpan="4" style="height: 70px;"></td>
        </tr>
        <tr>
            <td colSpan="2">Date&nbsp;:&nbsp;<?php echo format_date($manquement->constat_date, "dd/MM/yyyy", "fr_FR"); ?></td>
            <td colSpan="2">Nom,&nbsp;Prénom&nbsp;:&nbsp;&nbsp;<?php echo $controle->audit->nom_prenom; ?></td>
        </tr>
    </tbody>
</table>
<p style="text-align:right;">Signature<?php if($controle->audit->operateur_signature): ?><br /><img style="height: 90px" src="<?php echo $controle->audit->operateur_signature; ?>" alt="" /><?php endif; ?></p>
