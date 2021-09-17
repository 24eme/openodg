<?php use_helper('TemplatingFacture'); ?>
<?php use_helper('Display'); ?>
<?php
			$coordonnees_bancaire = sfConfig::get('app_facture_coordonnees_bancaire');
?>
\documentclass[a4paper, 10pt]{letter}
\usepackage[utf8]{inputenc}
\usepackage[T1]{fontenc}
\usepackage[francais]{babel}
\usepackage[top=3cm, bottom=0.5cm, left=1cm, right=1cm, headheight=2cm, headsep=0mm, marginparwidth=0cm]{geometry}
\usepackage{fancyhdr}
\usepackage{graphicx}
\usepackage[table]{xcolor}
\usepackage{units}
\usepackage{fp}
\usepackage{tikz}
\usepackage{array}
\usepackage{multicol}
\usepackage{textcomp}
\usepackage{marvosym}
\usepackage{truncate}
\usepackage{colortbl}
\usepackage{tabularx}
\usepackage{multirow}
\usepackage[framemethod=tikz]{mdframed}

\newcommand{\CutlnPapillon}{\Rightscissors \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline  \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline  \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline  \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline
\\
}
\def\LOGO{<?php echo sfConfig::get('sf_web_dir'); ?>/images/logo_provence2.png}


\def\TYPEFACTURE{<?php if($facture->isAvoir()): ?>AVOIR<?php else:?>FACTURE<?php endif; ?>}
\def\NUMFACTURE{<?php echo sprintf("%05d",$facture->numero_archive); ?>}
\def\NUMADHERENT{<?php echo $facture->code_comptable_client; ?>}
\def\EMETTEURLIBELLE{<?php echo $facture->emetteur->service_facturation; ?>}
\def\EMETTEURADRESSE{<?php echo $facture->emetteur->adresse; ?>}
\def\EMETTEURCP{<?php echo $facture->emetteur->code_postal; ?>}
\def\EMETTEURVILLE{<?php echo $facture->emetteur->ville; ?>}
\def\EMETTEURCONTACT{<?php echo $facture->emetteur->telephone; ?>}
\def\EMETTEUREMAIL{<?php echo $facture->emetteur->email; ?>}

\def\FACTURECOTISATIONDATE{Cotisation <?php $date = new DateTime($facture->date_facturation); echo "".(intval($date->format('Y')) - 1 ); ?>}
\def\FACTUREDATE{Date de facture : <?php echo $date->format("d/m/Y"); ?>}
\def\FACTUREDECLARANTRS{<?php echo wordwrap(escape_string_for_latex($facture->declarant->raison_sociale), 35, "\\\\\hspace{1.8cm}"); ?>}
\def\FACTUREDECLARANTADRESSE{<?php echo wordwrap(escape_string_for_latex($facture->declarant->adresse), 35, "\\\\\hspace{1.8cm}"); ?>}
\def\FACTUREDECLARANTCP{<?php echo $facture->declarant->code_postal; ?>}
\def\FACTUREDECLARANTCOMMUNE{<?php echo escape_string_for_latex($facture->declarant->commune); ?>}
\def\FACTUREMEMBRERS{<?php echo escape_string_for_latex($facture->declarant->raison_sociale); ?>}
\def\FACTUREMEMBREADRESSE{<?php echo escape_string_for_latex($facture->declarant->adresse); ?>}
\def\FACTUREMEMBRECOMMUNE{<?php echo escape_string_for_latex($facture->declarant->commune); ?>}
\def\FACTURETOTALHT{<?php echo formatFloat($facture->total_ht); ?>}
\def\FACTURETOTALTVA{<?php echo formatFloat($facture->total_taxe); ?>}
\def\FACTURETOTALTTC{<?php echo formatFloat($facture->total_ttc); ?>}

\def\SIRET{<?php echo $coordonnees_bancaire['siret']; ?>}
\def\BANQUENOM{<?php echo $coordonnees_bancaire['banquenom']; ?>}
\def\BANQUEADRESSE{<?php echo $coordonnees_bancaire['banqueadresse']; ?>}
\def\RIB{<?php echo $coordonnees_bancaire['rib']; ?>}
\def\BIC{<?php echo $coordonnees_bancaire['bic']; ?>}

\definecolor{bg-carte-membre}{RGB}{220,220,220}

\pagestyle{fancy}
\renewcommand{\headrulewidth}{0cm}
\renewcommand\sfdefault{phv}
\renewcommand{\familydefault}{\sfdefault}
\fancyhead[L]{\includegraphics[scale=0.11]{\LOGO}}
\fancyhead[R]{ \large{\textbf{\EMETTEURLIBELLE} \\ \EMETTEURADRESSE \\
		\EMETTEURCP \\ \EMETTEURVILLE}}
\fancypagestyle{nofooter}{%
	  \fancyfoot{}%
}
  	\fancyfoot[C]{}

\begin{document}
	\begin{minipage}{0.5\textwidth}
  \small{
 N° SIRET : \SIRET \\
   \hspace{1.8cm}\BANQUENOM \\
   \hspace{1.8cm} \BANQUEADRESSE \\
   \hspace{1.8cm} \RIB \\
   \hspace{1.8cm} \BIC \\
	 \hspace{1.8cm} \FACTUREDATE
   }
	\end{minipage}
	\begin{minipage}{0.5\textwidth}
		\begin{flushleft}
      \vspace{1.6cm}
      \hspace{1.8cm}\FACTUREDECLARANTRS \\
      \vspace{2mm}
      \hspace{1.8cm}\FACTUREDECLARANTADRESSE \\
      \vspace{2mm}
      \hspace{1.8cm}\FACTUREDECLARANTCP~\FACTUREDECLARANTCOMMUNE \\
      \vspace{2mm}
      \hspace{1.8cm}\textbf{Compte client} : \NUMADHERENT
    \end{flushleft}
	\end{minipage}

  \begin{center}
    \Large{\textbf{\TYPEFACTURE~N°~\NUMFACTURE}} \\
    \vspace{3mm}
		\large{\FACTURECOTISATIONDATE}
  \end{center}


\begin{center}
\renewcommand{\arraystretch}{1.2}
\begin{tabular}{|>{\raggedright}m{7.0cm}|r|r|>{\raggedleft}m{2.8cm}|}
<?php $uniqLigne = $facture->getLignesForPdf(); ?>
  \hline
  Désignation & Volume revendiqué (en hl) & Px unitaire (en €/hl) & Montant \rule[-7pt]{0pt}{20pt} \tabularnewline
  \hline
   \rule[7pt]{0pt}{11pt}Cotisation incluant les droits INAO, la cotisation O.D.G, la cotisation de défense du nom et la cotisation pour l’O.I & & & \tabularnewline

  \small{\textit{Volume net revendiqué total}} & \small{\textbf{<?php echo number_format($uniqLigne->quantite, 2, '.', ' '); ?>}} & \small{\textbf{<?php echo number_format($uniqLigne->prix_unitaire, 2, '.', ' '); ?>}} & \small{\textbf{<?php echo number_format($uniqLigne->montant_ht, 2, '.', ' '); ?>~€}}  \tabularnewline


	~&~&~& \\
	~&~&~& \\
	~&~&~& \\
	~&~&~& \\
	~&~&~& \\
	~&~&~& \\
	~&~&~& \\
	~&~&~& \\
  \hline
  \end{tabular}
\\\vspace{6mm}
\begin{tabular}{>{\centering}p{11.2cm} |>{\raggedleft}p{3.4cm}|>{\raggedleft}p{2.8cm}|}
  \cline{2-3}
~ & \textbf{TOTAL HT} \rule[-5pt]{0pt}{18pt} & \textbf{\FACTURETOTALHT~€}
\rule[-5pt]{0pt}{18pt} \tabularnewline
			~ & \textbf{TVA\up{*}} \rule[-5pt]{0pt}{18pt} & \textbf{<?php echo formatFloat($uniqLigne->montant_tva); ?>~€}
\rule[-5pt]{0pt}{18pt} \tabularnewline

    	  	  	    & \textbf{TOTAL A PAYER} \rule[-5pt]{0pt}{18pt} & {\textbf{<?php echo formatFloat($facture->total_ttc); ?>~€}} \rule[-5pt]{0pt}{18pt} \tabularnewline
  \cline{2-3}
\end{tabular}
\end{center}
<?php if(!$facture->isAvoir()): ?>
	\vspace{1mm}
\begin{flushright}
\footnotesize{\textit{(*) La TVA de 20\% est uniquement calculée sur la base HT de la cotisation destinée à l'O.I (0.22 x le volume net revendiqué total)}}
\end{flushright}~\\
\normalsize{\underline{\textbf{Vous avez la possibilité de régler en une seule fois sous 30 jours où en respectant l'échéancier suivant :}}}
\\ \\
\begin{tabular}{lll}
<?php foreach ($facture->echeances as $e): ?>
	<?php echo $e->echeance_date; ?> & <?php echo $e->echeance_code ?> & <?php echo formatFloat($e->montant_ttc); ?>~€ \\
<?php endforeach; ?>
\end{tabular}


~\\~\\
\textit{En cas de retard de paiement des pénalités aux taux légal pourraient être appliquées conformément à l'article L441-6 du code du commerce.}
\\~\\~\\
\CutlnPapillon
\\
\renewcommand{\arraystretch}{1.2}
\begin{tabular}{|>{\raggedright}m{18.5cm}|}
  \hline \\
  Nom : \FACTUREDECLARANTRS \\
  Client : \NUMADHERENT \\
  Facture : \NUMFACTURE \\
  	\begin{center}
  	PARTIE DETACHABLE - A joindre OBLIGATOIREMENT à votre règlement
  	\end{center}
  \tabularnewline
  \hline
  \end{tabular}
<?php endif; ?>
\end{document}
