<?php
/*********************************************************
 *        Bibliothèque de fonctions spécifiques          *
 *        à l'application La gazette de L-INFO           *
 *********************************************************/


define('IS_DEV', true);  //true en phase de développement, false en phase de production

if (IS_DEV){
    // Force l'affichage des erreurs
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting( E_ALL );
}

define('BD_SERVER', 'mariadb-hostname'); // nom d'hôte ou adresse IP du serveur de base de données
define('BD_NAME', 'gazette_bd'); // nom de la base sur le serveur de base de données
define('BD_USER', 'gazette_user'); // nom de l'utilisateur de la base
define('BD_PASS', 'gazette_pass'); // mot de passe de l'utilisateur de la base

// limites liées aux tailles des champs de la table utilisateur
define('LMAX_PSEUDO', 20);    // taille du champ utLogin de la table utilisateur
define('LMAX_NOM', 50);      // taille du champ utNom de la table utilisateur
define('LMAX_PRENOM', 60);   // taille du champ utPrenom de la table utilisateur
define('LMAX_EMAIL', 255);   // taille du champ utMail de la table utilisateur

define('LMIN_PSEUDO', 4);

define('AGE_MINIMUM', 18);

define('LMIN_PASSWORD', 4);


date_default_timezone_set('Europe/Paris');
//_______________________________________________________________
/**
 * Affichage du début de la page HTML, des élements nav et header, jusqu'au tag ouvrant de l'élément main.
 *
 * @param  string  $titre       la partie du titre de la page après le |
 * @param  string  $prefixe     chemin relatif vers la racine du site
 * @param  ?string $id          id de l'élément main, null si pas utilisé
 *
 * @return void
 */
function affDebutMenuEntete(string $titre, string $prefixe = '..', ?string $id = null) : void {
    affDebut("La gazette de L-INFO | $titre", "$prefixe/styles/gazette.css");

    echo
        '<nav>',
            '<ul>',
                '<li><a href="', $prefixe, '/index.php">Accueil</a></li>',
                '<li><a href="', $prefixe, '/php/actus.php">Toute l\'actu</a></li>',
                '<li><a href="', $prefixe, '/php/recherche.php">Recherche</a></li>',
                '<li><a href="', $prefixe, '/php/redaction.php">La rédac\'</a></li>';
    if (estAuthentifie()){
        echo
                '<li><a href="#">', htmlProtegerSorties($_SESSION['pseudo']), '</a>',
                    '<ul>',
                        '<li><a href="', $prefixe, '/php/compte.php">Mon profil</a></li>',
                        $_SESSION['redacteur'] ? "<li><a href=\"$prefixe/php/nouveau.php\">Nouvel article</a></li>" : '',
                        '<li><a href="', $prefixe,'/php/deconnexion.php">Se déconnecter</a></li>',
                    '</ul>',
                '</li>';
    }
    else{
        echo    '<li><a href="', $prefixe,'/php/connexion.php">Se connecter</a></li>';
    }
    echo

            '</ul>',
        '</nav>',
        '<header>',
            '<img src="', $prefixe, '/images/titre.png" alt="Image du titre | La gazette de L-INFO" width="780" height="83">',
            '<h1>', $titre, '</h1>',
        '</header>',
        '<main', $id ? " id='$id'": '', '>';

}

//_______________________________________________________________
/**
 * Affichage du pied et de la fin de la page (tag fermant de l'élément main + élément footer jusqu'à la fin)
 *
 * @return void
 */
function affPiedFin() : void {
    echo
        '</main>',
        '<footer>&copy; Licence Informatique - Juin 2025 - Tous droits réservés</footer>';
    affFin();
}

//_______________________________________________________________
/**
* Détermine si l'utilisateur est authentifié
*
* @return bool     true si l'utilisateur est authentifié, false sinon
*/
function estAuthentifie(): bool {
    return  isset($_SESSION['pseudo']);
}


//_______________________________________________________________
/**
 * Termine une session et effectue une redirection vers la page transmise en paramètre
 *
 * Cette fonction est appelée quand l'utilisateur se déconnecte "normalement" et quand une
 * tentative de piratage est détectée. On pourrait améliorer l'application en différenciant ces
 * 2 situations. Et en cas de tentative de piratage, on pourrait faire des traitements pour
 * stocker par exemple l'adresse IP, etc.
 *
 * @param string    $page URL de la page vers laquelle l'utilisateur est redirigé
 *
 * @return void
 */
function sessionExit(string $page = '../index.php'): void {

    // suppression de toutes les variables de session
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        // suppression du cookie de session
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 86400,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy();

    header("Location: $page");
    exit();
}

//_______________________________________________________________
/**
 * Renvoie le chemin web de l'image d'un article
 *
 * @param int $id id de l'article
 *
 * @return string chemin de l'image de l'article
 */
function cheminImageArticle(int $id): string {
    $upload = '/gazette/upload/';
    $images = '/gazette/images/';
    $chemin = $_SERVER['DOCUMENT_ROOT'];
    $nomImage = $id.'.jpg';
    if(file_exists($chemin.$upload.$nomImage)){
        return $upload.$nomImage;
    }
    else{
        return $images.'none.jpg';
    }
}

//_______________________________________________________________
/**
 * Recupère le mois et l'année d'une date
 *
 * @param string $date Date au format YYYY-MM-DD
 *
 * @return array [$moisNom, $annee] Nom du mois en minuscule et année
 */
function getMoisAnnee(string $date): array {
    $mois = (int)substr($date,-8,2);
    $annee = substr($date,0,-8);
    $months = getArrayMonths();
    $nomMois = mb_strtolower($months[$mois-1], encoding:'UTF-8');
    return [$nomMois, $annee];
}

//_______________________________________________________________
/**
 * Affiche le resumé d'un article avec son image
 *
 * @param array $article Tableau associatif contenant : arID, arTitre, arResume
 *
 * @return void
 */
function affArticleResume(array $article): void {
    $image = cheminImageArticle((int)$article['arID']);

    echo '<article class="resume">',
            '<img src="',$image,'" alt="">',
            '<h3>', htmlProtegerSorties($article['arTitre']), '</h3>',
            '<p>', htmlProtegerSorties($article['arResume']), '</p>',
            '<footer><a href="./article.php?id=', (int)$article['arID'], '">Lire l\'article</a></footer>',
        '</article>';
}

//_______________________________________________________________
/**
 * Affiche un groupe d'articles regroupés par mois
 *
 * @param array $article Tableau d'articles avec arID, arTitre, arResume, arDatePubli
 *
 * @return void
 */
function affArticlesGroupesParMois(array $articles): void {
    $moisCourant = '';
    $anneeCourante = '';
    foreach($articles as $article){
        [$mois, $annee] = getMoisAnnee($article['arDatePubli']);
        if($mois !== $moisCourant || $annee !== $anneeCourante){
            if($moisCourant !== ''){
                echo '</section>';
            }
            echo '<section>',
                '<h2>', ucfirst($mois), ' ', $annee, '</h2>';
            $moisCourant = $mois;
            $anneeCourante = $annee;
        }
        affArticleResume($article);
    }
    if($moisCourant !== ''){
        echo '</section>';
    }
}

//_______________________________________________________________
/**
 * Traite l'image uploadée, vérifie la taille et le ratio
 *
 * @param array $f       Données de $_FILES['image']
 * @param array $erreurs Tableau pour stocker les messages d'erreurs
 *
 * @return resource|null Ressource image redimensionnée, ou null si erreur
 */
function traiterImageUpload(array $f, array &$erreurs) {
    switch($f['error']){
        case 0:
            break;
        case 1:
        case 2:
            $erreurs[]="'{$f['name']}' est trop volumineuse.";
            return null;
        case 3:
            $erreurs[] = "Erreur lors du transfert de l'image.";
            return null;
        case 4:
            return null;
        default:
            $erreurs[] = "Fichier inconnue lors du transfert de l'image.";
            return null;
    }
    if($f['size']>2000000){
        $erreurs[] = 'Image trop volumineuse.';
        return null;
    }

    $ext = strtolower(substr($f['name'], strrpos($f['name'], '.')));
    if($ext !==  '.jpg' && $ext !== '.jpeg'){
        $erreurs[] = "Image JPG uniquement.";
        return null;
    }

    $oks = ['image/jpeg', 'image/pjpeg'];
    if(! in_array($f['type'], $oks)){
        $erreurs[] = "Type d'image invalide.";
        return null;
    }

    $info = @getimagesize($f['tmp_name']);
    if($info===false){
        $erreurs[] = "Fichier image invalide.";
        return null;
    }

    $larg = $info[0];
    $haut = $info[1];

    $ratio = $larg / $haut;
    if(abs($ratio - 4/3)>0.02){
        $erreurs[] = "Ratio de l'image invalide (4:3 requis).";
        return null;
    }

    $src = @imagecreatefromJPEG($f['tmp_name']);
    if($src===false){
        $erreurs[] = "Impossible de lire l'image.";
        return null;
    }

    $newlarg = 248;
    $newhaut = 186;

    $dst = @imagecreatetruecolor($newlarg, $newhaut);
    imagecopyresized($dst, $src, 0, 0, 0, 0, $newlarg, $newhaut, $larg, $haut);

    return $dst;
}

//_______________________________________________________________
/**
 * Vérifie les champs d'un article
 *
 * @param array $post    Données POST de l'article
 * @param array $erreurs Tableaupour stocker les erreurs
 *
 * @return array [$titre, $resume, $texte] valeurs extraites et validées
 */
function verifierChampsArticle(array $post, array &$erreurs) : array {
    $titre = $post['txtTitre'];
    $resume = $post['txtResume'];
    $texte = $post['txtArticle'];
    verifierTexte($titre, "Le titre de l'article", $erreurs, 5, 150 , null, "ne doit pas contenir de tags HTML.");
    if(strip_tags($resume)!==$resume){
        $erreurs[] = "Le résumé ne doit pas contenir de tags HTML.";
    }
    if(strip_tags($texte)!==$texte){
        $erreurs[] = "Le texte ne doit pas contenir de tags HTML.";
    }
    return [$titre, $resume, $texte];
}

//_______________________________________________________________
/**
 * Affiche le formulaire d'un article pour création ou édition
 *
 * @param array      $values Valeurs pré-remplies du formulaire
 * @param array|null $err    Tableau des erreurs à afficher
 * @param string     $mode   'edition' ou 'nouveau'
 * @param int|null   $id     ID de l'article si mode édition
 *
 * @return void
 */
function affFormulaireArticle(array $values, ?array $err, string $mode, ?int $id=null): void {
    echo '<section><h2>', ($mode === 'edition' ? "Édition de l'article" : "Nouveau article"), '</h2>';

    if(is_array($err)){
        echo '<div class="erreur">',
                '<ul>';
    foreach ($err as $e) {
        echo        '<li>', htmlspecialchars($e, ENT_QUOTES, 'UTF-8'), '</li>';
    }
    echo        '</ul>',
            '</div>';
    }

    echo
        '<form method="post" enctype="multipart/form-data" action="', basename($_SERVER['PHP_SELF']),($id ? '?id='.$id : ''),'">',
            '<input type="hidden" name="MAX_FILE_SIZE" value="100000">',

            '<p><strong>Titre de l\'article : </strong></p>',
            '<input type="text" name="txtTitre" id="txtTitre" maxlength="150" style="width: 99.8%;" value="', htmlspecialchars($values['txtTitre'], ENT_QUOTES, encoding:'UTF-8'), '" required>',

            '<p><strong>Résumé de l\'article :</strong></p>',
            '<textarea name="txtResume" rows="10" style="width: 100%; border: 0.9px solid; border-radius: 3px;">',htmlspecialchars($values['txtResume'], ENT_QUOTES, encoding:'UTF-8'),'</textarea>',

            '<p><strong>Texte de l\'article :</strong></p>',
            '<textarea name="txtArticle" rows="20" style="width: 100%; border: 0.9px solid; border-radius: 3px;">',htmlspecialchars($values['txtArticle'], ENT_QUOTES, encoding:'UTF-8'),'</textarea>',

            '<p><strong>Image (optionnelle) :</strong></p>',
            '<input type="file" name="image">',
            '<br><br>',

            '<input type="submit" name="',
            ($mode === 'edition' ? 'btnModifier' : 'btnNouveau'),
            '" value="',
            ($mode === 'edition' ? 'Enregistrer' : 'Publier cet article'),
            ($mode === 'edition' ? '" class="btnEnrEdition"' : '" style="position: absolute; right: 5px; bottom: 5px;"'),
            '>',
        '</form>';

    if($mode === 'edition'){
        echo '<input type="checkbox" id="modal-touche" hidden>',
            '<label for="modal-touche" class="btnSupprimer">Supprimer</label>',
            '<div class="modal">',
                '<div class="modal-content">',
                    '<p>Voulez-vous vraiment supprimer cet article ?</p>',
                    '<form method="post">',
                        '<input type="submit" name="confirmSuppr" class="btnOui" value="Oui, Supprimer">',
                        '<label for="modal-touche" class="btnAnnuler">Annuler</label>',
                    '</form>',
                '</div>',
            '</div>';
    }

    echo '</section>';
}


