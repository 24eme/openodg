<?php include_partial('habilitation/breadcrumb', array('habilitation' => $habilitation, 'last' => "Comparatif avec Certipaq" ));
  $etablissement = $habilitation->getEtablissementObject();
 ?>
<div class="page-header no-border">
    <h1>Demande Certipaq</h1>
</div>

<div>
<p>Voici la requête à réaliser :</p>
<pre>
<?php print_r($param->getRawValue()); ?>
</pre>
</div>
