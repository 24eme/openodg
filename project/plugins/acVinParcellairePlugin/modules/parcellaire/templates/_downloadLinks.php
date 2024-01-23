<div class="dropdown dropup center-block" style="width: 150px;">
    <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Télécharger...&nbsp;<span class="caret"></span></button>
    <ul class="dropdown-menu">
        <li><a href="<?php echo url_for('parcellaire_export_kml', array('id' => $parcellaire->_id)); ?>" class="dropdown-item">Télécharger les coordonnées géographiques en KML</a></li>
        <li><a href="<?php echo url_for('parcellaire_export_geojson', array('id' => $parcellaire->_id)); ?>" class="dropdown-item">Télécharger les coordonnées géographiques en geojson</a></li>
        <li><a href="<?php echo url_for('parcellaire_export_csv', array('id' => $parcellaire->_id)); ?>" class="dropdown-item">Télécharger le CSV du parcellaire</a></li>
        <li class="<?php if(!$parcellaire->hasParcellairePDF()): ?>disabled<?php endif; ?>"><a href="<?php echo url_for('parcellaire_pdf', array('id' => $parcellaire->_id)); ?>" class="dropdown-item">Télécharger le PDF Douanier</a></li>
    </ul>
</div>
