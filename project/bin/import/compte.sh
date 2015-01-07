echo "000000#id;type_ligne;cvi;siren;siret;num_tva_intracom;civilite;raison_sociale;nom;prenom;famille;adresse_1;adresse_2;adresse_3;code_postal;commune;code_insee;cedex;date_archivage;date_creation"

mysql -u root -pboris97 AVA_base -N -e "
SELECT 
       LPAD(p.CODE_IDENT_SITE, 6, '0') as ID,
       '1.COMPTE' as TYPE_LIGNE, 
       '' as CVI, 
       p.SIREN as SIREN, 
       p.SIRET as SIRET, 
       c.NR_TVA_INTRA as TVA_INTRA,
       p.TITRE as CIVILITE, 
       IF(p.TYPE = 'M',p.RS,'') as RAISON_SOCIALE,
       IF(p.TYPE = 'P',p.RS,'') as NOM,
       IF(p.TYPE = 'P',p.PRENOM,'') as PRENOM,
       IF(p.TYPE = 'M',p.PRENOM,'') as FAMILLE,
       c.ADRESSE1 as ADRESSE_1,
       c.ADRESSE2 as ADRESSE_2,
       c.ADRESSE3 as ADRESSE_3,
       c.CODE_POSTAL as CODE_POSTAL,
       co.NCCCT as COMMUNE,
       c.COMMUNE as CODE_INSEE,
       c.CEDEX as CEDEX,
       '' as TEL,
       '' as FAX,
       '' as PORTABLE,
       '' as EMAIL,
       '' as WEB,
       '' as ATTRIBUTS,
       p.DATE_ARCHIVAGE as DATE_ARCHIVAGE, 
       p.DATE_CREATION as DATE_CREATION
FROM PPM p
LEFT JOIN COORDONNEES c ON c.CODE_IDENT_SITE = p.CODE_IDENT_SITE AND c.NR_ORDRE = 0
LEFT JOIN COMMUNE_FRANCAISE as co ON co.INSEE = c.COMMUNE
" | sed 's/\t/;/g'

mysql -u root -pboris97 AVA_base -N -e "
SELECT        LPAD(pe.CODE_IDENT_SITE_EXPLT, 6, '0') as ID,         '2.   CVI' as TYPE_LIGNE,         e.NR_EVV as CVI,         '' as SIREN,         '' as SIRET,        '' as TVA_INTRA,         '' as CIVILITE,         e.INTITULE as RAISON_SOCIALE,        '' as NOM,        '' as PRENOM,        '' as FAMILLE,        e.ADRESSE1 as ADRESSE_1,        e.ADRESSE2 as ADRESSE_2,        e.ADRESSE3 as ADRESSE_3,        e.CODE_POSTAL as CODE_POSTAL,        c.NCCCT as COMMUNE,        e.COMMUNE as CODE_INSEE,        e.CEDEX as CEDEX,        '' as TEL,        '' as FAX,        '' as PORTABLE,        '' as EMAIL,        '' as WEB,        '' as ATTRIBUTS,        '' as DATE_ARCHIVAGE,         '' as DATE_CREATION FROM EVV e INNER JOIN PPM_EVV_MFV as pe ON e.CLE_EVV = pe.CLE_EVV LEFT JOIN COMMUNE_FRANCAISE as c ON c.INSEE = e.COMMUNE;" | sed 's/\t/;/g'

mysql -u root -pboris97 AVA_base -N -e "
SELECT
       LPAD(pe.CODE_IDENT_SITE, 6, '0') as ID, 
       '3.  CHAI' as TYPE_LIGNE, 
       '' as CVI, 
       '' as SIREN, 
       '' as SIRET,
       '' as TVA_INTRA, 
       '' as CIVILITE, 
       IF(c.ADRESSE1 = '', '', c.INTITULE) as RAISON_SOCIALE,
       '' as NOM,
       '' as PRENOM,
       c.TYPE_CHAI as FAMILLE,
       IF(c.ADRESSE1 = '', c.INTITULE, c.ADRESSE1) as ADRESSE_1,
       c.ADRESSE2 as ADRESSE_2,
       c.ADRESSE3 as ADRESSE_3,
       c.CODE_POSTAL as CODE_POSTAL,
       co.NCCCT as COMMUNE,
       c.COMMUNE as CODE_INSEE,
       c.CEDEX as CEDEX,
       '' as TEL,
       '' as FAX,
       '' as PORTABLE,
       '' as EMAIL,
       '' as WEB,
       CONCAT(IF(c.CHAI_DE_VINIFICATION > 0, 'CHAI_DE_VINIFICATION,', ','),IF(c.CENTRE_DE_CONDITIONNEMENT > 0, 'CENTRE_DE_CONDITIONNEMENT,', ','),IF(c.LIEU_DE_STOCKAGE > 0, 'LIEU_DE_STOCKAGE,', ','),IF(c.centre_de_pressurage > 0, 'CENTRE_DE_PRESSURAGE,', ',')) as ATTRIBUTS,
       '' as DATE_ARCHIVAGE, 
       '' as DATE_CREATION
FROM CHAI c
LEFT JOIN COMMUNE_FRANCAISE as co ON co.INSEE = c.COMMUNE
INNER JOIN PPM_EVV_CHAI as pe ON pe.CLE_CHAI = c.CLE_CHAI
INNER JOIN EVV as e ON e.CLE_EVV = pe.CLE_EVV
" | sed 's/\t/;/g'

mysql -u root -pboris97 AVA_extra -N -e "
SELECT
       LPAD(a.CODE_IDENT_SITE, 6, '0') as ID, 
       '4.ATTRIB' as TYPE_LIGNE, 
       '' as CVI, 
       '' as SIREN, 
       '' as SIRET,
       '' as TVA_INTRA, 
       '' as CIVILITE, 
       '' as RAISON_SOCIALE,
       '' as NOM,
       '' as PRENOM,
       '' as FAMILLE,
       '' as ADRESSE_1,
       '' as ADRESSE_2,
       '' as ADRESSE_3,
       '' as CODE_POSTAL,
       '' as COMMUNE,
       '' as CODE_INSEE,
       '' as CEDEX,
       '' as TEL,
       '' as FAX,
       '' as PORTABLE,
       '' as EMAIL,
       '' as WEB,
       r.LIBELLE_ATTRIBUT as ATTRIBUTS,
       '' as DATE_ARCHIVAGE, 
       '' as DATE_CREATION
FROM PPM_ATTRIBUTS a
LEFT JOIN PPM_ATTRIBUT_REF r ON r.CLE_ATTRIBUT = a.ATTRIBUT
" | sed 's/\t/;/g'

mysql -u root -pboris97 AVA_base -N -e "
SELECT
       LPAD(c.CODE_IDENT_SITE, 6, '0') as ID, 
       '5.COMMUN' as TYPE_LIGNE, 
       '' as CVI, 
       '' as SIREN, 
       '' as SIRET,
       '' as TVA_INTRA, 
       '' as CIVILITE, 
       '' as RAISON_SOCIALE,
       '' as NOM,
       '' as PRENOM,
       c.LIB_COORDONNEES as FAMILLE,
       '' as ADRESSE_1,
       '' as ADRESSE_2,
       '' as ADRESSE_3,
       '' as CODE_POSTAL,
       '' as COMMUNE,
       '' as CODE_INSEE,
       '' as CEDEX,
       c.TEL as TEL,
       c.FAX as FAX,
       c.PORTABLE as PORTABLE,
       c.EMAIL as EMAIL,
       c.WEB as WEB,
       '' as ATTRIBUTS,
       '' as DATE_ARCHIVAGE, 
       '' as DATE_CREATION
FROM COMMUNICATION c
WHERE c.CLE_COORDONNEES > 0
" | sed 's/\t/;/g'

