function (doc) {
  if (!doc.mouvements_lots) {
    return;
  }
  for(identifiant in doc.mouvements_lots) {
    for(key in doc.mouvements_lots[identifiant]) {
      var mouvement = doc.mouvements_lots[identifiant][key];
      var lot = doc.lots[mouvement.lot_hash.replace("/lots/", "")*1];
      emit([
        mouvement.statut, mouvement.declarant_identifiant, mouvement.lot_unique_id
      ], lot);

      emit([
        null, mouvement.declarant_identifiant, mouvement.lot_unique_id
      ], lot);
    }
  }
}
