<?php include_partial('habilitation/breadcrumb', array('habilitation' => $habilitation, 'last' => "Comparatif avec Certipaq" ));
  $etablissement = $habilitation->getEtablissementObject();
 ?>
<div class="page-header no-border">
    <h1>Demande Certipaq</h1>
</div>

<div class="well">
<?php if ($certipaq_operateur): ?>
Opérateur Certipaq : <strong><?php echo $certipaq_operateur->raison_sociale; ?></strong> ( <?php echo $certipaq_operateur->siret; ?> - <?php echo $certipaq_operateur->cvi; ?> )
<?php else:?>
Opérateur non trouvé dans l'API Certipaq
<?php endif; ?>
</div>

<div>
<p>Vous souhaitez convertir cette demande en requête Certipaq de :</p>
<?php foreach(CertipaqDI::getInstance()->getListeDemandeIdentificationType() as $id => $nom): ?>
    <p><?php echo link_to($nom, 'certipaq_demande_request_preview', array('identifiant' => $etablissement->identifiant, 'demande' => $demande, 'type' => $id)); ?></p>
<?php endforeach; ?>
</div>
