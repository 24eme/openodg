<ol class="breadcrumb">
    <?php if(!$sf_user->hasCredential(myUser::CREDENTIAL_HABILITATION)): ?>
        <li><a href="<?php echo url_for('habilitation_declarant', $habilitation->getEtablissementObject()); ?>">Habilitations</a></li>
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
  <?php if(isset($consultation)): ?>
      <li class="active"><a href=""><span class="glyphicon glyphicon-search" style="opacity: 0.25"></span> Consultation des habilitations</a></li>
  <?php else: ?>
  <li class="pull-right" ><a title="Consultation des habilitations" style="opacity: 0.25" href="<?php if(isset($habilitation)): ?><?php echo url_for('habilitation_consultation', array('numero' => $habilitation->declarant->cvi)) ?><?php else: ?><?php echo url_for('habilitation_consultation') ?><?php endif; ?>"><span class="glyphicon glyphicon-search"></span></a></li>
  <?php endif; ?>
</ol>
