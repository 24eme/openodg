<?php include_partial('drev/step', array('step' => null, 'drev' => $drev)) ?>
<div class="page-header">
    <h2>Récupération de la DR <small>sur le plateforme du CIVA</small></h2>
</div>

<form action="" method="post" class="form-horizontal">
    <p>Voulez vous récupérer la DR ?</p>
    <a class="btn btn-default" href="">Passer</a>
    <a class="btn btn-default" href="<?php echo url_for("drev_dr_recuperation", $drev) ?>">Continuer</a>
</form>