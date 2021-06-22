#!/usr/bin/env python
# coding: utf-8

# In[ ]:


import pandas as pd

pd.set_option('display.max_columns', None)

etablissements = pd.read_csv("../../web/exports/etablissements.csv", encoding="iso8859_15", delimiter=";", decimal=",", dtype={'Login': 'str', 'Identifiant etablissement': 'str'}, index_col=False, low_memory=False)
drev = pd.read_csv("../../web/exports/drev.csv", encoding="iso8859_15", delimiter=";", decimal=",", dtype={'Identifiant': 'str', 'Campagne': 'str', 'Siret Opérateur': 'str', 'Code postal Opérateur': 'str'}, low_memory=False)
dr = pd.read_csv("../../web/exports/dr.csv", encoding="iso8859_15", delimiter=";",decimal=",", dtype={'Identifiant': 'str', 'Campagne': 'str', 'Valeur': 'float64'}, low_memory=False)


# In[ ]:


#Filtre sur la dernière période

periode = "2020";
drev = drev.query("Campagne == @periode");
dr = dr.query("Campagne == @periode");


# In[ ]:


facturation = pd.DataFrame()
facturation["Identifiant"] = pd.Series(dtype='str')
facturation["Campagne"] = pd.Series(dtype='str')

drev_cdr_cdrv = drev.query("Appellation == 'CDR' or Appellation == 'CVG'").groupby(["Campagne", "Identifiant","Appellation"]).sum()
drev_cdrv_sablet = drev.query('Appellation == "CVG" and Lieu == "SAB"').groupby(["Campagne", "Identifiant","Appellation", "Lieu"]).sum()
drev_cdrv_vaison_romaine = drev.query('Appellation == "CVG" and Lieu == "VLR"').groupby(["Campagne", "Identifiant","Appellation", "Lieu"]).sum()


facturation = pd.merge(facturation, drev_cdr_cdrv,  how='outer', on=['Campagne', 'Identifiant'])
facturation = pd.merge(facturation, drev_cdrv_sablet,  how='outer', on=['Campagne', 'Identifiant'], suffixes=("", " sablet"))
facturation = pd.merge(facturation, drev_cdrv_vaison_romaine,  how='outer', on=['Campagne', 'Identifiant'], suffixes=("", " vaison la romaine"))


# In[ ]:


dr_cdr_cdrv = dr.query("(Appellation == 'CDR' or Appellation == 'CVG') and Code == '06'").groupby(["Campagne", "Identifiant"]).sum()
dr_cdrv_sablet = dr.query("Appellation == 'CVG' and Lieu == 'SAB' and Code == '06'").groupby(["Campagne", "Identifiant"]).sum()
dr_cdrv_vaison_romaine = dr.query("Appellation == 'CVG' and Lieu == 'VLR' and Code == '06'").groupby(["Campagne", "Identifiant"]).sum()

facturation = pd.merge(facturation, dr_cdr_cdrv,  how='outer', on=['Campagne', 'Identifiant'])
facturation = pd.merge(facturation, dr_cdrv_sablet,  how='outer', on=['Campagne', 'Identifiant'], suffixes=("", " sablet"))
facturation = pd.merge(facturation, dr_cdrv_vaison_romaine,  how='outer', on=['Campagne', 'Identifiant'], suffixes=("", " vaison la romaine"))

facturation["Vendange fraiche"] = facturation["Valeur"]
facturation["Vendange fraiche sablet"] = facturation["Valeur sablet"]
facturation["Vendange fraiche vaison la romaine"] = facturation["Valeur vaison la romaine"]


# In[ ]:


etablissements['Identifiant'] = etablissements['Identifiant etablissement']
facturation = pd.merge(facturation, etablissements,  how='inner', on=['Identifiant'], suffixes=("", " etablissement"))


# In[ ]:


facturation = facturation[['Campagne', 'Identifiant', 'CVI', 'Raison sociale', 'Adresse', 'Adresse 2', 'Adresse 3', 'Code postal', 'Commune', 'Téléphone bureau', 'Téléphone mobile', 'Email', 'Famille', 'Superficie revendiqué', 'Volume revendiqué net total', 'Superficie revendiqué sablet', 'Volume revendiqué net total sablet',  'Superficie revendiqué vaison la romaine', 'Volume revendiqué net total vaison la romaine', 'Vendange fraiche', 'Vendange fraiche sablet', 'Vendange fraiche vaison la romaine']]


# In[ ]:


facturation.to_csv('../../web/exports/facturation_cotisations.csv', encoding="iso8859_15", sep=";", decimal=",", index=False)
