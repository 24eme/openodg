<?php include_partial('drev/breadcrumb', array('drev' => $drev )); ?>
<?php include_partial('drev/step', array('step' => 'vci', 'drev' => $drev)) ?>

<div class="page-header">
    <h2>Répartition du VCI</h2>
</div>

<div style="margin-top: 20px;" class="row row-margin row-button">
    <div class="col-xs-6">
        <a href="<?php echo url_for("drev_revendication", $drev) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</a>
    </div>
    <form role="form" action="<?php echo url_for("drev_vci", $drev) ?>" method="post" class="ajaxForm" id="form_vci_drev_<?php echo $drev->_id; ?>">
    <div class="col-xs-6 text-right">
        <?php if ($drev->exist('etape') && $drev->etape == DrevEtapes::ETAPE_VALIDATION): ?>
            <button id="btn-validation" type="submit" class="btn btn-primary btn-upper">Retourner à la validation <span class="glyphicon glyphicon-check"></span></button>
            <?php else: ?>
            <button type="submit" class="btn btn-primary btn-upper">Continuer vers la validation</span></button>
        <?php endif; ?>
    </div>
    </form>
</div>
