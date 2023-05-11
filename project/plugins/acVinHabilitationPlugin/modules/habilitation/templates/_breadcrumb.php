<ol class="breadcrumb">
    <?php if(!$sf_user->hasCredential(myUser::CREDENTIAL_HABILITATION)): ?>
        <li><a href="<?php echo url_for('habilitation_declarant', $sf_user->getEtablissement()); ?>">Habilitations</a></li>
    <?php elseif(HabilitationConfiguration::getInstance()->isSuiviParDemande()): ?>
        <li><a href="<?php echo url_for('habilitation_demande'); ?>">Habilitations</a></li>
    <?php else : ?>
        <li><a href="<?php echo url_for('habilitation'); ?>">Habilitations</a></li>
    <?php endif; ?>
    <?php if(isset($habilitation)): ?>
  <li><a href="<?php echo url_for('habilitation_declarant', $habilitation->getEtablissementObject()); ?>">Habilitation de <?php echo $habilitation->getEtablissementObject()->getNom() ?> (<?php echo $habilitation->getEtablissementObject()->identifiant ?>) </a></li>
    <?php endif; ?>
  <?php if(isset($habilitation) && !$habilitation->isLastOne()): ?>
    <li class="active"><a href="<?php echo url_for('habilitation_edition', array('id' => $habilitation->_id)); ?>">Habilitation au <?php echo Date::francizeDate($habilitation->getDate()); ?> </a></li>
  <?php endif; ?>
  <?php if (isset($last)): ?>
    <li class="active"><?php echo $last; ?></li>
  <?php endif; ?>
  <li class="pull-right"><a title="Consultation des habilitations" style="opacity: 0.25" href="<?php echo url_for('habilitation_consultation') ?>"><span class="glyphicon glyphicon-search"></span> Consultation des habilitations</a></li>
</ol>
