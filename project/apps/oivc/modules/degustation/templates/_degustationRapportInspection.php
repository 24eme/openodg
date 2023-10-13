<?php use_helper("Date"); ?>
<?php use_helper('Lot'); ?>
<?php use_helper('TemplatingPDF'); ?>
<?php use_helper('Compte'); ?>

<style>
    <?php echo style(); ?>
    table {
        font-size: 12px;
    }

    th {
        font-weight: bold;
    }
</style>

<table style="text-align: center"><tr><td><strong>Rapport d'Inspection Contrôle Produit N° <?php echo $lot->unique_id ?></strong></td></tr></table>

<br/><br/>

<table border="1">
    <tbody>
        <tr><td>OPÉRATEUR</td></tr>
        <tr><td>Nom ou raison sociale de l'opérateur<br/>
                <?php echo $etablissement->getRaisonSociale() ?><br/>
                N° CVI : <?php echo $etablissement->getCvi() ?><i> </i><i> </i><i> </i><i> </i><i> </i><i> </i><i> </i> N° SIRET de l'opérateur : <?php echo formatSIRET($etablissement->getSiret()) ?>
        </td></tr>
        <tr><td>Catégorie : <?php echoCheck("Producteur viticole", true) ?> <?php echoCheck("Cave coopérative", false) ?> <?php echoCheck("Négociant", false) ?></td></tr>
        <tr><td>Adresse de l'opérateur<br/>
                <?php echo $etablissement->getAdresse() ?>, <?php echo $etablissement->getCodePostal() ?> <?php echo $etablissement->getCommune() ?><br/>
                Adresse du site de prélèvement<br/>
                <?php echo $lot->getAdresseLogement() ?> 
        </td></tr>
        <tr><td>PRÉLÈVEMENT N° ÉCHANTILLON : </td></tr>
        <tr><td>
            <table>
                <tr>
                    <td style="width: 10%">Type :</td><td><?php echoCheck('Aléatoire', true) ?></td><td><?php echoCheck('Aléatoire renforcé', false) ?></td><td><?php echoCheck('Vrac export', false) ?></td>
                </tr>
                <tr>
                    <td></td><td colspan="2"><?php echoCheck('Suite à contrôle produit ODG non conforme', false) ?></td><td><?php echoCheck('Sous traitance', false) ?></td>
                </tr>
                <tr>
                    <td></td><td colspan="2"><?php echoCheck('Suite à contrôle produit OIVC non conforme', false) ?></td><td></td>
                </tr>
                <tr>
                    <td></td><td><?php echoCheck('Recours', false) ?></td><td></td><td></td>
                </tr>
                <tr>
                    <td></td><td colspan="2"><?php echoCheck('Contrôle supplémentaire', false) ?></td><td></td>
                </tr>
            </table>
        </td></tr>
        <tr><td>Date du prélèvement : <?php echo DateTimeImmutable::createFromFormat('Y-m-d', $lot->preleve)->format('d/m/Y') ?></td></tr>
        <tr><td>Au moment du prélèvement, le vin est : </td></tr>
        <tr><td>Opérateur ou son représentant présent au cours du prélèvement<br/>
                Nom : XXXXX<i> </i><i> </i><i> </i><i> </i><i> </i><i> </i><i> </i>Fonction : YYYYYY
        </td></tr>
        <tr><td>Agent de l'OIVC<br/>
                Nom : XXXXX
        </td></tr>
        <tr><td>DESCRIPTION DU LOT</td></tr>
        <tr><td>
            <table cellspacing="0">
                <tr>
                    <td>AOC et couleur : <?php echo showProduitCepagesLot($lot, false) ?></td>
                    <td>Millésime : <?php echo $lot->millesime ?></td>
                    <td>Volume : <?php echo $lot->volume ?> <small>hl</small></td>
                </tr>
                <tr>
                    <td>N° lot : <?php echo $lot->numero_logement_operateur ?></td>
                    <td>N° Cuve si vrac : </td>
                    <td>Nb Cols : </td>
                </tr>
            </table>
        </td></tr>
        <tr>
            <td>Observations éventuelles :<br/><br/><br/><br/><br/><br/></td>
        </tr>
    </tbody>
</table>

<br/>
<br/>

<table border="1">
    <tr><td colspan="7">RÉSULTAT DU CONTRÔLE ET MANQUEMENTS OBSERVÉS</td></tr>
    <tr style="text-align: center">
        <td></td> <td>Date</td> <td>Conforme</td> <td>Non conforme</td> <td>Libellé manquement</td> <td>Code manquement</td> <td>Niveau de gravité</td>
    </tr>
    <tr>
        <td>Examen analytique<br/>(sous traitance)</td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td>
    </tr>
    <tr>
        <td>Examen organoleptique<br/></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td>
    </tr>
    <tr><td colspan="7">Date transmission INAO :</td></tr>
</table>

<br/><br/>

<table>
<tr>
  <td style="width: 50%"><strong>Nom du responsable d'inspection :</strong></td>
  <td style="width: 25%"><strong>Date :</strong></td>
  <td style="width: 25%"><strong>Signature :</strong></td>
</tr>
</table>

<br/><br/>
<br/><br/>
<br/><br/>
<br/><br/>

<p><small>Les points de contrôles n'ayant pas pu être vus par rapport au travail initialement prévu sont notés dans la ligne observations.<br/>
Les méthodes d'inspection utilisées sont décrites dans le Plan d'Inspection de l'AOC concernée.<br/>
En cas de sous traitance de point de contrôle noter SST à la ligne observations.</small></p>
