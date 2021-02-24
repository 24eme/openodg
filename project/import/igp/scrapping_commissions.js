var configFile = process.argv.slice(2)[0];
var Nightmare = require('nightmare');
var fs = require('fs');
const path = require('path');
const nightmare = Nightmare({ show: true, webPreferences: {
    preload: path.resolve("pre.js")
  }});
var config = require('./'+configFile);
var destination_file='imports/'+config.file_name+'/';

nightmare

    //authentification
    .goto(config.web_site)
    .type('#LoginPhp',config.user_name)
    .type('#PasswordPhp',config.user_password)
    .click('#identification')
    .wait('.menu')
    //fin authentification

    //Page de suivi des commissions
    .click('#ssmenu44 a:first-child')
    .wait('#Button1')
    .select('#ddlAnnee', '')
    .click('#Button1')
    .wait('#gvCommission1')
    .html(destination_file + "commissions_prevues.html", "HTMLOnly")
    .click('input#BntTermine')
    .wait('#gvCommission')
    .click('#BntTermine')
    .html(destination_file + "commissions_terminees.html", "HTMLOnly")

    for (var i = 1; i < 200; i++) {
        nightmare
            .goto(config.web_site_produits.replace("/odg/LstAOC.aspx", "")+"/commission/VisuCommission.aspx?IdCommission="+i)
            .wait('body')
            .html(destination_file + "commission_"+i+".html", "HTMLOnly");
    }

nightmare
    .end()
    .catch(error => {
      console.error('Search failed:', error)
  });
