<?php include_partial('degustation/step', array('active' => false)); ?>

<div class="page-header">
    <h2>Fin de la création</h2>
</div>

<form class="form-horizontal">
    <div class="row">
        <div class="col-xs-8">
            <h3>Appellation / Mention</h3>
            <div class="form-group">
                <strong  class="col-xs-6 text-right"></strong>
                <div class="col-xs-6"><span>AOC Alsace</span></div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-8">
            <h3>Prélévements</h3>
            <div class="form-group">
                <strong  class="col-xs-6 text-right">Date de prélevement</strong>
                <div class="col-xs-6"><span>du 12/12/2014 au 02/28/2014</span></div>
            </div>
            <div class="form-group">
                <strong  class="col-xs-6 text-right">Nombre d'opérateurs concernés</strong>
                <div class="col-xs-6"><span>60</span></div>
            </div>
            <div class="form-group">
                <label for="nb_a_prelever" class="col-xs-6 control-label">Nombre d'opérateurs à prélever</label>
                <div class="col-xs-2">
                    <input type="text" class="form-control" id="nb_a_prelever" placeholder="">
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-8">
            <h3>Dégustation</h3>
            <div class="form-group">
                <strong  class="col-xs-6 text-right">Date de la dégustation</strong>
                <div class="col-xs-6"><span>01/03/2014</span></div>
            </div>
            <div class="form-group">
                <label for="nb_a_prelever" class="col-xs-6 control-label">Nombre de commissions</label>
                <div class="col-xs-2">
                    <input type="text" class="form-control" id="nb_a_prelever" placeholder="">
                </div>
            </div>
            <div class="form-group">
                <label for="inputDate1" class="col-xs-6 control-label">Heure</label>
                <div class="col-xs-4">
                    <div class="input-group date-picker-time">
                        <input type="date" class="form-control" id="inputHoure" placeholder="">
                        <div class="input-group-addon">
                            <span class="glyphicon glyphicon-time"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <strong  class="col-xs-6 text-right">Lieu</strong>
                <div class="col-xs-6">Colmar</div>
            </div>
            <div class="form-group">
                <label for="nb_a_prelever" class="col-xs-6 control-label">Lieu</label>
                <div class="col-xs-6">
                    <input type="text" class="form-control" id="nb_a_prelever" placeholder="">
                </div>
            </div>
        </div>
    </div>

    <div class="row row-margin row-button">
        <div class="col-xs-6">
            <a href="<?php echo url_for('degustation') ?>" class="btn btn-danger btn-lg btn-upper">Annuler</a>
        </div>
        <div class="col-xs-6 text-right">
            <a href="<?php echo url_for('degustation_operateurs') ?>" class="btn btn-default btn-lg btn-upper">Continuer</a>
        </div>
    </div>
</form>