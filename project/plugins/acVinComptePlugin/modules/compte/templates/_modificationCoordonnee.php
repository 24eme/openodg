<?php
$isCompteSociete = isset($isCompteSociete) && $isCompteSociete;
$colClass = ($isCompteSociete) ? 'col-xs-8' : 'col-xs-4';
$isSameAdresseThanSociete = !$isCompteSociete && $compteForm->getObject()->isSameAdresseThanSociete() && !$compteForm->hasErrors();
$isSameContactThanSociete = !$isCompteSociete && $compteForm->getObject()->isSameContactThanSociete() && !$compteForm->hasErrors();

?>
<div id="coordonnees_modification">

    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">Adresse <?php if ($isSameAdresseThanSociete) : ?>&nbsp;-&nbsp;<span class="text-muted">Même adresse que la société</span><?php endif; ?></h4>
            <span class="pull-right <?php echo ($isCompteSociete) ? '' : ' clickable pointer '; echo ($isSameAdresseThanSociete) ? ' panel-collapsed ' : ' '; ?>" style="margin-top: -20px; font-size: 15px;">
                <span class="label-edit" ><?php echo ($isSameAdresseThanSociete) ? 'Editer' : 'Edition'; ?></span>&nbsp;
                <?php if (!$isCompteSociete): ?><i class="glyphicon <?php echo ($isSameAdresseThanSociete) ? ' glyphicon-chevron-down ' : 'glyphicon-chevron-up'; ?>"></i><?php endif; ?>
            </span>
        </div>
        <div class="panel-body  <?php echo ($isSameAdresseThanSociete) ? ' collapse ' : ''; ?>">
            <?php
            echo $compteForm->renderHiddenFields();
            echo $compteForm->renderGlobalErrors();
            ?>
            <div class="form-group">
                <?php echo $compteForm['adresse']->renderError(); ?>
                <?php echo $compteForm['adresse']->renderLabel(null, array("class" => "col-xs-4 control-label")); ?>

                <div class="col-xs-8"><?php echo $compteForm['adresse']->render(); ?></div>
            </div>
            <div class="form-group">

                <?php echo $compteForm['adresse_complementaire']->renderLabel(null, array('class' => 'col-xs-4 control-label')); ?>

                <div class="col-xs-8"><?php echo $compteForm['adresse_complementaire']->render(); ?></div>
                <?php echo $compteForm['adresse_complementaire']->renderError(); ?>
            </div>
            <div class="form-group">
                <?php echo $compteForm['code_postal']->renderLabel(null, array('class' => 'col-xs-4 control-label')); ?>
                <div class="col-xs-3"><?php echo $compteForm['code_postal']->render(); ?></div>
                <?php echo $compteForm['insee']->renderLabel(null, array('class' => 'col-xs-2 control-label')); ?>
                <div class="col-xs-3"><?php echo $compteForm['insee']->render(); ?></div>

                <?php echo $compteForm['code_postal']->renderError(); ?>
                <?php echo $compteForm['insee']->renderError(); ?>
            </div>
            <div class="form-group">

                <?php echo $compteForm['commune']->renderLabel(null, array('class' => 'col-xs-4 control-label')); ?>

                <div class="col-xs-8"><?php echo $compteForm['commune']->render(); ?></div>
                <?php echo $compteForm['commune']->renderError(); ?>
            </div>
            <div class="form-group">

                <?php echo $compteForm['pays']->renderLabel(null, array('class' => 'col-xs-4 control-label')); ?>

                <div class="col-xs-8"><?php echo $compteForm['pays']->render(); ?></div>
                <?php echo $compteForm['pays']->renderError(); ?>
            </div>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading"><h4 class="panel-title">E-mail / téléphone / fax <?php if ($isSameContactThanSociete) : ?>&nbsp;-&nbsp;<span class="text-muted">Même contact que la société</span><?php endif; ?></h4>
            <span class="pull-right <?php echo ($isCompteSociete) ? '' : ' clickable pointer '; echo ($isSameContactThanSociete) ? ' panel-collapsed ' : ' '; ?>" style="margin-top: -20px; font-size: 15px;">
                <span class="label-edit" ><?php echo ($isSameContactThanSociete) ? 'Editer' : 'Edition'; ?></span>&nbsp;
                <?php if (!$isCompteSociete): ?><i class="glyphicon <?php echo ($isSameContactThanSociete) ? ' glyphicon-chevron-down ' : 'glyphicon-chevron-up'; ?>"></i><?php endif; ?>
            </span>
        </div>
        <div class="panel-body  <?php echo ($isSameContactThanSociete) ? ' collapse ' : ''; ?>">
            <div class="form-group <?php if($compteForm['email']->hasError()): ?> has-error<?php endif; ?>">

                <?php echo $compteForm['email']->renderLabel(null, array('class' => 'col-xs-4 control-label')); ?>

                <div class="col-xs-8"><?php echo $compteForm['email']->render(); ?></div>

                <?php echo $compteForm['email']->renderError(); ?>
            </div>
            <?php if (isset($compteForm['email_teledeclaration'])): ?>
              <div class="form-group <?php if($compteForm['email_teledeclaration']->hasError()): ?> has-error<?php endif; ?>">

                  <?php echo $compteForm['email_teledeclaration']->renderLabel(null, array('class' => 'col-xs-4 control-label')); ?>

                  <div class="col-xs-8"><?php echo $compteForm['email_teledeclaration']->render(); ?></div>

                  <?php echo $compteForm['email_teledeclaration']->renderError(); ?>
              </div>
            <?php endif; ?>
            <div class="form-group">

                <?php echo $compteForm['telephone_perso']->renderLabel(null, array('class' => 'col-xs-4 control-label')); ?>

                <div class="col-xs-8"><?php echo $compteForm['telephone_perso']->render(); ?></div>
                <?php echo $compteForm['telephone_perso']->renderError(); ?>
            </div>
            <div class="form-group">

                <?php echo $compteForm['telephone_bureau']->renderLabel(null, array('class' => 'col-xs-4 control-label')); ?>

                <div class="col-xs-8"><?php echo $compteForm['telephone_bureau']->render(); ?></div>
                <?php echo $compteForm['telephone_bureau']->renderError(); ?>
            </div>
            <div class="form-group">

                <?php echo $compteForm['telephone_mobile']->renderLabel(null, array('class' => 'col-xs-4 control-label')); ?>

                <div class="col-xs-8"><?php echo $compteForm['telephone_mobile']->render(); ?></div>
                <?php echo $compteForm['telephone_mobile']->renderError(); ?>
            </div>
            <div class="form-group">

                <?php echo $compteForm['fax']->renderLabel(null, array('class' => 'col-xs-4 control-label')); ?>

                <div class="col-xs-8"><?php echo $compteForm['fax']->render(); ?></div>
                <?php echo $compteForm['fax']->renderError(); ?>
            </div>

                <div class="form-group">

                    <?php echo $compteForm['site_internet']->renderLabel(null, array('class' => 'col-xs-4 control-label')); ?>
                    <div class="col-xs-8"><?php echo $compteForm['site_internet']->render(); ?></div>
                    <?php echo $compteForm['site_internet']->renderError(); ?>
                </div>

        </div>
    </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">Droits</h4><span class="pull-right" style="margin-top: -20px; font-size: 15px;" >
                    <span>Edition</span>&nbsp;
                </span>
            </div>
            <div class="panel-body">
                <div class="form-group">
                    <div class="col-xs-12"><?php echo $compteForm['droits']->renderError(); ?></div>
                    <?php echo $compteForm['droits']->renderLabel(null, array('class' => 'col-xs-3 control-label')); ?>
                    <div class="col-xs-9"><?php echo $compteForm['droits']->render(); ?></div>
                    <div class="col-xs-12" style="margin-top: 20px;"><?php echo $compteForm['alternative_logins']->renderError(); ?></div>
                    <?php echo $compteForm['alternative_logins']->renderLabel(null, array('class' => 'col-xs-3 control-label select2')); ?>
                    <div class="col-xs-9"><?php echo $compteForm['alternative_logins']->render(); ?></div>
                </div>
            </div>
        </div>
    </div>
