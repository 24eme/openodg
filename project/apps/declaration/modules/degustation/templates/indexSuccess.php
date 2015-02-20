<?php include_partial('admin/menu', array('active' => 'tournees')); ?>

<form action="" method="post" class="form-horizontal">
    
    <div class="row">
        <div class="col-xs-6">
            <div class="form-group">
                <label for="inputEmail3" class="col-sm-6 control-label">Date de fin de prélèvements</label>
                <div class="col-sm-6">
                    <div class="input-group date-picker-all-days">
                        <input type="email" class="form-control" id="inputEmail3" placeholder="">
                        <div class="input-group-addon">
                            <span class="glyphicon-calendar glyphicon"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="inputEmail3" class="col-sm-6 control-label">Famille</label>
                <div class="col-sm-6">
                    <select data-placeholder="Sélectionner une famille" class="form-control select2 select2-offscreen select2autocomplete">
                        <option selected="selected"></option>
                        <option>AOC Alsace</option>
                        <option>VT / SGN</option>
                        <option>Grands Crus</option>
                    </select>
                </div>
            </div>
            <div class="form-group text-right">
            <a href="<?php echo url_for('degustation_operateurs') ?>" class="btn btn-default btn-lg btn-upper">Créer</a>
            </div>
        </div>
    </div>
    
</form>