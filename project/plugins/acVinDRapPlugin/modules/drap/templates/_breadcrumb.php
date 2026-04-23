<ol class="breadcrumb">
  <li><a href="<?php echo url_for('accueil'); ?>">Déclarations</a></li>
  <li><a href="<?php echo url_for('declaration_etablissement', array('identifiant' => $parcellaireIrrigable->identifiant, 'campagne' => $parcellaireIrrigable->campagne)); ?>"><?php echo $parcellaireIrrigable->getEtablissementObject()->getNom() ?> (<?php echo $parcellaireIrrigable->getEtablissementObject()->identifiant ?>)</a></li>
  <li class="active"><a href="">Déclaration de renonciation à produire de <?php echo $parcellaireIrrigable->getCampagne(); ?></a></li>
</ol>
