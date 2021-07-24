function(doc) {
  if (doc.lots) {
    for(lot_key in doc.lots) {
      var lot = doc.lots[lot_key];
      emit(["Lot", lot.campagne, lot.numero_archive], 1);
    }
  }

  if (!doc.numero_archive) {
    return;
  }

 	campagne_archive = doc.campagne;

 	if(doc.campagne_archive) {
 		campagne_archive = doc.campagne_archive;
 	}

 	type_archive = doc.type;

 	if(doc.type_archive) {
 		type_archive = doc.type_archive;
 	}

     emit([type_archive, campagne_archive, doc.numero_archive], 1)
 }
