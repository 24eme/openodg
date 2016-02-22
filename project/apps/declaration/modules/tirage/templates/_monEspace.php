<?php use_helper('Date'); ?>

<div class="col-xs-4">
    <div class="block_declaration panel panel-primary">     
        <div class="panel-heading"><h3>Tirage Crémant 2016<br /><br /></h3></div>
        <div class="panel-body">
            <p>Le Téléservice sera ouvert à partir du 1er mars 2016.</p>
        </div>
        <div class="panel-bottom"></div>
    </div>
</div>

<?php use_helper('Date'); ?>

<div class="col-xs-4">
    <div class="block_declaration panel <?php if ($tirage && $tirage->validation): ?>panel-success<?php else: ?>panel-primary<?php endif; ?>">     
        <div class="panel-heading">
            <h3>Tirage Crémant&nbsp;<?php echo ConfigurationClient::getInstance()->getCampagneManager()->getCurrent(); ?><br /><br /></h3>
        </div>
        <div class="panel-body">
            <p>Créer une nouvelle déclaration de tirage</p>
        </div>
        <div class="panel-bottom">  
            <p>
                <a class="btn btn-lg btn-block btn-default" href="<?php echo url_for('tirage_create', $etablissement) ?>">Démarrer la télédéclaration</a>
            </p>
            <?php if ($sf_user->isAdmin()): ?>
                <p>
                    <a class="btn btn-xs btn-warning btn-block" href="<?php echo url_for('drevmarc_create_papier', $etablissement) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Saisir la déclaration papier</a>
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>
