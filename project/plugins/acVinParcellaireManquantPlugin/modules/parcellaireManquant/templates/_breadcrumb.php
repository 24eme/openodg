<ol class="breadcrumb">
  <li><a href="<?php echo url_for('accueil'); ?>">Déclarations</a></li>
  <li><a href="<?php echo url_for('declaration_etablissement', array('identifiant' => $parcellaireManquant->identifiant, 'campagne' => ($parcellaireManquant->periode - 1).'-'.$parcellaireManquant->periode)); ?>"><?php echo $parcellaireManquant->getEtablissementObject()->getNom() ?> (<?php echo $parcellaireManquant->getEtablissementObject()->identifiant ?>)</a></li>
  <li class="active"><a href="">Déclaration de pieds manquants de <?php echo $parcellaireManquant->getCampagne(); ?></a></li>
</ol>
