<h3 class="mt-0"><RouterLink :to="{ name: 'operateur', params: { id: controleCourant._id } }"><span class="glyphicon glyphicon-chevron-left"></span></RouterLink> {{ controleCourant.declarant.nom }} <RouterLink :to="{ name: 'map' }" class="pull-right"><span class="glyphicon glyphicon-map-marker"></span></RouterLink><span class="pull-right mr-3" :class="savedClass" :style="savedStyle"></span></h3>
<hr class="mt-2" />

<?php include_partial('controle/terrainBlocDeclarant'); ?>

<h2>Synthèse terrain</h2>
<form class="form-horizontal">

    <div class="form-group">
        <label class="col-sm-3 control-label">Maturité</label>
        <div class="col-sm-9">
            <label class="radio-inline">
              <input type="radio" value="C" v-model="controleCourant.audit.maturite" :disabled="controleCourant.audit.saisie == 1"/>
              Conforme
            </label>

            <label class="radio-inline">
              <input type="radio" value="NC" v-model="controleCourant.audit.maturite" :disabled="controleCourant.audit.saisie == 1"/>
              Non Conforme
            </label>

            <label class="radio-inline">
              <input type="radio" value="NA" v-model="controleCourant.audit.maturite" :disabled="controleCourant.audit.saisie == 1"/>
              Non Applicable
            </label>
        </div>
    </div>

   <div class="form-group">
       <label class="col-sm-3 control-label">Nombre de points non conformes</label>
       <div class="col-sm-9">
           <p class="form-control-static" v-if="countPointsNCetGetLibelles().nombreNC">{{ countPointsNCetGetLibelles().nombreNC }}</p>
           <p class="form-control-static" v-else>Aucun</p>
       </div>
   </div>

   <div class="form-group">
       <label class="col-sm-3 control-label">Manquements constatés</label>
       <div class="col-sm-9">
           <pre class="form-control-static" style="white-space: pre-wrap;" v-if="countPointsNCetGetLibelles().manquements.length" v-for="manquement in countPointsNCetGetLibelles().manquements">{{ manquement }}</pre>
           <p class="form-control-static" v-else>Tous les points sont conformes</p>
       </div>
   </div>

   <div class="form-group">
       <label class="col-sm-3 control-label">Observation agent.e</label>
       <div class="col-sm-9">
           <textarea rows="3" class="form-control" v-model="controleCourant.audit.agent_observation" :disabled="controleCourant.audit.saisie == 1"></textarea>
       </div>
   </div>


   <div class="form-group">
       <label class="col-sm-3 control-label">Observation de l'opérateur.ice</label>
       <div class="col-sm-9">
           <textarea rows="3" class="form-control" v-model="controleCourant.audit.operateur_observation" :disabled="controleCourant.audit.saisie == 1"></textarea>
       </div>
   </div>

   <div class="form-group">
       <label class="col-sm-3 control-label">Nom et prénom de l'opérateur ou du représentant</label>
       <div class="col-sm-4">
           <textarea rows="1" class="form-control" v-model="controleCourant.audit.nom_prenom" :disabled="controleCourant.audit.saisie == 1"></textarea>
       </div>
   </div>

  <div class="form-group">
      <label class="col-sm-3 control-label">Signature Opérateur</label>
      <div class="col-sm-5">
          <canvas id="signature" width="300" height="300" style="border: 1px solid #cecece;"></canvas>
          <p id="signature-pad-reset" class="text-muted" style="padding-left: 240px; cursor: pointer;" title="Effacer la signature"><span class="glyphicon glyphicon-trash">Effacer</span></p>
      </div>
  </div>

</form>

<RouterLink class="btn btn-default" :to="{ name: 'operateur', params: { id: controleCourant._id } }"><span class="glyphicon glyphicon-chevron-left"></span> Retour</RouterLink>
<button v-if="controleCourant.audit.saisie != 1" class="btn btn-primary pull-right" @click="save()">Valider</button>
<button v-else class="btn btn-secondary pull-right" @click="devalider()">Dévalider</button>
