<ol class="breadcrumb">
  <li><a href="<?php echo url_for('declaration'); ?>">Déclarations</a></li>
  <li><a href="<?php echo url_for('declaration_etablissement', $parcellaire->getEtablissementObject()); ?>"><?php echo $parcellaire->getEtablissementObject()->getNom() ?> (<?php echo $parcellaire->getEtablissementObject()->identifiant ?>)</a></li>
  <li class="active"><a href="">Parcellaire <?php if($parcellaire->isParcellaireCremant()): ?>Crémant<?php endif; ?> <?php echo $parcellaire->getCampagne(); ?></a></li>
</ol>
