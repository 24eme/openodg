<?php include_partial('habilitation/breadcrumb', array('habilitation' => $habilitation, 'last' => "Demande Certipaq n°".$request_id ));
  $etablissement = $habilitation->getEtablissementObject();
 ?>
<div class="page-header no-border">
    <h1>Demande Certipaq n° <?php echo $request_id; ?></h1>
</div>

<div>

<form role="form" method="post">
<p>Pour terminer la demande, il faut transmettre des fichiers :</p>
<?php foreach ($form as $k => $f): ?>
<div class="row form-group">
<?php if (strpos($k, 'fichier') !== false): ?>
    <div class="col-xs-4 text-right control-label">
        <?php echo $f->renderLabel(); ?>
    </div>
<?php endif; ?>
    <div class="col-xs-6">
        <span class="text-danger"><?php echo $f->renderError() ?></span>
        <?php echo $f->render(); ?>
    </div>
</div>
<?php endforeach; ?>

<form class="text-right">
    <input class="btn btn-success" type="submit" name="confirm" value="Transmettre"/>
</form>
