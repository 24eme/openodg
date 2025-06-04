<div class="dropdown dropup center-block" style="width: 150px;">
    <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Télécharger...&nbsp;<span class="caret"></span></button>
    <ul class="dropdown-menu">
<?php if($sf_user->isAdmin()): ?>
        <li class="dropdown-header">Documents internes</li>
        <li><a href="<?php echo url_for('parcellaire_export_pp_ods', array('id' => $parcellaire->_id)); ?>" class="dropdown-item">Télécharger le tableur du potentiel de production</a></li>
        <li><a href="<?php echo url_for('parcellaire_export_ods', array('id' => $parcellaire->_id)); ?>" class="dropdown-item">Télécharger le doc de contrôle</a></li>
        <li><a href="<?php echo url_for('parcellaire_export_geojson', array('id' => $parcellaire->_id)); ?>" class="dropdown-item">Télécharger les coordonnées géographiques en geojson</a></li>
        <li><a href="<?php echo url_for('parcellaire_export_kml_parcelles', array('id' => $parcellaire->_id)); ?>" class="dropdown-item">Télécharger les coordonnées géographiques en KML - Parcelles</a></li>
        <li><a href="<?php echo url_for('parcellaire_export_kml_aires', array('id' => $parcellaire->_id)); ?>" class="dropdown-item">Télécharger les coordonnées géographiques en KML - Aires</a></li>
        <li class="divider"></li>
        <li class="dropdown-header">Documents partagés avec les opérateurs</li>
<?php endif; ?>
        <li><a href="<?php echo url_for('parcellaire_export_pp_pdf', array('id' => $parcellaire->_id)); ?>" class="dropdown-item">Télécharger le PDF du potentiel de production</a></li>
        <li><a href="<?php echo url_for('parcellaire_export_csv', array('id' => $parcellaire->_id)); ?>" class="dropdown-item">Télécharger le CSV du parcellaire</a></li>
        <li class="<?php if(!$parcellaire->hasParcellairePDF()): ?>disabled<?php endif; ?>"><a href="<?php echo url_for('parcellaire_pdf', array('id' => $parcellaire->_id)); ?>" class="dropdown-item">Télécharger le PDF Douanier</a></li>
    </ul>
</div>
