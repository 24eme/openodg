#!/usr/bin/env python
# coding: utf-8

# In[ ]:


import pandas as pd
import argparse


# In[ ]:


campagne = "2019"
appellation = "AOC Crémant d'Alsace"
exports_path = "../../web/exports"
output_path = exports_path + "/bilan_vci_"+campagne+"_aoc_cremant_alsace.csv"
parser = argparse.ArgumentParser()
parser.add_argument("campagne", help="Année de récolte", default=campagne, nargs='?')
parser.add_argument("appellation", help="Libellé de l'appellation", default=appellation, nargs='?')
parser.add_argument("exports_path", help="Chemin qui contient les exports", default=exports_path, nargs='?')
parser.add_argument("output_path", help="Chemin du fichier de sortie", default=output_path, nargs='?')

try:
    args = parser.parse_args()
    campagne = args.campagne
    appellation = args.appellation
    exports_path = args.exports_path
    output_path = args.output_path
except:
    print("Arguments pas défaut")



# In[ ]:


campagne_prev = str(int(campagne) - 1)
vci = pd.read_csv(exports_path + "/registre_vci.csv", encoding="iso8859_15", delimiter=";", decimal=",", dtype={'CVI': 'str', 'SIRET': 'str', 'Produit': 'str', 'Campagne': 'str'}, low_memory=False)
drev = pd.read_csv(exports_path + "/drev.csv", encoding="iso8859_15", delimiter=";", decimal=",", dtype={'CVI Opérateur': 'str', 'Campagne': 'str'}, low_memory=False)
vci_curr = vci.query("Campagne == @campagne").reset_index(drop=True)
vci_prev = vci.query("Campagne == @campagne_prev").reset_index(drop=True)
drev_curr = drev.query("Campagne == @campagne").reset_index(drop=True)
infos = pd.concat([vci_curr,vci_prev], ignore_index=True)


# In[ ]:


drev_curr.head(5)


# In[ ]:


vci_curr.head(5)


# In[ ]:


vci_curr.columns.tolist()


# In[ ]:


vci_curr[vci_curr['Produit'].str.contains(appellation)]


# In[ ]:


vci_curr_group = vci_curr[vci_curr['Produit'].str.contains(appellation)].iloc[:,[1,11,12,13,14,15,16,17]].groupby('CVI').agg('sum').reset_index()
vci_prev_group = vci_prev[vci_prev['Produit'].str.contains(appellation)].iloc[:,[1,11,12,13,14,15,16,17]].groupby('CVI').agg('sum').reset_index()


# In[ ]:


vci_curr_group.head()


# In[ ]:


vci_curr_group.describe()


# In[ ]:


vci_prev_group.head()


# In[ ]:


vci_prev_group.describe()


# In[ ]:


registres = pd.merge(vci_prev_group, vci_curr_group,  how='outer', on=['CVI'])


# In[ ]:


registres.head()


# In[ ]:


registres.describe()


# In[ ]:


type_ligne="Revendication" 
drev_curr['type_ligne'] = drev_curr['Type de ligne']
drev_curr.query("Produit == @appellation and type_ligne == @type_ligne")


# In[ ]:


drev_curr_group = drev_curr.query("Produit == @appellation and type_ligne == @type_ligne").iloc[:,[1,10,11,12,13]].reset_index()


# In[ ]:


drev_curr_group.head()


# In[ ]:


drev_curr_group.describe()


# In[ ]:


bilan = pd.merge(registres, drev_curr_group, how='left', left_on='CVI', right_on='CVI Opérateur')


# In[ ]:


infos.head()


# In[ ]:


infos_unique = infos.reindex(columns=["CVI", "SIRET", "Raison sociale", "Adresse", "Code postal", "Commune"]).drop_duplicates().reset_index();


# In[ ]:


infos_unique.head()


# In[ ]:


bilan_infos = pd.merge(bilan, infos_unique, how='left', on='CVI')


# In[ ]:


bilan_infos.head()


# In[ ]:


bilan_infos.describe()


# In[ ]:


bilan_infos['campagne'] = campagne
bilan_infos['appellation'] = appellation
bilan_infos['titre'] = ""
bilan_infos['raison_sociale'] = bilan_infos["Raison sociale"]
bilan_infos['adresse'] = bilan_infos["Adresse"]
bilan_infos['commune'] = bilan_infos["Commune"]
bilan_infos['code_postal'] = bilan_infos["Code postal"]
bilan_infos['siret'] = bilan_infos["SIRET"]
bilan_infos['stock_vci_n-1'] = round(bilan_infos["Constitue_x"].fillna(0) + bilan_infos["Stock précédent_x"].fillna(0), 2)
bilan_infos['dr_surface'] = bilan_infos["Superficie revendiqué"]
bilan_infos['dr_volume'] = bilan_infos["Volume revendiqué"]
bilan_infos['dr_vci'] = bilan_infos["Constitue_y"]
bilan_infos['vci_complement'] = bilan_infos["Complément_x"]
bilan_infos['vci_substitution'] = bilan_infos["Substitution_x"]
bilan_infos['vci_rafraichi'] = bilan_infos["Rafraichi_x"]
bilan_infos['vci_desctruction'] = bilan_infos["Destruction_x"]
bilan_infos['drev_revendique_n'] = round(bilan_infos["Volume revendiqué"].fillna(0) - bilan_infos["Volume revendiqué issu du VCI"].fillna(0), 2)
bilan_infos['drev_revendique_n-1'] = bilan_infos["Volume revendiqué issu du VCI"]
bilan_infos['stock_vci_n'] = bilan_infos["Stock_y"]


# In[ ]:


bilan_final = bilan_infos.reindex(columns=["campagne","appellation","titre", "raison_sociale", "adresse", "commune", "code_postal", "CVI", "siret", "stock_vci_n-1", "dr_surface", "dr_volume", "dr_vci", "vci_complement", "vci_substitution", "vci_rafraichi", "vci_desctruction", "drev_revendique_n", "drev_revendique_n-1", "stock_vci_n"])


# In[ ]:


bilan_final.describe()


# In[ ]:


bilan_final.head()


# In[ ]:


bilan_final.to_csv(output_path, encoding="iso8859_15", sep=";", index=False, decimal=",")


# In[ ]:




