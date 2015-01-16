<?php use_javascript("degustation.js", "last") ?>

<div class="page-header">
    <h2>Choix des Opérateurs</h2>
</div>

<form action methode="post" class="form-horizontal">

<div class="row">
    <div class="col-xs-12">
        <h3>Choix automatique <small>20 opérateurs concernés</small></h3>
        
        <div class="form-group">
            <label for="inputEmail3" class="col-sm-4 control-label">Nombre de prélèvements</label>
            <div class="col-sm-1">
              <input type="email" class="form-control" id="inputEmail3" placeholder="" value="10">
            </div>
            <button type="button" class="btn btn-warning">Choisir automatiquement</button>
        </div>
    </div>
        <div class="col-xs-12">
            <h3>Choix des opérateurs</h3>
            <h4>Opérateur à séléctionné</h4>
            <div id="choisi" class="list-group">
                <?php for($i = 0; $i <= 60; $i++): ?>
                <div id="choisi_<?php echo $i ?>" href="" class="list-group-item col-xs-12 list-group-item-success hidden">
                    <div class="col-xs-3">M. NOM PRENOM  <?php echo $i ?></div>
                    <div class="col-xs-3">AMMERSCHWIHR</div>
                    <div class="col-xs-3">Prélevé en 2012, 2014</div>
                    <div class="col-xs-2">
                        <select data-placeholder="Sélectionner" class="form-control ">
                            <option>Chasselas</option>
                            <option>Riesling</option>
                            <option>Pinot Gris</option>
                            <option>Gewurztraminer</option>
                        </select>
                    </div>
                    <div class="col-xs-1">
                        <button class="btn btn-danger pull-right trash" type="button"><span class="glyphicon glyphicon-trash"></span></button>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
        </div>
        <div class="col-xs-12">
            <h4>Opérateur à ajouter</h4>
            <div class="" style="overflow: scroll; height: 400px; overflow-x: hidden; border-top: 1px solid #DDD; border-bottom: 1px solid #DDD;">
                <div id="a_choisir" class="list-group" style="margin-top: -1px; margin-bottom: -1px;">
                    <?php for($i = 0; $i <= 60; $i++): ?>
                    <a id="a_choisir_<?php echo $i ?>" href="" class="list-group-item col-xs-12">
                        <div class="col-xs-3">M. NOM PRENOM <?php echo $i ?></div>
                        <div class="col-xs-3">AMMERSCHWIHR</div>
                        <div class="col-xs-3">Prélevé en 2012, 2014</div>
                        <div class="col-xs-2"></div>
                        <div class="col-xs-1">
                            <button class="btn btn-success pull-right" type="button"><span class="glyphicon glyphicon-plus-sign"></span></button>
                        </div>
                    </a>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    <div class="col-xs-12">
        <h3>Récapitulatif</h3>
        <p>
        <strong>Nombre de prélévements : </strong> 12
        </p>
        <p>
        <strong>Cépages : </strong> Riesling <span class="badge">3</span> Chasselas <span class="badge">3</span> Riesling <span class="badge">3</span> Pinot Gris <span class="badge">2</span> Gewurztraminer <span class="badge">2</span>
        </p>
    </div>
</div>

<div class="row row-margin row-button">
    <div class="col-xs-6">
        <a href="<?php echo url_for('degustation_creation') ?>" class="btn btn-primary btn-lg btn-upper">Précédent</a>
    </div>
    <div class="col-xs-6 text-right">
        <a href="<?php echo url_for('degustation_degustation') ?>" class="btn btn-default btn-lg btn-upper">Continuer</a>
    </div>
</div>

</form>