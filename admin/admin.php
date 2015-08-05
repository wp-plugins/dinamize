<?php
require_once DINAMIZE_PLUGIN_DIR . '/admin/class.list-form.php';

function dinamize_admin_menu() {
	add_object_page( __( 'Dinamize', 'dinamize' ),
		__( 'Dinamize', 'dinamize' ),
		'dinamize_read_forms', 'dinamize',
		'dinamize_forms_list', 'dashicons-admin-tools' );

	$edit = add_submenu_page( 'dinamize',
		__( 'List of registered forms', 'dinamize' ),
		__( 'Forms', 'dinamize' ),
		'dinamize_read_forms', 'dinamize',
		'dinamize_forms_list' );

	add_action( 'load-' . $edit, 'dinamize_load_form_admin' );

	$addnew = add_submenu_page( 'dinamize',
		__( 'Adding new form', 'dinamize' ),
		__( 'New form', 'dinamize' ),
		'dinamize_edit_forms', 'dinamize-forms-new',
		'dinamize_forms_add' );

	add_action( 'load-' . $addnew, 'dinamize_load_form_admin' );
}

function dinamize_load_form_admin() {
	global $plugin_page;
	
	$action = $_REQUEST['action'];
	
	if ( 'save' == $action ) {
		$id = $_POST['post_ID'];
		check_admin_referer( 'dinamize-save-form_' . $id );
	
		if ( ! current_user_can( 'dinamize_edit_form', $id ) )
			wp_die( __( 'You are not allowed to update this item.', 'dinamize' ) );
	
		$id = dinamize_save_form( $id );
	
		$query = array( 'message' => ( -1 == $_POST['post_ID'] ) ? 'created' : 'saved' );

		$redirect_to = add_query_arg( $query, menu_page_url( 'dinamize', false ) );
		wp_safe_redirect( $redirect_to );
		exit();
	}
	if ( 'delete' == $action ) {
		if ( ! empty( $_POST['post_ID'] ) )
			check_admin_referer( 'dinamize-delete-form_' . $_POST['post_ID'] );
		elseif ( ! is_array( $_REQUEST['post'] ) )
			check_admin_referer( 'dinamize-delete-form_' . $_REQUEST['post'] );
		else
			check_admin_referer( 'bulk-posts' );
		
		$posts = empty( $_POST['post_ID'] )
		? (array) $_REQUEST['post']
		: (array) $_POST['post_ID'];
		
		$deleted = 0;
		
		foreach ( $posts as $post ) {
			$post = Dinamize_Forms::get_instance( $post );
		
			if ( empty( $post ) )
				continue;
		
			if ( ! current_user_can( 'dinamize_delete_form', $post->id() ) )
				wp_die( __( 'You are not allowed to delete this item.', 'dinamize' ) );
		
			if ( ! $post->delete() )
				wp_die( __( 'Error removing', 'dinamize' ) );
		
			$deleted += 1;
		}
		
		$query = array();
		
		if ( ! empty( $deleted ) )
			$query['message'] = 'deleted';
		
		$redirect_to = add_query_arg( $query, menu_page_url( 'dinamize', false ) );
		
		wp_safe_redirect( $redirect_to );
		exit();
	}
	
	$_GET['post'] = isset( $_GET['post'] ) ? $_GET['post'] : '';
	
	$post = null;

	$current_screen = get_current_screen();
	
	// Pagina de inclusão de formulário
	if ( 'dinamize-forms-new' == $plugin_page ) {
		$post = Dinamize_Forms::get_template( array() );
		if (empty($_REQUEST["error"])) {
			unset($_SESSION["Dinamize_Form"]);
		}
	} else {
		if ( ! empty( $_GET['post'] ) ) {
			$post = Dinamize_Forms::get_instance( $_GET['post'] );
		}
		
		add_filter( 'manage_' . $current_screen->id . '_columns',
			array( 'Dinamize_List_Form', 'define_columns' ) );
		
		add_screen_option( 'per_page', array(
			'label' => __( 'Forms', 'dinamize' ),
			'default' => 20,
			'option' => 'dinamize_forms_per_page' ) );
	}
}

function dinamize_forms_list() {
	if ( $post = dinamize_get_current_form() ) {
		dinamize_forms_add();
		return;
	}
	
	unset($_SESSION["Dinamize_Form"]);
    echo '<div class="wrap">';
    echo '<h2>';
    echo esc_html( __( 'List of registered forms', 'dinamize' ) );
    echo ' <a href="' . esc_url( menu_page_url( 'dinamize-forms-new', false ) ) . '" class="add-new-h2">' . esc_html( __( 'New form', 'dinamize' ) ) . '</a>';
    echo '</h2>';
    
	$list_table = new Dinamize_List_Form();
	$list_table->prepare_items();
	echo '<form method="get" action="">';
	echo '<input type="hidden" name="page" value="'.esc_attr( $_REQUEST['page'] ).'" />';
    $list_table->display();
	echo '</form>';
	
	echo '</div>';
}

function dinamize_forms_add() {
	if ( $post = dinamize_get_current_form() ) {
		$post_id = $post->initial() ? -1 : $post->id();
	}

	echo '<div class="wrap">';
	
	echo '<h2>';
	if ( $post->initial() ) {
		echo esc_html( __( 'Adding new form', 'dinamize' ) );
	} else {
		echo esc_html( __( 'Editing form', 'dinamize' ) );
	
		if ( current_user_can( 'dinamize_edit_forms' ) ) {
			echo ' <a href="' . esc_url( menu_page_url( 'dinamize-forms-new', false ) ) . '" class="add-new-h2">' . esc_html( __( 'New form', 'dinamize' ) ) . '</a>';
		}
	}
	echo '</h2>';
	
	$title_value = (($post->initial()) ? '' : $post->title());
	$hash_value = '';
	if (isset($_SESSION["Dinamize_Form"])) {
		if (!empty($_SESSION["Dinamize_Form"]["title"])) {
			$title_value = $_SESSION["Dinamize_Form"]["title"];
		}
		if (!empty($_SESSION["Dinamize_Form"]["hash"])) {
			$hash_value = $_SESSION["Dinamize_Form"]["hash"];
		}
	}
		
    echo '<div style="width: 400px; float: left;">';
    // Mensagem de erro
    if (!empty($_SESSION["Dinamize_Form"]["err_msg"])) {
        echo '<p style="border: solid 1px #F00; background-color: #FCC; padding: 10px;">';
        echo $_SESSION["Dinamize_Form"]["err_msg"];
        echo '</p>';
    }
	echo '<form method="post" action="'.esc_url( add_query_arg( array( 'post' => $post_id ), menu_page_url( 'dinamize', false ) ) ).'" id="dinamize-admin-form-element" '.do_action( 'dinamize_post_edit_form' ).'>';
	if ( current_user_can( 'dinamize_edit_form', $post_id ) ) {
		wp_nonce_field( 'dinamize-save-form_' . $post_id );
	}
	echo '<input type="hidden" id="post_ID" name="post_ID" value="'.((int) $post_id).'" />';
	echo '<input type="hidden" id="hiddenaction" name="action" value="save" />';
    echo '<p>';
    echo '<label for="post_title">'.__('Form title', 'dinamize').':</label>';
    echo '<input id="post_title" type="text" value="'.$title_value.'" name="post_title" class="widefat" />';
    echo '</p>';
    $display_hash = "block";
    if ( ! $post->initial() ) {
	    echo '<p class="description" style="position: relative;">';
	    echo '<label for="post-shortcode">'.esc_html( __( "Shortcode", 'dinamize' ) ).'</label>';
	    echo '<span class="shortcode wp-ui-highlight"><input type="text" id="post-shortcode" onfocus="this.select();" readonly="readonly" class="large-text code" value="'.esc_attr( $post->shortcode() ).'" /></span>';
	    echo '<span class="dinamize-buttom-replace" style="position: absolute; left: 409px; bottom: 2px;">';
	    echo '<span style="height: 0px; width: 0px; display: inline-block; position: absolute; border-right: 10px solid #00A0D2; float: left; left: -9px; top: 5px; border-top: 9px solid transparent; border-bottom: 9px solid transparent;"></span>';
	    echo '<input type="button" class="button-primary" value="'.esc_attr( __( 'Replace contents of the form', 'dinamize' ) ).'" onClick="jQuery(\'.dinamize-buttom-replace\').hide();jQuery(\'#container_hash\').show();" />';
	    echo '</span>';
	    echo '</p>';
	    $display_hash = "none";
    }
    
	echo '<p id="container_hash" style="display: '.$display_hash.';">';
	echo '<label for="form_hash">'.__('Form code generated in the Dinamize system', 'dinamize').':</label>';
	echo '<input id="form_hash" type="text" value="'.$hash_value.'" name="form_hash" class="widefat" />';
	echo '</p>';
	
	echo '<p class="submit">';
	dinamize_admin_save_button( $post_id );
    if ( current_user_can( 'dinamize_edit_form', $post_id ) && ! $post->initial()) {
		echo '<span style="float: right;">';
    	$delete_nonce = wp_create_nonce( 'dinamize-delete-form_' . $post_id );
    	echo '<a style="line-height: 28px; color: #f00; cursor: pointer; text-decoration: underline;" onClick="if (confirm(\''. esc_js( __("Are you sure you want to remove this form?\n 'Cancel' to stop, 'OK' to remove.", 'dinamize' ) ) . '\')) {this.form._wpnonce.value = \''.$delete_nonce.'\'; this.form.action.value = \'delete\'; return true;} return false;">'.esc_attr( __( 'Remove', 'dinamize' ) ).'</a>';
    	echo '</span>';
    }
    
    echo '</p>';
    echo '</form>';
    echo '</div>';
	echo '</div>';
}

function dinamize_admin_save_button( $post_id ) {
	static $button = '';

	if ( ! empty( $button ) ) {
		echo $button;
		return;
	}

	$nonce = wp_create_nonce( 'dinamize-save-form_' . $post_id );

	$onclick = sprintf(
			"this.form._wpnonce.value = '%s';"
			. " this.form.action.value = 'save';"
			. " return true;",
			$nonce );

	$button = sprintf(
			'<input type="submit" class="button-primary" style="float: left;" name="dinamize-save" value="%1$s" onclick="%2$s" />',
			esc_attr( __( 'Save', 'dinamize' ) ),
			$onclick );

	echo $button;
}

function dinamize_save_form( $post_id = -1 ) {
	$edit_mode = false;
	if ( -1 != $post_id ) {
		$form = dinamize_form( $post_id );
		$edit_mode = true;
	}
	
	if ( empty( $form ) ) {
		$form = Dinamize_Forms::get_template();
		$edit_mode = false;
	}

	$_SESSION["Dinamize_Form"] = Array();
	if ( isset( $_POST['post_title'] ) ) {
		$_SESSION["Dinamize_Form"]["title"] = $_POST['post_title'];
		$form->set_title( $_POST['post_title'] );
	}

	$query = array( 'error' => 1, 'post' => $post_id );
	$page = 'dinamize-forms-new';
	if ( $edit_mode ) {
		$query['edit'] = 1;
		$page = 'dinamize';
	}
	
	// Se tiver em modo de edição trocando o hash ou modo de inclusão
	if (($edit_mode && !empty($_POST['form_hash'])) || !$edit_mode) {
		$properties = $form->get_properties();
		
		$_SESSION["Dinamize_Form"]["hash"] = $_POST['form_hash'];
		$ret = dinamize_get_form_by_hash($_POST['form_hash']);
		if ($ret === false) {
			$redirect_to = add_query_arg( $query, menu_page_url( $page, false ) );
			wp_safe_redirect( $redirect_to );
			exit();
		}
		
		$properties['form_hash'] = trim( $_POST['form_hash'] );
		$properties['html'] = $ret;

		$form->set_properties( $properties );
	}

	unset($_SESSION["Dinamize_Form"]);

	do_action( 'dinamize_save_form', $form );
	
	return $form->save();
}

function dinamize_get_form_by_hash($hash) {
	$args = Array(
			'timeout'     => 30,
			'redirection' => 0,
			'httpversion' => '1.1',
			'user-agent'  => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' ),
			'blocking'    => true,
			'headers'     => Array(),
			'cookies'     => Array(),
			'body'        => null,
			'compress'    => false,
			'decompress'  => true,
			'sslverify'   => false,
			'stream'      => false,
			'filename'    => null
	);

	
	$ret = wp_remote_get("http://download.josue.dev.com/d/".$hash, $args);
	if (!is_array($ret)) {
		$_SESSION["Dinamize_Form"]["err_msg"] = __("There was a communication error to download the form, please try again later.", "dinamize");
		return false;
	}
	if (empty($ret["response"]["message"]) || $ret["response"]["message"] != "OK") {
		$_SESSION["Dinamize_Form"]["err_msg"] = __("An error occurred while to download the form, please check the copied code.", "dinamize");
		return false;
	}

	return $ret["body"];
}


add_action( 'admin_menu', 'dinamize_admin_menu', 9 );
?>