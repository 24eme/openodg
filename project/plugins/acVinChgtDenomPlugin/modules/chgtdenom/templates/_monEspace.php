<?php use_helper('Date'); ?>

<?php if (!$hasLots): ?>
    <?php return; ?>
<?php endif; ?>
<div class="col-sm-6 col-md-4 col-xs-12">
    <div class="block_declaration panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Chgt.&nbsp;de&nbsp;dénomination&nbsp;/&nbsp;Déclassement</h3>
        </div>
        <div class="panel-body">
            <p>Espace permettant le changement de dénomination ou le déclassement de vos lots.</p>
            <div style="margin-top: 50px;">
                <a class="btn btn-block btn-default" href="<?php echo url_for('chgtdenom_create', array('sf_subject' => $etablissement)) ?>">Démarrer la télédéclaration</a>
                <?php if ($sf_user->isAdmin()): ?>
                <a class="btn btn-xs btn-default btn-block" href="<?php echo url_for('chgtdenom_create_papier', array('sf_subject' => $etablissement)) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Saisir le changement papier</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
