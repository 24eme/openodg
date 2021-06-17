<ol class="breadcrumb">
  <li><a href="<?php echo url_for('accueil'); ?>">Déclarations</a></li>
  <li><a href="<?php echo url_for('declaration_etablissement', $parcellaire->getEtablissementObject()); ?>"><?php echo $parcellaire->getEtablissementObject()->getNom() ?> (<?php echo $parcellaire->getEtablissementObject()->identifiant ?>)</a></li>
  <li class="active"><a href=""><?php if ($parcellaire->isIntentionCremant()): ?>Intention de production<?php else: ?>Parcellaire<?php endif; ?> <?php if($parcellaire->isParcellaireCremant()): ?><?php if($parcellaire->isIntentionCremant()): ?>AOC Crémant d'Alsace<?php else: ?>Crémant<?php endif; ?><?php endif; ?> <?php echo $parcellaire->getCampagne(); ?></a></li>
</ol>
