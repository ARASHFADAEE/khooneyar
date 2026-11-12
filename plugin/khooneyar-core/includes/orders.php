<?php
/**
 * Order form backend: validation, anti-spam, storage (order_request CPT), e-mail,
 * admin list with status (new / called / done) and WhatsApp hand-off.
 *
 * @package Khooneyar_Core
 */

defined( 'ABSPATH' ) || exit;

/* ------------------------------------------------------------------
 * CPT: order_request (private, admin UI only)
 * ---------------------------------------------------------------- */

add_action( 'init', 'khy_register_order_cpt' );
function khy_register_order_cpt() {
	$cap = 'manage_options';
	register_post_type(
		'order_request',
		array(
			'labels'              => array(
				'name'               => 'سفارش‌ها',
				'singular_name'      => 'سفارش',
				'menu_name'          => 'سفارش‌ها',
				'all_items'          => 'همهٔ سفارش‌ها',
				'edit_item'          => 'مشاهدهٔ سفارش',
				'search_items'       => 'جستجوی سفارش',
				'not_found'          => 'سفارشی ثبت نشده است',
				'not_found_in_trash' => 'سفارشی در زباله‌دان نیست',
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'menu_icon'           => 'dashicons-clipboard',
			'menu_position'       => 20,
			'supports'            => array( 'title' ),
			'map_meta_cap'        => true,
			'capability_type'     => 'post',
			'capabilities'        => array(
				'edit_posts'             => $cap,
				'edit_others_posts'      => $cap,
				'publish_posts'          => $cap,
				'read_private_posts'     => $cap,
				'delete_posts'           => $cap,
				'delete_private_posts'   => $cap,
				'delete_published_posts' => $cap,
				'delete_others_posts'    => $cap,
				'edit_private_posts'     => $cap,
				'edit_published_posts'   => $cap,
				'create_posts'           => 'do_not_allow',
			),
		)
	);
}

/**
 * Order statuses.
 *
 * @return array
 */
function khy_order_statuses() {
	return array(
		'new'    => 'جدید',
		'called' => 'تماس گرفته شد',
		'done'   => 'انجام شد',
	);
}

/**
 * Preferred-time choices.
 *
 * @return array
 */
function khy_time_choices() {
	return array(
		'asap'     => 'در اسرع وقت',
		'today'    => 'امروز',
		'tomorrow' => 'فردا',
		'week'     => 'طی این هفته',
	);
}

/* ------------------------------------------------------------------
 * Validation helpers
 * ---------------------------------------------------------------- */

/**
 * Normalise an Iranian mobile number to 09xxxxxxxxx; false if invalid.
 *
 * @param string $raw Raw input.
 * @return string|false
 */
function khy_normalize_mobile( $raw ) {
	$n = khy_to_ascii_digits( (string) $raw );
	$n = preg_replace( '/[\s\-\(\)\.]/u', '', $n );
	$n = preg_replace( '/^(\+98|0098|98)/', '0', $n );
	if ( preg_match( '/^9\d{9}$/', $n ) ) {
		$n = '0' . $n;
	}
	return preg_match( '/^09\d{9}$/', $n ) ? $n : false;
}

/**
 * Choices for the form <select>s.
 *
 * @return array { services: id=>title, areas: id=>title }
 */
function khy_order_choices() {
	$services = array();
	foreach ( get_posts( array( 'post_type' => 'service', 'posts_per_page' => 50, 'orderby' => 'menu_order title', 'order' => 'ASC', 'no_found_rows' => true ) ) as $s ) {
		$services[ $s->ID ] = $s->post_title;
	}
	$areas = array();
	foreach ( get_posts( array( 'post_type' => 'neighborhood', 'posts_per_page' => 50, 'orderby' => 'menu_order title', 'order' => 'ASC', 'no_found_rows' => true ) ) as $n ) {
		$areas[ $n->ID ] = $n->post_title;
	}
	return array( 'services' => $services, 'areas' => $areas );
}

/**
 * Validate and sanitise raw input.
 *
 * @param array $in Raw (unslashed) input.
 * @return array { data: array, errors: array }
 */
function khy_validate_order( $in ) {
	$errors  = array();
	$choices = khy_order_choices();

	$name = trim( preg_replace( '/\s+/u', ' ', sanitize_text_field( $in['khy_name'] ?? '' ) ) );
	$len  = function_exists( 'mb_strlen' ) ? mb_strlen( $name ) : strlen( $name );
	if ( $len < 2 ) {
		$errors['khy_name'] = 'لطفاً نام خود را وارد کنید.';
	} elseif ( $len > 60 ) {
		$errors['khy_name'] = 'نام نباید بیشتر از ۶۰ نویسه باشد.';
	}

	$phone = khy_normalize_mobile( $in['khy_phone'] ?? '' );
	if ( ! $phone ) {
		$errors['khy_phone'] = 'شمارهٔ موبایل معتبر نیست. مثال: ۰۹۱۲۱۲۳۴۵۶۷';
	}

	$service_id = absint( $in['khy_service'] ?? 0 );
	if ( ! $service_id || ! isset( $choices['services'][ $service_id ] ) ) {
		$errors['khy_service'] = 'لطفاً نوع خدمت را انتخاب کنید.';
	}

	$area_raw = $in['khy_area'] ?? '';
	$area_id  = absint( $area_raw );
	if ( $area_id && ! isset( $choices['areas'][ $area_id ] ) ) {
		$errors['khy_area'] = 'محله انتخاب‌شده معتبر نیست.';
	}

	$time_key = sanitize_key( $in['khy_time'] ?? 'asap' );
	if ( ! isset( khy_time_choices()[ $time_key ] ) ) {
		$time_key = 'asap';
	}

	$note = sanitize_textarea_field( $in['khy_note'] ?? '' );
	$nlen = function_exists( 'mb_strlen' ) ? mb_strlen( $note ) : strlen( $note );
	if ( $nlen > 500 ) {
		$errors['khy_note'] = 'توضیحات نباید بیشتر از ۵۰۰ نویسه باشد.';
	}

	return array(
		'data'   => array(
			'name'       => $name,
			'phone'      => $phone ? $phone : '',
			'service_id' => $service_id,
			'service'    => $choices['services'][ $service_id ] ?? '',
			'area_id'    => $area_id,
			'area'       => $area_id ? ( $choices['areas'][ $area_id ] ?? '' ) : 'سایر مناطق تهران',
			'time'       => $time_key,
			'time_label' => khy_time_choices()[ $time_key ],
			'note'       => $note,
		),
		'errors' => $errors,
	);
}

/* ------------------------------------------------------------------
 * Anti-spam
 * ---------------------------------------------------------------- */

function khy_client_ip() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0.0.0.0';
	return (string) apply_filters( 'khy_client_ip', $ip );
}

/**
 * Check + consume rate-limit budget. Returns true if allowed.
 *
 * @param string $ip IP address.
 * @return bool
 */
function khy_rate_limit_ok( $ip ) {
	$max    = (int) khy_get_option( 'rate_limit_max', 3 );
	$window = (int) khy_get_option( 'rate_limit_window', 600 );
	$key    = 'khy_rl_' . md5( $ip . wp_salt() );
	$state  = get_transient( $key );
	$now    = time();
	if ( ! is_array( $state ) || ( $now - $state['start'] ) >= $window ) {
		$state = array( 'start' => $now, 'count' => 0 );
	}
	if ( $state['count'] >= $max ) {
		return false;
	}
	++$state['count'];
	set_transient( $key, $state, $window - ( $now - $state['start'] ) + 1 );
	return true;
}

/* ------------------------------------------------------------------
 * Processing
 * ---------------------------------------------------------------- */

/**
 * Tracking code for an order.
 *
 * @param int $id Order post ID.
 * @return string
 */
function khy_order_code( $id ) {
	return 'KH-' . ( 1000 + (int) $id );
}

/**
 * WhatsApp deep-link with a prefilled message.
 *
 * @param array  $d    Cleaned order data.
 * @param string $code Tracking code.
 * @return string
 */
function khy_whatsapp_url( $d, $code ) {
	$msg  = 'سلام، من ' . $d['name'] . ' هستم و درخواست «' . $d['service'] . '» دارم.' . "\n";
	$msg .= 'محله: ' . $d['area'] . "\n";
	$msg .= 'زمان دلخواه: ' . $d['time_label'] . "\n";
	$msg .= 'شمارهٔ تماس: ' . $d['phone'] . "\n";
	$msg .= 'کد پیگیری: ' . $code;
	return 'https://wa.me/' . rawurlencode( khy_get_option( 'whatsapp' ) ) . '?text=' . rawurlencode( $msg );
}

/**
 * Validate, rate-limit, store, e-mail.
 *
 * @param array $in Raw unslashed input.
 * @return array { ok:bool, status:int, message:string, errors:array, code?:string, wa_url?:string, order_id?:int }
 */
function khy_process_order( $in ) {
	// Nonce.
	$nonce = isset( $in['khy_nonce'] ) ? sanitize_text_field( $in['khy_nonce'] ) : '';
	if ( ! wp_verify_nonce( $nonce, 'khy_order' ) ) {
		return array( 'ok' => false, 'status' => 403, 'errors' => array(), 'message' => 'نشست فرم منقضی شده است. صفحه را دوباره باز کنید و فرم را بفرستید.' );
	}

	// Honeypot: pretend success, store nothing.
	if ( ! empty( $in['khy_website'] ) ) {
		return array( 'ok' => true, 'status' => 200, 'errors' => array(), 'message' => 'درخواست شما ثبت شد.', 'code' => 'KH-0000', 'wa_url' => '', 'order_id' => 0, 'honeypot' => true );
	}

	$v = khy_validate_order( $in );
	if ( $v['errors'] ) {
		return array( 'ok' => false, 'status' => 422, 'errors' => $v['errors'], 'message' => 'لطفاً خطاهای فرم را اصلاح کنید.' );
	}
	$d = $v['data'];

	// Duplicate (same phone within 5 minutes).
	$dup_key = 'khy_dup_' . md5( $d['phone'] );
	if ( get_transient( $dup_key ) ) {
		return array( 'ok' => false, 'status' => 429, 'errors' => array(), 'message' => 'درخواست قبلی شما با این شماره ثبت شده و همکاران ما به‌زودی تماس می‌گیرند. برای موضوع فوری تماس بگیرید.' );
	}

	// Rate limit (per IP).
	if ( ! khy_rate_limit_ok( khy_client_ip() ) ) {
		return array( 'ok' => false, 'status' => 429, 'errors' => array(), 'message' => 'تعداد درخواست‌های شما از حد مجاز گذشته است. کمی بعد دوباره تلاش کنید یا تماس بگیرید.' );
	}

	$order_id = wp_insert_post(
		array(
			'post_type'   => 'order_request',
			'post_status' => 'private',
			'post_title'  => $d['name'] . ' — ' . $d['service'],
		),
		true
	);
	if ( is_wp_error( $order_id ) || ! $order_id ) {
		return array( 'ok' => false, 'status' => 500, 'errors' => array(), 'message' => 'ثبت سفارش با مشکل روبه‌رو شد. لطفاً تماس بگیرید.' );
	}

	$code = khy_order_code( $order_id );
	wp_update_post( array( 'ID' => $order_id, 'post_title' => $code . ' — ' . $d['name'] . ' — ' . $d['service'] ) );

	$referer = isset( $in['khy_source'] ) ? esc_url_raw( $in['khy_source'] ) : '';
	$meta    = array(
		'_khy_o_name'    => $d['name'],
		'_khy_o_phone'   => $d['phone'],
		'_khy_o_service' => $d['service'],
		'_khy_o_service_id' => $d['service_id'],
		'_khy_o_area'    => $d['area'],
		'_khy_o_time'    => $d['time_label'],
		'_khy_o_note'    => $d['note'],
		'_khy_o_source'  => $referer,
		'_khy_o_ip'      => wp_hash( khy_client_ip() ),
		'_khy_status'    => 'new',
		'_khy_o_code'    => $code,
	);
	foreach ( $meta as $k => $val ) {
		update_post_meta( $order_id, $k, $val );
	}

	set_transient( $dup_key, 1, 5 * MINUTE_IN_SECONDS );
	khy_send_order_email( $order_id, $d, $code, $referer );

	return array(
		'ok'       => true,
		'status'   => 200,
		'errors'   => array(),
		'message'  => 'درخواست شما ثبت شد.',
		'code'     => $code,
		'wa_url'   => khy_whatsapp_url( $d, $code ),
		'order_id' => $order_id,
		'name'     => $d['name'],
	);
}

function khy_send_order_email( $order_id, $d, $code, $source ) {
	$to      = khy_get_option( 'order_email' );
	$subject = sprintf( '[%s] سفارش جدید %s — %s', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ), $code, $d['service'] );
	$body    = "سفارش جدید ثبت شد\n\n"
		. 'کد: ' . $code . "\n"
		. 'نام: ' . $d['name'] . "\n"
		. 'موبایل: ' . $d['phone'] . "\n"
		. 'خدمت: ' . $d['service'] . "\n"
		. 'محله: ' . $d['area'] . "\n"
		. 'زمان: ' . $d['time_label'] . "\n"
		. 'توضیحات: ' . ( $d['note'] ? $d['note'] : '—' ) . "\n"
		. 'صفحهٔ ثبت: ' . ( $source ? $source : '—' ) . "\n\n"
		. 'مدیریت: ' . admin_url( 'post.php?post=' . $order_id . '&action=edit' ) . "\n";
	wp_mail( $to, $subject, $body, array( 'Content-Type: text/plain; charset=UTF-8' ) );
}

/* ------------------------------------------------------------------
 * Entry points: admin-post (no-JS fallback) + REST (fetch)
 * ---------------------------------------------------------------- */

add_action( 'admin_post_nopriv_khy_order', 'khy_handle_order_post' );
add_action( 'admin_post_khy_order', 'khy_handle_order_post' );
function khy_handle_order_post() {
	$in  = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified in khy_process_order().
	$res = khy_process_order( is_array( $in ) ? $in : array() );
	$back = isset( $in['khy_source'] ) ? wp_validate_redirect( esc_url_raw( $in['khy_source'] ), home_url( '/' ) ) : home_url( '/' );
	$back = remove_query_arg( array( 'khy_ok', 'khy_err' ), $back );
	if ( $res['ok'] && ! empty( $res['order_id'] ) ) {
		$token = substr( wp_hash( 'khy_ok|' . $res['order_id'] ), 0, 12 );
		wp_safe_redirect( add_query_arg( 'khy_ok', $res['order_id'] . '-' . $token, $back ) . '#order-result' );
		exit;
	}
	if ( $res['ok'] ) {
		wp_safe_redirect( $back );
		exit;
	}
	// Errors: show first message via short-lived transient keyed by a random id.
	$eid = wp_generate_password( 10, false );
	set_transient( 'khy_err_' . $eid, array( 'message' => $res['message'], 'errors' => $res['errors'], 'old' => array_intersect_key( $in, array_flip( array( 'khy_name', 'khy_phone', 'khy_service', 'khy_area', 'khy_time', 'khy_note' ) ) ) ), 5 * MINUTE_IN_SECONDS );
	wp_safe_redirect( add_query_arg( 'khy_err', $eid, $back ) . '#order-result' );
	exit;
}

add_action( 'rest_api_init', 'khy_register_rest' );
function khy_register_rest() {
	register_rest_route(
		'khooneyar/v1',
		'/order',
		array(
			'methods'             => 'POST',
			'callback'            => 'khy_rest_order',
			'permission_callback' => '__return_true',
		)
	);
	register_rest_route(
		'khooneyar/v1',
		'/nonce',
		array(
			'methods'             => 'GET',
			'callback'            => 'khy_rest_nonce',
			'permission_callback' => '__return_true',
		)
	);
}

function khy_rest_nonce() {
	$r = new WP_REST_Response( array( 'nonce' => wp_create_nonce( 'khy_order' ) ) );
	$r->header( 'Cache-Control', 'no-store, max-age=0' );
	return $r;
}

function khy_rest_order( WP_REST_Request $request ) {
	$params = $request->get_params();
	$res    = khy_process_order( is_array( $params ) ? $params : array() );
	unset( $res['order_id'], $res['honeypot'] );
	$r = new WP_REST_Response( $res, $res['status'] );
	$r->header( 'Cache-Control', 'no-store, max-age=0' );
	return $r;
}

/**
 * Server-side confirmation / error payload for the no-JS flow.
 *
 * @return array|null
 */
function khy_result_from_query() {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only display, protected by HMAC token.
	if ( ! empty( $_GET['khy_ok'] ) ) {
		$parts = explode( '-', sanitize_text_field( wp_unslash( $_GET['khy_ok'] ) ) );
		if ( 2 === count( $parts ) ) {
			$id = absint( $parts[0] );
			if ( $id && hash_equals( substr( wp_hash( 'khy_ok|' . $id ), 0, 12 ), $parts[1] ) && 'order_request' === get_post_type( $id ) ) {
				$d = array(
					'name'       => get_post_meta( $id, '_khy_o_name', true ),
					'phone'      => get_post_meta( $id, '_khy_o_phone', true ),
					'service'    => get_post_meta( $id, '_khy_o_service', true ),
					'area'       => get_post_meta( $id, '_khy_o_area', true ),
					'time_label' => get_post_meta( $id, '_khy_o_time', true ),
				);
				$code = khy_order_code( $id );
				return array( 'ok' => true, 'code' => $code, 'name' => $d['name'], 'wa_url' => khy_whatsapp_url( $d, $code ) );
			}
		}
	}
	if ( ! empty( $_GET['khy_err'] ) ) {
		$data = get_transient( 'khy_err_' . sanitize_key( wp_unslash( $_GET['khy_err'] ) ) );
		if ( is_array( $data ) ) {
			$data['ok'] = false;
			return $data;
		}
	}
	// phpcs:enable
	return null;
}

/* ------------------------------------------------------------------
 * Admin UI
 * ---------------------------------------------------------------- */

add_filter( 'manage_order_request_posts_columns', 'khy_order_columns' );
function khy_order_columns( $cols ) {
	return array(
		'cb'         => $cols['cb'],
		'title'      => 'سفارش',
		'khy_phone'  => 'موبایل',
		'khy_service' => 'خدمت',
		'khy_area'   => 'محله',
		'khy_time'   => 'زمان دلخواه',
		'khy_status' => 'وضعیت',
		'date'       => 'تاریخ ثبت',
	);
}

add_action( 'manage_order_request_posts_custom_column', 'khy_order_column_content', 10, 2 );
function khy_order_column_content( $col, $id ) {
	switch ( $col ) {
		case 'khy_phone':
			$p = get_post_meta( $id, '_khy_o_phone', true );
			echo '<a dir="ltr" href="' . esc_url( 'tel:' . $p ) . '">' . esc_html( $p ) . '</a>';
			break;
		case 'khy_service':
			echo esc_html( get_post_meta( $id, '_khy_o_service', true ) );
			break;
		case 'khy_area':
			echo esc_html( get_post_meta( $id, '_khy_o_area', true ) );
			break;
		case 'khy_time':
			echo esc_html( get_post_meta( $id, '_khy_o_time', true ) );
			break;
		case 'khy_status':
			$s  = get_post_meta( $id, '_khy_status', true );
			$s  = $s ? $s : 'new';
			$st = khy_order_statuses();
			echo '<span class="khy-status khy-status-' . esc_attr( $s ) . '">' . esc_html( $st[ $s ] ?? $s ) . '</span>';
			break;
	}
}

add_filter( 'post_row_actions', 'khy_order_row_actions', 10, 2 );
function khy_order_row_actions( $actions, $post ) {
	if ( 'order_request' !== $post->post_type ) {
		return $actions;
	}
	unset( $actions['inline hide-if-no-js'], $actions['view'] );
	foreach ( khy_order_statuses() as $key => $label ) {
		$url = wp_nonce_url( admin_url( 'admin-post.php?action=khy_set_status&order=' . $post->ID . '&status=' . $key ), 'khy_status_' . $post->ID );
		$actions[ 'khy_' . $key ] = '<a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a>';
	}
	return $actions;
}

add_action( 'admin_post_khy_set_status', 'khy_handle_set_status' );
function khy_handle_set_status() {
	$id     = isset( $_GET['order'] ) ? absint( $_GET['order'] ) : 0;
	$status = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : '';
	check_admin_referer( 'khy_status_' . $id );
	if ( ! current_user_can( 'manage_options' ) || 'order_request' !== get_post_type( $id ) || ! isset( khy_order_statuses()[ $status ] ) ) {
		wp_die( 'دسترسی مجاز نیست.', '', array( 'response' => 403 ) );
	}
	update_post_meta( $id, '_khy_status', $status );
	wp_safe_redirect( admin_url( 'edit.php?post_type=order_request' ) );
	exit;
}

// Status filter links with counts.
add_filter( 'views_edit-order_request', 'khy_order_views' );
function khy_order_views( $views ) {
	global $wpdb;
	$counts = array();
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$rows = $wpdb->get_results( "SELECT m.meta_value AS s, COUNT(*) AS c FROM {$wpdb->postmeta} m INNER JOIN {$wpdb->posts} p ON p.ID=m.post_id WHERE m.meta_key='_khy_status' AND p.post_type='order_request' AND p.post_status NOT IN ('trash','auto-draft') GROUP BY m.meta_value" );
	foreach ( (array) $rows as $r ) {
		$counts[ $r->s ] = (int) $r->c;
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$cur = isset( $_GET['khy_status'] ) ? sanitize_key( wp_unslash( $_GET['khy_status'] ) ) : '';
	foreach ( khy_order_statuses() as $key => $label ) {
		$url   = add_query_arg( array( 'post_type' => 'order_request', 'khy_status' => $key ), admin_url( 'edit.php' ) );
		$class = ( $cur === $key ) ? ' class="current"' : '';
		$views[ 'khy_' . $key ] = '<a href="' . esc_url( $url ) . '"' . $class . '>' . esc_html( $label ) . ' <span class="count">(' . (int) ( $counts[ $key ] ?? 0 ) . ')</span></a>';
	}
	return $views;
}

add_action( 'pre_get_posts', 'khy_order_filter_query' );
function khy_order_filter_query( $q ) {
	if ( ! is_admin() || ! $q->is_main_query() || 'order_request' !== $q->get( 'post_type' ) ) {
		return;
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$status = isset( $_GET['khy_status'] ) ? sanitize_key( wp_unslash( $_GET['khy_status'] ) ) : '';
	if ( $status && isset( khy_order_statuses()[ $status ] ) ) {
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		$q->set( 'meta_query', array( array( 'key' => '_khy_status', 'value' => $status ) ) );
	}
	if ( ! $q->get( 'orderby' ) ) {
		$q->set( 'orderby', 'date' );
		$q->set( 'order', 'DESC' );
	}
}

// New-order bubble in the admin menu.
add_filter( 'add_menu_classes', 'khy_order_menu_bubble' );
function khy_order_menu_bubble( $menu ) {
	$new = new WP_Query(
		array(
			'post_type'      => 'order_request',
			'post_status'    => 'private',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'meta_query'     => array( array( 'key' => '_khy_status', 'value' => 'new' ) ),
		)
	);
	$n = (int) $new->found_posts;
	if ( $n > 0 ) {
		foreach ( $menu as $k => $item ) {
			if ( isset( $item[2] ) && 'edit.php?post_type=order_request' === $item[2] ) {
				$menu[ $k ][0] .= ' <span class="awaiting-mod">' . $n . '</span>';
			}
		}
	}
	return $menu;
}

// Bulk actions.
add_filter( 'bulk_actions-edit-order_request', 'khy_order_bulk' );
function khy_order_bulk( $a ) {
	foreach ( khy_order_statuses() as $k => $l ) {
		$a[ 'khy_mark_' . $k ] = 'علامت‌گذاری: ' . $l;
	}
	return $a;
}
add_filter( 'handle_bulk_actions-edit-order_request', 'khy_order_bulk_handle', 10, 3 );
function khy_order_bulk_handle( $redirect, $action, $ids ) {
	if ( 0 !== strpos( $action, 'khy_mark_' ) || ! current_user_can( 'manage_options' ) ) {
		return $redirect;
	}
	$status = substr( $action, 9 );
	if ( ! isset( khy_order_statuses()[ $status ] ) ) {
		return $redirect;
	}
	foreach ( $ids as $id ) {
		update_post_meta( absint( $id ), '_khy_status', $status );
	}
	return $redirect;
}

// Detail meta box.
add_action( 'add_meta_boxes_order_request', 'khy_order_boxes' );
function khy_order_boxes() {
	add_meta_box( 'khy_order_detail', 'جزئیات سفارش', 'khy_order_detail_box', 'order_request', 'normal', 'high' );
}
function khy_order_detail_box( $post ) {
	wp_nonce_field( 'khy_order_save', 'khy_order_nonce' );
	$g = function ( $k ) use ( $post ) {
		return get_post_meta( $post->ID, $k, true );
	};
	$status = $g( '_khy_status' ) ? $g( '_khy_status' ) : 'new';
	echo '<table class="form-table"><tbody>';
	$rows = array(
		'کد پیگیری' => esc_html( $g( '_khy_o_code' ) ),
		'نام'       => esc_html( $g( '_khy_o_name' ) ),
		'موبایل'    => '<a dir="ltr" href="' . esc_url( 'tel:' . $g( '_khy_o_phone' ) ) . '">' . esc_html( $g( '_khy_o_phone' ) ) . '</a>',
		'خدمت'      => esc_html( $g( '_khy_o_service' ) ),
		'محله'      => esc_html( $g( '_khy_o_area' ) ),
		'زمان دلخواه' => esc_html( $g( '_khy_o_time' ) ),
		'توضیحات'   => nl2br( esc_html( $g( '_khy_o_note' ) ) ),
		'صفحهٔ ثبت' => $g( '_khy_o_source' ) ? '<a href="' . esc_url( $g( '_khy_o_source' ) ) . '">' . esc_html( $g( '_khy_o_source' ) ) . '</a>' : '—',
	);
	foreach ( $rows as $label => $val ) {
		echo '<tr><th>' . esc_html( $label ) . '</th><td>' . wp_kses_post( $val ) . '</td></tr>';
	}
	echo '<tr><th><label for="khy_status_sel">وضعیت</label></th><td><select id="khy_status_sel" name="khy_status">';
	foreach ( khy_order_statuses() as $k => $l ) {
		echo '<option value="' . esc_attr( $k ) . '"' . selected( $status, $k, false ) . '>' . esc_html( $l ) . '</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><th><label for="khy_admin_note">یادداشت مدیر</label></th><td><textarea id="khy_admin_note" rows="3" class="large-text" name="khy_admin_note">' . esc_textarea( $g( '_khy_admin_note' ) ) . '</textarea></td></tr>';
	echo '</tbody></table>';
}
add_action( 'save_post_order_request', 'khy_order_save', 10, 1 );
function khy_order_save( $post_id ) {
	if ( ! isset( $_POST['khy_order_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['khy_order_nonce'] ) ), 'khy_order_save' ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( isset( $_POST['khy_status'] ) ) {
		$s = sanitize_key( wp_unslash( $_POST['khy_status'] ) );
		if ( isset( khy_order_statuses()[ $s ] ) ) {
			update_post_meta( $post_id, '_khy_status', $s );
		}
	}
	if ( isset( $_POST['khy_admin_note'] ) ) {
		update_post_meta( $post_id, '_khy_admin_note', sanitize_textarea_field( wp_unslash( $_POST['khy_admin_note'] ) ) );
	}
}

// Orders are stored as "private" posts; hide the "— Private" suffix in titles.
add_filter( 'private_title_format', 'khy_order_private_title', 10, 2 );
function khy_order_private_title( $format, $post ) {
	return ( $post && 'order_request' === $post->post_type ) ? '%s' : $format;
}
