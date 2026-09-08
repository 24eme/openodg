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
<br/><br/>
<h1 style="text-align: center">NOTIFICATION DE L'ODG IGP ATLANTIQUE</h1>
<h2 style="text-align: center">Demande de prélèvement et de dégustation de vins IGP Atlantique</h2>
<h2 style="text-align: center">Contrôle interne produit</h2>
<p>&nbsp;</p>
<p>&nbsp;</p>
Madame, Monsieur,
<br/>
<br/>
Voici une demande de prélèvement et de dégustation <?php echo $degustation->lieu ?> pour la campagne <strong><?php echo $degustation->campagne ?></strong>. Les opérateurs sont les suivants :

<ul>
<?php foreach ($lots as $famille => $operateurs): ?>
    <li>
        <u><b><?php echo $famille ?></b></u>
        <ul>
            <?php foreach ($operateurs as $lots): ?>
                <li> <?php echo $lots[0]->declarant_nom ?> <span style="color: gray;"><?php echo $lots[0]->getEtablissement()->cvi. ' '.$lots[0]->getEtablissement()->siret; ?></span><br/>
                <span style="color: gray;">(<?php echo $lots[0]->adresse_logement ?>)</span><br/>
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
                </li>
            <?php endforeach; ?>
        </ul>
    </li>
<?php endforeach; ?>
</ul>
<?php if ($activite !== 'CONDITIONNEUR'): ?>
<br/>
<br/>
Conformément au Plan de contrôle de l’ODG IGP ATLANTIQUE, les dégustations devront avoir lieu au plus tard <strong>12 jours ouvrés</strong> après la date d'envoi de l'avis de prélèvement.
<?php endif; ?>
<br/>
<br/>
Vous trouverez ci-joint, la déclaration de revendication, ainsi que le bulletin d’analyse du lot. Nous vous demandons de bien vouloir nous tenir informés du résultat de la dégustation.
<br/>
<br/>
Meilleures salutations,
<br/>
<br/>
P/o
<br/>
<p><?php echo Organisme::getInstance(null, 'degustation')->getResponsable(); ?></p>
<br/>
