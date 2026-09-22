<?php

// chargement des bibliothèques de fonctions
require_once('./bibli_gazette.php');
require_once('./bibli_generale.php');

// bufferisation des sorties
ob_start();

//démarrage ou reprise de la session
session_start();

if(!estAuthentifie()){
    header('Location: connexion.php');
    exit();
}

if(isset($_POST['btnEnrInfo'])){
    $errInfo = traitementInfoL();
    $errMdp = null;
}
else if(isset($_POST['btnEnrMdp'])){
    $errMdp = traitementMdpL();
    $errInfo = null;
}
else{
    $errInfo = null;
    $errMdp = null;
}

affDebutMenuEntete('Mon compte');

affFormulaireInfoL($errInfo);
affFormulaireMdpL($errMdp);

affPiedFin();

/**
 * Traitement de la modification des informations personnelles du compte utilisateur
 *
 * Vérifie la validité des données envoyées via le formulaire :
 *  - civilité
 *  - nom et prénom
 *  - adresse email (format et unicité)
 *  - mot de passe courant (obligatoire pour valider les changements)
 *  - préférence de réception des mails pourris (0 = non , 1 = oui)
 *
 * En cas d'erreurs :
 *  - retourne un tableau contenant les messages d'erreur
 *
 * En cas de succès :
 *  - met à jour les informations dans la base de données
 *  - enregistre un message de confirmation en session
 *  - redirige vers la page compte.php
 *
 * Toute incohérece dans les paramètres POST est considérée comme une tentative de piratage
 * et entraine l'appel de sessionexit()
 *
 * @return array Tableau contenant les messages d'erreur, vide si aucun erreur
 */
function traitementInfoL() : array {
    if( !parametresControle('post', clesObligatoires:['nom', 'prenom', 'passe', 'email', 'btnEnrInfo'], clesFacultatives:['radSexe', 'cbSpam'])) {
        sessionExit();
    }
    $erreurs = [];

    // vérification de la civilité
    if (! isset($_POST['radSexe'])){
        $erreurs[] = 'Vous devez choisir une civilité.';
    }
    else if (! (estEntier($_POST['radSexe']) && estEntre($_POST['radSexe'], 1, 3))){
        sessionExit();
    }

    // vérification des noms et prénoms
    $expRegNomPrenom = '/^[[:alpha:]]([\' -]?[[:alpha:]]+)*$/u';
    $errNomPrenom = 'ne doit contenir que des lettres éventuellement séparés par un espace, un tiret ou une simple quote.';
    $nom = $_POST['nom'] = trim($_POST['nom']);
    $prenom = $_POST['prenom'] = trim($_POST['prenom']);
    verifierTexte($nom, 'Le nom', $erreurs, lMax:LMAX_NOM, expReg:$expRegNomPrenom, mesExpReg:$errNomPrenom);
    verifierTexte($prenom, 'Le prénom', $erreurs, lMax:LMAX_PRENOM, expReg:$expRegNomPrenom, mesExpReg:$errNomPrenom);

    // vérification du format de l'adresse email
    $email = $_POST['email'] = trim($_POST['email']);
    verifierTexte($email, 'L\'adresse email', $erreurs, lMax:LMAX_EMAIL);
    if(! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = 'L\'adresse email n\'est pas valide.';
    }

    // vérification de la valeur de $_POST['cbSpam'] si l'utilisateur accepte de recevoir des mails pourris
    if (isset($_POST['cbSpam']) && $_POST['cbSpam'] !== '1'){
        sessionExit();
    }

    // si erreurs --> retour
    if (count($erreurs) > 0) {
        return $erreurs;   //===> FIN DE LA FONCTION
    }

    $bd = bdConnect();
    $pseudoE = mysqli_real_escape_string($bd, $_SESSION['pseudo']);
    $emailE = mysqli_real_escape_string($bd, $email);
    $sql = "SELECT utEmail
            FROM utilisateur
            WHERE utEmail = '$emailE'
            AND utPseudo != '$pseudoE'";
    $res = bdSendRequest($bd, $sql);
    while($tab = mysqli_fetch_assoc($res)) {
        if ($tab['utEmail'] == $email){
            $erreurs[] = 'L\'adresse email est déjà utilisée.';
            mysqli_free_result($res);
            // fermeture de la connexion à la base de données
            mysqli_close($bd);
            return $erreurs;   //===> FIN DE LA FONCTION
        }
    }

    $sql = "SELECT utPasse FROM utilisateur WHERE utPseudo = '$pseudoE'";
    $res = bdSendRequest($bd, $sql);
    $rep = mysqli_fetch_assoc($res);
    if(!password_verify($_POST['passe'],$rep['utPasse'])){
        $erreurs[]='Le mot de passe saisi est erroné';
        mysqli_free_result($res);
        // fermeture de la connexion à la base de données
        mysqli_close($bd);
        return $erreurs;   //===> FIN DE LA FONCTION
    }

    $nomE = mysqli_real_escape_string($bd, $nom);
    $prenomE = mysqli_real_escape_string($bd, $prenom);

    $civilite = (int) $_POST['radSexe'];
    $civilite = $civilite == 1 ? 'h' : ($civilite == 2 ? 'f' : 'nb');

    $mailsPourris = isset($_POST['cbSpam']) ? 1 : 0;

    $sql = "UPDATE utilisateur
            SET utEmail = '$emailE',
            utNom = '$nomE',
            utPrenom = '$prenomE',
            utCivilite = '$civilite',
            utMailsPourris = $mailsPourris
            WHERE utPseudo = '$pseudoE'";

    bdSendRequest($bd, $sql);
    mysqli_close($bd);

    $_SESSION['ok_info'] = 'Les informations ont été mises à jour avec succès.';

    header('Location: compte.php');
    exit();
}

/**
 * Traitement de la modification du mot de passe utilisateur
 *
 * Vérifie :
 *  - la présence des champs requis
 *  - l'égalité des deux nouveaux mot de passe
 *  - la validité de l'ancien mot de passe via password_verify()
 *
 * En cas d'erreurs :
 *  - retourne un tableau contenant les messages d'erreur
 *
 * En cas de succès :
 *  - hash le nouveau mot de passe avec password_hash()
 *  - met à jour la base de données
 *  - enregistre un message de confirmation en session
 *  - redirige vers la page compte.php
 *
 * Toute incohérece dans les paramètres POST est considérée comme une tentative de piratage
 * et entraine l'appel de sessionexit()
 *
 * @return array Tableau contenant les messages d'erreur
 */
function traitementMdpL() : array {
    if( !parametresControle('post', clesObligatoires:['ancien', 'passe1', 'passe2', 'btnEnrMdp'], clesFacultatives:[])) {
        sessionExit();
    }
    $erreurs = [];

    if ($_POST['passe1'] !== $_POST['passe2']) {
        $erreurs[] = 'Les nouveaux mots de passe doivent être identiques.';
        return $erreurs;
    }

    $bd = bdConnect();

    $pseudoE = mysqli_real_escape_string($bd, $_SESSION['pseudo']);
    $sql = "SELECT utPasse FROM utilisateur WHERE utPseudo = '$pseudoE'";
    $res = bdSendRequest($bd, $sql);
    $rep = mysqli_fetch_assoc($res);
    if(!password_verify($_POST['ancien'],$rep['utPasse'])){
        $erreurs[]='L\'ancien mot de passe est erroné';
        mysqli_free_result($res);
        // fermeture de la connexion à la base de données
        mysqli_close($bd);
        return $erreurs;   //===> FIN DE LA FONCTION
    }

    $passeHash = password_hash($_POST['passe1'], PASSWORD_DEFAULT);
    $passeHashE = mysqli_real_escape_string($bd, $passeHash);

    $sql = "UPDATE utilisateur SET utPasse = '$passeHashE' WHERE utPseudo = '$pseudoE'";
    bdSendRequest($bd, $sql);
    mysqli_close($bd);

    $_SESSION['ok_mdp'] = 'Le mot de passe a été changé avec succès.';

    header('Location: compte.php');
    exit();
}

/**
 * Affiche le formulaire de modification des informations personnelles.
 *
 * Les données sont récupérées depuis la base de données pour l'utilisateur authentifié.
 * Les valeurs sont protégées contre les attaques XSS avant affichage.
 *
 * En cas d'erreur lors de la soumission précédente, les messages sont affichés au dessus du formulaire
 *
 * @param ?array $err Tableau des erreurs à afficher ou null lors du premier affichage
 *
 * @return void
 */
function affFormulaireInfoL(?array $err) : void {
    $bd = bdConnect();
    $pseudoE = mysqli_real_escape_string($bd, $_SESSION['pseudo']);
    $sql = "SELECT utNom, utPrenom, utEmail, utCivilite, utMailsPourris
            FROM utilisateur
            WHERE utPseudo = '$pseudoE'";
    $res = bdSendRequest($bd, $sql);
    $tab = mysqli_fetch_assoc($res);
    mysqli_free_result($res);
    mysqli_close($bd);

    $values = htmlProtegerSorties($tab);

    $values['radSexe'] = -1;
    if(isset($tab['utCivilite'])){
        $civ = $tab['utCivilite'];
        $values['radSexe'] = $civ === 'h' ? 1 :
                            ($civ === 'f' ? 2 :
                            ($civ === 'nb' ? 3 : -1));
    }

    $values['cbSpam'] = 0;
    if(isset($tab['utMailsPourris'])){
        $values['cbSpam'] = (int)$tab['utMailsPourris'];
    }

    echo '<section>',
            '<h2>Informations personnelles</h2>',
            '<p>Vous pouvez modifier les informations suivantes (le mot de pasee doit être saisi pour validation).</p>';

    if(isset($_SESSION['ok_info'])){
        echo '<p style="color: green; text-align: center;">', $_SESSION['ok_info'], '</p>';
        unset($_SESSION['ok_info']);
    }

    if (is_array($err)) {
        echo
            '<div class="erreur">Les erreurs suivantes ont été relevées lors de votre inscription :',
                '<ul>';
        foreach ($err as $e) {
            echo    '<li>', $e, '</li>';
        }
        echo    '</ul>',
            '</div>';
    }

    echo
        '<form method="post" action="', basename($_SERVER['PHP_SELF']),'">',
            '<table>';

    echo
                '<tr>',
                    '<td>Votre civilité :</td>',
                    '<td>';
    $radios = [1 => 'Monsieur', 2 => 'Madame', 3 => 'Non binaire'];
    foreach ($radios as $value => $label){
        echo            '<label><input type="radio" name="radSexe" value="', $value, '"',
                        $value === $values['radSexe'] ? ' checked' : '', '> ', $label, '</label> ';
    }
    echo            '</td>',
                '</tr>';

    affLigneInput('Votre nom :', array('type' => 'text', 'name' => 'nom', 'value' => $values['utNom'], 'required' => null));
    affLigneInput('Votre prénom :', array('type' => 'text', 'name' => 'prenom', 'value' => $values['utPrenom'], 'required' => null));
    affLigneInput('Votre email :', array('type' => 'email', 'name' => 'email', 'value' => $values['utEmail'], 'required' => null));
    affLigneInput('Mot de passe :', array('type' => 'password', 'name' => 'passe', 'value' => '', 'required' => ''));

    echo
                '<tr>',
                    '<td colspan="2">',
                        '<label><input type="checkbox" name="cbSpam" value="1"', $values['cbSpam']===1 ? ' checked': '','>',
                        'J\'accepte de recevoir des tonnes de mails pourris</label>',
                    '</td>',
                '</tr>',
                '<tr>',
                    '<td colspan="2">',
                        '<input type="submit" name="btnEnrInfo" value="Enregistrer">',
                        '<input type="reset" value="Réinitialiser">',
                    '</td>',
                '</tr>',
            '</table>',
    '</form>',
    '</section>';
}

/**
 * Affiche le formulaire de modification du mot de passe utilisateur
 *
 * Affiche :
 *  - un message de confirmation stocké en session en cas de succès
 *  - les erreurs éventuelles suite à une soumission invalide
 *
 * @param ?array $err Tableau des erreurs à afficher ou null lors du premier affichage
 *
 * @return void
 */
function affFormulaireMdpL(?array $err) : void {
    echo '<section>',
            '<h2>Mot de passe</h2>',
            '<p>Vous pouvez modifier votre mot de passe ci-dessous.</p>';

    if(isset($_SESSION['ok_mdp'])){
        echo '<p style="color: green; text-align: center;">', $_SESSION['ok_mdp'], '</p>';
        unset($_SESSION['ok_mdp']);
    }

    if (is_array($err)) {
        echo
            '<div class="erreur">Les erreurs suivantes ont été relevées lors de votre inscription :',
                '<ul>';
        foreach ($err as $e) {
            echo    '<li>', $e, '</li>';
        }
        echo    '</ul>',
            '</div>';
    }
    echo
            '<form method="post" action="', basename($_SERVER['PHP_SELF']),'">',
                '<table>',
    affLigneInput('Ancien mot de passe :', array('type' => 'password', 'name' => 'ancien', 'value' => '', 'required' => ''));
    affLigneInput('Choisissez un nouveau mot de passe :', array('type' => 'password', 'name' => 'passe1', 'value' => '',
                                                          'placeholder' => LMIN_PASSWORD . ' caractères minimum', 'required' => null));
    affLigneInput('Répétez mot de passe :', array('type' => 'password', 'name' => 'passe2', 'value' => '', 'required' => ''));
    echo
                    '<tr>',
                        '<td colspan="2">',
                            '<input type="submit" name="btnEnrMdp" value="Enregistrer">',
                        '</td>',
                    '</tr>',
                '</table>',
            '</form>',
        '</section>';
}
