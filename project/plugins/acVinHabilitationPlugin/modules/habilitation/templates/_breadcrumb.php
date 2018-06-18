<ol class="breadcrumb">
  <li><a href="<?php echo url_for('habilitation'); ?>">Habilitations</a></li>
  <li><a href="<?php echo url_for('habilitation_declarant', $habilitation->getEtablissementObject()); ?>">Habilitation de <?php echo $habilitation->getEtablissementObject()->getNom() ?> (<?php echo $habilitation->getEtablissementObject()->identifiant ?>) </a></li>
  <?php if(!$habilitation->isLastOne()): ?>
    <li class="active"><a href="<?php echo url_for('habilitation_edition', array('id' => $habilitation->_id)); ?>">Habilitation au <?php echo Date::francizeDate($habilitation->getDate()); ?> </a></li>
  <?php endif; ?>
</ol>
