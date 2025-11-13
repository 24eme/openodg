<h3 class="mt-0"><RouterLink :to="{ name: 'operateur', params: { id: controleCourant._id } }"><span class="glyphicon glyphicon-chevron-left"></span></RouterLink> {{ parcelleCourante.parcelle_id }} <RouterLink :to="{ name: 'map_parcelle', params: { idu: parcelleCourante.idu }}" class="pull-right"><span class="glyphicon glyphicon-map-marker"></span></RouterLink></h3>
<hr class="mt-2" />

<?php include_partial('controle/terrainBlocDeclarant'); ?>

<hr />

<h4 class="strong">Parcelle n° {{ parcelleCourante.parcelle_id }}</h4>

<dl class="dl-horizontal">
  <dt>Section / N° parcelle</dt>
  <dd>{{ parcelleCourante.section }} {{ parcelleCourante.numero_parcelle }}</dd>
  <dt>Superficie</dt>
  <dd>{{ echoFloat(parcelleCourante.superficie) }} <span class="text-muted">ha</span></dd>
  <dt>Commune / Lieu-dit</dt>
  <dd>{{ parcelleCourante.commune }} {{ parcelleCourante.lieu }}</dd>
  <dt>Cépage</dt>
  <dd>{{ parcelleCourante.cepage }}</dd>
  <dt>Appellation</dt>
  <dd>{{ parcelleCourante.source_produit_libelle }}</dd>
  <dt>Année de plantation</dt>
  <dd>{{ parcelleCourante.campagne_plantation }}</dd>
  <dt>Écart Pieds/Rang</dt>
  <dd>{{ parcelleCourante.ecart_pieds }} / {{ parcelleCourante.ecart_rang }}</dd>
</dl>

<hr />

<h2>Points de contrôle</h2>
<form class="form-horizontal">
   <div class="form-group" v-for="(val, key) in parcelleCourante.controle.points" :key="key">
       <label class="col-sm-2 control-label">{{ key }}</label>
       <div class="col-sm-10">
           <label class="radio-inline">
             <input type="radio" value="C" v-model="parcelleCourante.controle.points[key]" /> Conforme
           </label>
           <label class="radio-inline">
             <input type="radio" value="NC" v-model="parcelleCourante.controle.points[key]" /> Non Conforme
           </label>
       </div>
   </div>

   <div class="form-group">
       <label class="col-sm-2 control-label">Observations</label>
       <div class="col-sm-10">
           <textarea rows="4" class="form-control" placeholder="Saisir les observations terrain" v-model="parcelleCourante.controle.observations"></textarea>
       </div>
   </div>

  <div class="form-group">
      <label class="col-sm-2 control-label">Superficie à retirer (ha)</label>
      <div class="col-sm-10">
          <input type="text" class="form-control" v-model="parcelleCourante.controle.superficie_a_retirer" />
      </div>
  </div>

</form>

<hr />

<RouterLink class="btn btn-default" :to="{ name: 'operateur', params: { id: controleCourant._id } }"><span class="glyphicon glyphicon-chevron-left"></span> Retour</RouterLink>
<button class="btn btn-primary pull-right" @click="save()">Valider</button>
