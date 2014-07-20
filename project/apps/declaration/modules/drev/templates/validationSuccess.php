<?php include_partial('drev/step', array('step' => 'validation', 'drev' => $drev)) ?>

<div class="frame">
    <?php if($validation->hasPoints()): ?>
        <?php include_partial('drev/pointsAttentions', array('drev' => $drev, 'validation' => $validation)); ?>
    <?php endif; ?>
    <?php include_partial('drev/recap', array('drev' => $drev)); ?>
    <?php include_partial('drev/engagements', array('drev' => $drev)); ?>
</div>

<div class="row row-margin">
    <div class="col-xs-4">
        <a href="<?php echo url_for("drev_controle_externe", $drev) ?>" class="btn btn-primary btn-lg btn-block btn-prev">Étape précendente</a>
    </div>
    <div class="col-xs-4 text-center">
        <a href="" class="btn btn-default btn-lg">
            <span class="glyphicon glyphicon-save"></span>
            Prévisualiser
        </a>
    </div>
    <div class="col-xs-4">
        <button type="submit" class="btn btn-primary btn-lg btn-block btn-next">Valider</a>
    </div>
</div>