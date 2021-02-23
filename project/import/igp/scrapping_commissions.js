var configFile = process.argv.slice(2)[0];
var Nightmare = require('nightmare');
var fs = require('fs');
const nightmare = Nightmare({ show: true});
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

    for (var i = 35; i < 999; i++) {
        nightmare
            .on('page', function(type="alert", message) {  })
            .goto("commission/VisuCommission.aspx?IdCommission="+i)
            .wait('#gvPrelev')
            .html(destination_file + "commission_"+i+".html", "HTMLOnly");
        console.log(i);
    }

nightmare
    .end()
    .catch(error => {
      console.error('Search failed:', error)
  });
