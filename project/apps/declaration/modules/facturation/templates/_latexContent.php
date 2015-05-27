<?php use_helper('TemplatingFacture'); ?>
<?php use_helper('Display'); ?>
\documentclass[a4paper, 10pt]{letter}
\usepackage[utf8]{inputenc} 
\usepackage[T1]{fontenc}
\usepackage[francais]{babel}
\usepackage[top=3cm, bottom=1.5cm, left=1cm, right=1cm, headheight=2cm, headsep=0mm, marginparwidth=0cm]{geometry}
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
\usepackage{multirow}
\usepackage[style=1]{mdframed}

\definecolor{vertclair}{rgb}{0.70,0.79,0.32}
\definecolor{vertfonce}{rgb}{0.17,0.29,0.28}
\definecolor{vertmedium}{rgb}{0.63,0.73,0.22}

\def\LOGO{<?php echo sfConfig::get('sf_web_dir'); ?>/images/logo_site.png}
\def\NUMFACTURE{<?php echo $facture->numero_ava; ?>}
\def\NUMADHERENT{<?php echo $facture->numero_adherent; ?>}
\def\EMETTEURLIBELLE{<?php echo $facture->emetteur->service_facturation; ?>}
\def\EMETTEURADRESSE{<?php echo $facture->emetteur->adresse; ?>}
\def\EMETTEURCP{<?php echo $facture->emetteur->code_postal; ?>}
\def\EMETTEURVILLE{<?php echo $facture->emetteur->ville; ?>}
\def\EMETTEURCONTACT{<?php echo $facture->emetteur->telephone; ?>}
\def\EMETTEUREMAIL{<?php echo $facture->emetteur->email; ?>}
\def\FACTUREDATE{Colmar, le <?php $date = new DateTime($facture->date_emission); echo $date->format('d/m/Y'); ?>}
\def\FACTUREDECLARANTRS{<?php echo wordwrap(escape_string_for_latex($facture->declarant->raison_sociale), 35, "\\\\\hspace{1.8cm}"); ?>}
\def\FACTUREDECLARANTADRESSE{<?php echo wordwrap(escape_string_for_latex($facture->declarant->adresse), 35, "\\\\\hspace{1.8cm}"); ?>}
\def\FACTUREDECLARANTCP{<?php echo $facture->declarant->code_postal; ?>}
\def\FACTUREDECLARANTCOMMUNE{<?php echo $facture->declarant->commune; ?>}
\def\FACTUREMEMBRERS{<?php echo escape_string_for_latex($facture->declarant->raison_sociale); ?>}
\def\FACTUREMEMBREADRESSE{<?php echo escape_string_for_latex($facture->declarant->adresse); ?>}
\def\FACTUREMEMBRECOMMUNE{<?php echo escape_string_for_latex($facture->declarant->commune); ?>}
\def\FACTURETOTALHT{<?php echo formatFloat($facture->total_ht); ?>}
\def\FACTURETOTALTVA{<?php echo formatFloat($facture->total_taxe); ?>}
\def\FACTURETOTALTTC{<?php echo formatFloat($facture->total_ttc); ?>}

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
\fancyfoot[C]{}

\begin{document}
	\begin{minipage}{0.5\textwidth}
	    \vspace{-1cm}
		\small{
		\EMETTEURLIBELLE \\
		\EMETTEURADRESSE \\
		\EMETTEURCP~\EMETTEURVILLE \\
		\EMETTEURCONTACT \\
		Email : \EMETTEUREMAIL
		}
	\end{minipage}
	\begin{minipage}{0.5\textwidth}
		\begin{flushleft}
		\vspace{1.6cm}
		\hspace{1.8cm}\FACTUREDECLARANTRS \\
		\hspace{1.8cm}\FACTUREDECLARANTADRESSE \\
		\vspace{2mm}
		\hspace{1.8cm}\FACTUREDECLARANTCP~\FACTUREDECLARANTCOMMUNE
		\end{flushleft}
	\end{minipage}
		\begin{flushleft}
		\vspace{3mm}
		\FACTUREDATE
		\end{flushleft}
	
\begin{center}
\renewcommand{\arraystretch}{1.2}
\arrayrulecolor{vertclair}
\begin{tabular}{|>{\raggedleft}m{1.9cm}|m{8.8cm}|>{\raggedleft}m{1.9cm}|>{\raggedleft}m{1.9cm}|>{\raggedleft}m{2.3cm}|}
  \hline
  Quantité & Libellés & Prix (€) & Sous-Total & Total \rule[-7pt]{0pt}{20pt} \tabularnewline
  \hline
  <?php foreach ($facture->lignes as $ligne): ?>
  & \textbf{<?php echo $ligne->libelle; ?>} \rule[7pt]{0pt}{11pt} & & & \textbf{<?php echo formatFloat($ligne->montant_ht); ?> €} \rule[7pt]{0pt}{11pt} \tabularnewline
  	<?php foreach ($ligne->details as $detail): ?>
  	    \small{\textit{<?php echo formatQuantite($detail->quantite); ?>}} & \small{\textit{<?php echo $detail->libelle; ?>}} & \small{\textit{<?php echo formatFloat($detail->prix_unitaire); ?>}} & \small{\textit{<?php echo formatFloat($detail->montant_ht); ?>}} &  \tabularnewline
  	<?php endforeach; ?>
  <?php endforeach; ?>
  \hline
  \end{tabular}
\begin{center}
\end{center}
\begin{tabular}{|>{\centering}p{9.4cm} >{\raggedleft}p{6cm}|>{\raggedleft}p{2.3cm}|}
  \hline
  \multirow{4}{*} {\begin{minipage}{6cm}Paiement sous 30 jours à réception \newline de facture, net et sans escompte\end{minipage}}  & \textbf{TOTAL HT} \rule[-5pt]{0pt}{18pt} & \textbf{\FACTURETOTALHT~€} \rule[-5pt]{0pt}{18pt} \tabularnewline
  <?php foreach ($facture->lignes as $ligne): ?>
  	<?php foreach ($ligne->details as $detail): ?>
  	<?php if ($detail->taux_tva): ?>
  	    & \textbf{TVA <?php echo $detail->taux_tva * 100; ?>\% sur <?php echo strtolower($ligne->libelle); ?>} \rule[-5pt]{0pt}{18pt} & \textbf{<?php echo formatFloat($detail->montant_tva); ?> €} \rule[-5pt]{0pt}{18pt} \tabularnewline
  	<?php endif; ?>
  	<?php endforeach; ?>
  <?php endforeach; ?>
  & \textbf{TOTAL TTC A PAYER} \rule[-5pt]{0pt}{18pt} & \textbf{\FACTURETOTALTTC~€} \rule[-5pt]{0pt}{18pt} \tabularnewline
  \hline
\end{tabular}	
\end{center}
\begin{center}
\small{
SIRET : 778 904 599 00033 - APE : 9412 Z - TVA Intracom. : FR 08 778 904 599
}
\end{center}
	\vspace{1mm}
    \begin{minipage}{0.5\textwidth}
    <?php if($facture->arguments->exist('CARTE_MEMBRE') && $facture->arguments->get('CARTE_MEMBRE')): ?>
        \begin{beamerframe}
		\begin{center}
			\vspace{3mm}
			\textbf{\underline{\large{\textsc{association des viticulteurs d'alsace}}}} \\
			Maison des Vins d'Alsace - Colmar \\
			\vspace{8mm}
			\textbf{\large{CARTE DE MEMBRE}} \\
			\vspace{1mm}
			\textbf{\large{Année 2015}} \\
		\end{center}
		\vspace{6.9mm}
		\begin{center}
			\FACTUREMEMBRERS \\
			\FACTUREMEMBREADRESSE \\
			\FACTUREMEMBRECOMMUNE \\
			N° adhérent : \NUMADHERENT \\
		\end{center}
		\vspace{3mm}
		\end{beamerframe}
    <?php else: ?>
        \begin{center}
        \end{center}
    <?php endif; ?>
	\end{minipage}
	\begin{minipage}{0.5\textwidth}
		\vspace{1.2cm}
		\begin{center}
			\textsc{Crédit Agricole Colmar Entreprises} \\
			IBAN : FR76 1720 6007 7049 1243 9001 072 \\
			BIC : AGRIFRPP872
		\end{center}
		\vspace{1.2cm}
		\begin{beamerframe}
		    \vspace{1mm}
			\begin{tabularx}{\linewidth}{X c c}
			\rowcolor{vertclair} \multicolumn{3}{c}{\textbf{\textcolor{vertfonce}{\textsc{partie à joindre au règlement}}}} \\
			\textsc{n° facture} & \textsc{n° adhérent} & \textsc{montant ttc} \rule[-7pt]{0pt}{20pt} \\
			\textbf{\NUMFACTURE} & \textbf{\NUMADHERENT} & \textbf{\FACTURETOTALTTC~€} \\
			\end{tabularx}
		    \vspace{1mm}
		\end{beamerframe}
	\end{minipage}

\end{document}