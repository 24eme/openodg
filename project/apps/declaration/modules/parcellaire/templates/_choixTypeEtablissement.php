    <div class="panel panel-primary" > 
        <div class="panel-body">
            <form action="<?php echo url_for('parcellaire_creation', array('identifiant' => 'ETB000000')) ?>" method="post" class="form-horizontal"  >
                <div class="row">
                    <div class="col-xs-8">
                        <div class="form-group">
                            <div class="col-sm-6 text-right">
                                <input type="checkbox" class="form-control" name="info_type" value="">                              
                            </div>
                            <label for="info_type" class="col-sm-6 text-right control-label">Viticulteur - manipulant</label>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-6 text-right">
                                <input type="checkbox" class="form-control" name="info_type" value="">                              
                            </div>
                            <label for="info_type" class="col-sm-6 text-right control-label">Adhérent Cave Coop</label>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-6 text-right">
                                <input type="checkbox" class="form-control" name="info_type" value="">                              
                            </div>
                            <label for="info_type" class="col-sm-6 text-right control-label">Vendeur de raisin</label>
                        </div>

                         <div class="form-group">
                            <div class="col-sm-6 text-right">
                                <select class="form-control" name="vendeur_liste" value="">  
                                    <option>Nom - CVI</option>
                                    <option>Nom 2 - CVI 2</option>
                                </select>
                            </div>
                            <label for="info_type" class="col-sm-6 text-right control-label">Vendeur de raisin</label>
                        </div>


                        <div class="row row-margin row-button">
                            <div class="col-sm-6"></div>
                            <div class="col-xs-6 text-right">
                                <button type="submit" class="btn btn-default btn-lg btn-upper"><span class="glyphicon glyphicon-check"></span>&nbsp;&nbsp;Valider</button>
                            </div>               
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>