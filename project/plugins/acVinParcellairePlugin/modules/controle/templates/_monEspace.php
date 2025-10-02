<?php if (!$sf_user->isAdmin()) return; ?>
<?php if (!$etablissement->hasFamille(EtablissementFamilles::FAMILLE_PRODUCTEUR ) && !$etablissement->hasFamille(EtablissementFamilles::FAMILLE_PRODUCTEUR_VINIFICATEUR)) return; ?>

<?php use_helper('Date'); ?>

<div class="col-sm-6 col-md-4 col-xs-12">
    <div class="block_declaration panel <?php if($controle): ?>panel-primary<?php else: ?>panel-default<?php endif; ?>">
        <div class="panel-heading">
            <h3 class="panel-title">Contrôle <?php echo $periode ?></h3>
        </div>


    <?php if (!$parcellaire): ?>
      <div class="panel-body">
          <p class="explications">Les données parcellaire ne sont pas présente sur la plateforme.<br/><br/>Il n'est donc pas possible de contôler les parcelles de cet opérateur : <a href="<?php echo url_for("parcellaire_declarant", $etablissement) ?>">Voir le parcellaire</a></p>
      </div>
      <?php else:  ?>
    <div class="panel-body">
        <p class="explications">Identifier les parcelles à contrôler pour la période <?php echo $periode ?>.</p>
        <div class="actions">
            <a class="btn btn-block btn-default" href="<?php echo url_for('controle_nouveau', array('sf_subject' => $etablissement)) ?>">Démarrer le contrôle</a>
        </div>
    </div>
    <?php endif; ?>
    <div class="panel-footer" style="padding-top: 0; padding-bottom: 0;">
        <a href="<?php echo url_for('pieces_historique', array('sf_subject' => $etablissement, 'categorie' => 'controle')) ?>" class="btn btn-xs btn-link btn-block">Voir tous les documents</a>
    </div>
    </div>
</div>
