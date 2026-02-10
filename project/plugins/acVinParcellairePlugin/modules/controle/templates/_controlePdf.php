<?php use_helper('TemplatingPDF'); ?>
<?php use_helper('Text') ?>
<?php use_helper('Lot') ?>


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

<table>
    <thead>
        <tr>
            <td colspan="2">#LOGO#</td>
            <td colspan="6" style="text-align: center; height: 50px;">&nbsp;<br/><strong>AUDIT VIGNOBLE</strong></td>
        </tr>
        <tr>
            <td colSpan="4" style="height: 40px;"><u>Type de contrôle :</u><br/><?php echo $controle->type_tournee ?></td>
            <td colSpan="4"><u>Activités : </u><br/><?php foreach ($controle->getActiviteClient() as $activite) {echo $activite . '   ';} ?></td>
        </tr>
        <tr>
            <td colSpan="5" style="height: 30px;">&nbsp;<br/><strong>DATE : </strong><?php echo $controle->getDateFr(); ?><br/></td>
            <td colSpan="3">&nbsp;<br/><strong>AGENT : </strong></td>
        </tr>
        <tr>
            <td colSpan="8" style="text-align: center; height:40px;">&nbsp;<br/><strong><?php echo $controle->declarant->raison_sociale ?></strong><br/>N° SIRET : <?php echo $controle->declarant->siret ?>&nbsp;&nbsp;&nbsp;N° CVI : <?php echo $controle->declarant->cvi ?><br/></td>
        </tr>
        <tr>
            <td colspan="8" style="text-align: center;">&nbsp;<br/><strong><?php echo $controle->identifiant; ?></strong><br/></td>
        </tr>
        <tr>
            <td class="center-grey" colSpan="8"><strong>FICHE CONTACT</strong></td>
        </tr>
        <tr>
            <td colSpan="2"><strong>Adresse</strong></td>
            <td colSpan="6"><?php echo $controle->declarant->adresse . '<br/>' . $controle->declarant->code_postal . ' ' . $controle->declarant->commune; ?></td>
        </tr>
        <tr>
            <td colSpan="2"><strong>Tel.</strong></td>
            <td colSpan="2"><?php echo $controle->declarant->telephone_bureau ?></td>
            <td colSpan="2"><strong>Mobile</strong></td>
            <td colSpan="2"><?php echo $controle->declarant->telephone_mobile ?></td>
        </tr>
        <tr>
            <td colSpan="2"><strong>Mail</strong></td>
            <td colSpan="2"><?php echo $controle->declarant->email ?></td>
            <td colSpan="2"><strong>Fax</strong></td>
            <td colSpan="2"><?php echo $controle->declarant->fax ?></td>
        </tr>
        <tr>
            <td class="center-grey" colSpan="8"><strong>CONTRÔLE DOCUMENTAIRE</strong></td>
        </tr>
        <tr>
            <td colSpan="2"><strong>Surface totale (avec JV) :</strong></td>
            <td colSpan="2" style="text-align: center;"><strong><?php echo $parcellaire->getSuperficieTotale(true) ?></strong></td>
            <td colSpan="2">Surface totale en production&nbsp;:</td>
            <td colSpan="2" style="text-align: center;"><?php echo $parcellaire->getSuperficieTotale() ?></td>
        </tr>
        <tr>
            <td colSpan="2"><strong>PP de l'opérateur (ha) :</strong></td>
            <td colSpan="2"></td>
            <td colSpan="2">DGC :</td>
            <td colSpan="2"></td>
        </tr>
        <tr>
            <td colSpan="2"><strong>PP avec prise en compte des manquants (ha)</strong><br/><br/>à convertir en (hl) pour la revendication (surface*rendement autorisé)</td>
            <td colSpan="2"></td>
            <td colSpan="2">PP avec réfaction :</td>
            <td colSpan="2"></td>
        </tr>
        <tr>
            <td colSpan="2">Maturité :</td>
            <td colSpan="2"></td>
            <td colSpan="2"></td>
            <td colSpan="2"></td>
        </tr>
        <tr>
            <td colSpan="2">Convention VIFA : O/N :</td>
            <td colSpan="2"></td>
            <td colSpan="2"></td>
            <td colSpan="2"></td>
        </tr>
        <tr>
            <td class="center-grey" colSpan="8"><strong>SYNTHESE TERRAIN</strong></td>
        </tr>
        <tr>
            <td colSpan="3">&nbsp;<br/>Tous les points à contrôler ont été vus :</td>
            <td colSpan="5">&nbsp;<br/>
                <span style="font-family: Dejavusans">
                    ☐&nbsp;OUI&nbsp;&nbsp;&nbsp;☐&nbsp;NON&nbsp;; si non préciser :
            </span>
            <br/>
            </td>
        </tr>
        <tr>
            <td colSpan="3">&nbsp;<br/>Tous les points sont conformes :</td>
            <td colSpan="5">&nbsp;<br/>
                <span style="font-family: Dejavusans">
                <?php if ($controle->hasManquementsActif()): ?>
                    ☐&nbsp;OUI&nbsp;&nbsp;&nbsp;☒&nbsp;NON&nbsp;; si non préciser :
                <?php else: ?>
                    ☒&nbsp;OUI&nbsp;&nbsp;&nbsp;☐&nbsp;NON&nbsp;; si non préciser :
                <?php endif;?>
            </span>
            <br/>
            </td>
        </tr>
        <tr>
            <td colSpan="8" style="height: 120px;"><u>Observations de l'agent :</u></td>
        </tr>
        <tr>
            <td colSpan="8"><strong>&nbsp;<br/>L'opérateur ou son représentant :</strong><br/></td>
        </tr>
        <tr>
            <td colSpan="2">&nbsp;<br/>Nom et Prénom :<br/></td>
            <td colSpan="4">&nbsp;</td>
            <td colSpan="2" style="text-align: center;">&nbsp;<br/>Signature :<br/></td>
        </tr>
        <tr>
            <td colSpan="6" style="height: 65px">Observations :</td>
            <td colSpan="2">&nbsp;</td>
        </tr>
    </thead>
</table>
