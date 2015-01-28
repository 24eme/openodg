<?php include_partial('admin/menu', array('active' => 'tournees')); ?>

<form action="" method="post" class="form-horizontal">
    
    <div class="row">
        <div class="col-xs-8">
            <div class="form-group">
                <label for="inputDate1" class="col-xs-6 control-label">Date de fin de prélèvements</label>
                <div class="col-xs-6">
                    <div class="input-group date-picker-all-days">
                        <input type="date" class="form-control" id="inputDate1" placeholder="">
                        <div class="input-group-addon">
                            <span class="glyphicon-calendar glyphicon"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="inputEmail3" class="col-xs-6 control-label">Famille</label>
                <div class="col-xs-6">
                    <select data-placeholder="Sélectionner une famille" class="form-control select2 select2-offscreen select2autocomplete">
                        <option selected="selected"></option>
                        <option>AOC Alsace</option>
                        <option>VT / SGN</option>
                        <option>Grands Crus</option>
                    </select>
                </div>
            </div>
	    <div class="form-group">
                <label for="inputEmail3" class="col-xs-6 control-label">Nombre d'opérateurs concernés</label>
                <div class="col-xs-6"><p>60</p></div>
	    </div>
            <div class="form-group">
                <label for="nb_a_prelever" class="col-xs-6 control-label">Nombre d'opérateurs à prélever</label>
                <div class="col-xs-6">
                        <input type="text" class="form-control" id="nb_a_prelever" placeholder="">
                </div>
            </div>
            <div class="form-group text-right">
            <a href="<?php echo url_for('degustation_operateurs') ?>" class="btn btn-default btn-lg btn-upper">Créer</a>
            </div>
        </div>
    </div>
    
</form>
