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

// -----------------------------------------------------------------------------
// @section     Shortcodes
// @description Configuration du shortcode
// -----------------------------------------------------------------------------

if ( ! is_admin() ) {

  $uri = '//' .$_SERVER[ 'HTTP_HOST' ] . $_SERVER[ 'REQUEST_URI' ]; // Alternative à get_permalink()

  function ScripturaContactForm () {

    ob_start();

    // Passage à la ligne, normalisation pour certaines messageries
    if ( ! preg_match( '#^[a-z0-9._-]+@(hotmail|live|msn).[a-z]{2,4}$#', $mail ) ) :
    $oel = "\r\n";
    else :
    $oel = "\n";
    endif;

    if ( isset( $_POST[ 'submit' ] ) ) : // Cette condition est un test permettant d'éviter un conflit avec un autre formulaire placé sur la même page
    if ( ! empty( $_POST ) ) :
    extract( $_POST ); // @note Vérification nom de variable valide
    $valid = true;

    // BEGIN Controllers

    // Controller name:
    if ( strlen( $nom ) < 2 ) {
      $valid = false;
      $erreurNom = '<p>' . __( 'Invalid field: name contains a single character.', 'scriptura' ) . '</p>';
    }
    if ( preg_match( '#.{30,}#', $nom ) ) {
      $valid = false;
      $erreurNom = '<p>' . __( 'Invalid field: Your name is longer than 30 characters.', 'scriptura' ) . '</p>';
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
      $erreurNom = '<p>' . __( 'Invalid field: Your name is not specified.', 'scriptura' ) . '</p>';
    }

    // Controller email:
    if ( ! preg_match( '#^[a-z0-9._-]{3,50}@[a-z0-9._-]{2,}\.[a-z]{2,4}$#i', $email ) ) {
      $valid = false;
      $erreurEmail = '<p>' . __( 'Invalid field: Your email is not consistent.', 'scriptura' ) . '</p>';
    }
    if ( empty( $email ) ) {
      $valid = false;
      $erreurEmail = '<p>' . __( 'Invalid field: Your email is blank.', 'scriptura' ) . '</p>';
    }

    // Controller tel:
    if ( ! empty( $tel ) ) {
      if ( ! preg_match( '#^((\+\d{1,3}(-| )?\(?\d\)?(-| )?\d{1,5})|(\(?\d{2,6}\)?))(-| )?(\d{3,4})(-| )?(\d{4})(( x| ext)\d{1,5}){0,1}$#', $tel ) ) {
        $valid = false;
        $erreurTel = '<p>' . __( 'Invalid field: This item does not correspond to a local or international number. Do not put any spaces, dashes or points.', 'scriptura' ) . '</p>';
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
      $erreurMessage = '<p>' . __( 'Invalid field : Your Message contains more than two links (spam protection).', 'scriptura' ) . '</p>';
    }
    if ( strlen( $message ) < 10 ) {
      $valid = false;
      $erreurMessage = '<p>' . __( 'Invalid field : Your Message has less than 10 characters.', 'scriptura' ) . '</p>';
    }
    if ( strlen( $message ) > 4000) {
      $valid = false;
      $erreurMessage = '<p>' . __( 'Invalid field : Your message is longer than 4000 characters.', 'scriptura' ) . '</p>';
    }
    if ( empty( $message ) ) {
      $valid = false;
      $erreurMessage = '<p>' . __( 'Invalid field : you have not completed your message.', 'scriptura' ) . '</p>';
    }

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
    if ( get_option( 'scriptura_email_contact' ) ) :
      $to = get_option( 'scriptura_email_contact' );
    else :
      $to = get_option( 'admin_email' );
    endif;
      $toCopie = $email;
      $subject = 'Formulaire de contact';
      $subjectCopie = 'Copie de votre message';
      $post  = 'Nom : ' . $nom . '' . $oel;
      $post .= 'Email : ' . $email . '' . $oel;
      $post .= 'Téléphone : ' . $tel . '' . $oel;
      $post .= 'Pays : ' . $pays . '' . $oel . '' . $oel;
      $post .= 'Message : ' . $message;
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

    $uriCsv = plugins_url( 'ListOfCountries.csv', __FILE__ ); // Liste des pays, ISO 3166-1

    // Boucle récupérant la liste de tous les pays
    $id_file = fopen( $uriCsv, 'r' ); // 'r' lecture seule
    while ( $line = fgets( $id_file, 1024 ) ) { // '1024' Nombre d'octets max par ligne et par défaut
      $line = explode( ' ; ', $line ); // Choix du séparateur entre les données de la ligne
      echo '<option value="' . $line[0] . '">' . $line[1] . '</option>'; // Retourne toutes les valeurs sur ce format
      if ( $pays == $line[0] ) // Sélection en cours
        echo '<option value="' . $line[0] . '" selected="selected">' . $line[1] . '</option>'; // Retourne la sélection en cours
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
    echo '<div><button class="button" name="submit"><span class="icon-paper-plane"></span>&nbsp;&nbsp;' . __('Submit', 'scriptura') . '</button></div>';
    echo '</fieldset>';
    echo '</form>';
    endif; // isset($formPost)
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

}

