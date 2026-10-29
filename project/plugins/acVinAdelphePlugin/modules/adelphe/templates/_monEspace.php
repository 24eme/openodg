<?php use_helper('Date'); ?>
<?php if (file_exists(AdelpheConfiguration::getInstance()->getVolumesConditionnesCsv($periode))): ?>
<div class="col-sm-6 col-md-4 col-xs-12">
    <div class="block_declaration panel <?php if ($adelphe && $adelphe->validation): ?>panel-success<?php elseif($adelphe): ?>panel-primary<?php else : ?>panel-default<?php endif; ?>">
        <div class="panel-heading">
            <h3 class="panel-title">
                Adelphe&nbsp;<?php echo $periode; ?>
<?php if ( $sf_user->isAdmin() && $adelphe && $adelphe->isValideeOdg() ): ?>
                <span class="pull-right"><span class="glyphicon glyphicon-ok-circle"></span></span>
<?php endif; ?>
            </h3>
        </div>
        <?php if ($adelphe && $adelphe->validation): ?>
            <div class="panel-body">
                <p class="explications">Votre déclaration Adelphe a été validée pour cette année.</p>
                <div class="actions">
                    <a class="btn btn-block btn-default" href="<?php echo url_for('adelphe_visualisation', $adelphe) ?>">Visualiser la déclaration</a>
                </div>
            </div>
        <?php elseif ($adelphe): ?>
            <div class="panel-body">
                <p class="explications">Votre déclaration Adelphe de cette année a été débutée sans avoir été validée.</p>
                <div class="actions">
                    <a class="btn btn-block btn-primary" href="<?php echo url_for('adelphe_edit', $adelphe) ?>"><span class="glyphicon glyphicon-pencil"></span> Reprendre la saisie</a>
                    <a onclick='return confirm("Êtes vous sûr de vouloir supprimer cette saisie ?");' class="btn btn-block btn-xs btn-default pull-right" href="<?php echo url_for('adelphe_delete', $adelphe) ?>"><span class="glyphicon glyphicon-trash"></span>&nbsp;&nbsp;Supprimer le brouillon</a>
                </div>
            </div>
        <?php else: ?>
            <div class="panel-body">
                <p class="explications">Votre déclaration Adelphe pour cette année n'a pas encore été déclarée.</p>
                <div class="actions">
                    <a class="btn btn-block btn-default" href="<?php echo url_for('adelphe_create', array('sf_subject' => $etablissement, 'periode' => $periode)) ?>">Démarrer la télédéclaration</a>
                    <?php if ($sf_user->isAdmin() || $sf_user->hasDrevAdmin()): ?>
                        <a class="btn btn-xs btn-default btn-block pull-right" href="<?php echo url_for('adelphe_create', array('sf_subject' => $etablissement, 'periode' => $periode)) ?>?papier=1"><span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Saisie papier</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        <div class="panel-footer" style="padding-top: 0; padding-bottom: 0;">&nbsp;</div>
    </div>
</div>
<?php endif; ?>
