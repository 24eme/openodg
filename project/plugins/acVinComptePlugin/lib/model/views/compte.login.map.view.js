function(doc) {
     if (doc.type && doc.type != "Compte") {
   	   return ;
     }
     emit([doc.identifiant], doc.mot_de_passe);
     if (doc.alternative_logins) {
         for (var key in doc.alternative_logins) {
	     var login = doc.alternative_logins[key];
	     if(login) {
             emit([login], doc.mot_de_passe);
	     }
         }
     }
  }
