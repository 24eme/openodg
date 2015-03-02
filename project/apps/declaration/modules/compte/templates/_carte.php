<?php use_javascript('lib/leaflet/leaflet.js'); ?>
<?php use_javascript('lib/leaflet/marker.js'); ?>
<?php use_stylesheet('/js/lib/leaflet/leaflet.css'); ?>
<?php use_stylesheet('/js/lib/leaflet/marker.css'); ?>

<div id="carte" data-title="" data-point='<?php echo json_encode(array_values($compte->getRawValue()->getCoordonneesLatLon())) ?>' class="col-xs-12 carte" style="height: 250px; margin-bottom: 20px;"></div>