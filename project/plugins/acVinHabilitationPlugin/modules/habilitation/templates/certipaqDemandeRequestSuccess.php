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
<p class="danger">Erreur :</p>
<pre class="danger">
<?php print_r($res); ?>
</pre>
<?php endif; ?>
</div>

<form class="text-right">
    <input class="btn btn-success" type="submit" name="confirm" value="Transmettre"/>
</form>
