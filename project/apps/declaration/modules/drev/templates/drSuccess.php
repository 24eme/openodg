<?php include_partial('drev/step', array('step' => null, 'drev' => $drev)) ?>

<form action="" method="post" class="form-horizontal">
    <div class="frame clearfix">
        <p>Voulez vous récupérer la DR ?</p>
        <a class="btn btn-default" href="">Passer</a>
        <a class="btn btn-default" href="<?php echo url_for("drev_dr_recuperation", $drev) ?>">Continuer</a>
    </div>
</form>