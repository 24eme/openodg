#!/usr/bin/env python
# coding: utf-8

# In[ ]:


import pandas as pd
import sys
import os
from datetime import datetime
import dateutil.relativedelta

pd.set_option('display.max_columns', None)

dossier_igp = "exports_"+sys.argv[1]
igp = sys.argv[1].replace('igp',"")

if(len(sys.argv)<2):
    print ("DONNER EN PARAMETRE DU SCRIPT LE NOM DE L'IGP")
    exit()

millesime = str((datetime.now() - dateutil.relativedelta.relativedelta(months=10)).year)
    
if(len(sys.argv)>2):
    millesime = sys.argv[2]
    
exportdir = '../../web/'+dossier_igp
outputdir = exportdir+'/stats/'+millesime

if(not os.path.isdir(outputdir)):
    os.mkdir(outputdir)
    
datelimite = str(datetime.now().year)+'-08-01'
datelimite_exact = str(datetime.now().year)+'-07-31'
    
if(datetime.now().month >= 10):
    datelimite = str(datetime.now().year + 1)+'-01-01'
    datelimite_exact = str(datetime.now().year)+'-12-31'
    
if(datetime.now().month <= 3):
    datelimite = str(datetime.now().year )+'-01-01'
    datelimite_exact = str(datetime.now().year - 1)+'-12-31'

    """
dossier_igp = "exports_igpgascogne"
igp = 'gascogne'
campagne ="2020-2021"
datelimite = '2021-08-01'
"""
drev_lots = pd.read_csv("../../web/"+dossier_igp+"/drev_lots.csv", encoding="iso8859_15", delimiter=";", decimal=",", dtype={'Identifiant': 'str', 'Campagne': 'str', 'Siret Opérateur': 'str', 'Code postal Opérateur': 'str'}, low_memory=False)
drev_lots = drev_lots[drev_lots["Date lot"] < datelimite]


# In[ ]:


etablissements = pd.read_csv(exportdir+"/etablissements.csv", encoding="iso8859_15", delimiter=";", decimal=",", dtype={'Login': 'str', 'Identifiant etablissement': 'str'}, index_col=False, low_memory=False)
societe = pd.read_csv(exportdir+"/societe.csv", encoding="iso8859_15", delimiter=";", decimal=",", dtype={'Identifiant': 'str', 'Téléphone' :'str', 'Téléphone portable': 'str'}, index_col=False, low_memory=False)
lots = pd.read_csv(exportdir+"/lots.csv", encoding="iso8859_15", delimiter=";", decimal=",", index_col=False, low_memory=False)

changement_denomination = pd.read_csv(exportdir+"/changement_denomination.csv", encoding="iso8859_15", delimiter=";", decimal=",", dtype={'Campagne': 'str', 'Millésime':'str','Origine Millésime':'str'}, index_col=False, low_memory=False)
changement_denomination = changement_denomination[changement_denomination["Date de validation ODG"] < datelimite]


# In[ ]:


lots = lots[(lots['Origine'] == "DRev") | (lots['Origine'] == "DRev:Changé") ]
drev_lots = drev_lots[drev_lots["Type"] == "DRev"]
changement_denomination = changement_denomination[(changement_denomination["Type"] == "DRev") | (changement_denomination["Type"] == "DRev:Changé") ]


# In[ ]:


drev_lots = drev_lots.query("Millésime == @millesime");

drev_lots['Volume'] = drev_lots['Volume'].fillna(0)
drev_lots = drev_lots.fillna("") 

#drev_lots.loc[drev_lots.Volume ==  '', 'Volume'] = 0 

#VOLUME REVENDIQUE  
lignes_volume_revendique = drev_lots.groupby(['Identifiant','Appellation','Couleur','Produit','Lieu','Lot unique Id'])[["Volume"]].sum()
lignes_volume_revendique = lignes_volume_revendique.reset_index()             
lignes_volume_revendique = lignes_volume_revendique[['Identifiant','Appellation','Couleur','Produit','Lieu','Lot unique Id','Volume']]
lignes_volume_revendique['Type'] = "VOLUME REVENDIQUE"


drev_lots = drev_lots.groupby(['Identifiant','Appellation','Couleur','Produit','Lieu'])[["Volume"]].sum()
drev_lots = drev_lots.reset_index()             
drev_lots = drev_lots[['Identifiant','Appellation','Couleur','Produit','Lieu','Volume']]

final = lignes_volume_revendique


#VOLUME EN INSTANCE DE CONTROLE  

lots = lots.query("Millésime == @millesime");
lots = lots.fillna("")
lots_ini = lots

lots = lots[lots["Date lot"] < datelimite]  

lignes_volume_instance_controle = lots[(lignes_volume_instance_controle['Statut de lot'] != "Conforme") & (lignes_volume_instance_controle['Statut de lot'] != "Réputé conforme") & (lignes_volume_instance_controle['Statut de lot'] != "Conforme en appel") & (lignes_volume_instance_controle['Statut de lot'] != "En élevage")]
lignes_volume_instance_controle = lignes_volume_instance_controle.groupby(['Identifiant','Appellation','Couleur','Produit','Lieu','Lot unique Id'])[["Volume"]].sum()
lignes_volume_instance_controle = lignes_volume_instance_controle.reset_index()

lignes_volume_instance_controle= lignes_volume_instance_controle[['Identifiant','Appellation','Couleur','Produit','Volume','Lieu','Lot unique Id']]
lignes_volume_instance_controle['Type'] = "VOLUME EN INSTANCE DE CONTROLE"


final = final.append(lignes_volume_instance_controle,sort= True)    

#CHANGEMENT DE DENO & DECLASSEMENT   

changement_denomination =  changement_denomination.fillna("")    
changement_denomination = changement_denomination.query("Millésime == @millesime")
changement_denomination_initial = changement_denomination


#DECLASSEMENT

lignes_declassement = changement_denomination[changement_denomination['Type de changement'] == "DECLASSEMENT"]
lignes_declassement = lignes_declassement.fillna("")    
lignes_declassement = lignes_declassement.groupby(['Identifiant','Origine Appellation','Origine Couleur','Origine Produit','Origine Lieu','Origin Lot unique Id'])[["Volume changé"]].sum()
lignes_declassement = lignes_declassement.reset_index()  
lignes_declassement = lignes_declassement.rename(columns = {'Origine Appellation': 'Appellation','Origine Couleur':'Couleur','Origine Lieu':'Lieu','Origin Lot unique Id':'Lot unique Id','Volume changé':'Volume','Origine Produit':'Produit'})
lignes_declassement['Type']= 'DECLASSEMENT'
lignes_declassement = lignes_declassement[['Identifiant','Appellation','Couleur','Produit','Lieu','Lot unique Id','Volume','Type']]

final = final.append(lignes_declassement,sort= True)


#CHANGEMENT DENOMINATION SRC = PRODUIT

changement_deno = changement_denomination[changement_denomination['Type de changement'] == "CHANGEMENT"]

changement_deno = changement_deno.fillna("") 


changement_deno = changement_deno.groupby(['Identifiant','Origine Appellation','Origine Couleur','Origine Produit','Origine Lieu','Appellation','Couleur','Lieu','Produit','Origin Lot unique Id'])[["Volume changé"]].sum()
changement_deno  = changement_deno.reset_index()
changement_deno = changement_deno.rename(columns = {'Origine Appellation': 'Appellation','Origine Couleur':'Couleur','Origine Lieu':'Lieu','Origin Lot unique Id':'Lot unique Id','Volume changé':'Volume','Origine Produit':'Produit','Appellation':'Nv Appellation','Couleur':'Nv Couleur','Lieu':'NV Lieu','Produit':'Nv Produit'})

if(changement_deno.empty):
    changement_deno['Libelle'] = ""
else:
    changement_deno['Libelle'] = changement_deno['Produit'] +' en '+ changement_deno['Nv Produit']


changement_deno['Type']= 'CHANGEMENT DENOMINATION SRC = PRODUIT'
changement_deno = changement_deno[['Identifiant','Appellation','Couleur','Produit','Volume','Type','Libelle','Lieu','Lot unique Id']]



final = final.append(changement_deno,sort= True)

#CHANGEMENT DENOMINATION DEST = PRODUIT

changement_deno_dest = changement_denomination[changement_denomination['Type de changement'] == "CHANGEMENT"]
changement_deno_dest = changement_deno_dest.fillna("")    
changement_deno_dest = changement_deno_dest.groupby(['Identifiant','Origine Appellation','Origine Couleur','Origine Produit','Origine Lieu','Lot unique Id','Appellation','Couleur','Lieu','Produit'])[["Volume changé"]].sum()
changement_deno_dest = changement_deno_dest.reset_index()
changement_deno_dest = changement_deno_dest.rename(columns = {'Volume changé':'Volume'})

if(changement_deno_dest.empty):
    changement_deno_dest['Libelle']=""
else:
    changement_deno_dest['Libelle'] = changement_deno_dest['Origine Produit']+' en '+ changement_deno_dest['Produit']

changement_deno_dest['Type']= 'CHANGEMENT DENOMINATION DEST = PRODUIT'
changement_deno_dest = changement_deno_dest[['Identifiant','Appellation','Couleur','Produit','Volume','Type','Libelle','Lieu','Lot unique Id']]


final = final.append(changement_deno_dest,sort= True)

final['url']= "https://"+igp+".igp.vins.24eme.fr/historique/"+final['Identifiant']+'/'+final['Lot unique Id']


#CSV FINAL



#on mets en commun chaque volume d'un lot par opérateur en fonction du produit et de son type (volume revendique, en cours de controle ...) 
final = final.groupby(['Identifiant','Appellation','Couleur','Produit','Type','Lieu'])[["Volume"]].sum()
final = final.reset_index()  

final = final.sort_values(by=['Identifiant','Appellation','Couleur'])


final.reset_index(drop=True).to_csv(outputdir+'/'+datelimite_exact+'_'+millesime+'_igp_stats_droit_inao_operateurs_redevable.csv', encoding="iso8859_15", sep=";",index=False, decimal=",")


#tableau récapitulatif

type_vol_revendique = "VOLUME REVENDIQUE"
type_instance_controle = "VOLUME EN INSTANCE DE CONTROLE"
type_changement_deno_src_produit = "CHANGEMENT DENOMINATION SRC = PRODUIT"
type_changement_deno_dest_produit = "CHANGEMENT DENOMINATION DEST = PRODUIT"
type_declassement = "DECLASSEMENT"

tab_cal = final.groupby(['Identifiant','Appellation','Couleur','Produit','Lieu'])[["Volume"]].sum()



tab_cal['type_vol_revendique'] =  final.query("Type == @type_vol_revendique").groupby(['Identifiant','Appellation','Couleur','Produit','Lieu'])[["Volume"]].sum()      
tab_cal['type_instance_controle'] =  final.query("Type == @type_instance_controle").groupby(['Identifiant','Appellation','Couleur','Produit','Lieu'])[["Volume"]].sum()
tab_cal['type_changement_deno_src_produit'] =  final.query("Type == @type_changement_deno_src_produit").groupby(['Identifiant','Appellation','Couleur','Produit','Lieu'])[["Volume"]].sum()
tab_cal['type_changement_deno_dest_produit'] =  final.query("Type == @type_changement_deno_dest_produit").groupby(['Identifiant','Appellation','Couleur','Produit','Lieu'])[["Volume"]].sum()
tab_cal['type_declassement'] =  final.query("Type == @type_declassement").groupby(['Identifiant','Appellation','Couleur','Produit','Lieu'])[["Volume"]].sum()


#print(final.query("Type == @type_instance_controle").groupby(['Identifiant','Appellation','Couleur','Produit','Lieu'])[["Volume"]].sum())

tab_cal = tab_cal.fillna(0)

tab_cal['A'] = tab_cal['type_vol_revendique'] - tab_cal['type_instance_controle']
tab_cal ['B'] = tab_cal['type_changement_deno_dest_produit'] - tab_cal['type_changement_deno_src_produit'] - tab_cal['type_declassement']
tab_cal['A-B'] =  tab_cal['A'] + tab_cal ['B']
tab_cal = tab_cal.reset_index(level=['Identifiant','Appellation','Couleur','Produit','Lieu'])

tab_cal = tab_cal[['Identifiant','Appellation','Couleur','Produit','Lieu','type_vol_revendique','type_instance_controle','type_changement_deno_dest_produit','type_changement_deno_src_produit','type_declassement','A','B','A-B']]


tab_cal = pd.merge(tab_cal,drev_lots,how='left',left_on=["Identifiant",'Appellation','Couleur','Lieu','Produit'],right_on=["Identifiant",'Appellation','Couleur','Lieu','Produit'],suffixes=("", " info-operateur"))    
tab_cal = tab_cal[['Identifiant','Appellation','Couleur','Produit','Volume','Lieu','type_vol_revendique','type_instance_controle','type_changement_deno_dest_produit','type_changement_deno_src_produit','type_declassement','A','B','A-B']]

#print(tab_cal)

# Pour comparer A-B avec la somme des volumes de lots.csv

lots_init = lots_ini.groupby(['Id Opérateur','Appellation','Couleur','Produit','Lieu'])[["Volume"]].sum()
lots_init = lots_init.reset_index(level=['Id Opérateur','Appellation','Couleur','Produit','Lieu'])
lots_init = lots_init.rename(columns = {'Id Opérateur':'Identifiant','Volume':'Somme Volume lots.csv'})

tab_cal = pd.merge(tab_cal,lots_init,how='left', left_on=['Identifiant','Appellation','Couleur','Produit','Lieu'], right_on = ['Identifiant','Appellation','Couleur','Produit','Lieu'],suffixes=("", " lots"))
tab_cal = tab_cal[['Identifiant','Appellation','Couleur','Produit','Volume','Lieu','type_vol_revendique','type_instance_controle','type_changement_deno_dest_produit','type_changement_deno_src_produit','type_declassement','A','B','A-B','Somme Volume lots.csv']]

#info de l'etablissement et de la societe


etablissements = pd.merge(etablissements,societe,how='outer',left_on="Login",right_on='Identifiant',suffixes=('',' societe'))
tab_cal = pd.merge(tab_cal,etablissements,how='left',left_on=['Identifiant'],right_on=['Identifiant etablissement'],suffixes=(''," etablissement"))    
tab_cal = tab_cal[['Identifiant','Raison sociale societe','Raison sociale',"Adresse societe","Adresse 2 societe",'Adresse 3 societe','Code postal societe','Commune societe', 'Pays', 'Code comptable societe','Téléphone',"Téléphone portable",'Fax societe','Email societe','Appellation','Couleur','Produit','Volume','Lieu','type_vol_revendique','type_instance_controle','type_changement_deno_dest_produit','type_changement_deno_src_produit','type_declassement','A','B','A-B','Somme Volume lots.csv']]
tab_cal = tab_cal.rename(columns = {'Raison sociale': 'Nom etablissement','Raison sociale societe':'Nom societe','Adresse societe':'Adresse','Adresse 2 societe':'Adresse 2','Adresse 3 societe':'Adresse 3','Code postal societe':'Code postal','Email Operateur':'Email','Fax societe':'Fax'})

tab_cal.reset_index(drop=True).to_csv(outputdir+'/'+datelimite_exact+'_'+millesime+'_igp_stats_droit_inao_operateurs_redevable_A_B_A-B.csv', encoding="iso8859_15", sep=";",index=False,  decimal=",")


needincoherence = (len(lots) == len(lots_ini))

#pour comparer avec incoherence:
if (needincoherence):           

    incoherent = tab_cal
    incoherent['Difference A-B / Somme Lots.csv'] = round(incoherent['A-B'] - incoherent['Somme Volume lots.csv'],5)

    incoherent = incoherent[incoherent['Difference A-B / Somme Lots.csv'] != 0]

    incoherent = incoherent[incoherent['Difference A-B / Somme Lots.csv'] != -incoherent['type_instance_controle']]

    #pour avoir l'unique id du lot
    changement_denomination_initial = changement_denomination_initial[['Identifiant','Origine Appellation','Origine Couleur','Origine Produit','Origin Lot unique Id']]

    changement_denomination_initial.drop_duplicates(keep = 'first')  

    incoherent = pd.merge(incoherent,changement_denomination_initial,how='left',left_on = ['Identifiant','Appellation','Couleur','Produit'], right_on = ['Identifiant','Origine Appellation','Origine Couleur','Origine Produit'], suffixes=('',' plus'))

    incoherent = incoherent[['Identifiant','Appellation','Couleur','Produit','type_vol_revendique','type_instance_controle','type_changement_deno_dest_produit','type_changement_deno_src_produit','type_declassement','A','B','A-B','Somme Volume lots.csv','Difference A-B / Somme Lots.csv','Origin Lot unique Id']]

    incoherent['url lot'] = "https://"+igp+".igp.vins.24eme.fr/historique/"+incoherent['Identifiant']+'/'+incoherent['Origin Lot unique Id']

    incoherent.reset_index(drop=True).to_csv(outputdir+'/incoherent.csv', encoding="iso8859_15", sep=";",index=False,  decimal=",")


# In[ ]:





# In[ ]:





# In[ ]:




