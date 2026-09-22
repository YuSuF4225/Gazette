
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+01:00";


--
-- Base de données :  `gazette_bd`
--

CREATE USER IF NOT EXISTS 'gazette_user'@'%' IDENTIFIED BY 'gazette_pass';

CREATE DATABASE IF NOT EXISTS `gazette_bd` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;

GRANT SELECT, INSERT, UPDATE, DELETE ON `gazette_bd`.* TO "gazette_user"@"%";

USE `gazette_bd`;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `utPseudo` char(20) COLLATE utf8_unicode_ci NOT NULL,
  `utNom` char(50) COLLATE utf8_unicode_ci NOT NULL,
  `utPrenom` char(60) COLLATE utf8_unicode_ci NOT NULL,
  `utEmail` char(255) COLLATE utf8_unicode_ci NOT NULL,
  `utPasse` char(255) COLLATE utf8_unicode_ci NOT NULL,
  `utDateNaissance` int(8) unsigned NOT NULL,
  `utRedacteur` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `utCivilite` ENUM('h','f', 'nb') COLLATE utf8_unicode_ci NOT NULL,
  `utMailsPourris` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY(`utPseudo`),
  UNIQUE KEY utEmail (`utEmail`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`utPseudo`, `utNom`, `utPrenom`, `utEmail`, `utPasse`, `utDateNaissance`, `utRedacteur`, `utCivilite`, `utMailsPourris`) VALUES
('akuz', 'Kuzbidon', 'Alex', 'alex.kuzbidon@gazette-linfo.fr', '$2y$10$hJm2k.p8vz65DKpcAeNUAe0wv7QHifUzeNmInBAdVGDhK6MdYXTB2', 19740627, 1, 'h', 0),
('emerlet', 'Merlet', 'Éric', 'eric.merlet@univ-fcomte.fr', '$2y$10$hJm2k.p8vz65DKpcAeNUAe0wv7QHifUzeNmInBAdVGDhK6MdYXTB2', 19700528, 0, 'h', 0),
('jube', 'Bernard', 'Julien', 'julien.bernard@univ-fcomte.fr', '$2y$10$CaOMMrYi6hwgmCL5g8CxVexwyXwOLmweO3byxfAfcDnHW/GQHBswO', 19810126, 0, 'h', 1),
('freddd', 'Dadeau', 'Fred', 'frederic.dadeau@univ-fcomte.fr', '$2y$10$hJm2k.p8vz65DKpcAeNUAe0wv7QHifUzeNmInBAdVGDhK6MdYXTB2', 19800630, 0, 'h', 0),
('jhenriet', 'Henriet', 'Julien', 'julien.henriet@univ-fcomte.fr', '$2y$10$CaOMMrYi6hwgmCL5g8CxVexwyXwOLmweO3byxfAfcDnHW/GQHBswO', 19771119, 0, 'h', 1),
('adugois', 'Dugois', 'Anthony', 'anthony.dugois@univ-fcomte.fr', '$2y$10$CaOMMrYi6hwgmCL5g8CxVexwyXwOLmweO3byxfAfcDnHW/GQHBswO', 19980217, 0, 'h', 1),
('jbigoude', 'Bigoude', 'Johnny', 'johnny.bigoude@gazette-linfo.fr', '$2y$10$hJm2k.p8vz65DKpcAeNUAe0wv7QHifUzeNmInBAdVGDhK6MdYXTB2', 19630217, 1, 'h', 0),
('kdiot', 'Diot', 'Kelly', 'kelly.diot@gazette-linfo.fr', '$2y$10$hJm2k.p8vz65DKpcAeNUAe0wv7QHifUzeNmInBAdVGDhK6MdYXTB2', 19870917, 1, 'f', 0),
('noussachons', 'Sachons', 'Nous', 'nous.sachons@complots-faciles.com', '$2y$10$hJm2k.p8vz65DKpcAeNUAe0wv7QHifUzeNmInBAdVGDhK6MdYXTB2', 19900317, 0, 'h', 0),
('yjourdelesse', 'Jourdelesse', 'Yves', 'yves.jourdelesse@gazette-linfo.fr', '$2y$10$hJm2k.p8vz65DKpcAeNUAe0wv7QHifUzeNmInBAdVGDhK6MdYXTB2', 19911209, 1, 'h', 0),
('pheupakeur', 'Heupakeur', 'Pete', 'pete.heupakeur@gazette-linfo.fr', '$2y$10$hJm2k.p8vz65DKpcAeNUAe0wv7QHifUzeNmInBAdVGDhK6MdYXTB2', 19890503, 1, 'h', 1);

-- --------------------------------------------------------
--
-- Structure de la table `article`
--

CREATE TABLE `article` (
  `arID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `arTitre` char(150) COLLATE utf8_unicode_ci NOT NULL,
  `arResume` text COLLATE utf8_unicode_ci NOT NULL,
  `arTexte` text COLLATE utf8_unicode_ci NOT NULL,
  `arDatePubli` bigint(12) unsigned NOT NULL,
  `arDateModif` bigint(12) unsigned DEFAULT NULL,
  `arAuteur` char(20) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`arID`),
  FOREIGN KEY(`arAuteur`) REFERENCES `utilisateur`(`utPseudo`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `article`
--

INSERT INTO `article` (`arID`, `arTitre`, `arTexte`, `arResume`, `arDatePubli`, `arDateModif`, `arAuteur`) VALUES

(1, 'Sondage : allez-vous réussir votre année ?', '[p]Avec plus de 500 réponses, et après dépouillement des votes, 
 voici les résultats de notre grand sondage : allez-vous réussir votre année ? [/p][liste][item] Oui, j\'y crois, je suis motivé 
 comme jamais et j\'ai super-bien bossé (55%)[/item] [item]Bof, je sais pas trop, on verra bien (42%)[/item][item]Peut-être mais uniquement grâce aux compensations (8%)[/item][item]Oui, car je vais glisser quelques billets dans ma copie (4%)[/item][item]Sûrement, j\'ai des vidéo compromettantes du prof d\'algo (0.2%) [/item][/liste] [p] Les résultats ont été commentés par Frédéric Dadeau, directeur du département informatique :[/p] [citation]    Mouais bof, à part la dernière réponse, ce n\'est pas vraiment différent des années précédentes.[/citation][p]Nous ne pouvons que le remercier pour son analyse. [/p]', 'Comme chaque année, retrouvez les résultats de notre sondage de la rentrée, dont les résultats ont enfin été dépouillés par nos mains expertes.', 202301221056, NULL, 'jbigoude'),

(2, 'Il leur avait annoncé "Je vais vous défoncer" l\'enseignant relaxé', '[p]Pour Julien, cette journée de septembre était comme les autres. Après avoir démarré son TD de Théorie des Langages à 9h30, le brillant trentenaire a, d\'un coup, "pété un plomb" quand il a constaté, 15 secondes après avoir distribué le sujet, que seulement 90% des étudiants s\'étaient mis au travail et commençaient à chercher le premier exercice.[/p][citation]C\'est à ce demander ce que les autres attendaient. Du coup, mon sang n\'a fait qu\'un tour et je me suis laissé emporter.[/citation][p]reconnait l\'accusé dans son box.[/p][p]Déjà sous le coup d\'une mise à l\'épreuve pour avoir, l\'an passé, été reconnu responsable d\'un pantalon souillé à la simple vue de son sujet d\'Algorithmique et Structure de Données, Julien leur a asséné cette phrase qui en a choqué plus d\'un :[/p] [citation] Vous allez voir, à l\'exam, je vais vous défoncer. [/citation] [p] Des mots rudes, qui ont été pris personnellement par William (le prénom a été modifié pour préserver son anonymat). Ce dernier a été retrouvé, après 3 jours d\'inquiétante disparition, prostré au fond de la salle 501C, en boule et dos au mur.[/p][p]Absent pour ne pas avoir à croiser le regard de son tortionnaire, le témoignage de William aurait peut-être pu changer l\'issue du procès. Le juge a en effet estimé qu\'en l\'état, [it]"il n\'y a pas mort d\'homme"[/it] et que [it]"ça ne leur fait pas de mal à ces petits jeunes de se faire malmener un peu de temps en temps"[/it].[/p] [p] Malgré le réquisitoire du procureur de la république, qui demandait 6 mois de TIG au service des emplois du temps, Julien est sorti libre. [/p]', 'Jugé vendredi dernier pour avoir proféré des menaces un peu rudes, le professeur d\'algorithmique et structures de données a obtenu le relaxe dans une affaire qui devrait faire jurisprudence. ', 202311141247, NULL, 'kdiot'),

(3, 'Qui se cache derrière la Gazette de L-INFO ?', '[p]On n\'en a vraiment aucune idée.[/p]\r\n\r\n[p]Si l\'un de nos lecteurs possède la moindre information, il peut nous écrire à l\'adresse [a:mailto:contact@gazette-linfo.fr?subject=Moi%20je%20sais%20]contact@gazette-linfo.fr[/a]. [/p]\r\n\r\n[p]Nous vous remercions par avance pour vos nombreux témoignages.[/p]', 'Depuis plusieurs semaines, les rumeurs vont bon train. Un point sur les dernières nouvelles concernant les \"petits rigolos\" qui se cachent derrière la rédaction de la Gazette de L-INFO.', 202311261243, 202311291610, 'jbigoude'),


(4, 'Donald Trump veut importer les CMI aux Etats-Unis', '[p]En marge de son dernier déplacement en France, Donald Trump, est venu visiter en catimini l\'Université de Franche-Comté et notamment la filière informatique. À peine de retour dans la Trump Tower, il a promis d\'inscrire dans la constitution américaine un nouvel amendement, s\'il était réélu. Celui-ci aurait pour objectif d\'imposer à toutes les universités américaines de remplacer les cursus universitaires traditionnels par des Cursus Master en Ingénierie à la Française.[/p]\r\n\r\n[p]Le responsable du CMI, Emile Carry, a accueilli cette promesse avec le plus grand enthousiasme :[/p]\r\n\r\n[citation]Donald [Trump, ndlr] m\'a appelé depuis la Trump Tower, alors que j\'étais en train de tremper ma biscotte beurrée dans mon thé à la bergamotte. J\'étais très supris. Et voilà qu\'il se met à me parler du Réseau FIGURE et des CMI ![br]\r\nIl m\'a dit qu\'il trouvait l\'idée excellente et qu\'il souhaitait l\'imposer dans les université Américaines, rendez-vous compte ! Quelle belle récompense ce serait pour le CMI ![/citation]\r\n\r\n[p]Des émissaires des prestigieuses universités de Harvard, Yale, ou encore Berkeley sont attendus à la rentrée prochaine sur le campus de la Bouloie pour tout apprendre des CMI, et importer outre-atlantique la recette qui a fait le succès que l\'on lui connaît aujourd\'hui.[/p]', 'Récemment en visite en France, D. Trump a été très impressionné par les étudiants du Cursus Master en Ingénierie. À tel point qu\'il a promis, en cas d\'élection, d\'en importer quelques uns sur le territoire américain pour lancer quelques essais cliniques. ', 202311301058, NULL, 'jbigoude'),

(5, 'Le calendier des Dieux de la Licence bientôt disponible', '[p]À l\'instar du calendrier des Dieux du Stade dont les bénéfices sont reversés à diverses associations caritatives, le département info a choisi un moyen original de renflouer ses caisses.[/p]\r\n\r\n[p]Frédéric Dadeau, directeur du département info, est à l\'origine de cette initiative :[/p]\r\n\r\n[citation]En ces temps de situation budgétaire serrée, c\'est vraiment à nous, enseignants-chercheurs d\'aller chercher les financements là où ils sont. Voyant l\'engouement pour des sportifs dénudés, on s\'est dit que l\'on pourrait très bien, nous aussi, exhiber nos muscles les plus développés, c\'est-à-dire nos cerveaux, dans le plus simple appareil. [/citation]\r\n\r\n[p]Le résultat est étonnant : au fil des pages du calendrier, des crânes chauves, dégarnis, ou rasés pour l\'occasion, s\'enchaînent dans des poses toutes plus suggestives les unes que les autres.[/p]\r\n\r\n[p]Malgré l\'excellence de cette idée, le calendrier a pris du retard dans sa réalisation. A qui la faute ? A une bande de frondeurs, menée par notre typographe Yves Jourdelesse, qui n\'étaient pas d\'accord pour se faire tondre spécialement pour l\'occasion :[/p]\r\n\r\n[citation]Vous êtes gentils, mais moi, mes cheveux, j\'y tiens. Les autres, ils ont déjà perdu les leurs, ils s\'en fichent, mais moi j\'ai encore des poils sur le caillou, j\'aimerais bien les garder ! [/citation]\r\n\r\n[p]Remis dans le droit chemin, Yves et ses petits amis ont été sévèrement punis et le calendrier pourra être édité pour les fêtes de fin d\'année. Il sera en vente au prix de 18 euros, avec une offre promotionnelle spéciale pour les étudiants de Licence, qui le recevront automatiquement avec leur supplément au diplôme en fin d\'année. [/p]', 'Comme chaque année à la même période, le traditionnel calendrier des Dieux de la Licence Info va bientôt atterrir dans les kiosques. Son objectif : financer le coût de la formation, plombé par les trop nombreux redoublements des étudiants en 1ère et 2e année.', 202412141028, NULL, 'jbigoude'),

(6, 'Une arnarque au corrigé de TL mise à jour', '[p] Depuis 4 ans, une bande d\'arnaqueurs bien organisée sévit dans la filière informatique de l\'université de Franche-Comté.[/p][p]La procédure est simple : un faux étudiant prend contact individuellement avec les membres d\'une promotion de Licence Info 3e année, en se faisant passer pour un redoublant. Il appâte les primo-entrants avec une proposition alléchante qui ne peut pas se refuser : l\'intégralité des devoirs corrigés de Théorie des Langages, en échange d\'une somme rondelette.[/p][p]Intéressé par se faire bien voir de ses enseignants, Jean-Luc (le prénom a été changé pour préserver son anonymat) a cédé à la tentation :[/p][citation]J\'ai déboursé 3000 euros, déposés sur un compte aux îles Caïman. Ils disaient que j\'allais recevoir tous les corrigés des examens de TL de cette année.[/citation][p]Cette matière, très théorique, est en effet la bête noire des étudiants de L3 Informatique. Ces arnaqueurs l\'ont bien compris. Pourtant Jean-Luc a vite déchanté :[/p][citation] J\'ai reçu un corrigé très incomplet de l\'examen de 2003. Quand j\'ai voulu me plaindre, c\'était trop tard. J\'ai vraiment gaspillé mon argent. [/citation] [p]Jean-Luc n\'est malheureusement pas le seul à s\'être fait arnaquer ainsi. Au fil des années, on estime à plus de 400.000 euros la fortune amassée par ces brigands. Même les profs se sont faits avoir :[/p][citation] Je n\'arrivais plus à déterminiser l\'automate de la question 3 de mon propre sujet. Du coup, j\'ai choisi la facilité : j\'ai payé... J\'ai honte.[/citation] [p]nous confie Julien B. qui a souhaité conserver l\'anonymat.[/p][p]Pour démanteler cette économie parallèle de contrebande, nous avons travaillé en étroite collaboration avec les responsables de la filière. Frédéric Dadeau, directeur du département info, nous a donné accès au matériel de pointe. Nous nous sommes fait passer pour un étudiant fraîchement inscrit dans le cursus, et nous avons commencé à nous faire remarquer par quelques questions pointues sur le forum Moodle de TL.[/p][p]La réaction ne s\'est pas fait tarder. A peine quelques heures après avoir demandé si les automates du cours pouvaient jouer de la musique ou faire la vaisselle, nous avons été contactés en message privé sur Moodle par un des membres du-dit gang.[/p][citation] Si ça tintéresse, je te propose le corrigé du premier exo, gratuit, pour essayer. [/citation][p]Pour gagner la confiance de notre interlocuteur, nous sommes rentrés dans son jeu. Pendant 3 semaines, il nous a fourni, en augmentant la somme à chaque proposition, les corrigés des exercices de TL d\'une prétendue interro à venir.[/p][p]Cherchant à le coincer, nous lui avons donné rendez-vous au square Saint-Amour à Besançon, au pied de la Citadelle, pour conclure une importante transaction : la remise contre euros sonnants et trébuchants du sujet et du corrigé de l\'examen de TL de 1ère session.[/p][p]Malheureusement, le jour du RDV, à l\'heure convenue, personne ne s\'est présenté. Peut-être ont-ils pris peur en voyant arriver Fabien Peureux, qui passait là [it]"par pur hasard, vraiment"[/it]. Gageons que sa présence aura découragé les agissements de ces voyous, dont nous n\'avons plus jamais entendu parler par la suite. [/p]', 'Notre reporter a suivi la piste d\'une arnaque bien huilée qui a cours depuis plusieurs années en 3e année de Licence Informatique. Contre monnaie sonnante et trébuchante, un faussaire revendait des faux-corrigés des devoirs de Théorie des Langages aux étudiants.', 202501050755, 202501050815, 'kdiot'),

(7, 'Une famille de pingouins s\'installe dans l\'amphi B', '[p]Ce n\'est un secret pour personne, le système de chauffage de l\'UFR ST est possédé par un esprit farceur et semble hors de tout contrôle : bâtiments surchauffés en plein été, climatisation activée au mois de Décembre, nombreuses sont les incohérences de fonctionnement.[/p][citation] On a refusé d\'essayer de comprendre. Chacun a désormais une seconde garde-robe, dans son bureau, pour être prêt en toutes circonstances tout au long de l\'année.[/citation] [p]commente le directeur adjoint aux locaux de l\'UFR.[/p] [p] L\'amphithéatre B dans lequel les étudiants de L2 ont souvent cours n\'échappe pas à cette règle. Victime continuelle d\'un froid polaire toute l\'année, celui-ci a été le théatre d\'une découverte inattendue. Frédéric Dadeau nous raconte :[/p] [citation] J\'étais en train de remballer vite-fait mes affaires à la fin du cours de Web3 de 2e année. Mon attention a été attirée par un petit couinement venant de derrière les tableaux. Je me suis alors approché, j\'ai fait remonter les tableaux, je me suis penché et c\'est là que je les ai vus.[/citation] [p] Au milieu des restes de craies tombées au champ d\'honneur, toute une famille de pingouins avait en effet élu domicile dans cette zone glaciale et bien à l\'abri des regards.[/p][citation] Ces animaux sont les premiers à souffrir du réchauffement climatique.[/citation] [p]commente un chercheur du Laboratoire Chrono-Environnement, dépêché sur place pour l\'occasion. Il poursuit :[/p][citation]Il va falloir s\'attendre à voir débarquer dans nos régions de plus en plus d\'espèces menacées, chassées par les conséquences écologiques désastreuses de l\'activité humaine.[/citation] [p]Comment ces animaux sont-ils arrivés là ? Mystère. En attendant, la zone a été protégée, et un soigneur a été spécialement détaché du parc zoologique de la Citadelle de Besançon pour veiller aux bons soins de ces locataires inattendus. Celui-ci a par ailleurs observé que la femelle semble être en gestation et pourrait ainsi mettre bas pendant la période des examens de 2e chance en janvier prochain. Le plus grand silence et le plus grand calme sera alors exigé des étudiants pour ne pas déranger la naissance des bébés pingouins.[/p]', 'Les températures glaciales relevées au retour des vacances de fin d\'année dans l\'amphi B n\'ont pas fait que des malheureux. Une famille de pinguoins a décidé de s\'installer dans cet amphithéâtre et d\'y élire domicile en vue de fonder un foyer.', 202501270906, NULL, 'jbigoude'),

(8, 'L\'amphi A bientôt renommé Amphi Mélenchon', '[p] Après les amphis de Propédeutique, les amphis A et B restaient pour l\'instant anonymes. Ce sera désormais chose du passé pour l\'amphi A.[/p][p]En souvenir de la jeunesse Bisontine du leader de la France Insoumise, l\'amphithéatre A sera rebaptisé Amphithéatre Jean-Luc Mélenchon.[/p][citation] C\'est un honneur pour moi. J\'ai toujours su que l\'université, et en particulier celle de Besançon, que j\'ai fréquentée dans ma jeunesse, était un repaire de dangereux gauchistes. Mais de là à l\'afficher aussi clairement, c\'est très courageux de leur part par les temps qui courrent.[/citation][p]
En préambule de la cérémonie qui aura lieu le 16 octobre, une semaine complète de festivités sera organisée. Des portraits de Jean-Luc seront placés dans toutes les salles de TD, et les étudiants devront chanter l\'Internationale, poing levé, à chaque début de cours.[/p][p]Il se murmure même que Jean-Luc Mélenchon pourrait établir son QG de campagne pour 2027 dans les locaux de l\'Aqua. [/p]', 'L\'un des derniers amphis "anonymes" de l\'UFR ST est en passe d\'être baptisé par un patronyme des plus inattendus. En effet, Jean-Luc Mélenchon donnerait son nom à l\'actuel Amphi A, pour son plus grand plaisir.', 202502011058, 202502011244, 'kdiot'),

(9, 'Votez pour l\'hymne de la Licence', '[p] Le jeu concours de ce mois de février propose de choisir l\'hymne de la Licence Informatique. A l\'initiative de cette élection, son directeur nous explique : [/p] [citation] Je pense que c\'est important d\'avoir un hymne derrière lequel tout le monde peut se retrouver. Regardez les Anglais avec [it]"God Save the Queen"[/it]. Ils peuvent être en désaccord sur tout, mais dès que les premières notes retentissent, ils ne font qu\'un. Pour la Licence Info, c\'est la même idée. Cela participera à la cohésion du groupe. [/citation] [p]La sélection de cette année, comme il nous le précise, n\'a rien du hasard. [/p][citation] On a effectué une pré-sélection importante, et finalement, on arrive avec 3 morceaux représentatifs, qui proposent des lectures à plusieurs niveaux. Tout d\'abord, il était important que ce soit un hymne en anglais, les étudiants étant attendus d\'avoir le niveau C1 en fin de cursus. Pour varier, on a donc choisi des Australiens, des Anglais et des Américains. On a ensuite sélectionné des groupes, et non des artistes solo, puisque la filière info se veut renforcer l\'esprit de groupe et non juxtaposer des individus. Concernant le type de musique, le choix s\'est porté sur des morceaux plutôt rock, voire métal. L\'informaticien de base étant un g33k solitaire, c\'est le type de musique, relativement recherché du point de vue des harmonies, avec des paroles profondes, qu\'il affectionne tout particulièrement. [br] Pour finir, nous laissons aux étudiants le choix entre des chansons qui peuvent sembler négatives au premier abord, mais se révèlent finalement, à l\'écoute des paroles, être d\'un optimisme débordant. [/citation] [widget-deezer:250:148:https://widget.deezer.com/widget/dark/track/92719900 AC/DC - Highway To Hell] [widget-deezer:250:148:https://widget.deezer.com/widget/dark/track/585864292 Iron Maiden - Wasted Years] [widget-deezer:250:148:https://widget.deezer.com/widget/dark/track/2114406 Metallica - Nothing Else Matters] [p] Si cette initiative peut sembler quelque peu farfelue, il faut savoir que les équipes pédagogiques prennent très au sérieux l\'investissement et l\'adhésion des étudiants à cette nouveauté : [/p] [citation] Vous remarquerez que chacune de ces chansons, comme tout hymne qui se respecte, est reconnaissable dès les premières notes. On effectuera des tests en balançant à l\'improviste cet hymne dans les amphis pour voir si les étudiants se lèvent, la main sur le coeur et se mettent à chanter d\'une seule voix. On mesurera le temps de réaction et on punira sévèrement tous ceux qui feront de la résistance. [/citation] [p] Pour voter, cliquez sur les liens ci-dessous pour envoyer un mail prérempli avec votre choix (un seul mail par personne sera pris en compte) : [/p] [liste][item][a:mailto:contact@gazette-linfo.fr?subject=Vote%20hymne%20Licence&body=Je%20vote%20pour%20le%201]Voter pour AC/DC - Highway To Hell[/a][/item] [item][a:mailto:contact@gazette-linfo.fr?subject=Vote%20hymne%20Licence&body=Je%20vote%20pour%20le%202]Voter pour Iron Maiden - Wasted Years[/a][/item] [item][a:mailto:contact@gazette-linfo.fr?subject=Vote%20hymne%20Licence&body=Je%20vote%20pour%20le%203]Voter pour Metallica - Nothing Else Matters[/a][/item][/liste] [p] Pour rappel, notre dernier jeu concours consistait à trouver une chanson associée à Julien Bernard. Notre sondage a enregistré un taux de participation record (plus de 400 réponses). Plus des [#xBE] des participants ont voté pour le titre de [a:https://deezer.page.link/KL9WwthWX4kFUxAY7]Blue Oyster Cult [it]"Don\'t fear the reaper"[/it][/a].[/p]', 'En ce début d\'année, les responsables de la Licence Info invitent leurs étudiants à élire la chanson qui deviendra l\'hymne de leur diplôme. La compétition fait rage entre les morceaux choisis. ', 202502041423, NULL, 'jbigoude'),

(10, 'Un mouchard dans un corrigé de Web1', '[p] Une bien curieuse affaire a secoué la semaine dernière le module de Web1. En effet, un fichier diffusé en guise de correction espionnait en cachette le contenu des ordinateurs personnels des étudiants. [/p] [p] La nouvelle a fait grand bruit dans une période où le respect des données personnelles et de la confidentialité était au centre des débats. Le responsable de l\'UE, Julien Henriet, nous assure : [/p] [citation] Je peux vous garantir que je n\'y suis pour rien, et je ne comprends pas ce qui a pu se passer ! [/citation] [p]En cause, un banal fichier HTML diffusé aux étudiants comme solution au TP1. Sans crier gare, à l\'ouverture de ce script, un message d\'alerte s\'affichait indiquant [it]Vous avez été hacké ![/it] <script>alert("Vous avez été hacké !"); document.location.replace("https://fr.wikipedia.org/wiki/Cross-site_scripting#Protection_contre_les_XSS");</script>.[/p] [p]Les chercheurs en sécurité informatique du DISC se sont penchés sur ce script, qui, s\'il semble inoffensif au premier abord, révèle un lourd secret. Julien Bernard, expert en sécurité, nous explique : [/p] [citation]On pourrait penser que ce programme n\'est qu\'une menace en l\'air, comme on aime à en prononcer, mais il n\'en est rien. Le script effectue un scan minutieux des cookies de l\'utilisateur et profite d\'une faille de sécurité au niveau des instructions bas niveau du processeur pour ouvrir une backdoor qui sert ensuite à un troyan qui et envoie toutes les données de l\'étudiant vers un site en Corée du Nord. Nous avons remonté la piste et récupéré les données qui ont été collectées durant plusieurs mois. Nous sommes en train de les analyser. On verra bien ce qu\'il en ressort.[/citation] [p]Plusieurs étudiants, blêmes, nous ont confié leurs inquiétudes :[/p] [citation]C\'est à dire que... euh... je garde sur mon ordi des vidéos un peu...personnelles... J\'espère qu\'elles ne sont pas parties en Corée du Nord...[/citation]', 'En ce début de semaine, un corrigé frauduleux a été diffusé sur le Moodle du cours de Web1. S\'il pouvait à première vue sembler s\'agir de pages web tout à fait classiques, la réalité est toute autre. Le code contenait un très discret logiciel espion destiné à dérober les données les plus sensibles des étudiants.', 202502220915, NULL, 'jbigoude');

-- --------------------------------------------------------

--
-- Structure de la table `commentaire`
--

CREATE TABLE `commentaire` (
  `coID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `coAuteur` char(20) COLLATE utf8_unicode_ci NOT NULL,
  `coTexte` char(255) COLLATE utf8_unicode_ci NOT NULL,
  `coDate` bigint(12) unsigned NOT NULL,
  `coArticle` int(11) unsigned NOT NULL,
  PRIMARY KEY (`coID`),
  FOREIGN KEY(`coAuteur`) REFERENCES `utilisateur`(`utPseudo`) ON UPDATE CASCADE ON DELETE RESTRICT,
  FOREIGN KEY(`coArticle`) REFERENCES `article`(`arID`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `commentaire`
--

INSERT INTO `commentaire` (`coID`, `coAuteur`, `coTexte`, `coDate`, `coArticle`) VALUES
(1, 'jube', 'Ouf, j\'ai eu chaud sur ce coup-là...', 202311141312, 2),
(2, 'emerlet', 'Du coup, ça veut dire qu\'on peut menacer de défoncer les étudiants, sans risquer de représailles ?', 202311141426, 2),
(3, 'freddd', 'Oui, c\'est l\'idée. Et c\'est très efficace.', 202311141512, 2),
(4, 'jube', 'On dit merci qui ?', 202311141517, 2),

(5, 'emerlet', 'Ah mince, il va falloir éviter d\'attribuer cet amphi pour les L2...', 202501270919, 7),
(6, 'freddd', 'De toute façon l\'amphi B, en L2, il est un peu juste... ', 202501270928, 7),
(7, 'jube', 'Ca dépend, pour les cours d\'algo, en général il n\'y vite plus grand monde, et pour le coup, on a de la place...', 202501270943, 7),

(8, 'jhenriet', 'Non mais c\'est n\'importe quoi ! Le choix des chansons c\'est de la [#128169]', 202502041743, 9),
(9, 'jube', 'Non mais oh ! Reste poli ! [#129304]', 202502042358, 9),

(10, 'noussachons', 'C\'est bien évident, on nous cache des choses...', 202502220916, 10),
(11, 'jbigoude', 'Merci d\'éviter les commentaires complotistes sur notre site ! ', 202502220923, 10);


COMMIT;
