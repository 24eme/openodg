<?php use_helper('TemplatingPDF'); ?>
<?php use_helper('Text') ?>

<style>
table, th, td {
    border: 1px solid black;
    border-collapse: collapse;
}

.center-grey {
    text-align: center;
    background-color: #cccccc;
}
</style>

<?php foreach ($manquements as $rtmId => $manquement): ?>
<table>
    <thead>
        <tr>
            <td style="text-align: center;" colspan="3" rowSpan="4">#LOGO#</td>
            <td rowSpan="1">Référence:</td>
        </tr>
        <tr>
            <td rowSpan="1"></td>
        </tr>
        <tr>
            <td rowSpan="1">Révision et date :</td>
        </tr>
        <tr>
            <td rowSpan="1">x - xx/xx/26</td>
        </tr>
        <tr>
            <td style="text-align: center;" rowSpan="1" colspan="3">FICHE DE NOTIFICATION MANQUEMENT OPERATEUR</td>
            <td rowSpan="1">Page 1 sur 1</td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="center-grey" colspan="4">Identification de l'opérateur</td>
        </tr>
        <tr>
            <td colspan="4"><?php echo $controle->identifiant .' '.$controle->declarant->raison_sociale?></td>
        </tr>
        <tr>
            <td class="center-grey" colspan="4">Identification MANQUEMENT</td>
        </tr>
        <tr>
            <td colSpan="2"><strong>Code : </strong><?php echo $rtmId ?></td>
            <td colSpan="2"><strong>N° du manquement : </strong></td>
        </tr>
        <tr>
            <td colSpan="4"><strong>Points de contrôle :</strong> <?php echo $manquement->libelle_manquement; ?></td>
        </tr>
        <tr>
            <td colSpan="4"><strong>Portée du manquement (parcelles, cépages...) :</strong></td>
        </tr>
        <tr>
            <td colspan="4" style="height: 230px;"></td>
        </tr>
        <tr>
            <td colSpan="4"><strong>Détails du manquement constaté :</strong></td>
        </tr>
        <tr>
            <td colspan="4" style="height: 200px;"></td>
        </tr>
        <tr>
            <td colspan="2">Date du constat : <?php echo $manquement->constat_date; ?></td>
            <td colSpan="2" style="height: 40px;">Visa de l'agent de l'ODG : </td>
        </tr>
        <tr>
            <td class="center-grey" colspan="4">Mesure ODG</td>
        </tr>
        <tr>
            <td colSpan="4" style="height: 100px;"></td>
        </tr>
        <tr>
            <td colSpan="4">Date limite de mise en œuvre des actions correctrices :</td>
        </tr>
        <tr>
            <td class="center-grey" colspan="4">Observations de l'opérateur</td>
        </tr>
        <tr>
            <td colSpan="4" style="height: 95px;"></td>
        </tr>
        <tr>
            <td colSpan="2" style="height: 45px">Date</td>
            <td colSpan="2" rowSpan="2" style="height: 45px">Signature</td>
        </tr>
        <tr>
            <td colSpan="2" style="height: 45px">Nom, Prénom :</td>
        </tr>
    </tbody>
</table>
<?php endforeach; ?>
