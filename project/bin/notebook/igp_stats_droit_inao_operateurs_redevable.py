#!/usr/bin/env python
# coding: utf-8

# In[ ]:


import pandas as pd
pd.set_option('display.max_columns', None)

drev_lots = pd.read_csv("../../web/exports_igpgascogne/drev_lots.csv", encoding="iso8859_15", delimiter=";", decimal=",", dtype={'Identifiant': 'str', 'Campagne': 'str', 'Siret Opérateur': 'str', 'Code postal Opérateur': 'str'}, low_memory=False)
etablissements = pd.read_csv("../../web/exports_igpgascogne/etablissements.csv", encoding="iso8859_15", delimiter=";", decimal=",", dtype={'Login': 'str', 'Identifiant etablissement': 'str'}, index_col=False, low_memory=False)
societe = pd.read_csv("../../web/exports_igpgascogne/societe.csv", encoding="iso8859_15", delimiter=";", decimal=",", dtype={'Identifiant': 'str', 'Téléphone' :'str', 'Téléphone portable': 'str'}, index_col=False, low_memory=False)
lots = pd.read_csv("../../web/exports_igpgascogne/lots.csv", encoding="iso8859_15", delimiter=";", decimal=",", index_col=False, low_memory=False)
changement_denomination = pd.read_csv("../../web/exports_igpgascogne/changement_denomination.csv", encoding="iso8859_15", delimiter=";", decimal=",", dtype={'Campagne': 'str', 'Millésime':'str','Origine Millésime':'str'}, index_col=False, low_memory=False)


# In[ ]:


def createCSVByCampagne(campagne,drev_lots,etablissements,societe,lots,changement_denomination):
    drev_lots = drev_lots.query("Campagne == @campagne");
    drev_lots = drev_lots.groupby(['Identifiant','Famille','CVI Opérateur','Siret Opérateur','Nom Opérateur','Adresse Opérateur','Code postal Opérateur','Commune Opérateur','Email Operateur','Appellation','Couleur'])[["Volume"]].sum()
    drev_lots = drev_lots.reset_index()
    
    drev_lots = pd.merge(drev_lots, etablissements, how='left',left_on=["Identifiant"], right_on=["Identifiant etablissement"],suffixes=("", " etablissement"))
    drev_lots = pd.merge(drev_lots, societe , how ="left" , left_on ="Login", right_on ="Identifiant",suffixes=("", " societe"))
    
    conforme = "Conforme"
    rep_conforme = "Réputé conforme"
    
    lots = lots.query("Campagne == @campagne");    
    lots = lots.rename(columns = {'Statut de lot': 'Statut_de_lot'})
    lots = lots.query("Statut_de_lot != @conforme & Statut_de_lot != @rep_conforme");           
    lots = lots.groupby(['Id Opérateur','Nom Opérateur','Appellation','Couleur'])[["Volume"]].sum()
    lots = lots.reset_index() #à enlever peut être
        
    drev_lots = pd.merge(drev_lots, lots , how='left', left_on = ["Identifiant",'Nom Opérateur','Appellation','Couleur'], right_on = ["Id Opérateur",'Nom Opérateur','Appellation','Couleur'],suffixes=("", " lots"))
       
    drev_lots =  drev_lots.rename(columns = {'Volume': 'Volume revendiqué','Volume lots':'Volume en instance de conformité'})
            
    changement_denomination = changement_denomination.rename(columns = {'Type de changement':'Type_de_changement'})
    
    type_de_changement = "DECLASSEMENT"

    changement_denomination_declassement = changement_denomination.groupby(['Identifiant','Famille','CVI Opérateur','Siret Opérateur','Origine Appellation','Origine Couleur'])[["Volume changé"]].sum()

    changement_denomination_declassement  = changement_denomination_declassement.reset_index()
    
    print(changement_denomination_declassement['Volume changé'])
    
    type_de_changement = "CHANGEMENT"

    changement_denomination = changement_denomination.query("Campagne == @campagne")
    changement_denomination = changement_denomination.query("Type_de_changement == @type_de_changement")
    
    changement_denomination = changement_denomination.groupby(['Identifiant','Famille','CVI Opérateur','Siret Opérateur','Origine Appellation','Origine Couleur','Appellation','Couleur'])[["Volume changé"]].sum()

    changement_denomination  = changement_denomination.reset_index()
    
    changement_denomination = pd.merge(changement_denomination,changement_denomination_declassement,how='outer',left_on=["Identifiant",'Origine Appellation','Origine Couleur','Famille'],right_on= ["Identifiant",'Origine Appellation','Origine Couleur','Famille'],suffixes=("", " declassement"))
        
    drev_lots = drev_lots[['Identifiant','Famille','Appellation','Couleur','Nom Opérateur','Raison sociale','Siret Opérateur', 'CVI Opérateur','Adresse societe','Adresse 2 societe','Adresse 3 societe','Code postal societe', 'Commune societe','Email','Téléphone','Téléphone portable', 'Fax societe','Volume revendiqué','Volume en instance de conformité']]
    
    
    final = pd.merge(drev_lots,changement_denomination,how='left', left_on=['Identifiant','Appellation','Couleur'], right_on=['Identifiant','Origine Appellation','Origine Couleur'],suffixes=("", " changement_deno"))
    #,'Appellation','Couleur'
    # ,'Origine Appellation','Origine Couleur'
    
    #colonnes à avoir dans le csv final
    final = final[['Identifiant','Famille','Appellation','Couleur','Nom Opérateur','Raison sociale','Siret Opérateur', 'CVI Opérateur','Adresse societe','Adresse 2 societe','Adresse 3 societe','Code postal societe', 'Commune societe','Email','Téléphone','Téléphone portable', 'Fax societe','Volume revendiqué','Volume en instance de conformité','Appellation changement_deno', 'Couleur changement_deno','Volume changé','Volume changé declassement']]

    final.reset_index(drop=True).to_csv('../../web/exports/igp_stats_droit_inao_operateurs_redevable'+campagne+".csv", encoding="iso8859_15", sep=";",index=False, decimal=",")


# In[ ]:


createCSVByCampagne("2019-2020",drev_lots,etablissements,societe,lots,changement_denomination)
createCSVByCampagne("2020-2021",drev_lots,etablissements,societe,lots,changement_denomination)

