<?php use_javascript('lib/leaflet/leaflet.js'); ?>
<?php use_stylesheet('/js/lib/leaflet/leaflet.css'); ?>
<?php use_stylesheet('/js/lib/leaflet/marker.css'); ?>

<div id="map" class="col-12" style="height: 350px; margin-bottom: 20px;">
	<div class="leaflet-touch leaflet-bar"><a id="refreshButton" onclick="zoomOnMap(); return false;" href="#"><span class="glyphicon glyphicon-fullscreen"></span></a></div>
	<div class="leaflet-touch leaflet-bar"><a id="locate-position" href="#"><span class="glyphicon glyphicon-screenshot"></span></a></div>
</div>
<style>
.sectionlabel, .parcellelabel {
	text-shadow: 1px 1px #fff,-1px 1px #fff,1px -1px #fff,-1px -1px #fff,1px 1px 5px #555;
}
</style>
<script type="text/javascript">
<?php $geo = $parcellaire->getRawValue()->getGeoJson(); ?>
<?php if ($geo): ?>
	var parcelles = '<?php echo addslashes(json_encode($geo)) ?>';
<?php else: ?>
	var parcelles = '';
<?php endif; ?>
    var aires = [];
    <?php foreach($parcellaire->getAires() as $id => $aires): ?>
        aires.push({'geojson': '<?php echo addslashes(implode("|", $aires['jsons']->getRawValue())); ?>', 'color': '<?php echo $aires['infos']['color'] ?>', 'name': '<?php echo addslashes($aires['infos']['name']) ?>'});
    <?php endforeach; ?>
</script>
<?php use_javascript('lib/leaflet/parcelles-maker.js?202204131636'); ?>
