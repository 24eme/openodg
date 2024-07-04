<?php echo $form->renderHiddenFields(); ?>
<?php echo $form->renderGlobalErrors(); ?>

<?php foreach ($parcellaireIrrigable->declaration->getParcellesByCommune() as $commune => $parcelles): ?>
<h3><?php echo $commune; ?></h3>
<table class="table table-bordered table-condensed table-striped duplicateChoicesTable tableParcellaire">
    <thead>
        <tr>
            <th class="col-xs-2">Lieu-dit</th>
            <th class="col-xs-1">Section /<br />N° parcelle</th>
            <th class="col-xs-2">Cépage</th>
            <th class="col-xs-1 text-center">Année plantat°</th>
            <th class="col-xs-1 text-right">Surf. <span class="text-muted small">(ha)</span></th>
            <th class="col-xs-2 text-center">Type de matériel</th>
            <th class="col-xs-2 text-center">Type de ressource</th>
            <th class="col-xs-1 text-center">Dupliquer les types</th>

        </tr>
    </thead>
    <tbody>
    <?php
        foreach ($parcelles as $parcelle):
            $produitKey = str_replace('/declaration/', '', $parcelle->getProduit()->getHash());
        if (isset($form[$produitKey][$parcelle->getKey()])):
    ?>
        <tr class="vertical-center" id="tr_<?php echo str_replace("/","-",$produitKey)."-".$parcelle->getKey();?>">
            <td><?php echo $parcelle->lieu; ?></td>
            <td style="text-align: center;"><?php echo $parcelle->section; ?> <span class="text-muted">/</span> <?php echo $parcelle->numero_parcelle; ?></td>
            <td><span class="text-muted"><?php echo $parcelle->getProduitLibelle(); ?></span> <?php echo $parcelle->cepage; ?></td>
            <td class="text-center"><?php echo $parcelle->campagne_plantation; ?></td>
            <td class="text-right"><?php echoFloatFr($parcelle->superficie, 4); ?></td>
            <td>
                <div style="margin-bottom: 0;" class="form-group <?php if($form[$produitKey][$parcelle->getKey()]['materiel']->hasError()): ?>has-error<?php endif; ?>">
                    <?php echo $form[$produitKey][$parcelle->getKey()]['materiel']->renderError() ?>
                    <div class="col-xs-12">
                        <?php echo $form[$produitKey][$parcelle->getKey()]['materiel']->render(array('class' => 'form-control select2 select2-offscreen select2permissifNoAjax toDuplicate', "placeholder" => "Saisir un matériel", "data-new" => "ajouter", "data-duplicate" => "materiel", "data-choices" => json_encode(ParcellaireIrrigableClient::getInstance()->getMateriels($form[$produitKey][$parcelle->getKey()]['materiel']->getValue())))) ?>
                    </div>
                </div>
            </td>
            <td>
                <div style="margin-bottom: 0;" class="form-group <?php if($form[$produitKey][$parcelle->getKey()]['ressource']->hasError()): ?>has-error<?php endif; ?>">
                    <?php echo $form[$produitKey][$parcelle->getKey()]['ressource']->renderError() ?>
                    <div class="col-xs-12">
                        <?php echo $form[$produitKey][$parcelle->getKey()]['ressource']->render(array('class' => 'form-control select2 select2-offscreen select2permissifNoAjax toDuplicate', "placeholder" => "Saisir une ressource", "data-new" => "ajouter", "data-duplicate" => "ressources", "data-choices" => json_encode(ParcellaireIrrigableClient::getInstance()->getRessources($form[$produitKey][$parcelle->getKey()]['ressource']->getValue())))) ?>
                    </div>
                </div>
            </td>
            <td class="text-center">
                <div style="margin-bottom: 0;" class="form-group">
                    <div class="col-xs-12">
                        <a <?php if(!$parcelle->materiel || !$parcelle->ressource): ?> style="opacity:0.6;" <?php endif; ?>
                            data-confirm="Voulez-vous appliquer le type de materiel MATERIEL et de ressource RESSOURCE pour toutes les parcelles qui suivent de cette commune?" data-alert="Veuillez selectionner un type de materiel et un type de ressource#Ce bouton permet de dupliquer le type de materiel et de ressources pour toutes les parcelles qui suivent de cette commune."
                            class="btn btn-sm btn-default duplicateBtn <?php if(!$parcelle->materiel || !$parcelle->ressource): ?> inactif<?php endif; ?>" data-target="tr_<?php echo str_replace("/","-",$produitKey)."-".$parcelle->getKey();?>" ><span class="glyphicon glyphicon-arrow-down"></span></a>
                    </div>
                </div>
            </td>
        </tr>
    <?php  endif; endforeach; ?>
    </tbody>
</table>
<?php  endforeach; ?>
