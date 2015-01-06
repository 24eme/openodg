<script id="template_chai" type="text/x-jquery-tmpl">
	<div id="chai{nbChais}" class="chai">
		<div class="ligne_form">
			<label for="compte_chais_${nbChais}_adresse">Adresse </label>
			<input type="text" id="compte_etablissements_${nbChais}_adresse" name="compte[chai][${nbChais}][adresse]">
		</div>
		<div class="ligne_form">
			<label for="compte_chais_${nbChais}_commune">Commune </label>
			<input type="text" id="compte_chais_${nbChais}_commune" name="compte[chai][${nbChais}][commune]">
		</div>
		<div class="ligne_form">
			<label for="compte_chais_${nbChais}_code_postal">Code postal <a href="" class="msg_aide" data-msg="help_popup_mandat_siret" title="Message aide"></a></label>
			<input type="text" id="compte_chais_${nbChais}_code_postal" name="compte[chai][${nbChais}][code_postal]">
		</div>
		<a href="#" class="supprimer">Supprimer</a>
	</div>
</script>