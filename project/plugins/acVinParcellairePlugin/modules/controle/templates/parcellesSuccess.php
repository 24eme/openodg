<?php use_helper('Float'); ?>

<form action="<?php echo url_for("controle_parcelles", $controle) ?>" method="post" class="form-horizontal">

    <table class="table table-bordered table-condensed table-striped tableParcellaire">
        <thead>
            <tr>
                <th class="col-xs-2">Commune</th>
                <th class="col-xs-2">Lieu-dit</th>
                <th class="col-xs-1" style="text-align: right;">Section</th>
                <th class="col-xs-1">N° parcelle</th>
                <th class="col-xs-3">Cépage</th>
                <th class="col-xs-1">Année plantat°</th>
                <th class="col-xs-1" style="text-align: right;">Surface <span class="text-muted small"><?php echo ParcellaireConfiguration::getInstance()->isAres() ? 'ares' : 'ha' ?></span></th>
                <th class="col-xs-1 text-center">Contrôle ?</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($controle->getParcellaire()->getParcelles() as $parcelle): ?>
            <tr style="cursor: pointer;" class="parcellerow switch-to-higlight <?php if ($controle->hasParcelle($parcelle->getParcelleId())): ?>success<?php endif; ?>" >
                <td><?php echo $parcelle->commune; ?></td>
                <td><?php echo $parcelle->lieu; ?></td>
                <td style="text-align: right;"><?php echo $parcelle->section; ?></td>
                <td><?php echo $parcelle->numero_parcelle; ?></td>
                <td><span class="text-muted"><?php echo $parcelle->getProduitLibelle(); ?></span> <?php echo $parcelle->cepage; ?></td>
                <td class="text-center"><?php echo $parcelle->campagne_plantation; ?></td>
                <td class="text-right"><?php echoFloatFr($parcelle->getSuperficie(ParcellaireConfiguration::getInstance()->isAres()? ParcellaireClient::PARCELLAIRE_SUPERFICIE_UNIT_ARE : null)); ?></td>
                <td class="text-center"><input <?php if ($controle->hasParcelle($parcelle->getParcelleId())): ?>checked="checked"<?php endif; ?> type="checkbox" name="parcelles[]" value="<?php echo $parcelle->getParcelleId() ?>" class="bsswitch" data-size='small' data-on-text="<span class='glyphicon glyphicon-ok-sign'></span>" data-off-text="<span class='glyphicon'></span>" data-on-color="success" /></td>
            </tr>
        <?php  endforeach; ?>
        </tbody>
    </table>
    <div class="row row-margin row-button">
        <div class="col-xs-8"></div>
        <div class="col-xs-4 text-right"><button type="submit" class="btn btn-primary btn-upper">Continuer <span class="glyphicon glyphicon-chevron-right"></span></button></div>
    </div>
</form>
