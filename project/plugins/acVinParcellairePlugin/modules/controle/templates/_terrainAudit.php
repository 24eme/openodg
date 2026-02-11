<?php include_partial('controle/terrainBlocDeclarant'); ?>

<h2>Synthèse terrain</h2>
<form class="form-horizontal">

   <div class="form-group">
       <label class="col-sm-3 control-label">Nombre de points non conformes</label>
       <div class="col-sm-9">
           <p class="form-control-static" v-if="countPointsNCetRtm().nombreNC">{{ countPointsNCetRtm().nombreNC }}</p>
           <p class="form-control-static" v-else>Aucun</p>
       </div>
   </div>

   <div class="form-group">
       <label class="col-sm-3 control-label">Manquements constatés</label>
       <div class="col-sm-9">
           <p class="form-control-static" v-if="countPointsNCetRtm().manquements.length" v-for="manquement in countPointsNCetRtm().manquements">{{ manquement }}</p>
           <p class="form-control-static" v-else>Tous les points sont conformes</p>
       </div>
   </div>

   <div class="form-group">
       <label class="col-sm-3 control-label">Observation agent.e</label>
       <div class="col-sm-9">
           <textarea rows="3" class="form-control" v-model="controleCourant.audit.agent_observation"></textarea>
       </div>
   </div>


   <div class="form-group">
       <label class="col-sm-3 control-label">Observation de l'opérateur.ice</label>
       <div class="col-sm-9">
           <textarea rows="3" class="form-control" v-model="controleCourant.audit.operateur_observation"></textarea>
       </div>
   </div>

  <div class="form-group">
      <label class="col-sm-3 control-label">Signature Opérateur</label>
      <div class="col-sm-5">
          <canvas id="signature" style="width: 300px;height: 300px; border: 1px solid #cecece;"></canvas>
      </div>
  </div>

</form>

<RouterLink class="btn btn-default" :to="{ name: 'operateur', params: { id: controleCourant._id } }"><span class="glyphicon glyphicon-chevron-left"></span> Retour</RouterLink>
<button class="btn btn-primary pull-right" @click="save()">Valider</button>
