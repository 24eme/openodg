\documentclass[a4paper, 10pt]{letter}
\usepackage[utf8]{inputenc} 
\usepackage[T1]{fontenc}
\usepackage[francais]{babel}
\usepackage[top=3.5cm, bottom=1.5cm, left=1cm, right=1cm, headheight=4cm, headsep=5mm, marginparwidth=0cm]{geometry}
\usepackage{fancyhdr}
\usepackage{lastpage}
\usepackage{graphicx}
\usepackage[table]{xcolor}
\usepackage{units}
\usepackage{fp}
\usepackage{tikz}
\usepackage{array}
\usepackage{multicol}
\usepackage{textcomp}
\usepackage{marvosym}
\usepackage{lastpage}
\usepackage{truncate}
\usepackage{colortbl} 
\usepackage{tabularx}
\usepackage[style=1]{mdframed}

\definecolor{vertclair}{rgb}{0.70,0.79,0.32}
\definecolor{vertfonce}{rgb}{0.17,0.29,0.28}
\definecolor{vertmedium}{rgb}{0.63,0.73,0.22}

\def\LOGO{<?php echo sfConfig::get('sf_web_dir'); ?>/images/logo_site.png}
\def\NUMFACTURE{<?php echo $facture->numero_facture; ?>}
\def\NUMADHERENT{<?php echo $facture->numero_adherent; ?>}
\def\EMETTEURLIBELLE{<?php echo $facture->emetteur->service_facturation; ?>}
\def\EMETTEURADRESSE{<?php echo $facture->emetteur->adresse; ?>}
\def\EMETTEURCP{<?php echo $facture->emetteur->code_postal; ?>}
\def\EMETTEURVILLE{<?php echo $facture->emetteur->ville; ?>}
\def\EMETTEURCONTACT{<?php echo $facture->emetteur->telephone; ?>}
\def\EMETTEUREMAIL{<?php echo $facture->emetteur->email; ?>}
\def\FACTUREDATE{Colmar, le <?php $date = new DateTime($facture->date_emission); echo $date->format('dd/mm/YYYY'); ?>}
\def\FACTUREDECLARANTRS{<?php echo $facture->declarant->raison_sociale; ?>}
\def\FACTUREDECLARANTADRESSE{<?php echo $facture->declarant->adresse; ?>}
\def\FACTUREDECLARANTCP{<?php echo $facture->declarant->code_postal; ?>}
\def\FACTUREDECLARANTCOMMUNE{<?php echo $facture->declarant->commune; ?>}
\def\FACTURETOTALHT{<?php echo $facture->total_ht; ?>}
\def\FACTURETOTALTVA{<?php echo $facture->total_taxe; ?>}
\def\FACTURETOTALTTC{<?php echo $facture->total_ttc; ?>}

\newmdenv[tikzsetting={draw=vertclair,dashed,line width=1pt,dash pattern = on 10pt off 3pt},%
linecolor=white,backgroundcolor=white, outerlinewidth=1pt]{beamerframe}

\pagestyle{fancy}
\renewcommand{\headrulewidth}{0cm}
\renewcommand\sfdefault{phv}
\renewcommand{\familydefault}{\sfdefault}
\fancyhead[L]{\includegraphics[scale=0.5]{\LOGO}}
\fancyhead[R]{
\colorbox{vertclair}{\LARGE{\textbf{\textcolor{vertfonce}{FACTURE}}}} \\ 
\vspace{5mm}
N° facture : \textbf{\NUMFACTURE} \\
N° adhérent : \textbf{\NUMADHERENT}
}
\fancyfoot[C]{\thepage / \pageref{LastPage}}

\begin{document}
	\begin{minipage}{0.5\textwidth}
		\small{
		\EMETTEURLIBELLE \\
		\EMETTEURADRESSE \\
		\EMETTEURCP EMETTEURVILLE \\
		\EMETTEURCONTACT \\
		Email : \EMETTEUREMAIL
		}
	\end{minipage}
	\begin{minipage}{0.5\textwidth}
		\begin{flushright}
		\vspace{-2mm}
		\FACTUREDATE
		\end{flushright}
		\begin{flushleft}
		\vspace{7mm}
		\hspace{1.8cm}\FACTUREDECLARANTRS \\
		\hspace{1.8cm}\FACTUREDECLARANTADRESSE \\
		\vspace{2mm}
		\hspace{1.8cm}\FACTUREDECLARANTCP \FACTUREDECLARANTCOMMUNE
		\end{flushleft}
	\end{minipage}
	
\vspace{1.5cm}
\begin{center}
\renewcommand{\arraystretch}{1.2}
\arrayrulecolor{vertclair}
\begin{tabular}{r p{13.5cm} c|c}
  <?php foreach ($facture->lignes as $ligne): ?>
  \rowcolor{vertclair} \multicolumn{3}{l}{\textbf{\textcolor{vertfonce}{<?php echo $ligne->libelle; ?>}}} & \textbf{\textcolor{vertfonce}{<?php echo $ligne->montant_ht; ?> €}} \rule[-7pt]{0pt}{20pt} \\
  	<?php foreach ($ligne->details as $detail): ?>
  <?php echo $detail->quantite; ?> & <?php echo $detail->libelle; ?> & <?php echo $detail->prix_unitaire; ?> & <?php echo $detail->montant_ht; ?> \\
  	<?php endforeach; ?>
  <?php endforeach; ?>
  \rowcolor{vertclair} \multicolumn{3}{r}{\textbf{\textcolor{vertfonce}{\textsc{total}}}} & \textbf{\textcolor{vertfonce}{\FACTURETOTALHT €}} \\
  \rowcolor{vertclair} \multicolumn{3}{r}{\textbf{\textcolor{vertfonce}{\textsc{tva}}}} & \textbf{\textcolor{vertfonce}{\FACTURETOTALTVA €}} \\
  \rowcolor{vertclair} \multicolumn{3}{r}{\textbf{\textcolor{vertfonce}{\textsc{total ttc à payer}}}} & \textbf{\textcolor{vertfonce}{\FACTURETOTALTTC €}} \\
\end{tabular}	
\end{center}

	\vspace{1.5cm}
	\begin{minipage}{0.5\textwidth}
		\begin{beamerframe}
		\begin{center}
			\textbf{\underline{\large{\textsc{association des viticulteurs d'alsace}}}} \\
			Organisme de Défense et de Gestion des Appellations \\
			\small{Maison des Vins d'Alsace \\
			12 avenue de la Foire aux Vins - B.P. 91225 \\
			68012 COLMAR Cedex \\
			Téléphone 03 89 20 16 50 - Télécopie 03 89 20 16 60 \\
			Email : info@ava-aoc.fr} \\
			\vspace{2mm}
			\textbf{\large{CARTE DE MEMBRE}} \\
			\textbf{\large{Année 2014}} \\
		\end{center}
		\vspace{6mm}
		\begin{tabular}{r l}
			NOM : & EARL WEBER Bernard \\
			Adresse : & 49 rue de Saverne \\
			Commune : & MOLSHEIM \\
			N° adhérent : & 523 \\
		\end{tabular}
		\end{beamerframe}
	\end{minipage}
	\begin{minipage}{0.5\textwidth}
		\vspace{1.4cm}
		\begin{center}
			\textsc{Crédit Agricole Colmar Entreprises} \\
			17206 00770 49124390010 72 \\
			IBAN : FR76 1720 6007 7049 1243 9001 072 \\
			BIC : AGRIFRPP872
		\end{center}
		\vspace{1.4cm}
		\begin{beamerframe}
			\begin{tabularx}{\linewidth}{X c c}
			\rowcolor{vertclair} \multicolumn{3}{c}{\textbf{\textcolor{vertfonce}{\textsc{partie à joindre au règlement}}}} \\
			\textsc{n° facture} & \textsc{n° adhérent} & \textsc{montant ttc} \rule[-7pt]{0pt}{20pt} \\
			\textbf{\NUMFACTURE} & \textbf{\NUMADHERENT} & \textbf{\FACTURETOTALTTC €} \\
			\end{tabularx}
		\end{beamerframe}
	\end{minipage}

\end{document} 