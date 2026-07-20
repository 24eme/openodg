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
<?php foreach ($lots as $lot): ?>
<table class="table-operateur">
    <thead>
        <th>Fournisseur</th>
        <th>Désignation</th>
        <th>Millésime</th>
        <th>Volume (hl)</th>
        <th>Dates de mises</th>
    </thead>
    <tbody>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tobdy>
</table>
<?php endforeach; ?>
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
