<?php use_javascript('lib/leaflet/leaflet.js'); ?>
<?php use_stylesheet('/js/lib/leaflet/leaflet.css'); ?>
<?php use_stylesheet('/js/lib/leaflet/marker.css'); ?>

<div id="map" class="col-12" style="height: 350px; margin-bottom: 20px;">
	<button id="refreshButton" onclick="zoomOnMap()"><i class="glyphicon glyphicon-fullscreen"></i></button>
	<button id="locate-position"><i class="glyphicon glyphicon-screenshot"></i></button>
</div>

<script type="text/javascript">
	var parcelles = '<?php echo addslashes($parcellaire->getRawValue()->getGeoJson()) ?>';
    var aires = [];
    <?php foreach(ParcellaireConfiguration::getInstance()->getAiresInfos() as $jsonFolder => $infos): $json = addslashes(implode("|", $parcellaire->getAire($jsonFolder)->getRawValue())); if ($json): ?>
        aires.push({'geojson': '<?php echo $json ?>', 'color': '<?php $infos['color'] ?>', 'name': '<?php echo addslashes($infos['name']) ?>'});
    <?php endif; endforeach; ?>
    console.log(aires);
</script>
<?php use_javascript('lib/leaflet/parcelles-maker.js'); ?>
