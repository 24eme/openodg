<div class="row">
    <div class="col-xs-4">			
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
        <div class="panel <?php if ($drevmarc && $drevmarc->validation): ?>panel-success<?php else: ?>panel-primary<?php endif; ?>">     
            <div class="panel-heading">
                <h2 class="panel-title">DREVMARC de l'année</h2>
            </div>
            <div class="panel-body">
                <?php if ($drevmarc && $drevmarc->validation): ?>
                    <p>
                        <a class="btn btn-lg btn-block btn-primary" href="<?php echo url_for('drevmarc_visualisation', $drevmarc) ?>">Visualiser</a>
                    </p>
                    <p>
                        <a class="btn btn-xs btn-danger pull-right" href="<?php echo url_for('drevmarc_delete', $drevmarc) ?>"><span class="glyphicon glyphicon-trash"></span>&nbsp;&nbsp;Supprimer</a>
                    </p>
                <?php elseif ($drevmarc): ?>
                    <p>
                        <a class="btn btn-lg btn-block btn-default" href="<?php echo url_for('drevmarc_edit', $drevmarc) ?>">Continuer</a>
                    </p>
                    <p>
                        <a class="btn btn-xs btn-danger pull-right" href="<?php echo url_for('drevmarc_delete', $drevmarc) ?>"><span class="glyphicon glyphicon-trash"></span>&nbsp;&nbsp;Supprimer le brouillon</a>
                    </p>
                <?php else: ?>
                    <p>
                        <a class="btn btn-lg btn-block btn-default" href="<?php echo url_for('drevmarc_create', $etablissement) ?>">Démarrer</a>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-xs-4">			
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h2 class="panel-title">Historique</h2>
            </div>
            <div class="panel-body">
            <?php foreach ($drevsHistory as $drev_h): ?>
                <li class="list-unstyled">
                    <a class="btn btn-link btn-primary" href="<?php echo url_for('drev_visualisation', $drev_h) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;Revendication <?php echo $drev_h->campagne ?></a>
                </li>
            <?php endforeach; ?>
            </ul>
            </div>
        </div>
    </div>
</div>