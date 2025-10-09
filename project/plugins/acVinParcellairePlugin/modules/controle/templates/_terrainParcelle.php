<?php include_partial('controle/terrainBlocDeclarant'); ?>

<h2>Parcelle <span class="text-muted small">{{ parcelleCourante.parcelle_id }}</span></h2>
<table class="table table-bordered table-condensed table-striped tableParcellaire">
    <thead>
        <tr>
            <th class="col-xs-2">Commune</th>
            <th class="col-xs-1">Lieu-dit</th>
            <th class="col-xs-1 text-center">Section / N° parcelle</th>
            <th class="col-xs-4">Cépage</th>
            <th class="col-xs-1 text-center">Année plantat°</th>
            <th class="col-xs-1 text-right">Superficie <span class="text-muted small">(ha)</span></th>
            <th class="col-xs-1 text-center">Écart Pieds/Rang</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ parcelleCourante.commune }}</td>
            <td>{{ parcelleCourante.lieu }}</td>
            <td class="text-center">{{ parcelleCourante.section }} {{ parcelleCourante.numero_parcelle }}</td>
            <td><span class="text-muted">{{ parcelleCourante.source_produit_libelle }}</span> {{ parcelleCourante.cepage }}</td>
            <td class="text-center">{{ parcelleCourante.campagne_plantation }}</td>
            <td class="text-right">{{ echoFloat(parcelleCourante.superficie) }}</td>
            <td class="text-center">{{ parcelleCourante.ecart_pieds }} / {{ parcelleCourante.ecart_rang }}</td>
        </tr>
    </tbody>
</table>
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

<RouterLink class="btn btn-default" :to="{ name: 'operateur', params: { id: controleCourant._id } }"><span class="glyphicon glyphicon-chevron-left"></span> Retour</RouterLink>
<button class="btn btn-primary pull-right" @click="save()">Valider</button>
