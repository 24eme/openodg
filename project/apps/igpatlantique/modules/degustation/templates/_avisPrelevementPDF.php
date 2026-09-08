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
<h2><strong>NOTIFICATION DE L'ODG IGP ATLANTIQUE : </strong>avis de prélèvement et de dégustation de vins IGP Atlantique</h2>
<p>&nbsp;</p>
<div style="margin-left:50px; margin-right:50px;">
    <table class="no-border">
        <tbody>
            <tr>
                <td style="width:60%;">A: <?php echo $etablissement?><br/>
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
Nous avons bien reçu votre déclaration de conditionnement datée du 21/04/2026. Conformément à la nouvelle procédure d’agrément, nous vous informons que nous allons effectuer un prélèvement de votre pour un contrôle produit dans le cadre du contrôle interne :
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
        <?php foreach ($lots as $operateurlots): ?>
            <?php foreach ($operateurlots as $lot): ?>
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
        <?php endforeach; ?>
    </tbody>
</table>

<br/>
Nous avons confié la réalisation des prises d’échantillon et la dégustation à <strong>QUALI-BORDEAUX</strong>.
<br/>
<br/>
Cet organisme va prendre contact avec vous pour convenir d’une date de prélèvement pour ce lot.
<br/>
<br/>
Nous reviendrons vers vous dès que nous aurons le résultat de ce contrôle, pour vous indiquer si ce lot est apte à être commercialisé sous la mention IGP Atlantique.
<br/>
<br/>
Restant à votre disposition,
<br/>
<br/>
Bien cordialement,
<br/>
<br/>
P/o
<br/>
Elisabeth GALINEAU
<br/>
