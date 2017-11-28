function(doc) {

<<<<<<< HEAD
    if(doc.type != "DRev" && doc.type != "DRevMarc" && doc.type != "Parcellaire" && doc.type != "Tirage") {
=======
    if(doc.type != "DRev" && doc.type != "DRevMarc" && doc.type != "Parcellaire" && doc.type != "Tirage" && doc.type != "TravauxMarc") {
>>>>>>> ava_master

        return;
    }

    if(doc.lecture_seule) {

        return;
    }

    emit([doc.identifiant, doc.campagne, doc.type], 1);
}
