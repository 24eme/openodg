<?php include_partial('controle/terrainBlocDeclarant'); ?>

<h2>Synthèse terrain</h2>
<form class="form-horizontal">

   <div class="form-group">
       <label class="col-sm-2 control-label">Nombre de points non conformes</label>
       <div class="col-sm-10">
           <p class="form-control-static" v-if="countPointsNCetRtm().nombreNC">{{ countPointsNCetRtm().nombreNC }}</p>
           <p class="form-control-static" v-else>Aucun</p>
       </div>
   </div>

   <div class="form-group">
       <label class="col-sm-2 control-label">Manquements constatés</label>
       <div class="col-sm-10">
           <p class="form-control-static" v-if="countPointsNCetRtm().manquements.length" v-for="manquement in countPointsNCetRtm().manquements">{{ manquement }}</p>
           <p class="form-control-static" v-else>Tous les points sont conformes</p>
       </div>
   </div>


   <div class="form-group">
       <label class="col-sm-2 control-label">Observations Opérateur</label>
       <div class="col-sm-10">
           <textarea rows="3" class="form-control" v-model="controleCourant.audit.operateur_observations"></textarea>
       </div>
   </div>

  <div class="form-group">
      <label class="col-sm-2 control-label">Signature Opérateur</label>
      <div class="col-sm-5">
          <canvas id="signature" style="width: 300px;height: 300px; border: 1px solid #cecece;"></canvas>
      </div>
  </div>

</form>

<RouterLink class="btn btn-default" :to="{ name: 'operateur', params: { id: controleCourant._id } }"><span class="glyphicon glyphicon-chevron-left"></span> Retour</RouterLink>
<button class="btn btn-primary pull-right" @click="save()">Valider</button>
