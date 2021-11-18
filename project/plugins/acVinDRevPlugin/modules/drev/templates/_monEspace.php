<?php use_helper('Date'); ?>

<?php if (!EtablissementSecurity::getInstance($sf_user, $etablissement->getRawValue())->isAuthorized(EtablissementSecurity::DECLARANT_DREV) && (!$drev || !$sf_user->isAdmin() || !$sf_user->hasDrevAdmin())): ?>
    <?php return; ?>
<?php endif; ?>
<div class="col-sm-6 col-md-4 col-xs-12">
    <div class="block_declaration panel <?php if ($drev && $drev->validation): ?>panel-success<?php elseif($drev): ?>panel-primary<?php else : ?>panel-default<?php endif; ?>">
        <div class="panel-heading">
            <h3 class="panel-title">Revendication&nbsp;des&nbsp;produits&nbsp;<?php echo $campagne; ?></h3>
        </div>
        <?php if ($drev && $drev->validation): ?>
            <div class="panel-body">
                <p>Votre déclaration de revendication a été validée pour cette année.</p>
                <div style="margin-top: 76px;">
                    <a class="btn btn-block btn-default" href="<?php echo url_for('drev_visualisation', $drev) ?>">Voir la DRev</a>
                </div>
            </div>
        <?php elseif ($drev && (DRevClient::getInstance()->isOpen() || $sf_user->isAdmin() || $sf_user->hasDrevAdmin())): ?>
            <div class="panel-body">
                <p>Votre déclaration de revendication de cette année a été débutée sans avoir été validée.</p>
                <div style="margin-top: 50px;">
                    <a class="btn btn-block btn-primary" href="<?php echo url_for('drev_edit', $drev) ?>"><?php if($drev->isPapier()): ?><span class="glyphicon glyphicon-file"></span> Continuer la drev papier<?php else: ?>Continuer la télédéclaration<?php endif; ?></a>
                    <a onclick='return confirm("Êtes vous sûr de vouloir supprimer cette saisie ?");' class="btn btn-block btn-xs btn-default pull-right" href="<?php echo url_for('drev_delete', $drev) ?>"><span class="glyphicon glyphicon-trash"></span>&nbsp;&nbsp;Supprimer le brouillon</a>
                </div>
            </div>
        <?php elseif (!DRevClient::getInstance()->isOpen()): ?>
            <div class="panel-body">
                La télédéclaration de revendication 2021 s’effectue sur le nouveau portail : <a href="https://drev.vinsvaldeloire.pro">https://drev.vinsvaldeloire.pro</a>
                <?php if ($sf_user->isAdmin()): ?>
                <div style="margin-top: 50px;">
                    <a class="btn btn-default btn-block" href="<?php echo url_for('drev_create', array('sf_subject' => $etablissement, 'campagne' => $campagne)) ?>">Démarrer la télédéclaration</a>
                    <a class="btn btn-xs btn-default btn-block" href="<?php echo url_for('drev_create_papier', array('sf_subject' => $etablissement, 'campagne' => $campagne)) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Saisir la drev papier</a>
                </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="panel-body">
                La télédéclaration de revendication 2021 s’effectue sur le nouveau portail : <a href="https://drev.vinsvaldeloire.pro">https://drev.vinsvaldeloire.pro</a>
                    <?php if ($sf_user->isAdmin() || $sf_user->hasDrevAdmin()): ?>
                      <div style="margin-top: 50px;">
                        <a class="btn btn-xs btn-default btn-block pull-right" href="<?php echo url_for('drev_create_papier', array('sf_subject' => $etablissement, 'campagne' => $campagne)) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Saisir la drev papier</a>
                      </div>
                    <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
