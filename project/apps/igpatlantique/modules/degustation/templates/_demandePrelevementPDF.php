<?php use_helper("Date"); ?>
<?php use_helper('Lot'); ?>
<?php use_helper('TemplatingPDF'); ?>

<style>
    th {
        font-weight: bold;
    }

    table, th, td {
        border: 1px solid black;
    }

    .no-border, .no-border td {
        border: 1px solid lightgrey;
    }

    h2 {
        font-weight: normal !important;
    }

    .table-operateur {
        font-size: 12px;
    }

    .table-operateur th {
    background-color: #003366;
    color: white;
    font-weight: bold;
}
</style>
<br/>
<h2><strong>NOTIFICATION DE L'ODG IGP ATLANTIQUE : </strong>demande de prélèvement et de dégustation de vins IGP Atlantique : contrôle interne produit</h2>
<p>&nbsp;</p>
<div style="margin-left:50px; margin-right:50px;">
    <table class="no-border">
        <tbody>
            <tr>
                <td style="width:60%;">A: QUALI-BORDEAUX<br/>
                    Email: declarationvin@qualibordeaux.fr
                </td>
                <td style="width:40%;">
                    Le : <?php echo date('d/m/Y'); ?>
                </td>
            </tr>
            <tr>
                <td>DE L'ODG IGP ATLANTIQUE : Elisabeth GALINEAU<br/></td>
            </tr>
        </tbody>
    </table>
</div>
<br/>
<br/>
Madame, Monsieur,
<br/>
<br/>
Pour la campagne <strong><?php echo $degustation->campagne ?></strong>, dans le cadre de l’organisation mise en place, vous avez accepté de réaliser dans le cadre d’une prestation de service pour le compte de l’ODG de vins IGP Atlantique, une partie du contrôle interne à savoir, prélèvements et dégustations.
<br/>
<br/>
Dans ce schéma, nous vous demandons de bien vouloir procéder à cette opération suivante :

<ul>
<?php foreach ($lots as $famille => $operateurs): ?>
    <br/>
    <br/>
    <li>
        <?php echo EtablissementFamilles::$familles[$famille]; ?>
        <br/>
        <div>
            <?php foreach ($operateurs as $lots): ?>
<b>» <?php echo $lots[0]->declarant_nom ?></b>
                <br/>
                Adresse entrepôt : <?php echo $lots[0]->adresse_logement ?>
                <br/>
                <br/>
                <table class="table-operateur">
                    <thead>
                        <tr>
                            <th style="width:10%; height:25px;">N° lgmt</th>
                            <th style="width:15%; height:25px;">Cépage</th>
                            <th style="width:15%; height:25px;">Désignation</th>
                            <th style="width:7%; height:25px; text-align: right;">Mill.</th>
                            <th style="width:13%; height:25px; text-align: right;">Volume (hl)</th>
                            <th style="width:25%; height:25px;">Destination</th>
                            <th style="width:15%; height:25px;">Date condi.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lots as $lot): ?>
                            <tr>
                                <td style="width:10%;"><?php echo $lot->numero_logement_operateur ?></td>
                                <td style="width:15%;"><?php foreach ($lot->cepages as $cepage) {echo $cepage . ' ';} ?></td>
                                <td style="width:15%;"><?php echo $lot->produit_libelle ?></td>
                                <td style="width:7%; text-align: right;"><?php echo $lot->millesime ?></td>
                                <td style="width:13%; text-align: right;"><?php echo $lot->volume ?></td>
                                <td style="width:25%;"><?php echo $lot->destination_type ?></td>
                                <td style="width:15%;"><?php echo format_date($lot->destination_date, "dd/MM/yyyy") ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <br/>
                <br/>
            <?php endforeach; ?>
        </div>
    </li>
<?php endforeach; ?>
</ul>
<br/>
<strong>Conformément au Plan de contrôle de l’ODG IGP ATLANTIQUE, les dégustations devront avoir lieu au plus
tard le : </strong>
<br/>
<br/>
Vous trouverez ci-joint, les déclarations de revendication de vins IGP Atlantique, ainsi que les bulletins d’analyse des lots.
<br/>
<br/>
Nous vous demandons de bien vouloir nous tenir informés des résultats des dégustations.
<br/>
<br/>
Nous restons à votre entière disposition pour vous aider dans cette démarche et travailler ensemble à l’amélioration de la procédure.
<br/>
<br/>
Cordiales salutations,
<br/>
<br/>
P/o
<br/>
Elisabeth GALINEAU
<br/>
