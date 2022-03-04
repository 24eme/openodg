#!/usr/bin/env python
# coding: utf-8

# In[ ]:


import pandas as pd
import argparse


# In[ ]:


campagne = "2020"
appellation = "AOC Crémant d'Alsace"
exports_path = "../../web/exports"
output_path = exports_path + "/bilan_vci_"+campagne+"_aoc_alsace_blanc.csv"
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


drev = pd.read_csv(exports_path + "/drev.csv", encoding="iso8859_15", delimiter=";", decimal=",", dtype={'CVI Opérateur': 'str', 'Campagne': 'str'}, low_memory=False)
drev_curr = drev.query("Campagne == @campagne").reset_index(drop=True)
drev_curr.head(5)


# In[ ]:


dr = pd.read_csv(exports_path + "/"+ campagne + "/" + campagne + "_dr.csv", encoding="iso8859_15", delimiter=";", decimal=",", dtype={'CVI recoltant': 'str'}, low_memory=False)
dr = dr.query("type_ligne == \"total_cave_particuliere\" and cepage != \"TOTAL\" and vtsgn != vtsgn").reset_index(drop=True)
dr["Type de ligne"] = "Revendication"
dr["volume"].fillna(0)
dr["volume"] = dr["volume"].astype("float64")
dr["superficie totale"].fillna(0)
dr["superficie totale"] = dr["superficie totale"].astype("float64")
dr["volume total"].fillna(0)
dr["volume total"] = dr["volume total"].astype("float64")
dr["vci total"].fillna(0)
dr["vci total"] = dr["vci total"].astype("float64")
dr["Produit"] = dr["appellation"] + " " + dr["lieu"].fillna("") + " " + dr["cepage"].fillna("")
dr["Produit"] = dr["Produit"].replace(regex=r'TOTAL', value='')
dr["Produit"] = dr["Produit"].replace(regex=r'[ ]+', value=' ')
dr["Produit"] = dr["Produit"].replace(regex=r'[ ]+$', value='')
dr["Produit"] = dr["Produit"].replace(regex=r'Cremant', value='Crémant')
dr["Produit"] = dr["Produit"].replace(regex=r'Gewurztraminer', value='Gewurzt.')
dr["Produit"] = dr["Produit"].replace(regex=r'Assemblage', value='Assemblage/Edelzwicker')
dr["CVI Opérateur"] = dr["CVI recoltant"]

dr.head(10)


# In[ ]:


drev_dr_curr = pd.merge(drev_curr, dr,  how='outer', on=['CVI Opérateur', 'Produit', 'Type de ligne'])
drev_dr_curr.head(100)


# In[ ]:


campagne_prev = str(int(campagne) - 1)
vci = pd.read_csv(exports_path + "/registre_vci.csv", encoding="iso8859_15", delimiter=";", decimal=",", dtype={'CVI': 'str', 'SIRET': 'str', 'Produit': 'str', 'Campagne': 'str'}, low_memory=False)
vci['CVI Opérateur'] = vci['CVI']
vci_curr = vci.query("Campagne == @campagne").reset_index(drop=True)
vci_prev = vci.query("Campagne == @campagne_prev").reset_index(drop=True)
infos = pd.concat([vci_curr,vci_prev], ignore_index=True)


# In[ ]:


vci_curr.head(5)


# In[ ]:


vci_curr.columns.tolist()


# In[ ]:


vci_curr[vci_curr['Produit'].str.contains(appellation)]


# In[ ]:


vci_curr_total_group = vci_curr[vci_curr['Produit'].str.contains(appellation)].iloc[:,[18,8,11,12,13,14,15,16,17,10]].groupby(['CVI Opérateur', 'Stockage']).agg('sum').reset_index()
vci_curr_total_group['Produit'] = appellation
vci_curr_group = vci_curr[vci_curr['Produit'].str.contains(appellation)].iloc[:,[18,8,11,12,13,14,15,16,17,10]].groupby(['CVI Opérateur', 'Produit', 'Stockage']).agg('sum').reset_index()
vci_curr_group = pd.concat([vci_curr_group, vci_curr_total_group], ignore_index=True)
vci_prev_total_group = vci_prev[vci_prev['Produit'].str.contains(appellation)].iloc[:,[18,8,11,12,13,14,15,16,17,10]].groupby(['CVI Opérateur', 'Stockage']).agg('sum').reset_index()
vci_prev_total_group['Produit'] = appellation
vci_prev_group = vci_prev[vci_prev['Produit'].str.contains(appellation)].iloc[:,[18,8,11,12,13,14,15,16,17,10]].groupby(['CVI Opérateur', 'Produit', 'Stockage']).agg('sum').reset_index()
vci_prev_group = pd.concat([vci_prev_group, vci_prev_total_group], ignore_index=True)


# In[ ]:


vci_curr_group.head()


# In[ ]:


vci_curr_group.describe()


# In[ ]:


vci_prev_group.head()


# In[ ]:


vci_prev_group.describe()


# In[ ]:


registres = pd.merge(vci_prev_group, vci_curr_group,  how='outer', on=['CVI Opérateur', 'Produit', 'Stockage'])


# In[ ]:


registres.head()


# In[ ]:


registres.describe()


# In[ ]:


type_ligne="Revendication" 
drev_dr_curr['type_ligne'] = drev_dr_curr['Type de ligne']
drev_dr_curr[drev_dr_curr.Produit.str.contains(appellation)].query("type_ligne == @type_ligne")


# In[ ]:


drev_curr_group = drev_dr_curr[drev_dr_curr.Produit.str.contains(appellation)].query("type_ligne == @type_ligne").iloc[:,[1,9,10,11,12,13,32,33,36]].reset_index(drop=True)


# In[ ]:


drev_curr_group.head()


# In[ ]:


drev_curr_group.describe()


# In[ ]:


bilan = pd.merge(registres, drev_curr_group, how='left', on=['CVI Opérateur', 'Produit'])


# In[ ]:


infos["CVI Opérateur"] = infos["CVI"]
infos.head()


# In[ ]:


infos_unique = infos.reindex(columns=["CVI Opérateur", "SIRET", "Raison sociale", "Adresse", "Code postal", "Commune"]).drop_duplicates().reset_index(drop=True);


# In[ ]:


infos_unique.head()


# In[ ]:


bilan_infos = pd.merge(bilan, infos_unique, how='left', on='CVI Opérateur')


# In[ ]:


bilan_infos.head()


# In[ ]:


bilan_infos.describe()


# In[ ]:


bilan_infos['campagne'] = campagne
bilan_infos['appellation'] = appellation
bilan_infos['CVI'] = bilan_infos["CVI Opérateur"]
bilan_infos['titre'] = ""
bilan_infos['raison_sociale'] = bilan_infos["Raison sociale"]
bilan_infos['adresse'] = bilan_infos["Adresse"]
bilan_infos['commune'] = bilan_infos["Commune"]
bilan_infos['code_postal'] = bilan_infos["Code postal"]
bilan_infos['siret'] = bilan_infos["SIRET"]
bilan_infos['stockage'] = bilan_infos["Stockage"]
bilan_infos['stock_vci_n-1'] = round(bilan_infos["Constitue_x"].fillna(0) + bilan_infos["Stock précédent_x"].fillna(0), 2)
bilan_infos['dr_surface'] = bilan_infos["superficie totale"]
bilan_infos['dr_volume'] = bilan_infos["volume total"]
bilan_infos['dr_vci'] = bilan_infos["vci total"]
bilan_infos['vci_constitue'] = round(bilan_infos["Constitue_y"].fillna(0), 2)
bilan_infos['vci_complement'] = bilan_infos["Complément_x"]
bilan_infos['vci_substitution'] = bilan_infos["Substitution_x"]
bilan_infos['vci_rafraichi'] = bilan_infos["Rafraichi_x"]
bilan_infos['vci_desctruction'] = bilan_infos["Destruction_x"]
bilan_infos['drev_revendique_n'] = round(bilan_infos["Volume revendiqué"].fillna(0) - bilan_infos["Volume revendiqué issu du VCI"].fillna(0), 2)
bilan_infos['drev_revendique_n-1'] = bilan_infos["Volume revendiqué issu du VCI"]
bilan_infos['stock_vci_n'] = round(bilan_infos["Stock_y"].fillna(0), 2)
bilan_infos['rendement_vci_ha_hl'] = round((bilan_infos['vci_rafraichi'] + bilan_infos['vci_constitue']) / bilan_infos['dr_surface'] * 100, 2)


# In[ ]:


bilan_infos = bilan_infos.query("stock_vci_n-1 > 0 or vci_constitue > 0 or vci_complement > 0 or vci_substitution > 0 or vci_rafraichi > 0 or vci_desctruction > 0 or stock_vci_n > 0 ").reset_index(drop=True);
bilan_final = bilan_infos.sort_values(['campagne', 'CVI', 'Produit']).reindex(columns=["campagne","Produit","titre", "raison_sociale", "adresse", "commune", "code_postal", "CVI", "siret", "stockage", "stock_vci_n-1", "dr_surface", "dr_volume", "dr_vci", "vci_constitue", "vci_complement", "vci_substitution", "vci_rafraichi", "vci_desctruction", "drev_revendique_n", "drev_revendique_n-1", "stock_vci_n", "rendement_vci_ha_hl"]).drop_duplicates().reset_index(drop=True);


# In[ ]:


bilan_final.describe()


# In[ ]:


bilan_final.head(100)


# In[ ]:


bilan_final.to_csv(output_path, encoding="iso8859_15", sep=";", index=False, decimal=",")

