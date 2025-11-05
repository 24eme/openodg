<?php use_helper('Float'); ?>

<ol class="breadcrumb">
    <li><a href="<?php echo url_for('parcellaire'); ?>">Parcellaire</a></li>
    <li><a href="<?php echo url_for('parcellaire_declarant', $controle->getEtablissementObject()); ?>">Parcellaire de <?php echo $controle->getEtablissementObject()->getNom() ?> (<?php echo $controle->getEtablissementObject()->identifiant ?>) </a></li>
    <li><span class="text-muted"><?php echo $controle->_id; ?></span></li>
</ol>

<div class="page-header no-border">
    <h2>Sélection des parcelles à controler</h2>
</div>

<?php if($parcellaire && $parcellaire->getGeoJson() != false): ?>
    <div id="jump">
        <a name="carte"></a>
        <?php include_partial('parcellaire/parcellaireMap', array('parcellaire' => $parcellaire, 'js' => 'parcelles-maker-selections.js')); ?>
    </div>
<?php endif; ?>


<p>Superficie sélectionnée : <span id="total_surfaces_selectionnees">0</span> <span class="text-muted small"><?php echo ParcellaireConfiguration::getInstance()->isAres() ? 'ares' : 'ha' ?></span></p>

<form action="<?php echo url_for("controle_parcelles", $controle) ?>" method="post" class="form-horizontal">

    <table id="tableParcelle" class="table table-bordered table-condensed table-striped tableParcellaire">
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
                <td class="text-center inputTd">
                    <label class="switch-xl">
                        <input <?php if ($controle->hasParcelle($parcelle->getParcelleId())): ?>checked="checked"<?php endif; ?> type="checkbox" name="parcelles[]" data-superficie="<?php echo $parcelle->getSuperficie(ParcellaireConfiguration::getInstance()->isAres()? ParcellaireClient::PARCELLAIRE_SUPERFICIE_UNIT_ARE : null) ?>" value="<?php echo $parcelle->getParcelleId() ?>" data-parcelleid="<?php echo $parcelle->getParcelleId() ?>" />
                        <span class="slider-xl round"></span>
                    </label>
                </td>
                <td>
                    <button type="button" class="btn btn-link" onclick="event.stopPropagation();showParcelle('<?php echo $parcelle->idu; ?>');"><i class="glyphicon glyphicon-map-marker"></i></button>
                </td>
            </tr>
        <?php  endforeach; ?>
        </tbody>
    </table>
    <div class="row row-margin row-button">
        <div class="col-xs-8"></div>
        <div class="col-xs-4 text-right"><button type="submit" class="btn btn-primary btn-upper">Sauvegarder</div>
    </div>
</form>
