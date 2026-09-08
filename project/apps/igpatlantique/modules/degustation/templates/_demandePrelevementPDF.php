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
<h2 style="text-align: center">Demande de prélèvement et de dégustation - <?php echo $degustation->lieu; ?></h2>
<h2 style="text-align: center">Contrôle interne produit</h2>
<p>&nbsp;</p>
<p>Le syndicat des producteurs de vins IGP Atlantique</p>
<p>&nbsp;</p>
<p>Madame, Monsieur,</p>
<p>&nbsp;</p>
<p>Voici une demande de prélèvement et de dégustation pour la campagne <strong><?php echo $degustation->campagne ?></strong>. Les opérateurs sont les suivants avec le détail des lots à prélever :</p>

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
                            <th style="width:10%;text-align: center;">N° lgmt</th>
                            <th style="width:40%;text-align: center;">Désignation - Cépage</th>
                            <th style="width:7%;text-align: right;">Mill.</th>
                            <th style="width:13%;text-align: right;">Volume (hl)</th>
                            <th style="width:15%;text-align: center;">Destination</th>
                            <th style="width:15%;text-align: center;">Date condi.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lots as $lot): ?>
                            <tr>
                                <td style="width:10%;text-align: center;"><?php echo $lot->numero_logement_operateur ?></td>
                                <td style="width:40%;text-align: center;">
                                    <?php echo $lot->produit_libelle ?>
                                    <?php foreach ($lot->cepages as $cepage => $vol) {echo $cepage . ' ';} ?>
                                </td>
                                <td style="width:7%;text-align: right;"><?php echo $lot->millesime ?></td>
                                <td style="width:13%;text-align: right;"><?php echo $lot->volume ?></td>
                                <td style="width:15%;text-align: center;"><?php echo $lot->destination_type ?></td>
                                <td style="width:15%;text-align: center;"><?php echo format_date($lot->destination_date, "dd/MM/yyyy") ?></td>
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
<p>Nous vous remercions de bien vouloir nous tenir informés du résultat de la dégustation.
<p>Cordialement,</p>
<p>&nbsp;</p>
<p></p>
<br/>
<p>Le syndicat des producteurs de vins IGP Altlantique</p>
<br/>
