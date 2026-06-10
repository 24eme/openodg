<?php echo $form->renderHiddenFields(); ?>
<?php echo $form->renderGlobalErrors(); ?>

<?php foreach ($drap->declaration->getParcellesByCommune() as $commune => $parcelles): ?>
<h3><?php echo $commune; ?></h3>
<table class="table table-bordered table-condensed table-striped duplicateChoicesTable tableParcellaire">
    <thead>
        <tr>
            <th class="col-xs-2">Lieu-dit</th>
            <th class="col-xs-1">Section /<br />N° parcelle</th>
            <th class="col-xs-2">Cépage</th>
            <th class="col-xs-1 text-center">Année plantat°</th>
            <th class="col-xs-2 text-right">Surface renoncée <span class="text-muted small">(<?php echo ParcellaireConfiguration::getInstance()->isAres() ? 'ares' : 'ha' ?>)</span></th>
            <th class="col-xs-2 text-center">Appellation à laquelle on renonce</th>
            <th class="col-xs-2 text-center">Destination </br> Appellation revendiquée</th>
            <th class="col-xs-1 text-center">Dupliquer les destinations</th>

        </tr>
    </thead>
    <tbody>
    <?php
        foreach ($parcelles as $parcelle):
        if (isset($form[$parcelle->getParcelleId()])):
    ?>
        <tr class="vertical-center" id="tr_<?php echo $parcelle->getParcelleId();?>">
            <td><?php echo $parcelle->lieu; ?></td>
            <td style="text-align: center;"><?php echo $parcelle->section; ?> <span class="text-muted">/</span> <?php echo $parcelle->numero_parcelle; ?></td>
            <td><span class="text-muted"><?php echo $parcelle->getProduitLibelle(); ?></span> <?php echo $parcelle->cepage; ?></td>
            <td class="text-center"><?php echo $parcelle->campagne_plantation; ?></td>
            <td class="text-right">
                <div style="position: relative;" class=" <?php if($form[$parcelle->getParcelleId()]['superficie']->hasError()): ?>has-error<?php endif; ?>">
                    <small class="part-superficie text-muted" style="position: absolute; left: 8px; top: 8px;"></small>
                    <?php echo $form[$parcelle->getParcelleId()]['superficie']->renderError(); ?>
                    <?php if (ParcellaireConfiguration::getInstance()->isAres()): ?>
                        <?php echo $form[$parcelle->getParcelleId()]['superficie'](ParcellaireClient::PARCELLAIRE_SUPERFICIE_UNIT_ARE)->render(array("placeholder" => $parcelle->superficie_parcellaire, 'class' => 'valeur-superficie form-control text-right input-float')); ?>
                    <?php else: ?>
                        <?php echo $form[$parcelle->getParcelleId()]['superficie']->render(array("placeholder" => $parcelle->superficie_parcellaire, 'class' => 'valeur-superficie form-control text-right input-float')); ?>
                    <?php endif; ?>
                </div>
            </td>
            <?php if (ParcellaireConfiguration::getInstance()->hasDRaP()): ?>
            <td class="text-center"><?php echo $parcelle->getAppellation()->getLibelleComplet(); ?></td>
            <td>
                <div style="margin-bottom: 0;" class="form-group <?php if($form[$parcelle->getParcelleId()]['destination']->hasError()): ?>has-error<?php endif; ?>">
                    <?php echo $form[$parcelle->getParcelleId()]['destination']->renderError(); ?>
                    <div class="col-xs-12">
                        <?php echo $form[$parcelle->getParcelleId()]['destination']->render(array('class' => 'form-control select2 select2-offscreen select2permissifNoAjax toDuplicate', "placeholder" => "Saisir une destination", "data-new" => "ajouter", "data-duplicate" => "destination", "data-choices" => json_encode(DRaPConfiguration::getInstance()->getDestinations($form[$parcelle->getParcelleId()]['destination']->getValue())))) ?>
                    </div>
                </div>
            </td>
            <?php endif; ?>
            <td class="text-center">
                <div style="margin-bottom: 0;" class="form-group">
                    <div class="col-xs-12">
                        <a <?php if(!$parcelle->exist('destination') || !$parcelle->destination): ?> style="opacity:0.6;" <?php endif; ?>
                            data-confirm="Voulez-vous appliquer cette destination pour toutes les parcelles qui suivent de cette commune?" data-alert="Veuillez selectionner une destination#Ce bouton permet de dupliquer la destination pour toutes les parcelles qui suivent de cette commune."
                            class="btn btn-sm btn-default duplicateBtn <?php if(!$parcelle->exist('destination')  || !$parcelle->destination): ?> inactif<?php endif; ?>" data-target="tr_<?php echo $parcelle->getParcelleId();?>" ><span class="glyphicon glyphicon-arrow-down"></span></a>
                    </div>
                </div>
            </td>
        </tr>
    <?php  endif; endforeach; ?>
    </tbody>
</table>
<?php endforeach; ?>

<script>
    document.querySelectorAll("input[class^=valeur-superficie]").forEach(function (input) {
        input.addEventListener("change", (event) => {
            if (parseFloat(input.value || 0) < parseFloat(input.placeholder || 0)) {
                input.parentNode.querySelector(".part-superficie").innerText = "Partielle";
            } else {
                input.parentNode.querySelector(".part-superficie").innerText = "Totale";
            }
        })
        input.dispatchEvent(new Event("change"));
    });

</script>
