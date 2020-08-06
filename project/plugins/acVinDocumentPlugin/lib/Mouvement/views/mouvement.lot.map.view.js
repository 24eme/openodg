function (doc) {
  if (!doc.mouvements_lots) {
    return;
  }
  for(identifiant in doc.mouvements_lots) {
    for(key in doc.mouvements_lots[identifiant]) {
      lot = doc.mouvements_lots[identifiant][key];
      emit([lot.prelevable, lot.preleve, lot.region, lot.date, lot.declarant_identifiant, lot.origine_document_id], lot);
    }
  }
}
