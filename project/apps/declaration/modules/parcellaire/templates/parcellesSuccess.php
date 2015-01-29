<?php include_partial('step', array('active' => 'parcelles','identifiant' => 'XXX')); ?>

<div class="page-header">
    <h2>Saisie des parcelles</h2>
</div>

<ul class="nav nav-tabs">
    <li role="presentation"><a href="<?php echo url_for('parcellaire_parcelle_appellation', array('identifiant' => 'XXX', 'appellation' => 'COMMUNALE')) ?>">Communales</a></li>
    <li role="presentation" class="active"><a href="<?php echo url_for('parcellaire_parcelle_appellation', array('identifiant' => 'XXX', 'appellation' => 'LIEUX_DITS')) ?>">Lieux dits</a></li>
    <li role="presentation"><a href="<?php echo url_for('parcellaire_parcelle_appellation', array('identifiant' => 'XXX', 'appellation' => 'GRD_CRU')) ?>">Grand Crus</a></li>
    <li role="presentation"><a href="<?php echo url_for('parcellaire_parcelle_appellation', array('identifiant' => 'XXX', 'appellation' => 'CREMANT')) ?>">Crémant</a></li>
</ul>

<form action="" method="post" class="form-horizontal">
    <div class="row">       
        <div class="col-xs-12">
            <div id="listes_cepages" class="list-group">
                <table class="table table-striped">
                    <tr>
                        <th>Nom communale</th>           
                        <th>Identifiant parcelle</th>        
                        <th>Cépage</th>        
                        <th>Superficie</th>                 
                    </tr>

                    <?php for ($i = 0; $i <= 10; $i++): ?>
                        <tr>
                            <td>Klevener</td>           
                            <td>Commune X B 17</td>        
                            <td><input type="select"/>
                            </td>        
                            <td><input type="text"></td>                 
                        </tr>
                    <?php endfor; ?>
                </table>
            </div>
            <div class="text-left">
                <button class="btn btn-warning ajax btn-sm" data-toggle="modal" data-target="#popupForm" type="button">Ajouter un produit&nbsp;<span class="eleganticon icon_plus"></span></button>
            </div>
        </div>
    </div>

    <div class="row row-margin row-button">
        <div class="col-xs-6">
            <a href="<?php echo url_for('degustation_degustation') ?>" class="btn btn-primary btn-primary-step btn-lg btn-upper">Précédent</a>
        </div>
        <div class="col-xs-6 text-right">
            <a href="<?php echo url_for('degustation_agents') ?>" class="btn btn-default btn-default-step btn-lg btn-upper">Continuer</a>
        </div>
    </div>
</form>
