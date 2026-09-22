<?php

// chargement des bibliothèques de fonctions
require_once('bibli_gazette.php');
require_once('bibli_generale.php');

// bufferisation des sorties
ob_start();

// génération de la page
affDebutMenuEntete('Vérification des données reçues / Enregistrement en BdD');

$err = traitementInscriptionL();

affContenuL($err);

affPiedFin();


/*********************************************************
 *
 * Définitions des fonctions locales de la page
 *
 *********************************************************/
//_______________________________________________________________
/**
 * Affichage du contenu principal de la page
 *
 * @param array    $errs    Tableau contenant les erreurs
 *
 * @return  void
 */
function affContenuL(array $errs) : void {
    echo
            '<section>',
                '<h2>Vérification des données reçues / Enregistrement en BdD</h2>';
    if (count($errs) > 0) {
        echo    '<div class="erreur">Les erreurs suivantes ont été relevées lors de votre inscription :',
                    '<ul>';
        foreach ($errs as $e) {
            echo        '<li>', $e, '</li>';
        }
        echo        '</ul>',
                '</div>';
    }
    else {
        echo    '<p>Un nouvel utilisateur a été ajouté dans la table "utilisateur" de la base de données.</p>';
    }

    echo    '</section>';
}

/**
 * Traitement d'une demande d'inscription
 *
 * Vérification de la validité des données
 *
 * Les erreurs de type "étourderie" sont stockées dans un tableau php qui est retourné par la fonction.
 *
 * Toutes les erreurs détectées qui nécessitent une modification du code HTML sont considérées comme des tentatives de piratage
 * et donc entraînent une redirection de l'utilisateur vers la page index.php sauf :
 * - les éventuelles suppressions des attributs required car l'attribut required est une nouveauté apparue dans la version HTML5 et
 *   nous souhaitons que l'application fonctionne également correctement sur les vieux navigateurs qui ne supportent pas encore HTML5
 * - une éventuelle modification de l'input de type date en input de type text car c'est ce que font les navigateurs qui ne supportent
 *   pas les input de type date
 *
 * Si les données soumises sont valides, un nouvel utilisateur est ajouté dans la table utilisateur.
 *
 *  @return array    un tableau contenant les erreurs s'il y en a
 */
function traitementInscriptionL(): array {
    if( !parametresControle('post', ['pseudo', 'nom', 'prenom', 'naissance',
                                     'passe1', 'passe2', 'email', 'btnInscription'], ['radSexe', 'cbCGU', 'cbSpam'])) {
        header('Location: ../index.php');
        exit;
    }

    $erreurs = [];

    // vérification du pseudo
    $pseudo = trim($_POST['pseudo']);
    verifierTexte($pseudo, 'Le pseudo', $erreurs, LMIN_PSEUDO, LMAX_PSEUDO, '/^[0-9a-z]+$/u',
                  'ne doit contenir que des chiffres et des lettres minuscules sans accent.');

    // vérification de la civilité
    if (! isset($_POST['radSexe'])){
        $erreurs[] = 'Vous devez choisir une civilité.';
    }
    else if (! (estEntier($_POST['radSexe']) && estEntre($_POST['radSexe'], 1, 3))){
        header('Location: ../index.php');
        exit;
    }

    // vérification des noms et prénoms
    $expRegNomPrenom = '/^[[:alpha:]]([\' -]?[[:alpha:]]+)*$/u';
    $errNomPrenom = 'ne doit contenir que des lettres éventuellement séparés par un espace, un tiret ou une simple quote.';
    $nom = $_POST['nom'] = trim($_POST['nom']);
    $prenom = $_POST['prenom'] = trim($_POST['prenom']);
    verifierTexte($nom, 'Le nom', $erreurs, lMax:LMAX_NOM, expReg:$expRegNomPrenom, mesExpReg:$errNomPrenom);
    verifierTexte($prenom, 'Le prénom', $erreurs, lMax:LMAX_PRENOM, expReg:$expRegNomPrenom, mesExpReg:$errNomPrenom);

    // vérification du format de l'adresse email
    $email = trim($_POST['email']);
    verifierTexte($email, 'L\'adresse email', $erreurs, lMax:LMAX_EMAIL);

    // la validation faite par le navigateur en utilisant le type email pour l'élément HTML input
    // est moins forte que celle faite ci-dessous avec la fonction filter_var()
    // Exemple : 'l@i' passe la validation faite par le navigateur et ne passe pas
    // celle faite ci-dessous
    if(! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = 'L\'adresse email n\'est pas valide.';
    }

    // vérification de la date de naissance
    if (empty($_POST['naissance'])){
        $erreurs[] = 'La date de naissance doit être renseignée.';
    }
    else{
        if(! preg_match('/^\\d{4}(-\\d{2}){2}$/u', $_POST['naissance'])){ //vieux navigateur qui ne supporte pas le type date ?
            $erreurs[] = 'la date de naissance doit être au format "AAAA-MM-JJ".';
        }
        else{
            list($annee, $mois, $jour) = explode('-', $_POST['naissance']);
            if (!checkdate($mois, $jour, $annee)) {
                $erreurs[] = 'La date de naissance n\'est pas valide.';
            }
            else if (mktime(0,0,0,$mois,$jour,$annee + AGE_MINIMUM) > time()) {
                $erreurs[] = 'Vous devez avoir au moins '. AGE_MINIMUM. ' ans pour vous inscrire.';
            }
        }
    }

    // vérification des mots de passe
    $_POST['passe1'] = trim($_POST['passe1']);
    $_POST['passe2'] = trim($_POST['passe2']);
    if ($_POST['passe1'] !== $_POST['passe2']) {
        $erreurs[] = 'Les mots de passe doivent être identiques.';
    }
    $nb = mb_strlen($_POST['passe1'], encoding:'UTF-8');
    if ($nb < LMIN_PASSWORD){
        $erreurs[] = 'Le mot de passe doit être constitué d\'au moins '. LMIN_PASSWORD . ' caractères.';
    }

    // vérification de la valeur de l'élément cbCGU
    if (! isset($_POST['cbCGU'])){
        $erreurs[] = 'Vous devez accepter les conditions générales d\'utilisation .';
    }
    else if ($_POST['cbCGU'] !== '1'){
        header('Location: ../index.php');
        exit;
    }

    // vérification de la valeur de $_POST['cbSpam'] si l'utilisateur accepte de recevoir des mails pourris
    if (isset($_POST['cbSpam']) && $_POST['cbSpam'] !== '1'){
        header('Location: ../index.php');
        exit;
    }

    // si erreurs --> retour
    if (count($erreurs) > 0) {
        return $erreurs;   //===> FIN DE LA FONCTION
    }

    // on vérifie si le pseudo et l'adresse email ne sont pas encore utilisés que si tous les autres champs
    // sont valides car ces 2 dernières vérifications nécessitent une connexion au serveur de base de données
    // consommatrice de ressources système

    // ouverture de la connexion à la base
    $bd = bdConnect();

    // protection des entrées (suffixe E comme Escape ou échappée)
    $pseudoE = mysqli_real_escape_string($bd, $pseudo); // fait par principe, mais inutile ici car on a déjà vérifié que le pseudo
                                                        // ne contenait que des lettres minuscules et des chiffres
    $emailE = mysqli_real_escape_string($bd, $email);

    $sql = "SELECT utPseudo, utEmail FROM utilisateur WHERE utPseudo = '$pseudoE' OR utEmail = '$emailE'";
    $res = bdSendRequest($bd, $sql);

    while($tab = mysqli_fetch_assoc($res)) {
        if ($tab['utPseudo'] == $pseudo){
            $erreurs[] = 'Le pseudo choisi est déjà utilisé.';
        }
        if ($tab['utEmail'] == $email){
            $erreurs[] = 'L\'adresse email est déjà utilisée.';
        }
    }
    // Libération de la mémoire associée au résultat de la requête
    mysqli_free_result($res);

    // si erreurs --> retour
    if (count($erreurs) > 0) {
        // fermeture de la connexion à la base de données
        mysqli_close($bd);
        return $erreurs;   //===> FIN DE LA FONCTION
    }

    // calcul du hash du mot de passe pour enregistrement dans la base.
    $passeHash = password_hash($_POST['passe1'], PASSWORD_DEFAULT);

    $passeHashE = mysqli_real_escape_string($bd, $passeHash);

    $dateNaissance = $annee*10000 + $mois*100 + $jour;

    $nomE = mysqli_real_escape_string($bd, $nom);
    $prenomE = mysqli_real_escape_string($bd, $prenom);

    $civilite = (int) $_POST['radSexe'];
    $civilite = $civilite == 1 ? 'h' : ($civilite == 2 ? 'f' : 'nb');

    $mailsPourris = isset($_POST['cbSpam']) ? 1 : 0;

    // les valeurs sont écrites en respectant l'ordre de création des champs dans la table usager
    $sql = "INSERT INTO utilisateur (utPseudo, utNom, utPrenom, utEmail, utPasse, utDateNaissance, utRedacteur, utCivilite, utMailsPourris)
            VALUES ('$pseudoE', '$nomE', '$prenomE', '$emailE', '$passeHashE', $dateNaissance, 0, '$civilite', $mailsPourris)";

    bdSendRequest($bd, $sql);


    // fermeture de la connexion à la base de données
    mysqli_close($bd);

    return $erreurs; // tableau vide
}
