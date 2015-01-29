<?php include_partial('parcellaire/step', array('active' => 'creation','identifiant' => 'XXX')); ?>

<div class="page-header">
    <h2>Parcellaire - Informations</h2>
</div>

<div class="col-xs-8 col-xs-offset-2">
    <div class="panel panel-primary" > 
        <div class="panel-body">

            <div class="row form-horizontal" id="parcellaire_infos_visualisation" >
                <div class="col-xs-8">
                    <div class="form-group">
                        <label for="nom" class="col-sm-6 control-label">Nom :</label>
                        <div class="col-sm-6 text-right">
                            Petit
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="adresse" class="col-sm-6 control-label">Adresse :</label>
                        <div class="col-sm-6 text-right">
                            226, rue de tolbiac
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="cvi" class="col-sm-6 control-label">CVI :</label>
                        <div class="col-sm-6 text-right">
                            014278214301
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email" class="col-sm-6 control-label">Email :</label>
                        <div class="col-sm-6 text-right">
                            mathurin.petit@gmail.com
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-6"></div>
                        <div class="col-xs-6 text-right">
                            <a href="#" class="btn btn-default btn-lg btn-upper" id="parcellaire_infos_modification_btn" ><span class="glyphicon glyphicon-check"></span>&nbsp;&nbsp;Modifier</a>
                        </div>               
                    </div>
                </div>
            </div>

            <form action="<?php echo url_for('parcellaire_infos_modification', array('identifiant' => 'XXXXXX')) ?>" method="post" class="form-horizontal" id="parcellaire_infos_modification" style="display: none;" >
                <div class="row">
                    <div class="col-xs-8">
                        <div class="form-group">
                            <label for="nom" class="col-sm-6 control-label">Nom :</label>
                            <div class="col-sm-6 text-right">
                                <input type="text" class="form-control" id="nom" placeholder="Nom" value="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="adresse" class="col-sm-6 control-label">Adresse :</label>
                            <div class="col-sm-6 text-right">
                                <input type="text" class="form-control" id="adresse" placeholder="Adresse" value="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="cvi" class="col-sm-6 control-label">Cvi :</label>
                            <div class="col-sm-6 text-right">
                                <input type="text" class="form-control" id="cvi" placeholder="Cvi" value="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="email" class="col-sm-6 control-label">Email :</label>
                            <div class="col-sm-6 text-right">
                                <input type="text" class="form-control" id="email" placeholder="Email" value="">
                            </div>
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



    <div class="panel panel-primary" > 
        <div class="panel-body">
            <form action="<?php echo url_for('parcellaire_creation',array('identifiant' => 'ETB000000')) ?>" method="post" class="form-horizontal"  >
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
</div>

