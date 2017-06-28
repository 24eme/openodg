<ol class="breadcrumb">

  <li><a href="<?php echo url_for('accueil'); ?>">Déclarations</a></li>
  <li><a href="<?php echo url_for('declaration_etablissement', $etablissement); ?>"><?php echo $etablissement->getNom() ?> (<?php echo $etablissement->identifiant ?>)</a></li>
  <li class="active"><a href=""><?php echo $campagne ?>-<?php echo $campagne +1 ?></a></li>
</ol>

<div class="page-header">
    <div class="row">
        <div class="col-xs-7">
            <h2>Eléments déclaratifs </h2>
        </div>
        <div class="col-xs-5 text-right" style="padding-top: 20px;">
            <?php if ($sf_user->isAdmin()): ?>
            <form method="GET" class="form-inline" action="">
                Campagne :
                <select class="select2SubmitOnChange form-control" name="campagne">
                    <?php for($i=ConfigurationClient::getInstance()->getCampagneManager()->getCurrent(); $i > ConfigurationClient::getInstance()->getCampagneManager()->getCurrent() - 5; $i--): ?>
                        <option <?php if($campagne == $i): ?>selected="selected"<?php endif; ?> value="<?php echo $i ?>"><?php echo $i; ?>-<?php echo $i+1 ?></option>
                    <?php endfor; ?>
                </select>
                <button type="submit" class="btn btn-default-step">Changer</button>
            </form>
            <?php else: ?>
                <span style="margin-top: 8px; display: inline-block;" class="text-muted">Campagne <?php echo $campagne ?>-<?php echo $campagne + 1 ?></span>
            <?php endif; ?>
        </div>
    </div>
</div>

<h4>Veuillez trouver ci-dessous l'ensemble de vos éléments déclaratifs</h4>
<div class="row">
    <?php include_component('drev', 'monEspace', array('etablissement' => $etablissement, 'campagne' => $campagne)); ?>
    <?php include_component('drevmarc', 'monEspace', array('etablissement' => $etablissement, 'campagne' => $campagne)); ?>
    <?php include_component('parcellaire', 'monEspace', array('etablissement' => $etablissement, 'campagne' => ConfigurationClient::getInstance()->getCampagneManager()->getNext($campagne))); ?>
    <?php include_component('parcellaireCremant', 'monEspace', array('etablissement' => $etablissement, 'campagne' => ConfigurationClient::getInstance()->getCampagneManager()->getNext($campagne))); ?>
    <?php include_component('tirage', 'monEspace', array('etablissement' => $etablissement, 'campagne' => $campagne)); ?>
    <?php include_component('fichier', 'monEspace', array('etablissement' => $etablissement)); ?>
</div>
<?php include_partial('fichier/history', array('etablissement' => $etablissement, 'history' => PieceAllView::getInstance()->getPiecesByEtablissement($etablissement->identifiant), 'limit' => Piece::LIMIT_HISTORY)); ?>
