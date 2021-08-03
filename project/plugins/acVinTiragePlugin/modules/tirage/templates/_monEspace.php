<?php use_helper('Date'); ?>

<div class="col-sm-6 col-md-4 col-xs-12">
    <div class="block_declaration panel <?php if ($tirage && $tirage->validation): ?>panel-success<?php else: ?>panel-primary<?php endif; ?>">
        <div class="panel-heading">
            <h3>Tirage Crémant</h3>
        </div>
        <?php if ($tirage && $tirage->validation): ?>
            <div class="panel-body">
                <p>Votre déclaration de tirage de Crémant a été validée pour cette année.</p>
            </div>
            <div class="panel-bottom">
                <p>
                    <a class="btn btn-lg btn-block btn-primary" href="<?php echo url_for('tirage_visualisation', $tirage) ?>">Visualiser</a>
                </p>
                <?php if (TirageSecurity::getInstance($sf_user, $tirage->getRawValue())->isAuthorized(TirageSecurity::DEVALIDATION)): ?>
                    <p>
                        <a onclick='return confirm("Êtes vous sûr de vouloir dévalider cette déclaration ?");' class="btn btn-xs btn-warning pull-right" href="<?php echo url_for('tirage_devalidation', $tirage) ?>"><span class="glyphicon glyphicon-remove-sign"></span>&nbsp;&nbsp;Dévalider la déclaration</a>
                    </p>
                <?php endif; ?>
            </div>
        <?php elseif ($tirage): ?>
                <div class="panel-body">
                    <p>Une déclaration de tirage a été débutée.</p>
                </div>
                <div class="panel-bottom">
                    <p>
                        <a class="btn btn-lg btn-block btn-default" href="<?php echo url_for('tirage_edit', $tirage) ?>"><?php if($tirage->isPapier()): ?><span class="glyphicon glyphicon-file"></span> Continuer la saisie papier<?php else: ?>Continuer la télédéclaration<?php endif; ?></a>
                    </p>
                    <p>
                        <a onclick='return confirm("Êtes vous sûr de vouloir supprimer cette saisie ?");' class="btn btn-xs btn-danger pull-right" href="<?php echo url_for('tirage_delete', $tirage) ?>"><span class="glyphicon glyphicon-trash"></span>&nbsp;&nbsp;Supprimer le brouillon</a>
                    </p>
                </div>
        <?php elseif (!TirageClient::getInstance()->isOpen()): ?>
            <div class="panel-body">
                <?php if(date('Y-m-d') > TirageClient::getInstance()->getDateOuvertureFin()): ?>
                <p>Le Téléservice est fermé. Pour toute question, veuillez contacter directement l'AVA.</p>
                <?php else: ?>
                <p>Le Téléservice sera ouvert à partir du <?php echo format_date(TirageClient::getInstance()->getDateOuvertureDebut(), "D", "fr_FR") ?>.</p>
                <?php endif; ?>
            </div>
            <div class="panel-bottom">
                <?php if ($sf_user->isAdmin()): ?>
                    <p>
                        <a class="btn btn-lg btn-default btn-block" href="<?php echo url_for('tirage_create', array('sf_subject' => $etablissement, 'campagne' => $periode)) ?>">Démarrer la <?php echo $nieme; ?> déclaration</a>
                    </p>
                    <p>
                        <a class="btn btn-xs btn-warning btn-block" href="<?php echo url_for('tirage_create_papier', array('sf_subject' => $etablissement, 'campagne' => $periode)) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Saisir la déclaration papier</a>
                    </p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="panel-body">
            <p><?php
        echo "Déclaration à remplir dans les 60 jours qui suivent la fin du tirage.<br/>";

        if ($nbDeclaration > 0) {
            echo ($nbDeclaration == 1) ? "Une déclaration de tirage déjà validée" : $nbDeclaration." déclarations de tirage déjà validées";
        }?></p>
        </div>
        <div class="panel-bottom">
            <p>
                <a class="btn btn-lg btn-block btn-default" href="<?php echo url_for('tirage_create', array('sf_subject' => $etablissement, 'campagne' => $periode)) ?>">Démarrer la <?php echo $nieme; ?> déclaration</a>
            </p>
            <?php if ($sf_user->isAdmin()): ?>
                <p>
                    <a class="btn btn-xs btn-warning btn-block" href="<?php echo url_for('tirage_create_papier', array('sf_subject' => $etablissement, 'campagne' => $periode)) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Saisir la déclaration papier</a>
                </p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
