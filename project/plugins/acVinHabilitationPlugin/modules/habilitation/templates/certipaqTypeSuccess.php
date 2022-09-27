<?php include_partial('habilitation/breadcrumb', array('habilitation' => $habilitation, 'last' => "Comparatif avec Certipaq" ));
  $etablissement = $habilitation->getEtablissementObject();
 ?>
<div class="page-header no-border">
    <h1>Demande Certipaq</h1>
</div>

<div>
<p>Vous souhaitez convertir cette demande en requête Certipaq de :</p>
<?php foreach(CertipaqDI::getInstance()->getDemandeIdentificationType() as $id => $nom): ?>
    <p><?php echo link_to($nom, 'certipaq_demande_output', array('identifiant' => $etablissement->identifiant, 'demande' => $demande, 'type' => $id)); ?></p>
<?php endforeach; ?>
</div>
