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
        border: 0px solid lightgrey;
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
<h1 style="text-align: center;">NOTIFICATION DE L'ODG IGP ATLANTIQUE</h1>
<h2 style="text-align: center;">Avis de prélèvement et de dégustation de vins IGP Atlantique</h2>
<p>Le : <?php echo date('d/m/Y'); ?></p>
<p>A: <?php echo $etablissement?></p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>Madame, Monsieur,</p>
<p>Nous avons bien reçu votre déclaration de lots. Conformément au plan de contrôle, nous vous informons que nous allons effectuer un prélèvement des lots suivants pour une dégustation dans le cadre du contrôle interne.</p>
<?php $is_conditionneur = false; ?>
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
        <?php foreach ($lots as $operateurlots): ?>
            <?php foreach ($operateurlots as $lot): ?>
                <?php if (strpos('Conditionnement', $lot->initial_type) !== false) $is_conditionneur = true; ?>
        <tr>
            <td style="width:10%;text-align: center;"><?php echo $lot->numero_logement_operateur ?></td>
            <td style="width:40%;text-align: center;">
                <?php echo $lot->produit_libelle ?>
                <?php foreach ($lot->cepages as $cepage => $val) {echo ' - '.$cepage;} ?>
            </td>
            <td style="width:7%;text-align: right;"><?php echo $lot->millesime ?></td>
            <td style="width:13%;text-align: right;"><?php echo $lot->volume ?></td>
            <td style="width:15%;text-align: center;"><?php echo $lot->destination_type ?></td>
            <td style="width:15%;text-align: center;"><?php echo format_date($lot->destination_date, "dd/MM/yyyy") ?></td>
        </tr>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </tbody>
</table>

<p>Nous avons confié la réalisation des prélèvements et de la dégustation des vins IGP Atlantique à <strong><?php echo $degustation->lieu; ?></strong>.</p>
<p>Vous serez contacté prochainement  pour convenir d’une date de prélèvement pour vos lots.</p>
<?php if ($is_conditionneur): ?>
<p>Si vous avez réalisé plus de 4 mises l'année dernière, vous ne serez prélevés que 2 fois cette année.</p>
<?php endif; ?>
<p>Restant à votre disposition,</p>
<p>Bien cordialement,</p>
<br/>
<p>le Syndicat des producteurs de vins IGP Atlantique</p>
