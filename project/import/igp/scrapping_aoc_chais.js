const puppeteer = require('puppeteer');
const fs = require('fs');

if(!process.env.URLSITE){
  throw "Initialisez la variable d'environnement URLSITE";
}

const baseURL = process.env.URLSITE;
exports.baseURL = baseURL;
var browser;

(async () => {
  try {
  if (!process.env.DEBUG && (process.env.DEBUG_WITH_BROWSER != undefined)) {
    process.env.DEBUG = 1;
  }

  browser = await puppeteer.launch(
    {
      headless: !(process.env.DEBUG_WITH_BROWSER),  //mettre à false pour debug
      defaultViewport: {width: 1920, height: 1080},
      ignoreDefaultArgs: ['--disable-extensions'],
      args: ['--no-sandbox', '--disable-setuid-sandbox'],
    }
    );

    if(!process.env.USER){
      await browser.close();
      throw "Initialisez la variable d'environnement USER avec le login";
    }

    if(!process.env.PASSWORD){
      await browser.close();
      throw "Initialisez la variable d'environnement PASSWORD avec le mot de passe";
    }
    if(!process.env.DOSSIER){
      await browser.close();
      throw "Initialisez la variable d'environnement DOSSIER avec le nom du dossier dans imports";
    }
    if(process.env.DEBUG){
      console.log("===================");
    }
    const page = await browser.newPage();

    await page.goto(baseURL);

    await page.click('#TextBox1');
    await page.waitForSelector('#TextBox1');

    if(process.env.DEBUG){
      console.log("Login page: OK");
      console.log("===================");
    }

    await page.type('#TextBox1', process.env.USER);
    await page.type('#TextBox2', process.env.PASSWORD);

    await page.click('#Button2');

    if(process.env.DEBUG){
      console.log("CONNEXION: OK");
      console.log("===================");
    }

    await page.goto(baseURL+"/Tournee/ParamZonePrelev.aspx");

    if(process.env.DEBUG){
      console.log("PAGE A SCRAPPER: OK");
      console.log("===================");
    }

    await page.click('#btnAjout');
    await page.waitForSelector('#btnAjout');

    if(process.env.DEBUG){
      console.log("CLICK AJOUTER DES SITES : OK");
      console.log("===================");
    }

    const odg_array = await page.evaluate(() =>
      Array.from(document.querySelectorAll('#ddlODG option')).map(element=>element.value)
    );

    for(var odg of odg_array){
      if(process.env.DEBUG){
        console.log("ODG : "+ odg+"\n");
      }

      await page.select('select#ddlODG', odg);
      await page.click('#btnRechercher');

      if(process.env.DEBUG){
        console.log("CLICK SUR RECHERCHER : OK");
        console.log("===================");
      }

      await page.waitForSelector("#gvSiteStockAdd");

      fs.writeFileSync(process.env.DOSSIER+"/07_chais/"+odg+".html",await page.content());

      if(process.env.DEBUG){
        console.log("Enregistre la page HTML des stock de l'opérateur OK");
        console.log("===================");
      }
    }
    if(process.env.DEBUG){
      console.log("FIN");
      console.log("===================");
    }

    await browser.close();

}catch (e) {
    console.log("");
    console.log('FAILED !!');
    console.log(e);
    await browser.close();
    process.exit(255);
  }
})();
