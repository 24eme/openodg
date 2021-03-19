var configFile = process.argv.slice(2)[0];
var Nightmare = require('nightmare');
require('./nightmare-inline-download.js')(Nightmare);
var fs = require('fs');
var mkdirp = require("mkdirp");
const path = require('path');
var nightmare = Nightmare({ show: true, timeoutDownloadBeforeStart: 6000, maxDownloadRequestWait: 8000});
var config = require('./'+configFile);
var destination_file='imports/'+config.file_name+'/';
var baseUri = config.web_site_produits.replace("/odg/LstAOC.aspx", "");

nightmare

  //authentification
  .goto(config.web_site)
  .type('#LoginPhp',config.user_name)
  .type('#PasswordPhp',config.user_password)
  .click('#identification')
  .wait('.menu')
  //fin authentification
  .then(function() {
      var uri = baseUri+"/operateur/ListeOperateur.aspx";
      var exportFilename = destination_file+'operateurs.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .wait('#Button1')
      .click('#Button1')
      .wait('#Button2')
      .click('#Button2')
      .download(exportFilename)

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
  })
  .then(function() {
      var uri = baseUri+"/operateur/Adresses.aspx";
      var exportFilename = destination_file+'addresses_courrier_operateurs.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
        .goto(uri)
        .wait('#Button2')
        .click('#Button2')
        .download(exportFilename);
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
                  .download(exportFilename);
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
      .download(exportFilename);
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
  })
  .then(function() {
      var uri = baseUri+"/Declaration/LstLots.aspx";
      var exportFilename = destination_file+'lots.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
       .goto(uri)
       .wait("#ddlCamp")
       .select('#ddlCamp','')
       .click('#btnRech')
       .wait(4000)
       .click('#btnEE')
       .wait(4000)
       .download(exportFilename)
       .refresh()
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
  })
  .then(function() {
      var uri = baseUri+"/Declaration/LstDecla.aspx?declaId=10";
      var exportFilename = destination_file+'changement_denom_autre_igp.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .select('#ddlCampagne','')
      .click('#btnExcel')
      .download(exportFilename)
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
          var exportFilename = destination_file + "commission_"+id+".html";
          console.log("export " + uri + ": " + exportFilename);

          nightmare
                .goto(uri)
                .wait('body')
                .html(exportFilename, "HTMLOnly")
                .refresh()
                .wait(500)
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
      .catch(error => {console.error('Search failed:', error)});
  })
  .then(function() {
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
               .select('#ddlCampagne',i+"")
               .wait(3000)
               .click('#btnExportExcel')
               .download(exportFilename)
               .refresh()
               .wait(2000)
            }
        });

       return nightmare;
  })
  .then(function() {
      var uri = baseUri+"/Facture/LstFacture.aspx";
      var exportFilename = destination_file+'gestion_factures.xlsx';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .wait('#ddlCampagne')
      .select('#ddlCampagne','')
      .wait("#BtnRech")
      .click('#BtnRech')
      .wait(2000)
      .wait('#btnExport')
      .click('#btnExport')
      .download(exportFilename)
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
  })
  .then(function() {
      var uri = baseUri+"/commission/LstNonMembre.aspx";
      var exportFilename = destination_file+'membres_inactifs.html';
      console.log("export " + uri + ": " + exportFilename);

      return nightmare
      .goto(uri)
      .wait('#Button1')
      .click('#Button1')
      .wait('#gvMembre')
      .html(exportFilename, "MHTML")
      .catch(error => {console.error('Search failed:', error)});
  })
  .then(function() {
       var uri = baseUri+"/odg/LstAOC.aspx";
       var exportFilename = destination_file+'cepages.html';
       console.log("export " + uri + ": " + exportFilename);

       return nightmare
      .goto(uri)
      .wait('body')
      .exists("#btnCepage")
      .then(function (result) {
          if (result) {
              nightmare
              .click('#btnCepage')
              .wait('#ContentPlaceHolder1_gvCepage')
              .html(exportFilename, "MHTML");
          }
      });
  })
  .then(function() {
      return nightmare.end()
  })



