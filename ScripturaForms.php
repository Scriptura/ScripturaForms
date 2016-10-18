<?php

// -----------------------------------------------------------------------------
// @Plugin Name: Scriptura Forms
// @Plugin URI: https://github.com/Scriptura/ScripturaForms
// @Description: Formulaires
// @Version: 0.0.1
// @Author: Olivier Chavarin
// @Author URI: https://scriptura.github.io/
// @License: ISC
// -----------------------------------------------------------------------------


// @subsection  Languages
// @description Externalisation du plugin
// -----------------------------------------------------------------------------

// @note Fichiers d'externalisation pour les traductions du thème
// @note Name de domaine et emplacement des fichiers de traduction
//load_theme_textdomain( 'scriptura', plugins_url( 'Languages', __FILE__ ) );
load_plugin_textdomain( 'scriptura', false, dirname( plugin_basename( __FILE__ ) ) . '/Languages' );


// -----------------------------------------------------------------------------
// @section     Contact Form
// @description Configuration du formulaire de contact
// -----------------------------------------------------------------------------

if ( ! is_admin() ) {

  $uri = '//' . $_SERVER[ 'HTTP_HOST' ] . $_SERVER[ 'REQUEST_URI' ]; // Alternative à get_permalink()
  function ScripturaContactForm ( $mail = '', $uri = '' )
  {
    // BEGIN Test
    $capacityRead = current_user_can( 'read' );
    // END Test
    ob_start();
    // Passage à la ligne, normalisation pour certaines messageries
    if ( ! preg_match( '#^[a-z0-9._-]+@(hotmail|live|msn).[a-z]{2,4}$#', $mail ) ) {
        $oel = "\r\n";
    } else {
        $oel = "\n";
    }
    if ( isset( $_POST[ 'submit' ] ) ) : // Cette condition est un test permettant d'éviter un conflit avec un autre formulaire placé sur la même page
    if ( ! empty( $_POST ) ) :
    extract( $_POST ); // @note Vérification nom de variable valide
    $valid = true;
    // BEGIN Controllers
    // Controller name:
    if ( strlen( $nom ) < 2 ) {
      $valid = false;
      $erreurNom = '<p>' . __( 'Invalid field: your name contains a single character.', 'scriptura' ) . '</p>';
    }
    if ( preg_match( '#.{30,}#', $nom ) ) {
      $valid = false;
      $erreurNom = '<p>' . __( 'Invalid field: your name is longer than 30 characters.', 'scriptura' ) . '</p>';
    }
    if ( ! preg_match( '#^[A-Z]#', $nom ) ) {
      $valid = false;
      $erreurNom = '<p>' . __( 'Invalid field: it lacks a capital letter at the beginning of your name.', 'scriptura' ) . '</p>';
    }
    if ( preg_match( '#[0-9]#', $nom ) ) {
      $valid = false;
      $erreurNom = '<p>' . __( 'Invalid field: numeric characters are placed in your name.', 'scriptura' ) . '</p>';
    }
    if ( preg_match( '#[&!?/\+=_;:,$*()<>§@\#\".]#', $nom ) ) {
      $valid = false;
      $erreurNom = '<p>' . __( 'Invalid field: special characters or punctuation are placed in your name.', 'scriptura' ) . '</p>';
    }
    if ( empty( $nom ) ) {
      $valid = false;
      $erreurNom = '<p>' . __( 'Invalid field: your name is not specified.', 'scriptura' ) . '</p>';
    }
    // Controller email:
    if ( ! preg_match( '#^[a-z0-9._-]{3,50}@[a-z0-9._-]{2,}\.[a-z]{2,4}$#i', $email ) ) {
      $valid = false;
      $erreurEmail = '<p>' . __( 'Invalid field: your email is not consistent.', 'scriptura' ) . '</p>';
    }
    if ( empty( $email ) ) {
      $valid = false;
      $erreurEmail = '<p>' . __( 'Invalid field: your email is blank.', 'scriptura' ) . '</p>';
    }
    // Controller tel:
    if ( ! empty( $tel ) ) {
      if ( ! preg_match( '#^((\+\d{1,3}(-| )?\(?\d\)?(-| )?\d{1,5})|(\(?\d{2,6}\)?))(-| )?(\d{3,4})(-| )?(\d{4})(( x| ext)\d{1,5}){0,1}$#', $tel ) ) {
        $valid = false;
        $erreurTel = '<p>' . __( 'Invalid field: this item does not correspond to a local or international number. Do not put any spaces, dashes or points.', 'scriptura' ) . '</p>';
      }
    }
    // Controller pays:
    if ( preg_match( '#00#', $pays ) ) {
      $valid = false;
      $erreurPays = '<p>' . __( 'You must choose a country.', 'scriptura' ) . '</p>';
    }
    // Controller cover:
    if ( ! empty( $test ) ) {
      $valid = false;
    }
    // Controller message:
    if ( ( preg_match( '#<a(.+)<a(.+)<a#', $message ) )
    OR ( preg_match( '#http(.+)http(.+)http#', $message ) ) ) {
      $valid = false;
      $erreurMessage = '<p>' . __( 'Invalid field: your message contains more than two links (spam protection).', 'scriptura' ) . '</p>';
    }
    if ( strlen( $message ) < 10 ) {
      $valid = false;
      $erreurMessage = '<p>' . __( 'Invalid field: your message has less than 10 characters.', 'scriptura' ) . '</p>';
    }
    if ( strlen( $message ) > 4000 ) {
      $valid = false;
      $erreurMessage = '<p>' . __( 'Invalid field: your message is longer than 4000 characters.', 'scriptura' ) . '</p>';
    }
    if ( empty( $message ) ) {
      $valid = false;
      $erreurMessage = '<p>' . __( 'Invalid field: you have not completed your message.', 'scriptura' ) . '</p>';
    }


// BEGIN Test
if ( $capacityRead ) {
  $uploaddir = '//test/';
  $uploadfile = $uploaddir . basename($_FILES['file']['name']);
  echo '<div class="message">';
  if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
      echo "Le fichier est valide, et a été téléchargé
             avec succès. Voici plus d'informations :\n";
  } else {
      echo "Attaque potentielle par téléchargement de fichiers.
            Voici plus d'informations :\n";
  }
  echo '</div>';
}
// END Test





    // Controller-error:
    if ( ! $valid ) {
      $erreurNotification = '<div id="popin" class="popin">';
      $erreurNotification .= '<p class="message-warning">' . __( 'An error was detected on the form and it could not be sent. Please check all fields.', 'scriptura' ) . '</p>';
      $erreurNotification .= '<a href="#" id="cmd-popin"></a>';
      $erreurNotification .= '</div>';
    }
    // END Controllers
    if ( $valid ) :
      $nom = htmlentities( addslashes( $nom ) );
      $nom = stripslashes( $_POST[ 'nom' ] );
      $email = htmlentities( addslashes( $email ) );
      $tel = htmlentities( addslashes( $tel ) );
      $message = htmlentities( addslashes( $message ) );
      $message = stripslashes( $_POST[ 'message' ] );
      // BEGIN Test
      if ( $capacityRead ) {
        $file = htmlentities( addslashes( $file ) );
      }
      // END Test
      $emailContacts = get_option( 'scriptura_emails_contact' );
    if ( $emailsContact ) {
      $to = $emailContacts;
    } else {
      $to = get_option( 'admin_email' );
    }
      $toCopie = $email;
      $subject = 'Formulaire de contact';
      $subjectCopie = 'Copie de votre message';
      $post  = __( 'Name:', 'scriptura' ) . $nom . '' . $oel;
      $post .= __( 'Email:', 'scriptura' ) . $email . '' . $oel;
      $post .= __( 'Phone:', 'scriptura' ) . $tel . '' . $oel;
      $post .= __( 'Country:', 'scriptura' ) . $pays . '' . $oel . '' . $oel;
      $post .= __( 'Message:', 'scriptura' ) . $message;
      $header = 'Reply-To: ' . $to . '' . $oel;
      $headerCopie = 'Reply-To: ' . $email . '' . $oel;
      mail( $to, $subject, $post, $header );
      mail( $toCopie, $subjectCopie, $post, $headerCopie );
      $formPost = '<div><p class="message-success">' . __( 'Thank you Mr (Mrs)', 'scriptura' ) . ' <b>' . $nom . '</b>. ' . __( 'Your message has been posted. A copy was sent to the entered email address', 'scriptura' ) . ' (' . $email . ').</p></div>';
      $formPost .= '<div><form action="' . $uri . '" method="post"><button class="button"><span class="icon-paper-plane"></span>&nbsp;&nbsp;' . __( 'New message', 'scriptura' ) . '</button></form></div>';
      unset( $nom );
      unset( $email );
      unset( $tel );
      unset( $pays );
      unset( $test );
      unset( $message );
      // BEGIN Test
      if ( $capacityRead ) {
        unset( $file );
      }
      // END Test
    endif;
    endif;
    endif; // isset($_POST[ 'submit' ] )
    if ( isset( $formPost ) ) :
    echo $formPost;
      else :
    //echo '<h2 class="emphasized-left">' . __( 'Contact form', 'scriptura' ) . '</h2>';
    echo '<form method="post" action="' . $uri . '">';
    echo '<fieldset>';
    if ( isset( $erreurNotification ) )
      echo $erreurNotification;
    $invalidNom = '';
    if ( isset( $erreurNom ) )
      $invalidNom = ' invalid';
    echo '<div class="input' . $invalidNom . '"><label for="nom">' . __( 'Name', 'scriptura' ) . '</label><input type="text" name="nom" id="nom" value="';
    if ( isset( $nom ) )
      echo stripslashes( $nom );
    echo '" size=23 placeholder="' . __( 'John Smith', 'scriptura' ) . '" class="required" required="required">';
    if ( isset( $erreurNom ) )
      echo $erreurNom;
    echo '</div>';
    $invalidEmail = '';
    if ( isset( $erreurEmail ) )
      $invalidEmail = ' invalid';
    echo '<div class="input' . $invalidEmail . '"><label for="email">' . __( 'Email', 'scriptura' ) . '</label><input type="email" name="email" id="email" value="';
    if ( isset( $email ) )
      echo $email;
    echo '" size=23 placeholder="pseudo@gmail.com" class="required" required="required">';
    if ( isset( $erreurEmail ) )
      echo $erreurEmail;
    echo '</div>';
    $invalidTel = '';
    if ( isset( $erreurTel ) )
      $invalidTel = ' invalid';
    echo '<div class="input' . $invalidTel . '"><label for="tel">' . __( 'Phone', 'scriptura' ) . '</label><input type="tel" name="tel" id="tel" value="';
    if ( isset( $tel ) )
      echo stripslashes( $tel );
    echo '" size=23  placeholder="0158808080">';
    if ( isset( $erreurTel ) )
      echo $erreurTel;
    echo '</div>';
    $invalidPays = '';
    if ( isset( $erreurPays ) )
      $invalidPays = ' invalid';
    echo '<div class="input' . $invalidPays . '"><label for="pays"><span class="icon-world"></span><span>' . __( 'Country', 'scriptura' ) . '</span></label>';
    echo '<select name="pays" id="pays" value="';
    if ( isset( $pays ) )
      echo $pays;
    echo '" class="required">';
    echo '<option value="00" selected="selected">' . __( 'Country', 'scriptura' ) . '</option>'; // Sélection par défaut
    echo '<option value="FR">France</option>'; // Sélection mise en avant
    $uriCsv = plugin_dir_path( __FILE__ ) . 'ListOfCountries.csv'; // Liste des pays, ISO 3166-1
    //@note Le chemin doit être définit localement afin d'être compatible avec le SSL.
    // Boucle récupérant la liste de tous les pays
    $id_file = fopen( $uriCsv, 'r' ); // 'r' lecture seule
    while ( $line = fgets( $id_file, 1024 ) ) { // '1024' Nombre d'octets max par ligne et par défaut
      $line = explode( ' ; ', $line ); // Choix du séparateur entre les données de la ligne
      echo '<option value="' . $line[ 0 ] . '">' . $line[ 1 ] . '</option>'; // Retourne toutes les valeurs sur ce format
      if ( $pays == $line[0] ) // Sélection en cours
        echo '<option value="' . $line[ 0 ] . '" selected="selected">' . $line[ 1 ] . '</option>'; // Retourne la sélection en cours
    }
    echo '</select>';
    if ( isset( $erreurPays ) )
      echo $erreurPays;
    echo '</div>';
    echo '<div class="input hidden"><label for="test">Test</label>';
    echo '<input type="text" name="test" id="test" value="';
    if ( isset( $test ) )
      echo stripslashes( $test );
    echo '" size=23 placeholder="requis"></div>';
    $invalidMessage = '';
    if ( isset( $erreurMessage ) )
      $invalidMessage = ' invalid';
    echo '<div class="input' . $invalidMessage . '"><label for="message">' . __( 'Your message', 'scriptura' ) . '</label><textarea name="message" id="message" placeholder="' . __( 'Good Morning...', 'scriptura' ) . '" class="required" required="required">';
    if ( isset( $message ) )
      echo stripslashes( $message );
    echo '</textarea>';
    if ( isset( $erreurMessage ) )
      echo $erreurMessage;
    echo '</div>';

    // BEGIN Test
    if ( $capacityRead ) {
        echo '<div class="input"><label for="file">' . __( 'File', 'scriptura' ) . '</label><input type="file" name="file" id="file" value="';
      if ( isset( $file ) )
        echo stripslashes( $file );
      echo '" size=23>';
      if ( isset( $erreurFile ) )
        echo $erreurFile;
      echo '</div>';
    }
    // END Test

    echo '<div><button class="button" name="submit"><span class="icon-paper-plane"></span>&nbsp;&nbsp;' . __('Submit', 'scriptura') . '</button></div>';
    echo '</fieldset>';
    echo '</form>';
    endif; // isset( $formPost )
    $contactForm = ob_get_clean();
    return $contactForm;
  }
  add_shortcode( '_contact-form', 'ScripturaContactForm' ); // Permet d'appeler le script via un shortcode

} // admin


// -----------------------------------------------------------------------------
// @subsection  TinyMCE
// @description Configuration de TinyMCE v4
// -----------------------------------------------------------------------------

if ( is_admin() ) {

    function ScripturaFormsCmd ()
    {
      global $typenow; // Récupère la variable de contexte du type de post
      if ( ! in_array ( $typenow, [ 'post', 'page' ] ) ) // Activation du plugin pour les articles et les pages
        return;
      add_filter ( 'mce_external_plugins', 'ScripturaAddTinymcePluginForms' ); // Ajout javascript à l'éditeur de WP
      add_filter ( 'mce_buttons_4', 'ScripturaAddTinymceButtonForms' ); // Ajoute un bouton à la première ligne de boutons
    }
    add_action( 'admin_head', 'ScripturaFormsCmd' );

    function ScripturaAddTinymcePluginForms ( $plugin )
    {
      $plugin[ 'ScripturaButtonForms' ] = plugins_url( 'Scripts.js', __FILE__ ); // Emplacement de la fonction des bouttons
      return $plugin;
    }

    function ScripturaAddTinymceButtonForms ( $buttons )
    { // Id du bouton pour faire la correspondance avec le JS
      array_push( $buttons, 'ScripturaButtonContactFormAdd' ); // Passage d'un tableau contenant l'id du bouton, pour ajouter d'autres boutons il suffit de passer les autres id
      return $buttons;
    }

} // admin

