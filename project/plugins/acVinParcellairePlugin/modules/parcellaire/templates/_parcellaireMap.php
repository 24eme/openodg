<?php use_javascript('lib/leaflet/leaflet.js'); ?>
<?php use_stylesheet('/js/lib/leaflet/leaflet.css'); ?>
<?php use_stylesheet('/js/lib/leaflet/marker.css'); ?>

<div id="map" class="col-xs-12" style="height: 300px; margin-bottom: 20px;"></div>


<?php $parcellaire_client = ParcellaireClient::getInstance();
$import = $parcellaire_client->getParcellaireGeoJson($parcellaire->getEtablissementObject()->getIdentifiant(), $parcellaire->getEtablissementObject()->getCvi());
?>

<script type="text/javascript">
	var parcelles = JSON.parse('<?php echo $import; ?>');
</script>
<?php use_javascript('lib/leaflet/parcelles-maker.js'); ?>

