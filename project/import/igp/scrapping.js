var configFile = process.argv.slice(2)[0];
var regroupement = process.argv.slice(2)[1] == 1;

var Nightmare = require('nightmare');
require('./nightmare-inline-download.js')(Nightmare);
var fs = require('fs');
var mkdirp = require("mkdirp");
const path = require('path');
var nightmare = Nightmare({ show: true, typeInterval: 1, waitTimeout: 180000, gotoTimeout: 180000, executionTimeout: 180000, timeoutDownloadBeforeStart: 180000, maxDownloadRequestWait: 180000, webPreferences: { preload: path.resolve("pre.js") }});
var config = require('./'+configFile);
var destination_file='imports/'+config.file_name+'/';
var baseUri = config.web_site_produits.replace("/odg/LstAOC.aspx", "");

mkdirp(destination_file+'01_operateurs')
mkdirp(destination_file+'01_operateurs/fiches_contacts_connexion')
mkdirp(destination_file+'02_recoltes')
mkdirp(destination_file+'02_recoltes/syntheses')
mkdirp(destination_file+'03_declarations')
mkdirp(destination_file+'03_declarations/electroniques')
mkdirp(destination_file+'03_declarations/traitees')
mkdirp(destination_file+'03_declarations/syntheses_operateurs')
mkdirp(destination_file+'03_declarations/syntheses')
mkdirp(destination_file+'04_controles_produits')
mkdirp(destination_file+'04_controles_produits/commissions')
mkdirp(destination_file+'04_controles_produits/jures')
mkdirp(destination_file+'04_controles_produits/tableau_de_bord')
mkdirp(destination_file+'05_facturation')
mkdirp(destination_file+'06_administration')
mkdirp(destination_file+'06_administration/cepages')

nightmare

  //authentification
  .goto(config.web_site)
  .type('#LoginPhp',config.user_name)
  .type('#PasswordPhp',config.user_password)
  .click('#identification')
  .wait('.menu')
  .viewport(1400, 1800)
  //fin authentification
 .then(function() {
      var uri = baseUri+"/operateur/ListeOperateur.aspx";
      var exportFilename = destination_file+'01_operateurs/operateurs.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .click('#Button1')
      .wait(10000)
      .click('#Button2')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
  })
  .then(function() {
       var uri = baseUri+"/operateur/ListeOperateur.aspx?type=etiquettes";
       var exportFilename = destination_file+'01_operateurs/operateurs_etiquettes.pdf';
       console.log("export " + uri + ": " + exportFilename);

       return nightmare
       .goto(uri)
       .click('#Button1')
       .wait(10000)
       .click('#btnEtiquette')
       .download(exportFilename)
   })
   .then(function() {
        var uri = baseUri+"/operateur/ListeOperateur.aspx?type=inao";
        var exportFilename = destination_file+'01_operateurs/operateurs_inao.xlsx';
        console.log("export " + uri + ": " + exportFilename);

        return nightmare
        .goto(uri)
        .click('#Button1')
        .wait(10000)
        .click('#btnExportINAO')
        .download(exportFilename)
    })
  .then(function() {
    if(regroupement) {
      return;
    }
      var uri = baseUri+"/operateur/AppRaisin.aspx";
      var exportFilename = destination_file+'01_operateurs/apporteurs_de_raisins.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .click('#Button1')
      .wait(10000)
      .click('#Button2')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
  })
  .then(function() {
    if(regroupement) {
      return;
    }
      var uri = baseUri+"/operateur/AppRaisin.aspx?type=etiquettes";
      var exportFilename = destination_file+'01_operateurs/apporteurs_de_raisins_etiquettes.pdf';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .click('#Button1')
      .wait(10000)
      .click('#btnEtiquette')
      .download(exportFilename)
  })
  .then(function() {
    if(regroupement) {
      return;
    }
      var uri = baseUri+"/operateur/AppRaisin.aspx?type=inao";
      var exportFilename = destination_file+'01_operateurs/apporteurs_de_raisins_inao.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .click('#Button1')
      .wait(10000)
      .click('#btnExportINAO')
      .download(exportFilename)
  })
  .then(function() {
    if(regroupement) {
      return;
    }
      var uri = baseUri+"/operateur/Adresses.aspx?type=courrier";
      var exportFilename = destination_file+'01_operateurs/addresses_operateurs_courrier.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
        .goto(uri)
        .wait('#Button2')
        .click('#Button2')
        .download(exportFilename)
        .screenshot(exportFilename+".png")
  })
  .then(function() {
    if(regroupement) {
      return;
    }
      var uri = baseUri+"/operateur/Adresses.aspx?type=facturation";
      var exportFilename = destination_file+'01_operateurs/addresses_operateurs_facturation.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
        .goto(uri)
        .wait('#Button2')
        .click('#rblA_1')
        .wait(5000)
        .click('#Button2')
        .download(exportFilename)
        .screenshot(exportFilename+".png")
  })
  .then(function() {
    if(regroupement) {
      return;
    }
      var uri = baseUri+"/operateur/Adresses.aspx?type=exploitation";
      var exportFilename = destination_file+'01_operateurs/addresses_operateurs_exploitation.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
        .goto(uri)
        .wait('#Button2')
        .click('#rblA_2')
        .wait(5000)
        .click('#Button2')
        .download(exportFilename)
        .screenshot(exportFilename+".png")
  })
  .then(function() {
    if(regroupement) {
      return;
    }
      var uri = baseUri+"/operateur/ListeOpCessation.aspx";
      var exportFilename = destination_file+'01_operateurs/operateurs_inactifs.xlsx';

      console.log("export " + uri + ": " + exportFilename);

      return nightmare
          .goto(uri)
          .wait('body')
          .exists("#btnExportExcel")
          .then(function (result) {
              if (result) {
                  nightmare
                  .click('#btnExportExcel')
                  .download(exportFilename)
                  .screenshot(exportFilename+".png")
              }
          });
  })
  .then(function() {
      var uri = baseUri+"/operateur/SynOperateurR.aspx";
      var exportFilename = destination_file+'01_operateurs/synthese_operateurs.html';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
        .goto(uri)
        .wait(1000)
        .html(exportFilename, 'HTMLOnly')
        .screenshot(exportFilename+".png")
  })
  .then(function() {
      var uri = baseUri+"/Administration/FicheContact.aspx";
      var exportFilename = destination_file+'01_operateurs/contacts.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .wait('#ContentPlaceHolder1_btnExcel')
      .click('#ContentPlaceHolder1_btnExcel')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
  })
  .then(function() {
      var uri = baseUri+"/Habilitation/GestionDI.aspx";
      var exportFilename = destination_file+'01_operateurs/suivi_DI.html';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
        .goto(uri)
        .wait(1000)
        .html(exportFilename, 'HTMLOnly')
        .screenshot(exportFilename+".png")
  })
  .then(function() {
      var uri = baseUri+"/Habilitation/listeHab.aspx";
      var exportFilename = destination_file+'01_operateurs/gestion_DI.html';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
        .goto(uri)
        .wait(1000)
        .html(exportFilename, 'HTMLOnly')
        .screenshot(exportFilename+".png")
  })
  .then(function() {
      var uri = baseUri+"/Habilitation/HistHab.aspx";
      var exportFilename = destination_file+'01_operateurs/historique_DI.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
        .goto(uri)
        .wait('#btnExcel')
        .click('#btnExcel')
        .download(exportFilename)
        .screenshot(exportFilename+".png")
  })
  .then(function() {
      var uri = baseUri+"/Habilitation/SuiviHab.aspx";
      var exportFilename = destination_file+'01_operateurs/habilitations.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .wait('#btExportExcel')
      .click('#btExportExcel')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
  })
  .then(function() {
      var uri = baseUri+"/Habilitation/SyntheseHab_ODG.aspx";
      var exportFilename = destination_file+'01_operateurs/synthese_habilitations.html';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .wait(1000)
      .html(exportFilename, 'HTMLOnly')
      .screenshot(exportFilename+".png")
  })
  .then(function() {
      var uri = baseUri+"/GestionMail/GestGroupes.aspx";
      var exportFilename = destination_file+'01_operateurs/operateurs_groupes.html';
      console.log("export " + uri + ": " + exportFilename);

     return nightmare
     .goto(uri)
     .wait(1500)
     .html(exportFilename, 'HTMLOnly')
     .screenshot(exportFilename+".png")
     .evaluate(function() {
       var ids = [];
       document.querySelectorAll('tr td input').forEach(
         function(input) {
           if(input.value != "Excel") {
              return;
           }
           ids.push(input.id);
         }
       )
       return ids;
     })
     .then(async function(ids) {
       var i = 0;
       for (key in ids) {
         var id = ids[key];
         var exportFilename = destination_file+'01_operateurs/operateurs_groupes_'+i+'.xslx';
         console.log("export " + uri + ": " + exportFilename);
         i++;

         await nightmare
               .goto(uri+"?uniq="+id)
               .wait(1500)
               .click('#'+id)
               .download(exportFilename);
       }
     })
     .catch(error => {
       console.error('Search failed:', error)
     })
  })
  .then(function() {
      var uri = baseUri+"/GestionMail/gestionGroupesR.aspx";
      var exportFilename = destination_file+'01_operateurs/contacts_groupes.html';
      console.log("export " + uri + ": " + exportFilename);

     return nightmare
     .goto(uri)
     .wait(1500)
     .html(exportFilename, 'HTMLOnly')
     .screenshot(exportFilename+".png")
     .evaluate(function() {
       var ids = [];
       document.querySelectorAll('tr td input').forEach(
         function(input) {
           if(input.value != "Excel") {
              return;
           }
           ids.push(input.id);
         }
       )
       return ids;
     })
     .then(async function(ids) {
       var i = 0;
       for (key in ids) {
         var id = ids[key];
         var exportFilename = destination_file+'01_operateurs/contacts_groupes_'+i+'.xslx';
         console.log("export " + uri + ": " + exportFilename);
         i++;

         await nightmare
               .goto(uri+"?uniq="+id)
               .wait(1500)
               .click('#'+id)
               .download(exportFilename);
       }
     })
     .catch(error => {
       console.error('Search failed:', error)
     })
  })
  .then(function() {
      var uri = baseUri+"/GestionMail/BounceMail.aspx";
      var exportFilename = destination_file+'01_operateurs/mails_erreurs.html';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .wait(1000)
      .html(exportFilename, 'HTMLOnly')
      .screenshot(exportFilename+".png")
  })
  .then(async function() {
      if(regroupement) {
        return;
      }
      var uri = baseUri+"/Declaration/LstLotRecolte.aspx";

      for(var i = 2016; i <= 2020; i++) {
          var exportFilename = destination_file+'02_recoltes/recoltes_details_'+i+'.xlsx';
          console.log("export " + uri + ": " + exportFilename);
          await nightmare
          .goto(uri+"?annee="+i)
          .wait(2000)
          .select('#ddlAnnee',i+"")
          .wait(3000)
          .click('#Button1')
          .wait(2000)
          .click('#btnExport')
          .download(exportFilename)
          .screenshot(exportFilename+".png")
          .refresh()
          .wait(2000)
      }

      return nightmare;
  })
  .then(function() {
      var uri = baseUri+"/Declaration/LstLotRecolteNC.aspx";
      var exportFilename = destination_file+'02_recoltes/recoltes_non_conforme.html';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
       .goto(uri)
       .click('#Button1')
       .wait(2000)
       .html(exportFilename, 'HTMLOnly')
       .screenshot(exportFilename+".png")
       .refresh()
  })
  .then(async function() {
      var uri = baseUri+"/Declaration/SyntheseRecolte.aspx";
       await nightmare
        .goto(uri)
        .wait('body')
        .exists("#ddlAnnee")
        .then(async function (result) {
            if (!result) {
                return nightmare;
            }

            for(var i = 2016; i <= 2020; i++) {
                var exportFilename = destination_file+'02_recoltes/syntheses/recoltes_syntheses_'+i+'.html';
                console.log("export " + uri + ": " + exportFilename);

               await nightmare
               .goto(uri+"?uniqid="+i)
               .wait(1000)
               .select('#ddlAnnee',i+"")
               .wait(1000)
               .click('#Button1')
               .wait(3000)
               .html(exportFilename)
               .screenshot(exportFilename+".png")
               .refresh()
               .catch(error => {
                 console.error('Search failed:', error)
               })
            }

            return nightmare;
        });

       return nightmare;
  })
  .then(function() {
      var uri = baseUri+"/Declaration/LstLots.aspx";
      var exportFilename = destination_file+'03_declarations/lots.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
       .goto(uri)
       .select('#ddlCamp','')
       .click('#btnRech')
       .wait(20000)
       .click('#btnEE')
       .wait(4000)
       .download(exportFilename)
       .screenshot(exportFilename+".png")
       .refresh()
  })
  .then(function() {
    if(regroupement) {
      return;
    }
      var uri = baseUri+"/Declaration/LstDeclaRevBoth.aspx";
      var exportFilename = destination_file+'03_declarations/electroniques/revendication_vin_apte_au_controle.html';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
       .goto(uri)
       .click('#btnRech')
       .wait(2000)
       .html(exportFilename, 'HTMLOnly')
       .screenshot(exportFilename+".png")
       .refresh()
  })
  .then(function() {
    if(regroupement) {
      return;
    }
      var uri = baseUri+"/Declaration/LstDeclaRev.aspx";
      var exportFilename = destination_file+'03_declarations/traitees/revendication_vin_apte_au_controle.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
       .goto(uri)
       .click('#btnRech')
       .wait(6000)
       .click('#btn_Excel')
       .wait(2000)
       .download(exportFilename)
       .screenshot(exportFilename+".png")
       .refresh()
  })
  .then(function() {
      if(regroupement) {
        return;
      }
      var uri = baseUri+"/Declaration/LstLots.aspx";
      var exportFilename = destination_file+'03_declarations/lots_changements.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
       .goto(uri)
       .wait(1000)
       .select('#ddlCamp','')
       .select('#ddlDecl', 'C')
       .click('#btnRech')
       .wait(10000)
       .click('#btnEE')
       .download(exportFilename)
       .screenshot(exportFilename+".png")
  })
  .then(async function() {
      var uri = baseUri+"/Declaration/LstChangDenNT.aspx";

      var statuts = ["En attente", "Validée", "Refusée"];
      for(key in statuts) {
         var statut = statuts[key];
         var exportFilename = destination_file+'03_declarations/electroniques/changement_denomination_'+statut+'.html';
         console.log("export " + uri + ": " + exportFilename);

         await nightmare
         .goto(uri+"?campagne="+statut)
         .wait(1000)
         .type('#tbNumDos', "' --")
         .select('#ddlEtatDem',statut)
         .click('#btnRech')
         .wait(2000)
         .html(exportFilename)
         .screenshot(exportFilename+".png")
      }
  })
  .then(function() {
      var uri = baseUri+"/Declaration/LstChangDen.aspx";
      var exportFilename = destination_file+'03_declarations/traitees/changement_denomination.xls';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .select('#ddlCampagne','')
      .click('#Button1')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
      .catch(error => {
        console.error('Search failed:', error)
      })
  })
  .then(function() {
      var uri = baseUri+"/Declaration/SyntOpChangDen.aspx";
      var exportFilename = destination_file+'03_declarations/synthese_operateurs/changement_denomination.xls';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .select('#ddlCampagne','')
      .click('#Button1')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
      .catch(error => {
        console.error('Search failed:', error)
      })
  })
  .then(function() {
      var uri = baseUri+"/Declaration/LstLots.aspx";
      var exportFilename = destination_file+'03_declarations/lots_primeur.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
       .goto(uri+"?uniqid=primeur")
       .select('#ddlCamp','')
       .select('#ddlPrimeur','true')
       .click('#btnRech')
       .wait(5000)
       .click('#btnEE')
       .download(exportFilename)
       .screenshot(exportFilename+".png")
       .refresh()
  })
  .then(function() {
      var uri = baseUri+"/Declaration/SyntheseDeclassement.aspx";
      var exportFilename = destination_file+'03_declarations/syntheses/declassements.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .click('#Button2')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
      .catch(error => {
        console.error('Search failed:', error)
      })
  })
  .then(function() {
      if(regroupement) {
        return;
      }
      var uri = baseUri+"/Declaration/LstLots.aspx";
      var exportFilename = destination_file+'03_declarations/lots_changements_primeur.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
       .goto(uri+"?uniqid=primeur")
       .wait(1000)
       .select('#ddlCamp','')
       .select('#ddlDecl', 'C')
       .select('#ddlPrimeur','true')
       .click('#btnRech')
       .wait(5000)
       .click('#btnEE')
       .download(exportFilename)
       .screenshot(exportFilename+".png")
  })
  .then(function() {
    if(regroupement) {
      return;
    }
      var uri = baseUri+"/Declaration/SyntOpRev.aspx";
      var exportFilename = destination_file+'03_declarations/syntheses_operateurs/revendication_apte_controle_par_declaration.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .select('#ddlCampagne','')
      .click('#btn_Excel')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
      .catch(error => {
        console.error('Search failed:', error)
      })
  })
  .then(function() {
    if(regroupement) {
      return;
    }
      var uri = baseUri+"/Declaration/SyntOpRev.aspx";
      var exportFilename = destination_file+'03_declarations/syntheses_operateurs/synthese_revendication_apte_controle_par_changement.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .select('#ddlCampagne','')
      .select('#ddlRevend','C')
      .click('#btn_Excel')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
      .catch(error => {
        console.error('Search failed:', error)
      })
  })
  .then(function() {
      var uri = baseUri+"/Declaration/LstDeclaNT.aspx?declaId=1";
      var exportFilename = destination_file+'03_declarations/electroniques/conditionnement.html';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .click('#btnRech')
      .html(exportFilename)
      .screenshot(exportFilename+".png")
      .catch(error => {
        console.error('Search failed:', error)
      })
  })
  .then(function() {
      var uri = baseUri+"/Declaration/LstDecla.aspx?declaId=1";
      var exportFilename = destination_file+'03_declarations/traitees/conditionnement.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .select('#ddlCampagne','')
      .click('#btnExcel')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
      .catch(error => {
        console.error('Search failed:', error)
      })
  })
  .then(function() {
      var uri = baseUri+"/Declaration/SyntOpDecla.aspx?declaId=1";
      var exportFilename = destination_file+'03_declarations/syntheses_operateurs/conditionnement.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .select('#ddlCampagne','')
      .click('#btn_Excel')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
      .catch(error => {
        console.error('Search failed:', error)
      })
  })
  .then(function() {
      var uri = baseUri+"/Declaration/LstDeclaNT.aspx?declaId=4";
      var exportFilename = destination_file+'03_declarations/electroniques/transaction_vrac_hors_france.html';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .click('#btnRech')
      .html(exportFilename)
      .screenshot(exportFilename+".png")
      .catch(error => {
        console.error('Search failed:', error)
      })
  })
  .then(function() {
      var uri = baseUri+"/Declaration/LstDecla.aspx?declaId=4";
      var exportFilename = destination_file+'03_declarations/traitees/transaction_vrac_hors_france.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .select('#ddlCampagne','')
      .click('#btnExcel')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
      .catch(error => {
        console.error('Search failed:', error)
      })
  })
  .then(function() {
      if(regroupement) {
        return;
      }
      var uri = baseUri+"/Declaration/SyntOpDecla.aspx?declaId=4";
      var exportFilename = destination_file+'03_declarations/syntheses_operateurs/transaction_vrac_hors_france.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .select('#ddlCampagne','')
      .click('#btn_Excel')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
      .catch(error => {
        console.error('Search failed:', error)
      })
  })
  .then(function() {
      var uri = baseUri+"/Declaration/LstDeclaNT.aspx?declaId=6";
      var exportFilename = destination_file+'03_declarations/electroniques/transaction_vrac_france.html';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .click('#btnRech')
      .html(exportFilename)
      .screenshot(exportFilename+".png")
      .catch(error => {
        console.error('Search failed:', error)
      })
  })
  .then(function() {
      var uri = baseUri+"/Declaration/LstDecla.aspx?declaId=6";
      var exportFilename = destination_file+'03_declarations/traitees/transaction_vrac_france.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .select('#ddlCampagne','')
      .click('#btnExcel')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
      .catch(error => {
        console.error('Search failed:', error)
      })
  })
  .then(function() {
    if(regroupement) {
      return;
    }
      var uri = baseUri+"/Declaration/SyntOpDecla.aspx?declaId=6";
      var exportFilename = destination_file+'03_declarations/syntheses_operateurs/transaction_vrac_france.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .select('#ddlCampagne','')
      .click('#btn_Excel')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
      .catch(error => {
        console.error('Search failed:', error)
      })
  })
  .then(function() {
      var uri = baseUri+"/Declaration/LstDeclaNT.aspx?declaId=9";
      var exportFilename = destination_file+'03_declarations/electroniques/changement_denomination_negociant.html';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .click('#btnRech')
      .html(exportFilename)
      .screenshot(exportFilename+".png")
      .catch(error => {
        console.error('Search failed:', error)
      })
  })
  .then(function() {
      var uri = baseUri+"/Declaration/LstDecla.aspx?declaId=9";
      var exportFilename = destination_file+'03_declarations/traitees/changement_denomination_negociant.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .select('#ddlCampagne','')
      .click('#btnExcel')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
      .catch(error => {
        console.error('Search failed:', error)
      })
  })
  .then(function() {
    if(regroupement) {
      return;
    }
      var uri = baseUri+"/Declaration/SyntOpDecla.aspx?declaId=9";
      var exportFilename = destination_file+'03_declarations/syntheses_operateurs/changement_denomination_negociant.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .select('#ddlCampagne','')
      .click('#btn_Excel')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
      .catch(error => {
        console.error('Search failed:', error)
      })
  })
  .then(function() {
      var uri = baseUri+"/Declaration/LstDeclaNT.aspx?declaId=10";
      var exportFilename = destination_file+'03_declarations/electroniques/changement_denomination_autre_igp.html';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .click('#btnRech')
      .html(exportFilename)
      .screenshot(exportFilename+".png")
      .catch(error => {
        console.error('Search failed:', error)
      })
  })
  .then(function() {
      var uri = baseUri+"/Declaration/LstDecla.aspx?declaId=10";
      var exportFilename = destination_file+'03_declarations/traitees/changement_denomination_autre_igp.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .select('#ddlCampagne','')
      .click('#btnExcel')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
      .catch(error => {
        console.error('Search failed:', error)
      })
  })
  .then(function() {
    if(regroupement) {
      return;
    }
      var uri = baseUri+"/Declaration/SyntOpDecla.aspx?declaId=10";
      var exportFilename = destination_file+'03_declarations/syntheses_operateurs/changement_denomination_autre_igp.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .select('#ddlCampagne','')
      .click('#btn_Excel')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
      .catch(error => {
        console.error('Search failed:', error)
      })
  })
  .then(async function() {
    if(regroupement) {
      return;
    }
      var uri = baseUri+"/Declaration/SyntheseChangDen.aspx";
      var exportFilename = destination_file+'03_declarations/syntheses/changement_denomination_tous.html';
      console.log("export " + uri + ": " + exportFilename);

      await nightmare
      .goto(uri)
      .click('#Button1')
      .wait(1000)
      .html(exportFilename)
      .screenshot(exportFilename+".png")
      .refresh()
      .catch(error => {
        console.error('Search failed:', error)
      })

      for(var i = 2016; i <= 2020; i++) {
          var exportFilename = destination_file+'03_declarations/syntheses/changement_denomination_'+i+'.html';
          console.log("export " + uri + ": " + exportFilename);

         await nightmare
         .goto(uri+"?uniqid="+i)
         .select('#ddlCampagne',i+"/"+(i+1))
         .click('#Button1')
         .wait(1000)
         .html(exportFilename)
         .screenshot(exportFilename+".png")
         .refresh()
         .catch(error => {
           console.error('Search failed:', error)
         })
      }
  })
  .then(async function() {
    if(regroupement) {
      return;
    }
      var uri = baseUri+"/Declaration/BilanAnnuelDeclaratif.aspx";

      for(var i = 2018; i <= 2020; i++) {
          var exportFilename = destination_file+'03_declarations/syntheses/bilan_annuel'+i+'.html';
          console.log("export " + uri + ": " + exportFilename);

         await nightmare
         .goto(uri+"?uniqid="+i)
         .select('#ddlAnnee',i+"/"+(i+1))
         .wait(1000)
         .html(exportFilename)
         .screenshot(exportFilename+".png")
         .refresh()
         .catch(error => {
           console.error('Search failed:', error)
         })
      }
  })
    // .then(function() {
    //     var uri = baseUri+"/Analyse/ListeProdNC.aspx";
    //
    //     return nightmare
    //     .goto(uri)
    //     .wait('#ddlCommission')
    //     .evaluate(function() {
    //       var ids = [];
    //       document.querySelectorAll('#ddlCommission option').forEach(
    //         function(option) {
    //           if(!option.value) {
    //             return;
    //           }
    //           ids.push(option.value.replace(/ .*$/, ''));
    //         }
    //       )
    //       return ids;
    //     })
    //   .then(async function(ids) {
    //     for (key in ids) {
    //       var id = ids[key];
    //       var uri = baseUri+"/commission/VisuCommission.aspx?IdCommission="+id;
    //       var exportFilename = destination_file + "04_controles_produits/commissions/commission_"+id+".html";
    //       console.log("export " + uri + ": " + exportFilename);
    //
    //       await nightmare
    //             .goto(uri)
    //             .wait('body')
    //             .html(exportFilename, "HTMLOnly")
    //             .screenshot(exportFilename+".png")
    //             .wait(1000)
    //             .click('#btnPVDegust')
    //             .download(exportFilename.replace(".html", "")+"_pv.pdf")
    //             .refresh()
    //             .catch(error => {
    //               console.error('Search failed:', error)
    //             })
    //     }
    //   })
    // })
  //   .then(function() {
  //       var uri = baseUri+"/Analyse/ListeProdNC.aspx";
  //
  //       return nightmare
  //       .goto(uri)
  //       .wait('#ddlCommission')
  //       .evaluate(function() {
  //         var ids = [];
  //         document.querySelectorAll('#ddlCommission option').forEach(
  //           function(option) {
  //             if(!option.value) {
  //               return;
  //             }
  //             ids.push(option.value.replace(/ .*$/, ''));
  //           }
  //         )
  //         return ids;
  //       })
  //     .then(async function(ids) {
  //       for (key in ids) {
  //         var id = ids[key];
  //         var uri = baseUri+"/commission/VisuCommission.aspx?IdCommission="+id;
  //         var exportFilename = destination_file + "04_controles_produits/commissions/commission_"+id+"_notif.pdf";
  //         console.log("export " + uri + ": " + exportFilename);
  //
  //         await nightmare
  //               .goto(uri)
  //               .wait('body')
  //               .click('#gvPrelev_cbxNotifCAll')
  //               .wait(1000)
  //               .click('#btnImprimer')
  //               .download(exportFilename)
  //               .catch(error => {
  //                 console.error('Search failed:', error)
  //               })
  //       }
  //     })
  // })
 .then(function() {
     var uri = baseUri+"/commission/SuiviCommission.aspx";
     var exportFilename = destination_file+'04_controles_produits/commissions_prevues.html';
     console.log("export " + uri + ": " + exportFilename);

     return nightmare
     .goto(uri)
     .wait(5000)
     .select('#ddlAnnee', '')
     .wait(1000)
     .click('#Button1')
     .wait(5000)
     .html(exportFilename)
     .screenshot(exportFilename+".png")
 })
  .then(function() {
      var uri = baseUri+"/commission/SuiviCommission.aspx";
      var exportFilename = destination_file+'04_controles_produits/commissions_terminees.html';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .wait(5000)
      .click('#BntTermine')
      .wait(5000)
      .select('#ddlAnnee', '')
      .wait(1000)
      .click('#Button1')
      .wait(5000)
      .html(exportFilename)
      .screenshot(exportFilename+".png")
  })
  .then(function() {
      var uri = baseUri+"/Analyse/ListeProdNC.aspx";
      var exportFilename = destination_file+'04_controles_produits/gestion_nc.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .wait('#Button1')
      .click('#Button1')
      .wait('#btnE')
      .click('#btnE')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
  })
  .then(function() {
      var uri = baseUri+"/Facture/SuiviReglement.aspx";
      var exportFilename = destination_file+'05_facturation/reglements_remises.pdf';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .wait(2000)
      .select('#ddlCampagne','')
      .click('#rblRemise_0')
      .click('#btnRechercher')
      .wait(6000)
      .click('#gvFactureAExporter_CheckAll')
      .wait(10000)
      .click('#btnRemiseCheque')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
      .catch(error => {
        console.error('Search failed:', error)
      })
  })
  .then(async function() {
    if(regroupement) {
      return;
    }
      var uri = baseUri+"/Analyse/TableauBordControleP.aspx";
      var exportFilename = destination_file+'04_controles_produits/tableau_de_bord/tous.html';
      console.log("export " + uri + ": " + exportFilename);

      await nightmare
      .goto(uri)
      .wait(1000)
      .html(exportFilename)
      .screenshot(exportFilename+".png")
      .refresh()
      .catch(error => {
        console.error('Search failed:', error)
      })

      for(var i = 2016; i <= 2020; i++) {
          var exportFilename = destination_file+'04_controles_produits/tableau_de_bord/'+i+'.html';
          console.log("export " + uri + ": " + exportFilename);

         await nightmare
         .goto(uri+"?uniqid="+i)
         .select('#ddlAnnee',i+"/"+(i+1))
         .wait(1500)
         .html(exportFilename)
         .screenshot(exportFilename+".png")
         .refresh()
         .catch(error => {
           console.error('Search failed:', error)
         })
      }
  })
  .then(async function() {
    if(regroupement) {
      return;
    }
      var uri = baseUri+"/commission/JuresConv.aspx";

      for(var i = 2016; i <= 2020; i++) {
         var exportFilename = destination_file+'04_controles_produits/jures/jures_convoque_'+i+'.xlsx';
         console.log("export " + uri + ": " + exportFilename);

         await nightmare
         .goto(uri+"?campagne="+i)
         .wait(2000)
         .select('#ddlCampagne',i+"")
         .click('#btnResearch')
         .wait(3000)
         .click('#btnExportExcel')
         .download(exportFilename)
         .screenshot(exportFilename+".png")
       }
  })
  .then(async function() {
    if(regroupement) {
      return;
    }
      var uri = baseUri+"/commission/DefraiementJures.aspx";

      for(var i = 2016; i <= 2020; i++) {
         var exportFilename = destination_file+'04_controles_produits/jures/jures_defraiement'+i+'.xlsx';
         console.log("export " + uri + ": " + exportFilename);

         await nightmare
         .goto(uri+"?campagne="+i)
         .select('#ddlCampagne',i+"")
         .wait(3000)
         .click('#btnExportExcel')
         .download(exportFilename)
         .screenshot(exportFilename+".png")
         .refresh()
         .wait(2000)
      }

       return nightmare;
  })
  .then(function() {
      var uri = baseUri+"/Facture/LstFacture.aspx";
      var exportFilename = destination_file+'05_facturation/factures.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .wait('#ddlCampagne')
      .select('#ddlCampagne','')
      .wait(3000)
      .click('#BtnRech')
      .wait(8000)
      .click('#btnExport')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
      .catch(error => {
        console.error('Search failed:', error)
      })
  })
  .then(function() {
      var uri = baseUri+"/Facture/LstFacture.aspx";
      var exportFilename = destination_file+'05_facturation/factures.pdf';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .wait('#ddlCampagne')
      .select('#ddlCampagne','')
      .wait(3000)
      .click('#BtnRech')
      .wait(6000)
      .click('#gvFactureAExporter_CheckAll')
      .wait(10000)
      .click('#btnEditPdf2')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
      .catch(error => {
        console.error('Search failed:', error)
      })
  })
  .then(function() {
      var uri = baseUri+"/Facture/SuiviReglement.aspx";
      var exportFilename = destination_file+'05_facturation/reglements_factures.pdf';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .wait(2000)
      .select('#ddlCampagne','')
      .click('#rblRemise_0')
      .click('#btnRechercher')
      .wait(6000)
      .click('#gvFactureAExporter_CheckAll')
      .wait(10000)
      .click('#btnEditPdf2')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
      .catch(error => {
        console.error('Search failed:', error)
      })
  })
  .then(function() {
      var uri = baseUri+"/Facture/SuiviReglement.aspx";
      var exportFilename = destination_file+'05_facturation/reglements.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .wait(2000)
      .select('#ddlCampagne','')
      .click('#rblRemise_0')
      .click('#btnRechercher')
      .wait(6000)
      .click('#btnExport')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
  })
  .then(function() {
    if(regroupement) {
      return;
    }
       var uri = baseUri+"/odg/FicheODG.aspx";
       var exportFilename = destination_file+'06_administration/fiche_odg.html';
       console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .html(exportFilename, "HTMLOnly")
      .screenshot(exportFilename+".png")
  })
  // .then(function() {
  //     var uri = baseUri+"/Administration/FicheContact.aspx";
  //
  //     return nightmare
  //      .goto(uri)
  //      .type('#ContentPlaceHolder1_tbNom', "' AND password != '' ORDER BY Nom --")
  //      .click('#ContentPlaceHolder1_btnRechercher')
  //      .wait(2000)
  //      .evaluate(function() { return document.querySelector('#ContentPlaceHolder1_NbLignes').innerHTML.replace(/[^0-9]*([0-9]+)[^0-9]*/, '$1'); })
  //      .then(async function(total) {
  //         console.log("Export des " + total + " fiches ayant des identifiants de connexion");
  //         for(let i=0; i < total; i++) {
  //           var exportFilename = destination_file+'01_operateurs/fiches_contacts_connexion/contact_'+i+'html';
  //           console.log("export " + uri + ": " + exportFilename);
  //           await nightmare
  //           .goto(uri+"?i="+i)
  //           .type('#ContentPlaceHolder1_tbNom', "' AND password != '' ORDER BY Nom OFFSET "+i+" ROWS FETCH NEXT 1 ROWS ONLY --")
  //           .click('#ContentPlaceHolder1_btnRechercher')
  //           .wait(1500)
  //           .click('#ContentPlaceHolder1_gvPersonne_btnModifier_0')
  //           .wait(1500)
  //           .goto(baseUri+"/Administration/FichePersonnel.aspx?TP=1")
  //           .wait(1500)
  //           .html(exportFilename, "HTMLOnly")
  //         }
  //
  //         return nightmare;
  //      })
  //  })
  .then(function() {
    if(regroupement) {
      return;
    }
       var uri = baseUri+"/odg/LstCepage.aspx";
       var exportFilename = destination_file+'06_administration/cepages.html';
       console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .wait('#ContentPlaceHolder1_ddlAOC')
      .evaluate(function() {
        var keys = [];
        document.querySelectorAll('#ContentPlaceHolder1_ddlAOC option').forEach(
          function(option) {
            if(!option.value) {
              return;
            }
            keys.push(option.value);
          }
        )
        return keys;
      })
      .then(async function(keys) {
        console.log(keys);
        for (key in keys) {
          var key = keys[key];
          var exportFilename = destination_file + "06_administration/cepages/cepages_"+key+".html";
          console.log("export " + uri + ": " + exportFilename);

          await nightmare
                .goto(uri+"?uniq="+key)
                .wait(1000)
                .select('#ContentPlaceHolder1_ddlAOC', key)
                .wait(2000)
                .html(exportFilename)
                .screenshot(exportFilename+".png");
        }
      });
  })
  .then(function() {
    if(regroupement) {
      return;
    }
     var uri = baseUri+"/odg/LstLogin.aspx";
     var exportFilename = destination_file+'06_administration/personnes.html';
     console.log("export " + uri + ": " + exportFilename);

    return nightmare
    .goto(uri)
    .wait(1000)
    .html(exportFilename, "HTMLOnly")
    .screenshot(exportFilename+".png")
  })
  .then(function() {
    if(regroupement) {
      return;
    }
       var uri = baseUri+"/odg/LstAOC.aspx";
       var exportFilename = destination_file+'06_administration/aoc.html';
       console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .wait(1000)
      .html(exportFilename, "HTMLOnly")
      .screenshot(exportFilename+".png")
  })
  .then(function() {
     var uri = baseUri+"/odg/ParamODG.aspx";
     var exportFilename = destination_file+'06_administration/parametrage.html';
     console.log("export " + uri + ": " + exportFilename);

    return nightmare
    .goto(uri)
    .wait(1000)
    .html(exportFilename, "HTMLOnly")
    .screenshot(exportFilename+".png")
  })
    .then(function() {
        var uri = baseUri+"/commission/LstMembre.aspx";
        var exportFilename = destination_file+'06_administration/membres.xlsx';
        console.log("export " + uri + ": " + exportFilename);

        return nightmare
        .goto(uri)
        .wait(2000)
        .click('#Button1')
        .wait(2000)
        .click('#Button2')
        .download(exportFilename)
        .screenshot(exportFilename+".png")
    })
    .then(function() {
        var uri = baseUri+"/commission/CourrierMembre.aspx";
        var exportFilename = destination_file+'06_administration/membres_courrier.xlsx';
        console.log("export " + uri + ": " + exportFilename);

        return nightmare
        .goto(uri)
        .wait(2000)
        .click('#btnExcel')
        .download(exportFilename)
        .screenshot(exportFilename+".png")
    })
    .then(function() {
        var uri = baseUri+"/commission/LstNonMembre.aspx";
        var exportFilename = destination_file+'06_administration/membres_inactifs.html';
        console.log("export " + uri + ": " + exportFilename);

        return nightmare
        .goto(uri)
        .wait(1000)
        .click('#Button1')
        .wait(3000)
        .html(exportFilename)
        .screenshot(exportFilename+".png")
    })
  .then(function() {
       var uri = baseUri+"/commission/LstLieu.aspx";
       var exportFilename = destination_file+'06_administration/commissions_lieux.html';
       console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .wait(1000)
      .html(exportFilename, "HTMLOnly")
      .screenshot(exportFilename+".png")
  })
  .then(function() {
       var uri = baseUri+"/Analyse/GestionLaboratoire.aspx";
       var exportFilename = destination_file+'06_administration/laboratoires.xlsx';
       console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .wait(1000)
      .click('#Button2')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
  })
  .then(function() {
    if(regroupement) {
      return;
    }
       var uri = baseUri+"/odg/Defraiement.aspx";
       var exportFilename = destination_file+'06_administration/comptabilite_parametrage.html';
       console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .wait(1000)
      .html(exportFilename, "HTMLOnly")
      .screenshot(exportFilename+".png")
  })
  .then(function() {
       var uri = baseUri+"/Administration/ParamManq.aspx";
       var exportFilename = destination_file+'06_administration/manquements.html';
       console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .wait(1000)
      .html(exportFilename, "HTMLOnly")
      .screenshot(exportFilename+".png")
  })
  .then(function() {
      return nightmare.end()
  })
