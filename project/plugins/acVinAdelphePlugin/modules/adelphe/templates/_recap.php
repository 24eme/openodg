<div class="well mb-5">
    <?php include_partial('etablissement/blocDeclaration', array('etablissement' => $adelphe->getEtablissementObject())); ?>
</div>

<?php if ($adelphe->redirect_adelphe): ?>

<p class="bg-warning text-center p-5 mb-5">
    <span class="glyphicon glyphicon-alert"></span><br />
    Votre volume conditionné déclaré est supérieur au seuil géré par votre syndicat. Vous devez déclarer directement sur le site de l'ADELPHE.</h4>
</p>

<?php else: ?>

<h3>Votre contribution Adelphe</h3>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th class="col-xs-2 text-center"></th>
            <th class="col-xs-4 text-center">Volume conditionné<small> (hl)</small></th>
            <th class="col-xs-2 text-center">Prix unitaire</th>
            <th class="col-xs-4 text-center">Prix total</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="text-left">BIB</td>
            <td class="text-right"><?php echo $adelphe->volume_conditionne_bib ?></td>
            <td class="text-right"><?php echo $adelphe->prix_unitaire_bib ?>€</td>
            <td class="text-right"><?php echo $total_bib = $adelphe->volume_conditionne_bib * $adelphe->prix_unitaire_bib ?>€</td>
        </tr>
        <tr>
            <td class="text-left">Bouteille</td>
            <td class="text-right"><?php echo $adelphe->volume_conditionne_bouteille ?></td>
            <td class="text-right"><?php echo $adelphe->prix_unitaire_bouteille ?>€</td>
            <td class="text-right"><?php echo $total_bouteille = $adelphe->volume_conditionne_bouteille * $adelphe->prix_unitaire_bouteille ?>€</td>
        </tr>
        <tr>
            <td class="text-left"><strong>Total</strong></td>
            <td class="text-right"><?php echo $adelphe->volume_conditionne_total ?></td>
            <td class="text-right">---</td>
            <td class="text-right"><?php echo $total_total = $total_bib + $total_bouteille ?>€</td>
        </tr>
    </tbody>
</table>

<?php endif; ?>
