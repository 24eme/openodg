<div class="page-header">
    <h2>Compte <?php echo $compte->identifiant; ?> (<?php echo CompteClient::getInstance()->getCompteTypeLibelle($compte->type_compte); ?>)</h2>
</div>

<div class="row col-xs-offset-1 col-xs-10">
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
                    <div class="form-group">
                        <div class="row">
                            <label class="col-xs-6">Téléphone bureau</label>    
                            <div class="col-xs-6">
                                <?php echo $compte->telephone_bureau; ?>
                            </div>                
                        </div>                        
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <label class="col-xs-6">Téléphone mobile</label>    
                            <div class="col-xs-6">
                                <?php echo $compte->telephone_mobile; ?>
                            </div>                
                        </div>     
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <label class="col-xs-6">Téléphone privé</label>    
                            <div class="col-xs-6">
                                <?php echo $compte->telephone_prive; ?>
                            </div>                
                        </div>     
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <label class="col-xs-6">Fax</label>    
                            <div class="col-xs-6">
                                <?php echo $compte->fax; ?>
                            </div>                
                        </div>   
                    </div>
                    <div class="form-group"> 
                        <div class="row">
                            <label class="col-xs-6">Email</label>    
                            <div class="col-xs-6">
                                <?php echo $compte->email; ?>
                            </div>                
                        </div>     
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 panel panel-primary">
            <div class="panel-heading">
                <h3>Informations complémentaire</h3>
            </div>
            <div class="panel-body">
                <div class="form-group">
                    <label class="col-xs-3">Attributs</label> 
                    <div class="col-xs-9">
                        <ul>                            
                            <?php foreach ($compte->getAttributs() as $attribut_code => $attribut_libelle): ?>
                                <li><?php echo $attribut_libelle ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <?php if ($compte->hasProduits()): ?>
                    <div class="form-group">
                        <label class="col-xs-3">Produit</label> 
                        <div class="col-xs-9">
                            <ul>                            
                                <?php foreach ($compte->getProduits() as $produit_code => $produit_libelle): ?>
                                    <li><?php echo $produit_libelle ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($compte->isTypeCompte(CompteClient::TYPE_COMPTE_ETABLISSEMENT)): ?>
                    <div class="form-group">
                        <label class="col-xs-3">Chais</label>
                        <div class="col-xs-9">
                            <ul>        
                                <?php foreach ($compte->chais as $chai) : ?>
                                    <li>
                                        <strong>
                                            <?php echo $chai->adresse . ' ' . $chai->code_postal . ' ' . $chai->commune; ?>
                                        </strong>                                    
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<div class="row col-xs-offset-1 col-xs-10 text-center">
    <a class="btn btn-warning" href="<?php echo url_for('compte_modification_admin',array('id' => $compte->identifiant))?>">Modifier</a>
</div>