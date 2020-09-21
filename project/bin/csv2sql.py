# -*- coding: iso-8859-1 -*
import sys, os, pandas as pd
from sqlalchemy import create_engine
engine = create_engine('sqlite:///'+sys.argv[1], echo=False, encoding='iso-8859-1')

if os.path.exists(sys.argv[2]+"etablissements.csv"):
    sys.stderr.write("etablissements.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/etablissements.csv", encoding='iso-8859-1', delimiter=";", index_col=False)
    csv.to_sql('etablissement', con=engine, if_exists='replace')

if os.path.exists(sys.argv[2]+"chais.csv"):
    sys.stderr.write("chais.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/chais.csv", encoding='iso-8859-1', delimiter=";", index_col=False)
    csv.to_sql('chai', con=engine, if_exists='replace')

if os.path.exists(sys.argv[2]+"habilitation.csv"):
    sys.stderr.write("habilitation.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/habilitation.csv", encoding='iso-8859-1', delimiter=";", index_col=False)
    csv.to_sql('habilitation', con=engine, if_exists='replace')

if os.path.exists(sys.argv[2]+"habilitation_demandes.csv"):
    sys.stderr.write("habilitation_demandes.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/habilitation_demandes.csv", encoding='iso-8859-1', delimiter=";", index_col=False)
    csv.to_sql('habilitation_demandes', con=engine, if_exists='replace')

if os.path.exists(sys.argv[2]+"drev.csv"):
    sys.stderr.write("drev.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/drev.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False)
    csv.to_sql('drev', con=engine, if_exists='replace')

if os.path.exists(sys.argv[2]+"dr.csv"):
    sys.stderr.write("dr.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/dr.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False)
    csv.to_sql('dr', con=engine, if_exists='replace')

if os.path.exists(sys.argv[2]+"/sv12.csv"):
    sys.stderr.write(sys.argv[2]+"/sv12.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/sv12.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False)
    csv.to_sql('sv12', con=engine, if_exists='replace')

if os.path.exists(sys.argv[2]+"/sv11.csv"):
    sys.stderr.write(sys.argv[2]+"/sv11.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/sv11.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False)
    csv.to_sql('sv11', con=engine, if_exists='replace')

if os.path.exists(sys.argv[2]+"/drev_marc.csv"):
    sys.stderr.write(sys.argv[2]+"/drev_marc.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/drev_marc.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False)
    csv.to_sql('drev_marc', con=engine, if_exists='replace')

if os.path.exists(sys.argv[2]+"/facture.csv"):
    sys.stderr.write(sys.argv[2]+"/facture.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/facture.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False)
    csv.to_sql('facture', con=engine, if_exists='replace')

if os.path.exists(sys.argv[2]+"/parcellaire.csv"):
    sys.stderr.write(sys.argv[2]+"/parcellaire.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/parcellaire.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False)
    csv.to_sql('parcellaire', con=engine, if_exists='replace')

if os.path.exists(sys.argv[2]+"/travaux_marc.csv"):
    sys.stderr.write(sys.argv[2]+"/travaux_marc.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/travaux_marc.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False)
    csv.to_sql('travaux_marc', con=engine, if_exists='replace')

if os.path.exists(sys.argv[2]+"/tirage.csv"):
    sys.stderr.write(sys.argv[2]+"/tirage.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/tirage.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False)
    csv.to_sql('tirage', con=engine, if_exists='replace')

if os.path.exists(sys.argv[2]+"/registre_vci.csv"):
    sys.stderr.write(sys.argv[2]+"/registre_vci.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/registre_vci.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False)
    csv.to_sql('registre_vci', con=engine, if_exists='replace')

if os.path.exists(sys.argv[2]+"/constats.csv"):
    sys.stderr.write(sys.argv[2]+"/constats.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/constats.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False)
    csv.to_sql('constats', con=engine, if_exists='replace')

if os.path.exists(sys.argv[2]+"/facture_stats.csv"):
    sys.stderr.write(sys.argv[2]+"/facture_stats.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/facture_stats.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False)
    csv.to_sql('facture_stats', con=engine, if_exists='replace')

if os.path.exists(sys.argv[2]+"/bilan_vci.csv"):
    sys.stderr.write(sys.argv[2]+"/bilan_vci.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/bilan_vci.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False)
    csv.to_sql('bilan_vci', con=engine, if_exists='replace')
