<div class="page-header">
    <h2>Compte <?php echo $compte->identifiant; ?> (<?php echo CompteClient::getInstance()->getCompteTypeLibelle($compte->type_compte); ?>)</h2>
</div>

<div class="row col-xs-12">
    <div class="row">
        <div class="col-xs-6">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3>Identité</h3>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <label class="col-xs-6">Nom / Raison Sociale </label>    
                        <div class="col-xs-6">
                            <?php echo $compte->nom_a_afficher; ?>
                        </div>                
                    </div>
                    <?php if ($compte->cvi): ?>
                        <div class="row">
                            <label class="col-xs-6">Cvi </label>    
                            <div class="col-xs-6">
                                <?php echo $compte->cvi; ?>
                            </div>                
                        </div>
                    <?php endif; ?>
                    <?php if ($compte->code_insee): ?>
                        <div class="row">
                            <label class="col-xs-6">Code Insee</label>    
                            <div class="col-xs-6">
                                <?php echo $compte->code_insee; ?>
                            </div>                
                        </div>
                    <?php endif; ?>
                    <?php if ($compte->siren): ?>
                        <div class="row">
                            <label class="col-xs-6">Siren</label>    
                            <div class="col-xs-6">
                                <?php echo $compte->siren; ?>
                            </div>                
                        </div>
                    <?php endif; ?>
                    <?php if ($compte->siret): ?>
                        <div class="row">
                            <label class="col-xs-6">Siret</label>    
                            <div class="col-xs-6">
                                <?php echo $compte->siret; ?>
                            </div>                
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-xs-6">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3>Coordonnées</h3>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <div class="row">
                            <label class="col-xs-6">Adresse</label>    
                            <div class="col-xs-6">
                                <?php echo $compte->adresse; ?>
                            </div>                
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <label class="col-xs-6">Code postal</label>    
                            <div class="col-xs-6">
                                <?php echo $compte->code_postal; ?>
                            </div>                
                        </div>
                    </div>                             
                    <div class="form-group">
                        <div class="row">
                            <label class="col-xs-6">Commune</label>    
                            <div class="col-xs-6">
                                <?php echo $compte->commune; ?>
                            </div>                
                        </div>
                    </div>
                    <?php if ($compte->telephone_bureau): ?>
                        <div class="form-group">
                            <div class="row">
                                <label class="col-xs-6">Téléphone bureau</label>    
                                <div class="col-xs-6">
                                    <?php echo $compte->telephone_bureau; ?>
                                </div>                
                            </div>                        
                        </div>
                    <?php endif; ?>
                    <?php if ($compte->telephone_mobile): ?>
                        <div class="form-group">
                            <div class="row">
                                <label class="col-xs-6">Téléphone mobile</label>    
                                <div class="col-xs-6">
                                    <?php echo $compte->telephone_mobile; ?>
                                </div>                
                            </div>     
                        </div>
                    <?php endif; ?>
                    <?php if ($compte->telephone_prive): ?>
                        <div class="form-group">
                            <div class="row">
                                <label class="col-xs-6">Téléphone privé</label>    
                                <div class="col-xs-6">
                                    <?php echo $compte->telephone_prive; ?>
                                </div>                
                            </div>     
                        </div>
                    <?php endif; ?>
                    <?php if ($compte->fax): ?>
                        <div class="form-group">
                            <div class="row">
                                <label class="col-xs-6">Fax</label>    
                                <div class="col-xs-6">
                                    <?php echo $compte->fax; ?>
                                </div>                
                            </div>   
                        </div>
                    <?php endif; ?>
                    <?php if ($compte->email): ?>
                        <div class="form-group"> 
                            <div class="row">
                                <label class="col-xs-6">Email</label>    
                                <div class="col-xs-6">
                                    <?php echo $compte->email; ?>
                                </div>                
                            </div>     
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3>Informations complémentaire</h3>
                </div>
                <div class="panel-body">

                    <div class="form-group">
                        <label class="col-xs-3">Type de compte :</label> 
                        <label class="col-xs-9 "><?php echo CompteClient::getInstance()->getCompteTypeLibelle($compte->getTypeCompte()); ?></label>
                    </div>
                    <br/>  
                    <?php if ($compte->hasAttributs()): ?>
                        <div class="form-group">
                            <label class="col-xs-3">Attributs :</label> 
                            <div>                                                   
                                <?php foreach ($compte->getInfosAttributs() as $attribut_code => $attribut_libelle): ?>
                                    <span class="label label-xs label-default" style="display: inline-block; margin: 2px;"><?php echo $attribut_libelle ?></span>
                                <?php endforeach; ?>                           
                            </div>
                        </div>
                        <br/>
                    <?php endif; ?>
                    <?php if ($compte->hasProduits()): ?>
                        <div class="form-group">
                            <label class="col-xs-3">Produits :</label> 
                            <p>                         
                                <?php foreach ($compte->getInfosProduits() as $produit_code => $produit_libelle): ?>
                                    <span class="label label-xs label-info" style="display: inline-block; margin: 2px;"><?php echo $produit_libelle ?></span>
                                <?php endforeach; ?>

                            </p>
                        </div>
                        <br/>
                    <?php endif; ?>
                    <?php if ($compte->hasManuels()): ?>
                        <div class="form-group">
                            <label class="col-xs-3">Tags manuels :</label> 
                            <div>           
                                <?php foreach ($compte->getInfosManuels() as $tag_manuel_code => $tag_manuel): ?>
                                    <span class="label label-xs label-success" style="display: inline-block; margin: 2px;"><?php echo $tag_manuel ?></span>
                                <?php endforeach; ?>                               
                            </div>
                        </div>
                        <br/>
                    <?php endif; ?>  
                    <?php if ($compte->hasSyndicats()): ?>
                        <div class="form-group">
                            <label class="col-xs-3">Syndicats :</label> 
                            <p>                         
                                <?php foreach ($compte->getInfosSyndicats() as $syndicat_code => $syndicat_libelle): ?>
                                    <span class="label label-xs label-danger" style="display: inline-block; margin: 2px;"><?php echo $syndicat_libelle ?></span>
                                <?php endforeach; ?>

                            </p>
                        </div>
                        <br/>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php if ($compte->isTypeCompte(CompteClient::TYPE_COMPTE_ETABLISSEMENT) && count($compte->chais)): ?>
        <div class="row">
            <div class="col-xs-12">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h3>Chais</h3>
                    </div>
                    <div class="panel-body">
                        <div class="form-group">
                            <div class="row"> 
                                <?php foreach ($compte->chais as $key => $chai) : ?>
                                    <div class="col-xs-4 text-center">
                                        <strong>Chai N° <?php echo $key + 1; ?></strong>                                    
                                    </div>  
                                    <div class="col-xs-6 text-center">
                                        <?php echo $chai->adresse . ' ' . $chai->code_postal . ' ' . $chai->commune; ?> 
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>  
    <?php endif; ?>

    <div class="row row-margin row-button">
        <div class="col-xs-4">
            <a href="<?php echo url_for("compte_recherche") ?>" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>Retour à la recherche</a>
        </div>
        <div class="col-xs-4 text-center">
            <a class="btn btn-warning" href="<?php echo url_for('compte_modification_admin', array('id' => $compte->identifiant)) ?>">Modifier</a>
        </div>
        <?php if ($compte->isTypeCompte(CompteClient::TYPE_COMPTE_ETABLISSEMENT)): ?>
            <div class="col-xs-4 text-right">               

                <a class="btn btn-default btn-lg btn-upper" href="<?php echo url_for('compte_redirect_espace_etablissement', array("id" => $compte->identifiant)); ?>">Espace etablissement<span class="eleganticon arrow_carrot-right"></span></a>
            </div>
        <?php endif; ?>
    </div>
</div>