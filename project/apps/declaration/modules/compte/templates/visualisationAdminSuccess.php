<div class="page-header">
    <h2>Compte <?php echo $compte->identifiant; ?> (<?php echo $compte->type_compte; ?>)</h2>
</div>

<div class="row">
    <div class="row col-xs-offset-1 col-xs-10" id="row_form_compte_modification">
        <div class="col-xs-6">
            <div class="row">
                <label class="col-xs-6">Nom / Raison Sociale </label>    
                <div class="col-xs-6">
                    <?php echo $compte->nom_a_afficher; ?>
                </div>                
            </div>
            <div class="row">
                <label class="col-xs-6">Adresse</label>   
                <div class="col-xs-6">
                        <?php echo $compte->adresse; ?>
                </div>
              </div>
            <div class="row">
                <label class="col-xs-6 ">Code postal</label>         
                <div class="col-xs-6">
                          <?php echo $compte->code_postal; ?>    
                </div>
            </div>
            <div class="row">
                <label  class="col-xs-6">Commune</label>          
                <div class="col-xs-6">
                    <?php echo $compte->commune; ?>    
                </div>
            </div>
             <div class="row">
                <label class="col-xs-6">Email</label>            
                <div class="col-xs-6">
                    <?php echo $compte->email; ?>    
                </div>
             </div>
        </div>              
        <div class="col-xs-6">       
              <div class="row">
                <label class="col-xs-6">Tél. Bureau</label>            
                <div class="col-xs-6">
                    <?php echo $compte->telephone_bureau; ?>    
                </div>
             </div>
                    
               <div class="row">
                <label class="col-xs-6">Tél. Mobile</label>            
                <div class="col-xs-6">
                    <?php echo $compte->telephone_mobile; ?>    
                </div>
             </div>
            
            <div class="row">
                <label class="col-xs-6">Tél. Pivé</label>            
                <div class="col-xs-6">
                    <?php echo $compte->telephone_prive; ?>    
                </div>
             </div>
             <div class="row">
                <label class="col-xs-6">Fax</label>            
                <div class="col-xs-6">
                    <?php echo $compte->fax; ?>    
                </div>
             </div>
            
           
             <div class="row">
                <label class="col-xs-6">N°&nbsp;SIRET/SIREN</label>            
                <div class="col-xs-6">
                    <?php echo $compte->siret; ?>    
                </div>
             </div>  
        </div>
        <div class="row">
            
            <label class="col-xs-2">Attributs</label>   
            <div class="col-xs-9">
                <?php foreach ($compte->getAttributs() as $attribut_code => $attribut): ?>
                    <div class="row">
                <label class="col-xs-6"><?php echo $attribut_code; ?></label>            
                <div class="col-xs-6"><?php echo $attribut; ?></div>
             </div>  
               <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="row row-margin row-button">
        <div class="col-xs-4"><a href="<?php echo url_for("home") ?>" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retourner <small>à mon espace</small></a></div>
        <div class="col-xs-4 text-center">
            <a href="<?php echo url_for('compte_modification_admin',array('id' => $compte->identifiant)); ?>" class="btn btn-warning">Modifier</a>
        </div>
    </div>
</div>