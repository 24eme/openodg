#!/usr/bin/env python
# coding: utf-8

# In[ ]:


import pandas as pd
import sys
from datetime import datetime
import dateutil.relativedelta

pd.set_option('display.max_columns', None)

if(len(sys.argv)<2):
    print ("DONNER EN PARAMETRE DU SCRIPT LE NOM DE L'IGP")
    exit()
dossier_igp = "exports_"+sys.argv[1]
igp = sys.argv[1].replace('igp',"")

if(len(sys.argv)>2):
    millesime = sys.argv[2]
else:
    today= datetime.now()
    debutcampagne = today - dateutil.relativedelta.relativedelta(months=10)
    millesime = str(debutcampagne.year)

if(len(sys.argv)>3):
    datemax = sys.argv[3]
else:
    datemax = str(int(millesime)+1)+'-08-01'

#dossier_igp = "exports_igpgascogne"
#igp = "gascogne"
#datemax = "2022-01-01"
#millesime = "2019"

drev_lots = pd.read_csv("../../web/"+dossier_igp+"/drev_lots.csv", encoding="iso8859_15", delimiter=";", decimal=",", dtype={'Identifiant': 'str', 'Campagne': 'str', 'Siret Opérateur': 'str', 'Code postal Opérateur': 'str', 'Millésime':'str'}, low_memory=False)
lots = pd.read_csv("../../web/"+dossier_igp+"/lots.csv", encoding="iso8859_15", delimiter=";", decimal=",", dtype={'Campagne': 'str', 'Millésime':'str'}, index_col=False, low_memory=False)
changement_deno = pd.read_csv("../../web/"+dossier_igp+"/changement_denomination.csv", encoding="iso8859_15", delimiter=";", decimal=",", dtype={'Campagne': 'str', 'Millésime':'str','Origine Millésime':'str'}, index_col=False, low_memory=False)

#lots = lots[(lots['Origine'] == "DRev") | (lots['Origine'] == "DRev:Changé") ]
drev_lots = drev_lots[drev_lots["Type"] == "DRev"]
changement_deno = changement_deno[(changement_deno["Type"] == "DRev") | (changement_deno["Type"] == "DRev:Changé") ]
changement_deno = changement_deno[changement_deno["Date de validation ODG"] < datemax]

degustations = pd.read_csv("../../web/"+dossier_igp+"/degustations.csv", encoding="iso8859_15", delimiter=";", decimal=",", dtype={'Identifiant': 'str', 'Campagne': 'str', 'Siret Opérateur': 'str', 'Code postal Opérateur': 'str'}, low_memory=False)


# In[ ]:


drev_lots = drev_lots.rename(columns = {'Date lot': 'Date_lot'})

drev_lots['Millesime'] = millesime
drev_lots = drev_lots.query("Millésime == @millesime")
drev_lots = drev_lots.query("Date_lot < @datemax")
drev_lots['Lieu'] = drev_lots['Lieu'].fillna('')
drev_lots = drev_lots.groupby(['Appellation','Couleur','Lieu','Produit'])[["Volume"]].sum()
drev_lots ['Type'] = "VOLUME REVENDIQUE"
drev_lots = drev_lots.reset_index()
final = drev_lots


# In[ ]:



degustations = degustations.query("Millésime == @millesime")
degustations = degustations[degustations["Date"] < datemax]

conforme = "Conforme"
rep_conforme = "Réputé conforme"
#en_recours="En recours OC"

degustations = degustations.rename(columns = {'Statut de lot': 'Statut_de_lot'})
degustations = degustations.query("Statut_de_lot != @conforme & Statut_de_lot != @rep_conforme");
# & Statut_de_lot != @en_recours

degustations['Lieu'] = degustations['Lieu'].fillna('')
degustations = degustations.groupby(['Appellation','Couleur','Lieu','Produit'])[['Volume']].sum()
degustations['Type'] = "VOLUME EN INSTANCE DE CONFORMITE"
degustations = degustations.reset_index()
final = final.append(degustations,sort= True)


# In[ ]:


changement_denomination = changement_deno
changement_denomination['Origine Lieu'] = changement_denomination['Origine Lieu'].fillna('')
changement_denomination['Lieu'] = changement_denomination['Lieu'].fillna('')
changement_denomination = changement_denomination.rename(columns = {'Origine Millésime': 'Origine_Millésime','Type de changement':'Type_de_changement'})
changement_denomination = changement_denomination.query("Origine_Millésime == @millesime")


# In[ ]:


type_de_changement = "DECLASSEMENT"
changement_denomination_declassement = changement_denomination.query("Type_de_changement == @type_de_changement")
changement_denomination_declassement = changement_denomination_declassement.groupby(['Origine Appellation','Origine Couleur','Origine Lieu','Origine Produit'])[["Volume changé"]].sum()
changement_denomination_declassement  = changement_denomination_declassement.reset_index()
changement_denomination_declassement['Type']= 'DECLASSEMENT'
changement_denomination_declassement = changement_denomination_declassement.rename(columns = {'Origine Appellation': 'Appellation','Origine Couleur':'Couleur','Origine Lieu':'Lieu','Volume changé':'Volume','Origine Produit':'Produit'})
final = final.append(changement_denomination_declassement,sort= True)


# In[ ]:


type_de_changement = "CHANGEMENT"
changement_denomination = changement_denomination.query("Type_de_changement == @type_de_changement")
changement_denomination = changement_denomination.groupby(['Origine Appellation','Origine Couleur','Origine Lieu','Origine Produit','Appellation','Couleur','Lieu','Produit'])[["Volume changé"]].sum()
changement_denomination = changement_denomination.reset_index()
changement_denomination['Type'] = "CHANGEMENT DENOMINATION SRC = PRODUIT"
changement_denomination = changement_denomination.rename(columns = {'Origine Appellation': 'Appellation','Origine Couleur':'Couleur','Origine Lieu':'Lieu','Volume changé':'Volume','Origine Produit':'Produit','Appellation':'Nv Appellation','Couleur':'Nv Couleur','Lieu':'NV Lieu','Produit':'Nv Produit'})

if(changement_denomination.empty):
    changement_denomination['Libelle'] = ""
else:
    changement_denomination['Libelle'] = changement_denomination['Produit']+' en '+changement_denomination['Nv Produit']

changement_denomination = changement_denomination[['Appellation','Couleur','Lieu','Volume','Type','Libelle','Produit']]
final = final.append(changement_denomination,sort= True)


# In[ ]:


changement_deno['Origine Lieu'] = changement_deno['Origine Lieu'].fillna('')
changement_deno['Lieu'] = changement_deno['Lieu'].fillna('')
changement_deno = changement_deno.rename(columns = {'Origine Millésime': 'Origine_Millésime','Type de changement':'Type_de_changement'})
changement_deno = changement_deno.query("Origine_Millésime == @millesime")

type_de_changement = "CHANGEMENT"

changement_deno = changement_deno.query("Type_de_changement == @type_de_changement")
changement_deno = changement_deno.groupby(['Appellation','Couleur','Lieu','Produit','Origine Produit','Origine Appellation','Origine Couleur','Origine Lieu'])[["Volume changé"]].sum()
changement_deno = changement_deno.reset_index()

changement_deno['Type'] = "CHANGEMENT DENOMINATION DEST = PRODUIT"

if(changement_deno.empty):
    changement_deno['Libelle'] = ""
else:
    changement_deno['Libelle'] = changement_deno['Origine Produit']+' en '+changement_deno['Produit']

changement_deno = changement_deno.rename(columns = {'Volume changé':'Volume'})
changement_deno = changement_deno[['Appellation','Couleur','Lieu','Volume','Type','Libelle','Produit']]

final = final.append(changement_deno,sort= True)


# In[ ]:


final['Millesime'] = millesime
final = final[['Millesime','Appellation','Couleur','Lieu','Produit','Type','Libelle','Volume']]
final = final.sort_values(by=['Appellation','Couleur','Lieu'])

#tableau récapitulatif
type_vol_revendique = "VOLUME REVENDIQUE"
type_instance_conformite = "VOLUME EN INSTANCE DE CONFORMITE"
type_changement_deno_src_produit = "CHANGEMENT DENOMINATION SRC = PRODUIT"
type_changement_deno_dest_produit = "CHANGEMENT DENOMINATION DEST = PRODUIT"
type_declassement = "DECLASSEMENT"


tab_cal = final.groupby(['Appellation','Lieu','Couleur','Produit'])[["Volume"]].sum()

tab_cal['type_vol_revendique'] = final.query("Type == @type_vol_revendique").groupby(['Appellation','Lieu','Couleur','Produit'])[["Volume"]].sum()
tab_cal['type_instance_conformite'] = final.query("Type == @type_instance_conformite").groupby(['Appellation','Lieu','Couleur','Produit'])[["Volume"]].sum()
tab_cal['type_changement_deno_src_produit'] = final.query("Type == @type_changement_deno_src_produit").groupby(['Appellation','Lieu','Couleur','Produit'])[["Volume"]].sum()
tab_cal['type_changement_deno_dest_produit'] = final.query("Type == @type_changement_deno_dest_produit").groupby(['Appellation','Lieu','Couleur','Produit'])[["Volume"]].sum()
tab_cal['type_declassement'] =  final.query("Type == @type_declassement").groupby(['Appellation','Lieu','Couleur','Produit'])[["Volume"]].sum()

tab_cal = tab_cal.fillna(0)

tab_cal['A'] = tab_cal['type_vol_revendique'] - tab_cal['type_instance_conformite']
tab_cal ['B'] = (tab_cal['type_changement_deno_dest_produit'] - tab_cal['type_changement_deno_src_produit'] - tab_cal['type_declassement']) * (-1)
tab_cal['A-B'] =  tab_cal['A'] - tab_cal ['B']
tab_cal = tab_cal.reset_index(level=['Appellation','Lieu','Couleur','Produit'])

tab_cal = tab_cal[['Appellation','Couleur','Lieu','Produit','type_vol_revendique','type_instance_conformite','type_changement_deno_dest_produit','type_changement_deno_src_produit','type_declassement','A','B','A-B']]


# In[ ]:


final.reset_index(drop=True).to_csv('../../web/'+dossier_igp+'/stats/stats_bilan_millesime_'+millesime+'_au_'+datemax+'.csv', encoding="iso8859_15", sep=";",index=False,  decimal=",")


# In[ ]:


tab_cal.reset_index(drop=True).to_csv('../../web/'+dossier_igp+'/stats/stats_bilan_millesime_'+millesime+'_au_'+datemax+'_A_B_A-B.csv', encoding="iso8859_15", sep=";",index=False,  decimal=",")
