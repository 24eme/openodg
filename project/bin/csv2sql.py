# -*- coding: iso-8859-1 -*
import sys, os, pandas as pd
from sqlalchemy import create_engine
engine = create_engine('sqlite:///'+sys.argv[1], echo=False, encoding='iso-8859-1')

if os.path.exists(sys.argv[2]+"/etablissements.csv") and os.path.getsize(sys.argv[2]+"/etablissements.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/etablissements.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/etablissements.csv", encoding='iso-8859-1', delimiter=";", index_col=False, dtype={'Code postal': 'str', 'CVI': 'str', 'SIRET': 'str', 'Login': 'str', 'Identifiant etablissement': 'str', 'Date Habilitation': 'str', 'Date Archivage': 'str', 'Adresse 3': 'str', 'Réception ODG': 'str', 'Enresgistrement ODG': 'str', 'Transmission AVPI': 'str', 'Date Habilitation': 'str', 'Date Archivage': 'str', 'Code comptable': 'str', 'En Alerte': 'str'})
    csv.to_sql('etablissement', con=engine, if_exists='replace')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/chais.csv") and os.path.getsize(sys.argv[2]+"/chais.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/chais.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/chais.csv", encoding='iso-8859-1', delimiter=";", index_col=False)
    csv.to_sql('chai', con=engine, if_exists='replace', dtype={'IdCIVP': 'str', 'EA1': 'str', 'EA2': 'str', 'SIRET': 'str'})
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/societe.csv") and os.path.getsize(sys.argv[2]+"/societe.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/societe.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/societe.csv", encoding='iso-8859-1', delimiter=";", index_col=False, dtype={'Siret': 'str', 'Identifiant etablissement': 'str', 'Identifiant societe': 'str', 'Code comptable client': 'str', 'Code Postal': 'str', 'Piece': 'str', 'Identifiant Analytique': 'str', 'Export comptable': 'str', 'Campagne': 'str', 'Identifiant': 'str', 'Adresse 3': 'str', 'Code comptable': 'str', 'Code NAF': 'str', 'TVA Intra': 'str', 'Téléphone': 'str', 'Téléphone portable': 'str', 'Fax': 'str', 'Site': 'str', 'Région': 'str', 'En alerte': 'str'})
    csv.to_sql('societe', con=engine, if_exists='replace')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/habilitation.csv") and os.path.getsize(sys.argv[2]+"/habilitation.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/habilitation.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/habilitation.csv", encoding='iso-8859-1', delimiter=";", index_col=False, dtype={'Identifiant': 'str', 'Mention': 'str', 'Lieu': 'str', 'Couleur': 'str', 'Cépage': 'str', 'Siret Opérateur': 'str', 'Adresse complémentaire (etablissement)': 'str', 'Adresse complémentaire 2 (etablissement)': 'str', 'Adresse complémentaire 2 (etablissement)': 'str', 'Adresse complémentaire 2 (société)': 'str'})
    csv.to_sql('habilitation', con=engine, if_exists='replace')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/habilitation_demandes.csv") and os.path.getsize(sys.argv[2]+"/habilitation_demandes.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/habilitation_demandes.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/habilitation_demandes.csv", encoding='iso-8859-1', delimiter=";", index_col=False, dtype={'Identifiant': 'str'})
    csv.to_sql('habilitation_demandes', con=engine, if_exists='replace')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/drev.csv") and os.path.getsize(sys.argv[2]+"/drev.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/drev.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/drev.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype={'Identifiant': 'str', 'Code postal Opérateur': 'str', 'Genre Libelle': 'str', 'Mention Libelle': 'str', 'Lieu Libelle': 'str', 'Cepage Libelle': 'str', "Date d'envoi à l'OI": 'str', 'Numéro du lot': "str", 'Date Rev': 'str', 'Produit (millesime)': 'str', 'Destination': 'str'})
    csv.to_sql('drev', con=engine, if_exists='replace')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/dr.csv") and os.path.getsize(sys.argv[2]+"/dr.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/dr.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/dr.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype={'Identifiant': 'str', 'Campagne': 'str', 'CVI': 'str', 'Code': 'str', 'CVI Tiers':'str', 'Millesime': 'str', 'Bailleur PPM': 'str', 'Code Commune': 'str', 'Id Colonne': 'str', 'Last DRev id with produit filter if exist': 'str'})
    csv['CVI'] = csv['CVI'].str.zfill(10)
    csv['CVI Tiers'] = csv['CVI Tiers'].str.zfill(10)
    csv.to_sql('dr', con=engine, if_exists='replace')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/sv12.csv") and os.path.getsize(sys.argv[2]+"/sv12.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/sv12.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/sv12.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype={'Identifiant': 'str', 'Campagne': 'str', 'CVI': 'str', 'Code': 'str', 'CVI Tiers':'str', 'Millesime': 'str', 'Bailleur PPM': 'str', 'Code Commune': 'str', 'Bailleur Nom': 'str', 'Code Commune Tiers': 'str', 'Commune Tiers': 'str', 'Id Colonne': 'str'})
    csv['CVI'] = csv['CVI'].str.zfill(10)
    csv['CVI Tiers'] = csv['CVI Tiers'].str.zfill(10)
    csv.to_sql('sv12', con=engine, if_exists='replace')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/sv11.csv") and os.path.getsize(sys.argv[2]+"/sv11.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/sv11.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/sv11.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype={'Identifiant': 'str', 'Campagne': 'str', 'CVI': 'str', 'Code': 'str', 'CVI Tiers':'str', 'Millesime': 'str', 'Bailleur PPM': 'str', 'Code Commune': 'str', 'Bailleur Nom': 'str', 'Code Commune Tiers': 'str', 'Commune Tiers': 'str', 'Id Colonne': 'str', 'label calculee': 'str'})
    csv['CVI'] = csv['CVI'].str.zfill(10)
    csv['CVI Tiers'] = csv['CVI Tiers'].str.zfill(10)
    csv.to_sql('sv11', con=engine, if_exists='replace')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/production.csv") and os.path.getsize(sys.argv[2]+"/production.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/production.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/production.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype={'Identifiant': 'str', 'Campagne': 'str', 'CVI': 'str', 'Code': 'str', 'CVI Tiers':'str', 'Millesime': 'str', 'Bailleur PPM': 'str', 'Code Commune': 'str', 'Id Colonne': 'str', 'Code Commune Tiers': 'str', 'Commune Tiers': 'str', 'Id Colonne': 'str'})
    csv['CVI'] = csv['CVI'].str.zfill(10)
    csv['CVI Tiers'] = csv['CVI Tiers'].str.zfill(10)
    csv.to_sql('production', con=engine, if_exists='replace')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/drev_marc.csv") and os.path.getsize(sys.argv[2]+"/drev_marc.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/drev_marc.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/drev_marc.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False)
    csv.to_sql('drev_marc', con=engine, if_exists='replace')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/facture.csv") and os.path.getsize(sys.argv[2]+"/facture.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/facture.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/facture.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False)
    csv.to_sql('facture', con=engine, if_exists='replace')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/parcellaire.csv") and os.path.getsize(sys.argv[2]+"/parcellaire.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/parcellaire.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/parcellaire.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype={'CVI Opérateur': 'str', 'Code postal Opérateur': 'str', 'Mention': 'str', 'Lieu': 'str', 'Cepage': 'str', 'Code commune': 'str', 'Numéro parcelle': 'str'})
    csv.to_sql('parcellaire', con=engine, if_exists='replace')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/parcellaireirrigable.csv") and os.path.getsize(sys.argv[2]+"/parcellaireirrigable.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/parcellaireirrigable.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/parcellaireirrigable.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False)
    csv.to_sql('parcellaireirrigable', con=engine, if_exists='replace')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/parcellaireirrigue.csv") and os.path.getsize(sys.argv[2]+"/parcellaireirrigue.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/parcellaireirrigue.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/parcellaireirrigue.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False)
    csv.to_sql('parcellaireirrigue', con=engine, if_exists='replace')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/parcellaireintentionaffectation.csv") and os.path.getsize(sys.argv[2]+"/parcellaireintentionaffectation.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/parcellaireintentionaffectation.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/parcellaireintentionaffectation.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False)
    csv.to_sql('parcellaireintentionaffectation', con=engine, if_exists='replace')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/parcellaireaffectation.csv") and os.path.getsize(sys.argv[2]+"/parcellaireaffectation.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/parcellaireaffectation.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/parcellaireaffectation.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False)
    csv.to_sql('parcellaireaffectation', con=engine, if_exists='replace')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/parcellairemanquant.csv") and os.path.getsize(sys.argv[2]+"/parcellairemanquant.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/parcellairemanquant.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/parcellairemanquant.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype={'Identifiant Société': 'str', 'Identifiant Opérateur': 'str', 'CVI Opérateur': 'str', 'Nom Opérateur': 'str', 'Code postal Opérateur': 'str', 'Mention': 'str', 'Lieu': 'str', 'Cepage': 'str', 'Code commune': 'str', 'Signataire': 'str'})
    csv.to_sql('parcellairemanquant', con=engine, if_exists='replace')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/travaux_marc.csv") and os.path.getsize(sys.argv[2]+"/travaux_marc.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/travaux_marc.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/travaux_marc.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False)
    csv.to_sql('travaux_marc', con=engine, if_exists='replace')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/tirage.csv") and os.path.getsize(sys.argv[2]+"/tirage.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/tirage.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/tirage.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False)
    csv.to_sql('tirage', con=engine, if_exists='replace')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/registre_vci.csv") and os.path.getsize(sys.argv[2]+"/registre_vci.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/registre_vci.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/registre_vci.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False)
    csv.to_sql('registre_vci', con=engine, if_exists='replace')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/constats.csv") and os.path.getsize(sys.argv[2]+"/constats.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/constats.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/constats.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False)
    csv.to_sql('constats', con=engine, if_exists='replace')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/facture_stats.csv") and os.path.getsize(sys.argv[2]+"/facture_stats.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/facture_stats.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/facture_stats.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False)
    csv.to_sql('facture_stats', con=engine, if_exists='replace')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/bilan_vci.csv") and os.path.getsize(sys.argv[2]+"/bilan_vci.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/bilan_vci.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/bilan_vci.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False)
    csv.to_sql('bilan_vci', con=engine, if_exists='replace')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/pieces.csv") and os.path.getsize(sys.argv[2]+"/pieces.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/pieces.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/pieces.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype={'Identifiant': 'str', 'piece numero': 'str', 'visibilite': 'str'})
    csv.to_sql('pieces', con=engine, if_exists='replace')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/liaisons.csv") and os.path.getsize(sys.argv[2]+"/liaisons.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/liaisons.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/liaisons.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype={'identifiant etablissement source': 'str', 'identifiant etablissement lié': 'str', 'hash chais lié': 'str', 'attribus chais liés': 'str', 'aliases': 'str'})
    csv.to_sql('liaisons', con=engine, if_exists='replace')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/comptes.csv") and os.path.getsize(sys.argv[2]+"/comptes.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/comptes.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/comptes.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype={'N° identifiant': 'str', 'Téléphone': 'str', 'Fax': 'str', 'En alerte': 'str', 'Login': 'str', 'Code 1ere connexion': 'str'})
    csv.to_sql('comptes', con=engine, if_exists='replace')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/lots.csv") and os.path.getsize(sys.argv[2]+"/lots.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/lots.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/lots.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype={'Identifiant': 'str', 'Campagne': 'str', 'Mention': 'str', 'Cepage': 'str', 'Num dossier': 'str', 'Millésime': 'str', 'Id Opérateur': 'str', 'Num lot': 'str', 'Lieu': 'str', 'Cépages': 'str', 'Date de destination': 'str', 'Pays de destination': 'str', 'Elevage': 'str', 'Centilisation': 'str', 'Date de conformité en appel': 'str'})
    #CVI Opérateur
    csv[csv.columns[4]] = csv[csv.columns[4]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
    #Millésime
    csv[csv.columns[21]] = csv[csv.columns[21]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
    csv.to_sql('lots', con=engine, if_exists='replace')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/lots-historique.csv") and os.path.getsize(sys.argv[2]+"/lots-historique.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/lots-historique.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/lots-historique.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype={'Id Opérateur': 'str', 'Campagne': 'str', 'Num Dossier': 'str', 'Num Lot': 'str', 'Doc Ordre': 'str', 'Lot Id Opérateur': 'str', 'Lot Code postal Opérateur': 'str', 'Lot Num lot': 'str', 'Lot Mention': 'str', 'Lot Lieu': 'str', 'Lot Cepage': 'str', 'Lot Cepage': 'str', 'Lot Cépages': 'str', 'Lot Millésime': 'str', 'Lot Date de destination': 'str', 'Lot Pays de destination': 'str', 'Lot Elevage': 'str', 'Lot Centilisation': 'str', 'Lot Date de conformité en appel': 'str', 'Lot Passage': 'str'})
    #Lot Code postal Opérateur
    csv[csv.columns[22]] = csv[csv.columns[22]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
    #Lot Millésime
    csv[csv.columns[39]] = csv[csv.columns[39]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
    csv.to_sql('lots-historique', con=engine, if_exists='replace')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/lots_suivi.csv") and os.path.getsize(sys.argv[2]+"/lots_suivi.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/lots_suivi.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/lots_suivi.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype={'Campagne': 'str', 'Mention': 'str', 'Cepage': 'str', 'Num dossier': 'str', 'Num lot': 'str', 'Id Opérateur': 'str', 'Millésime': 'str'})
    #CVI Opérateur
    csv[csv.columns[4]] = csv[csv.columns[4]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
    #Millésime
    csv[csv.columns[22]] = csv[csv.columns[22]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
    csv.to_sql('lots_suivi', con=engine, if_exists='replace')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/drev_lots.csv") and os.path.getsize(sys.argv[2]+"/drev_lots.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/drev_lots.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/drev_lots.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype={'Identifiant': 'str', 'Campagne': 'str', 'Mention': 'str', 'Cepage': 'str', 'Num dossier': 'str'})
    #CVI Opérateur
    csv[csv.columns[4]] = csv[csv.columns[4]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
    #SIRET Opérateur
    csv[csv.columns[5]] = csv[csv.columns[5]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
    #Millésime
    csv[csv.columns[25]] = csv[csv.columns[25]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
    csv.to_sql('drev_lots', con=engine, if_exists='replace')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/conditionnement_lots.csv") and os.path.getsize(sys.argv[2]+"/conditionnement_lots.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/conditionnement_lots.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/conditionnement_lots.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype={'Identifiant': 'str', 'Campagne': 'str', 'Mention': 'str', 'Cepage': 'str', 'Num dossier': 'str'})
    #CVI Opérateur
    csv[csv.columns[4]] = csv[csv.columns[4]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
    #SIRET Opérateur
    csv[csv.columns[5]] = csv[csv.columns[5]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
    #Millésime
    csv[csv.columns[25]] = csv[csv.columns[25]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
    csv.to_sql('conditionnement_lots', con=engine, if_exists='replace')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/transaction_lots.csv") and os.path.getsize(sys.argv[2]+"/transaction_lots.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/transaction_lots.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/transaction_lots.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype={'Identifiant': 'str', 'Campagne': 'str', 'Mention': 'str', 'Cepage': 'str', 'Num dossier': 'str', 'Code postal Opérateur': 'str', 'Num lot': 'str', 'Lieu': 'str', 'Cépages': 'str', 'Spécificités': 'str', 'Destination': 'str', 'Date de destination': 'str', 'Pays de destination': 'str', 'Centilisation': 'str', 'Elevage': 'str', 'Eleve': 'str', 'Prelevable': 'str', 'Preleve': 'str', 'Changé': 'str', 'Date de degustation voulue': 'str', "Date d'envoi OI": 'str', "Type document douanier if exists": 'str'})
    #CVI Opérateur
    csv[csv.columns[4]] = csv[csv.columns[4]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
    #SIRET Opérateur
    csv[csv.columns[5]] = csv[csv.columns[5]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
    #Millésime
    csv[csv.columns[25]] = csv[csv.columns[25]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
    csv.to_sql('transaction_lots', con=engine, if_exists='replace')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/pmc_lots.csv") and os.path.getsize(sys.argv[2]+"/transaction_lots.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/pmc_lots.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/pmc_lots.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype={'Identifiant': 'str', 'Campagne': 'str', 'Mention': 'str', 'Cepage': 'str', 'Num dossier': 'str', 'Code postal Opérateur': 'str', 'Num lot': 'str', 'Lieu': 'str', 'Cépages': 'str', 'Date de destination': 'str', 'Date de destination': 'str', 'Pays de destination': 'str', 'Centilisation': 'str', 'Elevage': 'str', 'Eleve': 'str', 'Prelevable': 'str', 'Preleve': 'str', 'Changé': 'str', "Date d'envoi OI": 'str', 'Type document douanier if exists': 'str', 'Doc Id': 'str'})
    #CVI Opérateur
    csv[csv.columns[4]] = csv[csv.columns[4]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
    #SIRET Opérateur
    csv[csv.columns[5]] = csv[csv.columns[5]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
    #Millésime
    csv[csv.columns[25]] = csv[csv.columns[25]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
    csv.to_sql('pmc_lots', con=engine, if_exists='replace')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/pmcnc_lots.csv") and os.path.getsize(sys.argv[2]+"/transaction_lots.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/pmcnc_lots.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/pmcnc_lots.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype={'Identifiant': 'str', 'Campagne': 'str', 'Mention': 'str', 'Cepage': 'str', 'Num dossier': 'str', 'Code postal Opérateur': 'str', 'Num lot': 'str', 'Lieu': 'str', 'Cépages': 'str', 'Date de destination': 'str', 'Date de destination': 'str', 'Pays de destination': 'str', 'Centilisation': 'str', 'Elevage': 'str', 'Eleve': 'str', 'Prelevable': 'str', 'Preleve': 'str', 'Changé': 'str', "Date d'envoi OI": 'str', 'Type document douanier if exists': 'str', 'Doc Id': 'str'})
    #CVI Opérateur
    csv[csv.columns[4]] = csv[csv.columns[4]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
    #SIRET Opérateur
    csv[csv.columns[5]] = csv[csv.columns[5]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
    #Millésime
    csv[csv.columns[25]] = csv[csv.columns[25]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
    csv.to_sql('pmcnc_lots', con=engine, if_exists='replace')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/changement_denomination.csv") and os.path.getsize(sys.argv[2]+"/changement_denomination.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/changement_denomination.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/changement_denomination.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype={'Identifiant': 'str', 'Campagne': 'str'})
    #CVI Opérateur
    csv[csv.columns[4]] = csv[csv.columns[4]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
    #SIRET Opérateur
    csv[csv.columns[5]] = csv[csv.columns[5]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
    #Origine Millésime
    csv[csv.columns[26]] = csv[csv.columns[26]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
    #Millésime
    csv[csv.columns[45]] = csv[csv.columns[45]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
    csv.to_sql('changement_denomination', con=engine, if_exists='replace')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/factures.csv") and os.path.getsize(sys.argv[2]+"/factures.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/factures.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/factures.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False)
    csv.to_sql('factures', con=engine, if_exists='replace')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/paiements.csv") and os.path.getsize(sys.argv[2]+"/paiements.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/paiements.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/paiements.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype={'Identifiant': 'str', 'Code comptable client': 'str', 'Execute': 'str', 'Exporte': 'str'})
    csv.to_sql('paiements', con=engine, if_exists='replace')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/degustations.csv") and os.path.getsize(sys.argv[2]+"/degustations.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/degustations.csv\n")
    csv = pd.read_csv(sys.argv[2]+"/degustations.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype={'Num archive': 'str', 'Code postal lieu': 'str', 'Id Opérateur': 'str', 'Adresse Opérateur': 'str', 'Code postal Opérateur': 'str', 'Commune Opérateur': 'str', 'Num dossier': 'str', 'Num lot': 'str', 'Num Table': 'str', 'Mention': 'str', 'Lieu': 'str', 'Cepage': 'str', 'Millésime': 'str', 'Date de conformité en appel': 'str'})
    csv.to_sql('degustations', con=engine, if_exists='replace')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");
