<?php include_partial('admin/menu', array('active' => 'tournees')); ?>

<form action="" method="post" class="form-horizontal">
    
    <div class="row">
        <div class="col-xs-8">
            <div class="form-group">
                <label for="inputDate1" class="col-xs-6 control-label">Date de dégustation</label>
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
                <label for="inputEmail3" class="col-xs-6 control-label">Appellation / Mention</label>
                <div class="col-xs-6">
                    <select class="form-control">
                        <option selected="selected"></option>
                        <option>AOC Alsace</option>
                        <option>VT / SGN</option>
                        <option>Grands Crus</option>
                    </select>
                </div>
            </div>
            <div class="form-group text-right">
            <a href="<?php echo url_for('degustation_creation') ?>" class="btn btn-default btn-lg btn-upper">Créer</a>
            </div>
        </div>
    </div>
    
</form>
