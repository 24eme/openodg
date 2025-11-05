<?php include_partial('controle/terrainBlocDeclarant'); ?>

<h2>Synthèse terrain</h2>
<form class="form-horizontal">

   <div class="form-group">
       <label class="col-sm-2 control-label">Tous les points controlés</label>
       <div class="col-sm-10">
           <label class="radio-inline">
             <input type="radio" value="1" v-model="controleCourant.audit.all_points_controles" /> Oui
           </label>
           <label class="radio-inline">
             <input type="radio" value="0" v-model="controleCourant.audit.all_points_controles" /> Non
           </label>
       </div>
   </div>

   <div class="form-group">
       <label class="col-sm-2 control-label">Tous les points conformes</label>
       <div class="col-sm-10">
           <label class="radio-inline">
             <input type="radio" value="1" v-model="controleCourant.audit.all_points_conformes" /> Oui
           </label>
           <label class="radio-inline">
             <input type="radio" value="0" v-model="controleCourant.audit.all_points_conformes" /> Non
           </label>
       </div>
   </div>

   <div class="form-group">
       <label class="col-sm-2 control-label">Observations</label>
       <div class="col-sm-10">
           <textarea rows="5" class="form-control" v-model="controleCourant.audit.observations"></textarea>
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
          <input type="text" class="form-control input-lg" v-model="controleCourant.audit.operateur_signature" />
      </div>
  </div>

</form>

<RouterLink class="btn btn-default" :to="{ name: 'operateur', params: { id: controleCourant._id } }"><span class="glyphicon glyphicon-chevron-left"></span> Retour</RouterLink>
<button class="btn btn-primary pull-right" @click="save()">Valider</button>
