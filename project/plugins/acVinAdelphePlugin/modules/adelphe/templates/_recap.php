<?php use_helper('Float'); ?>
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
            <th class="col-xs-2 text-center">Condtionnement</th>
            <th class="col-xs-4 text-center">Volume conditionné <small class="text-muted">(hl)</small></th>
            <th class="col-xs-2 text-center">Prix unitaire <small class="text-muted">(€)</small></th>
            <th class="col-xs-4 text-center">Prix total <small class="text-muted">(€)</small></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="text-left">BIB <?php if($adelphe->isRepartitionForfaitaire()): ?><small>forfaitaire</small><?php endif; ?></td>
            <td class="text-right"><?php echo sprintFloat($adelphe->volume_conditionne_bib) ?> <small class="text-muted">hl</small></td>
            <td class="text-right"><?php echo sprintFloat($adelphe->prix_unitaire_bib) ?> <small class="text-muted">€</small></td>
            <td class="text-right"><?php echo sprintFloat($adelphe->getPrixBib()) ?> <small class="text-muted">€</small></td>
        </tr>
        <tr>
            <td class="text-left">Bouteille</td>
            <td class="text-right"><?php echo sprintFloat($adelphe->volume_conditionne_bouteille) ?> <small class="text-muted">hl</small></td>
            <td class="text-right"><?php echo sprintFloat($adelphe->prix_unitaire_bouteille) ?> <small class="text-muted">€</small></td>
            <td class="text-right"><?php echo sprintFloat($adelphe->getPrixBouteille()) ?> <small class="text-muted">€</small></td>
        </tr>
        <tr>
            <td class="text-left"><strong>Total</strong></td>
            <td class="text-right"><strong><?php echo sprintFloat($adelphe->volume_conditionne_total) ?> <small class="text-muted">hl</small></strong></td>
            <td class="text-right"></td>
            <td class="text-right"><strong><?php echo sprintFloat($adelphe->getPrixTotal()) ?> <small class="text-muted">€</small></strong></td>
        </tr>
    </tbody>
</table>

<?php endif; ?>
