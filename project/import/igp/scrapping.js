
var Nightmare = require('nightmare');
require('nightmare-inline-download')(Nightmare);
var fs = require('fs');
var mkdirp = require("mkdirp");
const nightmare = Nightmare({ show: true})
var config = require('./config.json');
var destination_file='imports/'+config.file_name+'/';

mkdirp('imports/'+config.file_name);

nightmare

  //authentification
  .goto(config.web_site)
  .type('#LoginPhp',config.user_name)
  .type('#PasswordPhp',config.user_password)
  .click('#identification')
  .wait('.menu')
  //fin authentification


  //operateurs
  .evaluate(()=>{
    var elements = Array.from(document.querySelectorAll('a'))
    elements[4].className += "ordre_alphabetiques";
  })
  .click('.ordre_alphabetiques')
  .wait('#Button1')
  .click('#Button1')
  .click('#Button2')
  .download(destination_file+'operateurs.xlsx')
  .refresh()
  // fin operateurs


  //apporteurs de raisins
  .evaluate(()=>{
    var elements = Array.from(document.querySelectorAll('a'))
    elements[6].className += "apporteurs_de_raisins";
  })
  .click('.apporteurs_de_raisins')
  .wait('#Button2')
  .click('#Button2')
  .download(destination_file+'apporteurs_de_raisins.xlsx')
  .refresh()

  //fin apporteurs de raisins



  //addresses courrier
  .evaluate(()=>{
    var elements = Array.from(document.querySelectorAll('a'))
    elements[7].className += "addresses_courrier_operateurs";
  })
  .click('.addresses_courrier_operateurs')
  .wait('#Button2')
  .click('#Button2')
  .download(destination_file+'addresses_courrier_operateurs.xlsx')
  .refresh()
  //fin apporteurs de raisins

  //operateurs innactifs
  .evaluate(()=>{
    var elements = Array.from(document.querySelectorAll('a'))
    elements[9].className += "operateurs_innactifs";
  })
  .click('.operateurs_innactifs')
  .wait('#btnExportExcel')
  .click('#btnExportExcel')
  .download(destination_file+'operateurs_innactifs.xlsx')
  .refresh()
  //fin operateurs innactifs

  //contacts
  .evaluate(()=>{
    var elements = Array.from(document.querySelectorAll('a'))
    elements[12].className += "list_contacts";
  })
  .click('.list_contacts')
  .wait('#ContentPlaceHolder1_btnExcel')
  .click('#ContentPlaceHolder1_btnExcel')
  .download(destination_file+'contacts.xlsx')
  .refresh()
  // fin contacts


  //historique DI
  .evaluate(()=>{
    var elements = Array.from(document.querySelectorAll('a'))
    elements[17].className += "historique_DI";
  })
  .click('.historique_DI')
  .wait('#btnExcel')
  .click('#btnExcel')
  .download(destination_file+'historique_DI.xlsx')
  .refresh()

  //fin historique DI

  //habilitation
  .evaluate(()=>{
    var elements = Array.from(document.querySelectorAll('a'))
    elements[19].className += "habilitation";
  })
  .click('.habilitation')
  .wait('#btExportExcel')
  .click('#btExportExcel')
  .download(destination_file+'habilitations.xlsx')
  .refresh()
  //fin habilitation

  //scraping lots
  .evaluate(()=>{
    var elements = Array.from(document.querySelectorAll('a'))
    elements[63].className += "lots_declarations";
    })
   .click('.lots_declarations')
   .wait('#btnEE')
   .click('#btnEE')
   .download(destination_file+'lots_2020-2021.xlsx')
   .refresh()

   .select('#ddlCamp','')
   .click('#btnEE')
   .download(destination_file+'lots.xlsx')
   .refresh()

   .select('#ddlCamp','2019/2020')
   .click('#btnEE')
   .download(destination_file+'lots_2019-2020.xlsx')
   .refresh()

   .select('#ddlCamp','2018/2019')
   .click('#btnEE')
   .download(destination_file+'lots_2018-2019.xlsx')
   .refresh()

   .select('#ddlCamp','2017/2018')
   .click('#btnEE')
   .download(destination_file+'lots_2017-2018.xlsx')
   .refresh()

   .select('#ddlCamp','2016/2017')
   .click('#btnEE')
   .download(destination_file+'lots_2016-2017.xlsx')
   .refresh()
  //fin lots

  //changement de dénomination
  .evaluate(()=>{
    var elements = Array.from(document.querySelectorAll('a'))
    elements[56].className += "changement_denom";
    })
  .click('.changement_denom')
  .click('#Button1')
  .download(destination_file+'changement_denom_2020-2021.xlsx')

  .select('#ddlCampagne','')
  .click('#Button1')
  .download(destination_file+'changement_denom.xlsx')

   .select('#ddlCampagne','2019')
   .click('#Button1')
   .download(destination_file+'changement_denom_2019-2020.xlsx')

   .select('#ddlCampagne','2018')
   .click('#Button1')
   .download(destination_file+'changement_denom_2018-2019.xlsx')

   .select('#ddlCampagne','2017')
   .click('#Button1')
   .download(destination_file+'changement_denom_2017-2018.xlsx')

   .select('#ddlCampagne','2016')
   .click('#Button1')
   .download(destination_file+'changement_denom_2016-2017.xlsx')
   //fin changement de dénomination

   //changement denom autre igp
   .evaluate(()=>{
     var elements = Array.from(document.querySelectorAll('a'))
     elements[57].className += "changement_denom_autre_igp";
     })
    .click('.changement_denom_autre_igp')
    .click('#btnExcel')
    .download(destination_file+'changement_denom_autre_igp_2020-2021.xlsx')

    .select('#ddlCampagne','')
    .click('#btnExcel')
    .download(destination_file+'changement_denom_autre_igp.xlsx')

     .select('#ddlCampagne','2019')
     .click('#btnExcel')
     .download(destination_file+'changement_denom_autre_igp_2019-2020.xlsx')

     .select('#ddlCampagne','2018')
     .click('#btnExcel')
     .download(destination_file+'changement_denom_autre_igp_2018-2019.xlsx')

     .select('#ddlCampagne','2017')
     .click('#btnExcel')
     .download(destination_file+'changement_denom_autre_igp_2017-2018.xlsx')

     .select('#ddlCampagne','2016')
     .click('#btnExcel')
     .download(destination_file+'changement_denom_autre_igp_2016-2017.xlsx')
     //fin changement denom autre igp


     //details récoltes
     .evaluate(()=>{
       var elements = Array.from(document.querySelectorAll('a'))
       elements[33].className += "details_recoltes";
     })
       .click('.details_recoltes')
       .wait('#Button1')
       .click('#Button1')
       .wait('#btnExport')
       .click('#btnExport')
       .download(destination_file+'details_recoltes_2020.xlsx')
      .refresh()

      .wait('#ddlAnnee')
      .select('#ddlAnnee','2019')
      .wait('#Button1')
      .click('#Button1')
      .wait("#btnExport")
      .click("#btnExport")
      .download(destination_file+'details_recoltes_2019.xlsx')
      .refresh()


      .select('#ddlAnnee','2018')
      .click('#Button1')
      .click('#btnExport')
      .download(destination_file+'details_recoltes_2018.xlsx')
      .refresh()

    .select('#ddlAnnee','2017')
       .click('#Button1')
       .click('#btnExport')
       .download(destination_file+'details_recoltes_2017.xlsx')
      .refresh()

      .select('#ddlAnnee','2016')
      .click('#Button1')
      .click('#btnExport')
      .download(destination_file+'details_recoltes_2016.xlsx')
      .refresh()

     //fin détail récoltes



     //produits
     .evaluate(()=>{
       var elements = Array.from(document.querySelectorAll('a'))
       elements[117].className += "produits";
     })
      .click('.produits')   //pour donner accès au lien sinon site en maintenance
      .goto(config.web_site_produits)
      .click('#btnAOC')
      .evaluate(()=>{
        var elements = document.querySelector('#ContentPlaceHolder1_GridView1').innerText
        return elements;
        })
     .end()
      .then((text) => {
        fs.writeFileSync(destination_file+'produits.txt',text);
      })
     //fin produits

  .catch(error => {
    console.error('Search failed:', error)
  })
