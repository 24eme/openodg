<ol class="breadcrumb">
  <li><a href="<?php echo url_for('habilitation'); ?>">Habilitation</a></li>
  <li><a href="<?php echo url_for('habilitation_declarant', $habilitation->getEtablissementObject()); ?>"><?php echo $habilitation->getEtablissementObject()->getNom() ?> (<?php echo $habilitation->getEtablissementObject()->identifiant ?>)</a></li>
  <li class="active"><a href="">Habilitation de <?php echo  "(derniere date version)" //$habilitation->getCampagne(); ?></a></li>
</ol>
