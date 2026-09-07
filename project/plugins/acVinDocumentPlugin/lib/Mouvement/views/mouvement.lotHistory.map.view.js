function (doc) {
  if (!doc.mouvements_lots) {
    return;
  }
  for (var identifiant in doc.mouvements_lots) {
    for (var key in doc.mouvements_lots[identifiant]) {
      var mouvement = doc.mouvements_lots[identifiant][key];
      if (mouvement.region) {
        var regions = mouvement.region.split('|');
        for (var i = 0; i < regions.length; i++) {
          var region = regions[i];
          emit([
            region,
            mouvement.declarant_identifiant,
            mouvement.campagne,
            mouvement.numero_dossier,
            mouvement.numero_archive,
            mouvement.document_ordre,
            mouvement.statut,
            mouvement.document_id,
            mouvement.unique_id
          ], mouvement);
        }
      }
      emit([
        null,
        mouvement.declarant_identifiant,
        mouvement.campagne,
        mouvement.numero_dossier,
        mouvement.numero_archive,
        mouvement.document_ordre,
        mouvement.statut,
        mouvement.document_id,
        mouvement.unique_id
      ], mouvement);
    }
  }
}
