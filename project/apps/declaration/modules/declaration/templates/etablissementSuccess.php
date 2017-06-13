<ol class="breadcrumb">
  <li><a href="<?php echo url_for('declaration'); ?>">Déclarations</a></li>
  <li class="active"><a href="<?php echo url_for('declaration_etablissement', $etablissement); ?>"><?php echo $etablissement->getNom() ?> (<?php echo $etablissement->identifiant ?>)</a></li>
</ol>

<h4>Veuillez trouver ci-dessous l'ensemble de vos éléments déclaratifs</h4>
<div class="row">
    <?php include_component('drev', 'monEspace', array('etablissement' => $etablissement)); ?>
    <?php include_component('drevmarc', 'monEspace', array('etablissement' => $etablissement)); ?>
    <?php include_component('parcellaire', 'monEspace', array('etablissement' => $etablissement)); ?>
    <?php include_component('parcellaireCremant', 'monEspace', array('etablissement' => $etablissement)); ?>
    <?php include_component('tirage', 'monEspace', array('etablissement' => $etablissement)); ?>
    <?php include_component('fichier', 'monEspace', array('etablissement' => $etablissement)); ?>
</div>
<?php include_partial('fichier/history', array('etablissement' => $etablissement, 'history' => PieceAllView::getInstance()->getPiecesByEtablissement($etablissement->identifiant), 'limit' => Piece::LIMIT_HISTORY)); ?>
