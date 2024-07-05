<ol class="breadcrumb">
  <li><a href="<?php echo url_for('accueil'); ?>">Déclarations</a></li>
  <li><a href="<?php echo url_for('declaration_etablissement', array('identifiant' => $parcellaireAffectationCoop->identifiant, 'campagne' => $parcellaireAffectationCoop->campagne)); ?>"><?php echo $parcellaireAffectationCoop->getEtablissementObject()->getNom() ?> (<?php echo $parcellaireAffectationCoop->getEtablissementObject()->identifiant ?>)</a></li>
  <li class="<?php if(!isset($declaration)): ?>active<?php endif; ?>"><a href="<?php echo url_for('parcellaireaffectationcoop_liste', $parcellaireAffectationCoop) ?>">Déclarations <?php echo $parcellaireAffectationCoop->getCampagne(); ?> des apporteurs</a></li>
  <?php if(isset($declaration)): ?>
  <li class="active"><a href=""><?php echo ucfirst(str_replace("Parcellaire", "", get_class($declaration->getRawValue()))) ?> de <?php echo $declaration->declarant->nom ?></a></li>
  <?php endif; ?>
</ol>
