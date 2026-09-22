<?php

// chargement des bibliothèques de fonctions
require_once('./bibli_gazette.php');
require_once('./bibli_generale.php');

// bufferisation des sorties
ob_start();

//démarrage ou reprise de la session
session_start();

$res = verifParamGET();
if (is_int($res)){ // $res est égal à l'identifiant de l'article
    // connexion au serveur de BdD (une seule connexion dans le script !)
    $bd = bdConnect();
    $idArticle = $res;
    $res = bdSelectInfosArticleL($bd, $res);


    if($_SERVER['REQUEST_METHOD'] === 'POST'){

        if(!estAuthentifie()){
            header('Location: connexion.php');
            exit();
        }

        if(isset($_POST['supprimerComm']) && isset($_POST['idComm'])){
            $coID = (int)($_POST['idComm']);
            $sql = "DELETE FROM commentaire WHERE coID=$coID AND coArticle=$idArticle";
            bdSendRequest($bd, $sql);
            header('Location: article.php?id='.(int)$_GET['id']);
            exit();
        }


        if(isset($_POST['btnCommenter']) && isset($_POST['commentaire'])){
            $pseudo = $_SESSION['pseudo'];
            $text = trim($_POST['commentaire']);
            $text = mysqli_real_escape_string($bd, $text);
            $date = date('YmdHi', time());
            $sql = "INSERT INTO commentaire SET
                    coAuteur = '$pseudo',
                    coTexte = '$text',
                    coDate = $date,
                    coArticle = $idArticle";
            bdSendRequest($bd, $sql);
            header('Location: article.php?id='.(int)$_GET['id']);
            exit();
        }
    }


    // Fermeture de la connexion au serveur de BdD, réalisée le plus tôt possible
    mysqli_close($bd);
}

if (is_string($res)){
    // l'élément main ne doit pas avoir d'id en cas d'erreur (cf. feuille de styles)
    affDebutMenuEntete('Article');
    affErreurL($res);
}
else{ // $res est un objet mysqli_result
    affDebutMenuEntete('Article', id:'article');
    // encapsulation des caractéristiques de l'article dans du code HTML
    affArticleCommentairesL($res);
}
affPiedFin();

// envoi du buffer et fin de la bufferisation
// facultatif car fait automatiquement par PHP
ob_end_flush();


/*********************************************************
 *
 * Définitions des fonctions locales de la page
 *
 *********************************************************/
//_______________________________________________________________
/**
 * Vérification des paramètres reçus dans l'URL, c'est-à-dire dans $_GET
 *
 * @return  int|string      entier identifiant de l'article si pas d'erreur, string contenant un message d'erreur sinon
 */
function verifParamGET() : int|string{
    // $_GET est un tableau superglobal, il est donc accessible directement dans toutes les fonctions.
    // Par conséquent, il n'est pas nécessaire de le transmettre en paramètre à la fonction.
    if (count($_GET) != 1 OR ! isset($_GET['id'])){
    // ou
    //if (! parametresControle('get', ['id'])){
        return 'Il faut utiliser une URL de la forme : http://..../php/article.php?id=XXX';
    }
    if (! estEntier($_GET['id'])){
        return 'L\'identifiant doit être un entier';
    }

    $id = (int)$_GET['id'];
    // il n'y a plus d'erreur ou d'attaque possible après ce transtypage.

    if ($id <= 0){
        return 'L\'identifiant doit être un entier strictement positif';
    }
    return $id; // fin sans erreur
}

//_______________________________________________________________
/**
 * Récupération en BdD des caractéristiques de l'article, des informations sur son auteur,
 * et de ses éventuelles commentaires
 *
 * @param   mysqli            $bd     Objet connecteur sur la base de données
 * @param   int               $id     Identifiant de l'article
 *
 * @return  mysqli_result|string      objet permettant d'accéder aux données si article trouvé, string avec un message d'erreur sinon
 */
function bdSelectInfosArticleL(mysqli $bd, int $id) : mysqli_result|string{
    // Récupération de l'article, des informations sur son auteur,
    // et de ses éventuelles commentaires
    // $id est un entier, donc pas besoin de le protéger avec mysqli_real_escape_string()
    $sql = "SELECT *
            FROM (article INNER JOIN utilisateur ON arAuteur = utPseudo)
            LEFT OUTER JOIN commentaire ON arID = coArticle
            WHERE arID = $id
            ORDER BY coDate DESC, coID DESC";

    $result = bdSendRequest($bd, $sql);

    // pas d'articles --> fin de la fonction
    if (mysqli_num_rows($result) == 0) {
        // Libération de la mémoire associée au résultat de la requête
        mysqli_free_result($result);
        return "L'article d'identifiant \"$id\" n'a pas été trouvé dans la base de données";
    }
    return $result; // fin sans erreur
}

//_______________________________________________________________
/**
 * Affichage de l'article et de ses éventuels commentaires
 *
 * @param   mysqli_result   $result     objet permettant d'accéder aux données sélectionnées
 *
 * @return  void
 */
function affArticleCommentairesL(mysqli_result $result) : void {
    $tab = mysqli_fetch_assoc($result);

    // Mise en forme du prénom et du nom de l'auteur pour affichage dans le pied du texte de l'article
    // Exemple :
    // - pour 'johNnY' 'bigOUde', cela donne 'J. Bigoude'
    // - pour 'éric' 'merlet', cela donne 'É. Merlet'
    // À faire avant la protection avec htmlentities() à cause des éventuels accents
    $auteur = upperCaseFirstLetterLowerCaseRemainderL(mb_substr($tab['utPrenom'], 0, 1, encoding:'UTF-8')) . '. ' . upperCaseFirstLetterLowerCaseRemainderL($tab['utNom']);

    if(estAuthentifie() && $_SESSION['pseudo']===$tab['utPseudo']){
        $id = (int)($_GET['id']);
        echo '<div class="blanche">',
                '<p>Vous êtes l\'auteur de cet article, <a href="./edition.php?id=',$id,'">cliquez ici pour le modifier</a>.</p>',
            '</div>';
    }

    // ATTENTION : protection contre les attaques XSS, à ne pas oublier !!!
    $auteur = htmlProtegerSorties($auteur);

    // ATTENTION : protection contre les attaques XSS, à ne pas oublier !!!
    $tab = htmlProtegerSorties($tab);

    // la protection des sorties est réalisée au dernier moment, juste avant l'encapsulation des chaînes
    // issues de la BdD dans le code HTML

    echo
            '<article>',
                '<h3>', $tab['arTitre'], '</h3>',
                '<img src="../upload/', $tab['arID'], '.jpg" alt="Photo d\'illustration | ', $tab['arTitre'], '">',
                $tab['arTexte'],
                '<footer>',
                    'Par <a href="redaction.php#', $tab['utPseudo'], '">', $auteur, '</a>. ',
                    'Publié le ', dateIntToStringL($tab['arDatePubli']),
                    isset($tab['arDateModif']) ? ', modifié le '. dateIntToStringL($tab['arDateModif']) : '',
                '</footer>',
            '</article>';

    //pour accéder une seconde fois au premier enregistrement de la sélection
    mysqli_data_seek($result, 0);

    // Génération du début de la zone de commentaires
    echo '<section>',
            '<h2>Réactions</h2>';

    // s'il existe des commentaires, on les affiche un par un.
    if (isset($tab['coID'])) {
        echo '<ul>';
        while ($tab = mysqli_fetch_assoc($result)) {
            $droit = false;
            if(estAuthentifie() && ($_SESSION['pseudo']===$tab['coAuteur'] || $_SESSION['pseudo']===$tab['arAuteur'])){
                $droit = true;
            }
            echo '<li class="comm">',
                    '<p>Commentaire de <strong>', htmlProtegerSorties($tab['coAuteur']),
                        '</strong>, le ', dateIntToStringL($tab['coDate']),
                    '</p>',
                    '<blockquote>', htmlProtegerSorties($tab['coTexte']), '</blockquote>';
            if($droit){
                echo '<div class="lihide">',
                        '<form method="post" action="', htmlspecialchars(basename($_SERVER['PHP_SELF']).'?id='.(int)$_GET['id']), '">',
                            '<input type="hidden" name="idComm" value="', $tab['coID'], '">',
                            '<input type="submit" name="supprimerComm" value="Supprimer" style="border-radius: 7px; margin-top: 10px;">',
                        '</form>',
                    '</div>';
            }
            echo  '</li>';
        }
        echo '</ul>';
    }
    // sinon on indique qu'il n'y a pas de commentaires
    else {
        echo '<p>Il n\'y a pas de commentaire pour cet article. </p>';
    }

    // Libération de la mémoire associée au résultat de la requête
    mysqli_free_result($result);

    if(!estAuthentifie()){
        echo
            '<p>',
                '<a href="./connexion.php">Connectez-vous</a> ou <a href="./inscription.php">inscrivez-vous</a> pour pouvoir commenter cet article !',
            '</p>';
    }
    else{
        echo
        '<form method="post" action="', htmlspecialchars(basename($_SERVER['PHP_SELF']).'?id='.(int)$_GET['id']), '">',
            '<fieldset style="border: 0.9px solid; padding: 10px;">',
                '<legend>Ajoutez un commentaire</legend>',
                '<textarea name="commentaire" rows="20" style="width: 75%; border: 0.9px solid; border-radius: 3px;"></textarea>',
                '<br>',
                '<input type="submit" name="btnCommenter" value="Publier ce commentaire" style="border-radius: 5px;  margin-top: 10px;">',
            '</fieldset>',
        '</form>';
    }
    echo '</section>';
}

//_______________________________________________________________
/**
 * Conversion d'une date format AAAAMMJJHHMM au format JJ mois AAAA à HHhMM
 *
 * @param  int      $date   la date à afficher.
 *
 * @return string           la chaîne qui représente la date
 */
function dateIntToStringL(int $date) : string {
    // les champs date (coDate, arDatePubli, arDateModif) sont de type BIGINT dans la base de données
    // donc pas besoin de les protéger avec htmlentities()

    // si un article a été publié avant l'an 1000, ça marche encore :-)
    $minutes = substr($date, -2);
    $heure = (int)substr($date, -4, 2); //conversion en int pour supprimer le 0 de '07' pax exemple
    $jour = (int)substr($date, -6, 2);
    $mois = substr($date, -8, 2);
    $annee = substr($date, 0, -8);

    $months = getArrayMonths();

    return $jour. ' '. mb_strtolower($months[$mois - 1], encoding:'UTF-8'). ' '. $annee . ' à ' . $heure . 'h' . $minutes;
}


//___________________________________________________________________
/**
 * Renvoie une copie de la chaîne UTF8 transmise en paramètre après avoir mis sa
 * première lettre en majuscule et toutes les suivantes en minuscule
 *
 * @param  string   $str    la chaîne à transformer
 *
 * @return string           la chaîne résultat
 */
function upperCaseFirstLetterLowerCaseRemainderL(string $str) : string {
    $str = mb_strtolower($str, encoding:'UTF-8');
    $fc = mb_strtoupper(mb_substr($str, 0, 1, encoding:'UTF-8'));
    return $fc.mb_substr($str, 1, encoding:'UTF-8');
}


//_______________________________________________________________
/**
 * Affichage d'un message d'erreur dans une zone dédiée de la page.
 *
 * @param  string  $msg    le message d'erreur à afficher.
 *
 * @return void
 */
function affErreurL(string $message) : void {
    echo
        '<section>',
            '<h2>Oups, il y a eu une erreur...</h2>',
            '<p>La page que vous avez demandée a terminé son exécution avec le message d\'erreur suivant :</p>',
            '<blockquote>', $message, '</blockquote>',
        '</section>';
}
