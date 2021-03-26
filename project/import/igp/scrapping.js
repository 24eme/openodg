var configFile = process.argv.slice(2)[0];
var Nightmare = require('nightmare');
require('./nightmare-inline-download.js')(Nightmare);
var fs = require('fs');
var mkdirp = require("mkdirp");
const path = require('path');
var nightmare = Nightmare({ show: true, typeInterval: 1, waitTimeout: 180000, gotoTimeout: 180000, executionTimeout: 180000, timeoutDownloadBeforeStart: 180000, maxDownloadRequestWait: 180000, webPreferences: { preload: path.resolve("pre.js") }});
var config = require('./'+configFile);
var destination_file='imports/'+config.file_name+'/';
var baseUri = config.web_site_produits.replace("/odg/LstAOC.aspx", "");

mkdirp(destination_file+'commissions')
mkdirp(destination_file+'fichescontacts')

nightmare

  //authentification
  .goto(config.web_site)
  .type('#LoginPhp',config.user_name)
  .type('#PasswordPhp',config.user_password)
  .click('#identification')
  .wait('.menu')
  .viewport(1400, 2000)
  //fin authentification
 .then(function() {
      var uri = baseUri+"/operateur/ListeOperateur.aspx";
      var exportFilename = destination_file+'operateurs.xlsx';
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
      var uri = baseUri+"/operateur/AppRaisin.aspx";
      var exportFilename = destination_file+'apporteurs_de_raisins.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .wait('#Button2')
      .click('#Button2')
      .wait('body')
      .on('will-download', function() { console.log('test')})
      .download(exportFilename)
      .screenshot(exportFilename+".png")
  })
  .then(function() {
      var uri = baseUri+"/operateur/Adresses.aspx";
      var exportFilename = destination_file+'addresses_courrier_operateurs.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
        .goto(uri)
        .wait('#Button2')
        .click('#Button2')
        .download(exportFilename)
        .screenshot(exportFilename+".png")
  })
  .then(function() {
      var uri = baseUri+"/operateur/ListeOpCessation.aspx";
      var exportFilename = destination_file+'operateurs_inactifs.xlsx';

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
      var uri = baseUri+"/Administration/FicheContact.aspx";
      var exportFilename = destination_file+'contacts.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .wait('#ContentPlaceHolder1_btnExcel')
      .click('#ContentPlaceHolder1_btnExcel')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
  })
  .then(function() {
      var uri = baseUri+"/Habilitation/HistHab.aspx";
      var exportFilename = destination_file+'historique_DI.xlsx';
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
      var exportFilename = destination_file+'habilitations.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .wait('#btExportExcel')
      .click('#btExportExcel')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
  })
  .then(function() {
      var uri = baseUri+"/Declaration/LstLots.aspx";
      var exportFilename = destination_file+'lots.xlsx';
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
      var uri = baseUri+"/Declaration/LstDeclaRev.aspx";
      var exportFilename = destination_file+'revendication_vin_apte_au_controle.xlsx';
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
      var uri = baseUri+"/Declaration/LstLots.aspx";
      var exportFilename = destination_file+'lots_changements.xlsx';
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
  .then(function() {
      var uri = baseUri+"/Declaration/LstChangDen.aspx";
      var exportFilename = destination_file+'changement_denom.xls';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .select('#ddlCampagne','')
      .click('#Button1')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
  })
  .then(function() {
      var uri = baseUri+"/Declaration/SyntheseDeclassement.aspx";
      var exportFilename = destination_file+'synthese_declassements.xls';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .click('#Button2')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
  })
  .then(function() {
      var uri = baseUri+"/Declaration/SyntOpRev.aspx";
      var exportFilename = destination_file+'synthese_revendication_apte_controle.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .select('#ddlCampagne','')
      .click('#btn_Excel')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
  })
  .then(function() {
      var uri = baseUri+"/Declaration/SyntOpRev.aspx";
      var exportFilename = destination_file+'synthese_revendication_apte_controle_changement.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .select('#ddlCampagne','')
      .select('#ddlRevend','C')
      .click('#btn_Excel')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
  })
  .then(function() {
      var uri = baseUri+"/Declaration/LstDecla.aspx?declaId=1";
      var exportFilename = destination_file+'declaration_conditionnement.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .select('#ddlCampagne','')
      .click('#btnExcel')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
  })
  .then(function() {
      var uri = baseUri+"/Declaration/SyntOpDecla.aspx?declaId=1";
      var exportFilename = destination_file+'synthese_conditionnement.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .select('#ddlCampagne','')
      .click('#btn_Excel')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
  })
  .then(function() {
      var uri = baseUri+"/Declaration/LstDecla.aspx?declaId=2";
      var exportFilename = destination_file+'declaration_revendication_1.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .select('#ddlCampagne','')
      .click('#btnExcel')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
  })
  .then(function() {
      var uri = baseUri+"/Declaration/SyntOpDecla.aspx?declaId=2";
      var exportFilename = destination_file+'synthese_revendication_1.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .select('#ddlCampagne','')
      .click('#btn_Excel')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
  })
  .then(function() {
      var uri = baseUri+"/Declaration/LstDecla.aspx?declaId=3";
      var exportFilename = destination_file+'declaration_intention_changement_denomination.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .select('#ddlCampagne','')
      .click('#btnExcel')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
  })
  .then(function() {
      var uri = baseUri+"/Declaration/SyntOpDecla.aspx?declaId=3";
      var exportFilename = destination_file+'synthese_intention_changement_denomination.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .select('#ddlCampagne','')
      .click('#btn_Excel')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
  })
  .then(function() {
      var uri = baseUri+"/Declaration/LstDecla.aspx?declaId=4";
      var exportFilename = destination_file+'declaration_transaction_vrac_hors_france.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .select('#ddlCampagne','')
      .click('#btnExcel')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
  })
  .then(function() {
      var uri = baseUri+"/Declaration/SyntOpDecla.aspx?declaId=4";
      var exportFilename = destination_file+'synthese_transaction_vrac_hors_france.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .select('#ddlCampagne','')
      .click('#btn_Excel')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
  })
  .then(function() {
      var uri = baseUri+"/Declaration/LstDecla.aspx?declaId=5";
      var exportFilename = destination_file+'declaration_recolte.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .select('#ddlCampagne','')
      .click('#btnExcel')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
  })
  .then(function() {
      var uri = baseUri+"/Declaration/SyntOpDecla.aspx?declaId=5";
      var exportFilename = destination_file+'synthese_recolte.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .select('#ddlCampagne','')
      .click('#btn_Excel')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
  })
  .then(function() {
      var uri = baseUri+"/Declaration/LstDecla.aspx?declaId=6";
      var exportFilename = destination_file+'declaration_transaction_vrac_france.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .select('#ddlCampagne','')
      .click('#btnExcel')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
  })
  .then(function() {
      var uri = baseUri+"/Declaration/SyntOpDecla.aspx?declaId=6";
      var exportFilename = destination_file+'synthese_transaction_vrac_france.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .select('#ddlCampagne','')
      .click('#btn_Excel')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
  })
  .then(function() {
      var uri = baseUri+"/Declaration/LstDecla.aspx?declaId=7";
      var exportFilename = destination_file+'declaration_revendication_2.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .select('#ddlCampagne','')
      .click('#btnExcel')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
  })
  .then(function() {
      var uri = baseUri+"/Declaration/SyntOpDecla.aspx?declaId=7";
      var exportFilename = destination_file+'synthese_revendication_2.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .select('#ddlCampagne','')
      .click('#btn_Excel')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
  })
  .then(function() {
      var uri = baseUri+"/Declaration/LstDecla.aspx?declaId=9";
      var exportFilename = destination_file+'declaration_changement_denomination_negociant_igp_non_geree.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .select('#ddlCampagne','')
      .click('#btnExcel')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
  })
  .then(function() {
      var uri = baseUri+"/Declaration/SyntOpDecla.aspx?declaId=9";
      var exportFilename = destination_file+'synthese_changement_denomination_negociant_igp_non_geree.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .select('#ddlCampagne','')
      .click('#btn_Excel')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
  })
  .then(function() {
      var uri = baseUri+"/Declaration/LstDecla.aspx?declaId=10";
      var exportFilename = destination_file+'declaration_changement_denomination_autre_igp.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .select('#ddlCampagne','')
      .click('#btnExcel')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
  })
  .then(function() {
      var uri = baseUri+"/Declaration/SyntOpDecla.aspx?declaId=10";
      var exportFilename = destination_file+'synthese_changement_denomination_autre_igp.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .select('#ddlCampagne','')
      .click('#btn_Excel')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
  })
  .then(function() {
      var uri = baseUri+"/Declaration/LstDecla.aspx?declaId=11";
      var exportFilename = destination_file+'declaration_changement_denomination_negociant_med.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .select('#ddlCampagne','')
      .click('#btnExcel')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
  })
  .then(function() {
      var uri = baseUri+"/Declaration/SyntOpDecla.aspx?declaId=11";
      var exportFilename = destination_file+'synthese_changement_denomination_negociant_medp.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .select('#ddlCampagne','')
      .click('#btn_Excel')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
  })
  .then(function() {
      var uri = baseUri+"/Declaration/LstLotRecolte.aspx";

      for(var i = 2016; i <= 2020; i++) {
          var exportFilename = destination_file+'details_recoltes_'+i+'.xlsx';
          console.log("export " + uri + ": " + exportFilename);
          nightmare
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
      var uri = baseUri+"/Analyse/ListeProdNC.aspx";

      return nightmare
      .goto(uri)
      .wait('#ddlCommission')
      .evaluate(function() {
        var ids = [];
        document.querySelectorAll('#ddlCommission option').forEach(
          function(option) {
            if(!option.value) {
              return;
            }
            ids.push(option.value.replace(/ .*$/, ''));
          }
        )
        return ids;
      })
      .then(function(ids) {
        for (key in ids) {
          var id = ids[key];
          var uri = baseUri+"/commission/VisuCommission.aspx?IdCommission="+id;
          var exportFilename = destination_file + "commissions/commission_"+id+".html";
          console.log("export " + uri + ": " + exportFilename);

          nightmare
                .goto(uri)
                .wait('body')
                .html(exportFilename, "HTMLOnly")
                .screenshot(exportFilename+".png")
                .refresh()
                .wait(1000)
        }
      });
  })
  .then(function() {
      var uri = baseUri+"/Analyse/ListeProdNC.aspx";
      var exportFilename = destination_file+'gestion_nc.xlsx';
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
/*  .then(function() {
      var uri = baseUri+"/commission/JuresConv.aspx";
       nightmare
        .goto(uri)
        .wait('body')
        .exists("#ddlCampagne")
        .then(function (result) {
            if (!result) {
                return nightmare;
            }

            for(var i = 2016; i <= 2020; i++) {
               var exportFilename = destination_file+'jures_convoque_'+i+'.xlsx';
               console.log("export " + uri + ": " + exportFilename);

               nightmare
               .goto(uri+"?campagne="+i)
               .wait(2000)
               .select('#ddlCampagne',i+"")
               .click('#btnResearch')
               .wait(3000)
               .click('#btnExportExcel')
               .download(exportFilename)
               .screenshot(exportFilename+".png")
               .refresh()
               .wait(2000)
            }
        });

       return nightmare;
  })*/
  .then(function() {
      var uri = baseUri+"/Facture/SuiviReglement.aspx";
      var exportFilename = destination_file+'reglements_remises.pdf';
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
      .wait(20000)
      .download(exportFilename)
      .screenshot(exportFilename+".png")
      .catch(error => {
        console.error('Search failed:', error)
      })
  })
  .then(function() {
      var uri = baseUri+"/commission/DefraiementJures.aspx";
       nightmare
        .goto(uri)
        .wait('body')
        .exists("#ddlCampagne")
        .then(function (result) {
            if (!result) {
                return nightmare;
            }

            for(var i = 2016; i <= 2020; i++) {
               var exportFilename = destination_file+'defraiement_jures'+i+'.xlsx';
               console.log("export " + uri + ": " + exportFilename);

               nightmare
               .goto(uri+"?campagne="+i)
               .select('#ddlCampagne',i+"")
               .wait(3000)
               .click('#btnExportExcel')
               .download(exportFilename)
               .screenshot(exportFilename+".png")
               .refresh()
               .wait(2000)
            }
        });

       return nightmare;
  })
  .then(function() {
      var uri = baseUri+"/Facture/LstFacture.aspx";
      var exportFilename = destination_file+'factures.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .wait('#ddlCampagne')
      .select('#ddlCampagne','')
      .wait(3000)
      .click('#BtnRech')
      .wait(6000)
      .click('#btnExport')
      .download(exportFilename)
      .screenshot(exportFilename+".png")
  })
  .then(function() {
      var uri = baseUri+"/Facture/LstFacture.aspx";
      var exportFilename = destination_file+'factures.pdf';
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
      .wait(20000)
      .download(exportFilename)
      .screenshot(exportFilename+".png")
      .catch(error => {
        console.error('Search failed:', error)
      })
  })
  .then(function() {
      var uri = baseUri+"/Facture/SuiviReglement.aspx";
      var exportFilename = destination_file+'reglements_factures.pdf';
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
      .wait(20000)
      .download(exportFilename)
      .screenshot(exportFilename+".png")
      .catch(error => {
        console.error('Search failed:', error)
      })
  })
  .then(function() {
      var uri = baseUri+"/Facture/SuiviReglement.aspx";
      var exportFilename = destination_file+'reglements.xlsx';
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
      var uri = baseUri+"/commission/LstMembre.aspx";
      var exportFilename = destination_file+'membres.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .wait('#Button1')
      .click('#Button1')
      .click('#Button2')
      .wait('#Button2')
      .download(destination_file+'membres.xlsx')
      .screenshot(exportFilename+".png")
  })
  .then(function() {
      var uri = baseUri+"/commission/LstNonMembre.aspx";
      var exportFilename = destination_file+'membres_inactifs.html';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .wait('#Button1')
      .click('#Button1')
      .wait(5000)
      .html(exportFilename, "HTMLOnly")
      .screenshot(exportFilename+".png")
  })
  .then(function() {
       var uri = baseUri+"/odg/FicheODG.aspx";
       var exportFilename = destination_file+'fiche_odg.html';
       console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .html(exportFilename, "HTMLOnly")
      .screenshot(exportFilename+".png")
  })
  .then(function() {
      var uri = baseUri+"/Administration/FicheContact.aspx";
       var exportFilename = destination_file+'operateurs.xlsx';
       console.log("export " + uri + ": " + exportFilename);

       return nightmare
       .goto(uri)
       .type('#ContentPlaceHolder1_tbNom', "' AND password != '' ORDER BY Nom --")
       .click('#ContentPlaceHolder1_btnRechercher')
       .wait(2000)
       .evaluate(function() { return document.querySelector('#ContentPlaceHolder1_NbLignes').innerHTML.replace(/[^0-9]*([0-9]+)[^0-9]*/, '$1'); })
       .then(function(total) {
          console.log("Export des " + total + " fiches ayant des identifiants de connexion");
          for(let i=0; i < total; i++) {
            var exportFilename = destination_file+'fichescontacts/contact_'+i+'html';
            nightmare
            .goto(uri+"?i="+i)
            .type('#ContentPlaceHolder1_tbNom', "' AND password != '' ORDER BY Nom OFFSET "+i+" ROWS FETCH NEXT 1 ROWS ONLY --")
            .click('#ContentPlaceHolder1_btnRechercher')
            .wait(1500)
            .click('#ContentPlaceHolder1_gvPersonne_btnModifier_0')
            .wait(1500)
            .goto(baseUri+"/Administration/FichePersonnel.aspx?TP=1")
            .wait(1500)
            .html(exportFilename, "HTMLOnly")
          }

          return nightmare;
       })
   })
  .then(function() {
       var uri = baseUri+"/odg/LstAOC.aspx";
       var exportFilename = destination_file+'aoc.html';
       console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .html(exportFilename, "HTMLOnly")
      .screenshot(exportFilename+".png")
  })
  .then(function() {
       var uri = baseUri+"/odg/LstCepage.aspx";
       var exportFilename = destination_file+'cepages.html';
       console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .html(exportFilename, "HTMLOnly")
      .screenshot(exportFilename+".png")
  })
  .then(function() {
      return nightmare.end()
  })


