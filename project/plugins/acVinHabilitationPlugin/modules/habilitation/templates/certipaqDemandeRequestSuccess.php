<?php include_partial('habilitation/breadcrumb', array('habilitation' => $habilitation, 'last' => "Comparatif avec Certipaq" ));
  $etablissement = $habilitation->getEtablissementObject();
 ?>
<div class="page-header no-border">
    <h1>Demande Certipaq</h1>
</div>

<div>
<p>Vous êtes sur le point de communiquer à certipaq les informations suivantes :</p>
<table class="table">
<?php foreach($param_printable as $k => $o): ?>
    <tr><th><?php echo $k ; ?></th><td><?php print_r($o); ?></td></tr>
<?php endforeach; ?>
</table>

<?php if (isset($res) && $res) : ?>
<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
  <div class="panel panel-default">
    <div class="panel-heading" role="tab">
      <h4 class="panel-title text-danger">
          Erreur
      </h4>
    </div>
    <div>
      <div class="panel-body bg-danger">
          <?php if ($errors): ?>
          <ul>
              <?php foreach($errors as $e): ?>
                  <li class="text-danger"><?php echo $e; ?></li>
              <?php endforeach; ?>
          </ul>
          <?php endif; ?>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title">
        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#detailtechnique" aria-expanded="true" aria-controls="collapseOne">
            Détail technique
        </a>
      </h4>
    </div>
    <div id="detailtechnique" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
      <div class="panel-body">
          <?php print_r($res->getRawValue()); ?>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

</div>

<form class="text-right">
    <input class="btn btn-success" type="submit" name="confirm" value="Transmettre"/>
</form>
