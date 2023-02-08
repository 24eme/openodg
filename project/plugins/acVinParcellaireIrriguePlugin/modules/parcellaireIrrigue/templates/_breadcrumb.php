<ol class="breadcrumb">
  <li><a href="<?php echo url_for('accueil'); ?>">Déclarations</a></li>
  <li><a href="<?php echo url_for('declaration_etablissement', array('identifiant' => $parcellaireIrrigue->identifiant, 'campagne' => ($parcellaireIrrigue->periode - 1).'-'.$parcellaireIrrigue->periode)); ?>"><?php echo $parcellaireIrrigue->getEtablissementObject()->getNom() ?> (<?php echo $parcellaireIrrigue->getEtablissementObject()->identifiant ?>)</a></li>
  <li class="active"><a href="">Parcellaire Irrigué de <?php echo $parcellaireIrrigue->getCampagne(); ?></a></li>
</ol>
