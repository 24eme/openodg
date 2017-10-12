<?php use_helper('Float'); ?>

<h3>Distillation</h3>
<table class="table table-striped">
    <tbody>
        <tr>
            <td class="col-sm-6">Date de distillation</td>
            <td><?php echo $travauxmarc->getDateDistillationFr(); ?></td>
        </tr>
        <tr>
            <td>Distillation par un prestataire</td>
            <td><?php if($travauxmarc->distillation_prestataire): ?>Oui<?php else: ?>Non<?php endif; ?></td>
        </tr>
        <tr>
            <td>L’alambic utilisé est celui de la DI</td>
            <td><?php if($travauxmarc->alambic_connu): ?>Oui<?php else: ?>Non<?php endif; ?></td>
        </tr>
        <tr>
            <td>Adresse de distillation</td>
            <td>
                <?php echo $travauxmarc->adresse_distillation->adresse ?><br />
                <?php echo $travauxmarc->adresse_distillation->code_postal ?>
                <?php echo $travauxmarc->adresse_distillation->commune ?>
            </td>
        </tr>
    </tbody>
</table>

<h3>Fournisseurs de Marcs</h3>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th class="col-sm-6">Nom</th>
            <th class="col-sm-3 text-center">Date de livraison</th>
            <th class="col-sm-3 text-center">Quantité de Marcs (en kg)</th>
        </th>
    </thead>
    <tbody>
        <?php foreach ($travauxmarc->fournisseurs as $fournisseur): ?>
            <tr>
                <td><?php echo $fournisseur->nom ?></td>
                <td class="text-center"><?php echo $fournisseur->getDateLivraisonFr() ?></td>
                <td class="text-right"><?php echoFloat($fournisseur->quantite) ?> <small class="text-muted">kg</small></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
