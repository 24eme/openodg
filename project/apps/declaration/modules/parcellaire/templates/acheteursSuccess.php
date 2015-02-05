<?php include_partial('step', array('step' => 'acheteurs', 'identifiant' => 'XXX')); ?>

<div class="page-header">
    <h2>Saisie des acheteurs</h2>
</div>


<form action="" method="post" class="">
    <div class="row">       
        <div class="col-xs-12">
            <div id="listes_cepages" class="list-group">
                <table class="table table-striped">
                    <tr>
                        <th></th>           
                        <th>Acheteur 1</th>        
                        <th>Acheteur 2</th>     
                        <th>Acheteur 3</th>        
                        <th>
                    <div class="text-center">
                        <button class="btn btn-warning ajax btn-sm" data-toggle="modal" data-target="#popupForm" type="button"><span class="eleganticon icon_plus"></span></button>
                    </div>
                    </th>        
                    </tr>
                    <tr>
                        <td>Communale Klevener</td>               
                        <td><input type="checkbox"></td>                  
                        <td><input type="checkbox"></td>                  
                        <td><input type="checkbox"></td> 
                        <td></td> 
                    </tr>
                    <tr>        
                        <td>Communale X</td>           
                        <td><input type="checkbox"></td>                  
                        <td><input type="checkbox"></td>               
                        <td><input type="checkbox"></td>               
                        <td></td>                 
                    </tr>
                </table>
            </div>

        </div>
    </div>

    <div class="row row-margin row-button">
        <div class="col-xs-6">
            <a href="<?php echo url_for('parcellaire_infos_modification', array('identifiant' => 'XXX')) ?>" class="btn btn-primary btn-primary-step btn-lg btn-upper">Précédent</a>
        </div>
        <div class="col-xs-6 text-right">
            <a href="<?php echo url_for('parcellaire_acheteurs', array('identifiant' => 'XXX')) ?>" class="btn btn-default btn-default-step btn-lg btn-upper">Continuer</a>
        </div>
    </div>
</form>
