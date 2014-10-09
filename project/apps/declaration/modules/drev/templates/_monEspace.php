<?php if(!count($drevsHistory) && !$etablissement->hasFamille(EtablissementClient::FAMILLE_VINIFICATEUR)): ?>
    <?php return; ?>
<?php endif; ?>
<div class="row">
    <div class="col-xs-4">
         <?php if($etablissement->hasFamille(EtablissementClient::FAMILLE_VINIFICATEUR)): ?>
        <div class="panel <?php if ($drev && $drev->validation): ?>panel-success<?php else: ?>panel-primary<?php endif; ?>">
            <div class="panel-heading">
                <h2 class="panel-title">DREV de l'année</h2>
            </div>
            <div class="panel-body">
                <?php if ($drev && $drev->validation): ?>
                    <p>
                        <a class="btn btn-lg btn-block btn-primary" href="<?php echo url_for('drev_visualisation', $drev) ?>">Visualiser</a>
                    </p>
                    <p>
                        <a class="btn btn-xs btn-danger pull-right" href="<?php echo url_for('drev_delete', $drev) ?>"><span class="glyphicon glyphicon-trash"></span>&nbsp;&nbsp;Supprimer</a>
                    </p>
                <?php elseif ($drev): ?>
                    <p>
                        <a class="btn btn-lg btn-block btn-default" href="<?php echo url_for('drev_edit', $drev) ?>">Continuer</a>
                    </p>
                    <p>
                        <a class="btn btn-xs btn-danger pull-right" href="<?php echo url_for('drev_delete', $drev) ?>"><span class="glyphicon glyphicon-trash"></span>&nbsp;&nbsp;Supprimer le brouillon</a>
                    </p>
                <?php else: ?>
                    <p>
                        <a class="btn btn-lg btn-block btn-default" href="<?php echo url_for('drev_create', $etablissement) ?>">Démarrer</a>
                    </p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <div class="col-xs-4">
        <?php if(count($drevsHistory) > 0): ?>  	
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h2 class="panel-title">Historique</h2>
            </div>
            <ul class="list-group">
            <?php foreach ($drevsHistory as $drev_h): ?>
                <li class="list-group-item">
                    <a class="btn btn-link btn-primary" href="<?php echo url_for('drev_visualisation', $drev_h) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;Revendication <?php echo $drev_h->campagne ?></a>
                </li>
            <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
    </div>
</div>