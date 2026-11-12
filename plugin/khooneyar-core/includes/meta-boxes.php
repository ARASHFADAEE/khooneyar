<?php
/**
 * Meta boxes for pages, services and neighborhoods.
 *
 * @package Khooneyar_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Post types that get the SEO / content box.
 *
 * @return string[]
 */
function khy_content_types() {
	return array( 'page', 'service', 'neighborhood', 'post' );
}

add_action( 'add_meta_boxes', 'khy_add_meta_boxes' );
function khy_add_meta_boxes() {
	foreach ( khy_content_types() as $pt ) {
		add_meta_box( 'khy_seo', 'سئو و محتوای صفحه (خونه‌یار)', 'khy_box_seo', $pt, 'normal', 'high' );
	}
	foreach ( array( 'page', 'service', 'neighborhood' ) as $pt ) {
		add_meta_box( 'khy_faq', 'سؤالات متداول این صفحه', 'khy_box_faq', $pt, 'normal', 'default' );
	}
	add_meta_box( 'khy_service', 'اطلاعات خدمت', 'khy_box_service', 'service', 'side', 'default' );
	add_meta_box( 'khy_hood', 'اطلاعات محله', 'khy_box_hood', 'neighborhood', 'side', 'default' );
	add_meta_box( 'khy_hood_services', 'خدمت‌های قابل ارائه در این محله', 'khy_box_hood_services', 'neighborhood', 'normal', 'default' );
	add_meta_box( 'khy_service_hoods', 'محله‌های تحت پوشش این خدمت', 'khy_box_service_hoods', 'service', 'normal', 'default' );
	add_meta_box( 'khy_page_extra', 'جدول قیمت و مراحل (فقط برای قالب‌های قیمت و نظافتچی خانم)', 'khy_box_page_extra', 'page', 'normal', 'default' );
}

function khy_box_seo( $post ) {
	wp_nonce_field( 'khy_save_meta', 'khy_meta_nonce' );
	$g       = function ( $k ) use ( $post ) {
		return get_post_meta( $post->ID, $k, true );
	};
	$aliases = implode( "\n", get_post_meta( $post->ID, '_khy_alias', false ) );
	echo '<div class="khy-box">';
	if ( defined( 'RANK_MATH_VERSION' ) || defined( 'WPSEO_VERSION' ) ) {
		echo '<p><em>افزونهٔ سئو فعال است؛ عنوان و توضیحات سئو را از همان افزونه تنظیم کنید. فیلدهای H1، مقدمه و ریدایرکت همچنان اینجا کار می‌کنند.</em></p>';
	}
	echo '<label class="khy-f"><span>H1 (عنوان اصلی صفحه؛ خالی = عنوان نوشته)</span><input type="text" name="khy_h1" value="' . esc_attr( $g( '_khy_h1' ) ) . '"></label>';
	echo '<label class="khy-f"><span>مقدمهٔ کوتاه زیر H1</span><textarea rows="2" name="khy_lead">' . esc_textarea( $g( '_khy_lead' ) ) . '</textarea></label>';
	echo '<label class="khy-f"><span>عنوان سئو (Title؛ حدود ۵۵ تا ۶۰ نویسه)</span><input type="text" name="khy_seo_title" value="' . esc_attr( $g( '_khy_seo_title' ) ) . '"></label>';
	echo '<label class="khy-f"><span>توضیحات متا (Meta description؛ حدود ۱۴۰ تا ۱۵۵ نویسه)</span><textarea rows="2" name="khy_seo_desc">' . esc_textarea( $g( '_khy_seo_desc' ) ) . '</textarea></label>';
	echo '<label class="khy-f"><span>نشانی‌های فارسی/قدیمی که باید با ریدایرکت ۳۰۱ به این صفحه بیایند (هر خط یک مسیر، مثال: نظافت-منزل-تهران)</span><textarea rows="2" dir="ltr" name="khy_alias">' . esc_textarea( $aliases ) . '</textarea></label>';
	echo '</div>';
}

function khy_box_faq( $post ) {
	$faq = get_post_meta( $post->ID, '_khy_faq', true );
	khy_repeater_field( 'khy_faq', $faq, array( 'q' => 'سؤال', 'a' => 'پاسخ', 'g' => 'گروه (فقط صفحهٔ FAQ؛ اختیاری)' ), array( 'a' => 'textarea' ) );
}

function khy_box_service( $post ) {
	$g = function ( $k ) use ( $post ) {
		return get_post_meta( $post->ID, $k, true );
	};
	$group = $g( '_khy_group' ) ? $g( '_khy_group' ) : 'cleaning';
	echo '<div class="khy-box">';
	echo '<label class="khy-f"><span>گروه</span><select name="khy_group"><option value="cleaning"' . selected( $group, 'cleaning', false ) . '>نظافت</option><option value="plumbing"' . selected( $group, 'plumbing', false ) . '>لوله‌کشی</option></select></label>';
	echo '<label class="khy-f"><span>خلاصهٔ قیمت (نمایشی)</span><input type="text" name="khy_price_summary" value="' . esc_attr( $g( '_khy_price_summary' ) ) . '"></label>';
	echo '<label class="khy-f"><span>حداقل قیمت (تومان، عدد؛ برای اسکیما)</span><input type="number" min="0" name="khy_price_from" dir="ltr" value="' . esc_attr( $g( '_khy_price_from' ) ) . '"></label>';
	echo '<label class="khy-f"><span>حداکثر قیمت (تومان)</span><input type="number" min="0" name="khy_price_to" dir="ltr" value="' . esc_attr( $g( '_khy_price_to' ) ) . '"></label>';
	echo '<label class="khy-f"><span>زمان رسیدن / شروع کار</span><input type="text" name="khy_arrival" value="' . esc_attr( $g( '_khy_arrival' ) ) . '"></label>';
	echo '</div>';
}

function khy_box_hood( $post ) {
	echo '<div class="khy-box"><label class="khy-f"><span>زمان رسیدن در این محله</span><input type="text" name="khy_arrival" value="' . esc_attr( get_post_meta( $post->ID, '_khy_arrival', true ) ) . '"></label>';
	echo '<p class="description">خلاصهٔ کوتاه محله را در «چکیده» بنویسید؛ در کارت‌ها نمایش داده می‌شود.</p></div>';
}

function khy_box_hood_services( $post ) {
	$sel = array_map( 'absint', get_post_meta( $post->ID, '_khy_service', false ) );
	echo '<div class="khy-checks">';
	foreach ( get_posts( array( 'post_type' => 'service', 'posts_per_page' => 50, 'orderby' => 'menu_order title', 'order' => 'ASC', 'no_found_rows' => true ) ) as $s ) {
		echo '<label><input type="checkbox" name="khy_hood_services[]" value="' . esc_attr( $s->ID ) . '"' . checked( in_array( $s->ID, $sel, true ), true, false ) . '> ' . esc_html( $s->post_title ) . '</label>';
	}
	echo '</div><h4>محله‌های نزدیک (لینک داخلی)</h4><div class="khy-checks">';
	$near = array_map( 'absint', get_post_meta( $post->ID, '_khy_nearby', false ) );
	foreach ( get_posts( array( 'post_type' => 'neighborhood', 'posts_per_page' => 50, 'post__not_in' => array( $post->ID ), 'orderby' => 'title', 'order' => 'ASC', 'no_found_rows' => true ) ) as $n ) {
		echo '<label><input type="checkbox" name="khy_nearby[]" value="' . esc_attr( $n->ID ) . '"' . checked( in_array( $n->ID, $near, true ), true, false ) . '> ' . esc_html( $n->post_title ) . '</label>';
	}
	echo '</div>';
}

function khy_box_service_hoods( $post ) {
	$linked = wp_list_pluck( khy_get_service_neighborhoods( $post->ID ), 'ID' );
	echo '<input type="hidden" name="khy_service_hoods_present" value="1"><div class="khy-checks">';
	foreach ( get_posts( array( 'post_type' => 'neighborhood', 'posts_per_page' => 50, 'orderby' => 'menu_order title', 'order' => 'ASC', 'no_found_rows' => true ) ) as $n ) {
		echo '<label><input type="checkbox" name="khy_service_hoods[]" value="' . esc_attr( $n->ID ) . '"' . checked( in_array( $n->ID, $linked, true ), true, false ) . '> ' . esc_html( $n->post_title ) . '</label>';
	}
	echo '</div><p class="description">رابطه دوطرفه است: با تیک زدن، این خدمت در صفحهٔ آن محله هم نمایش داده می‌شود.</p>';
}

function khy_box_page_extra( $post ) {
	echo '<h4>ردیف‌های جدول قیمت</h4>';
	khy_repeater_field(
		'khy_price_rows',
		get_post_meta( $post->ID, '_khy_price_rows', true ),
		array( 'g' => 'گروه (مثال: نظافت منزل)', 'title' => 'عنوان ردیف', 'duration' => 'مدت / واحد', 'price' => 'قیمت (متن، مثال: ۹۰۰ هزار تا ۱ میلیون تومان)', 'note' => 'توضیح', 'service' => 'اسلاگ خدمت برای دکمهٔ سفارش' )
	);
	echo '<h4>مراحل (برای صفحهٔ نظافتچی خانم: مراحل احراز)</h4>';
	khy_repeater_field( 'khy_steps', get_post_meta( $post->ID, '_khy_steps', true ), array( 'title' => 'عنوان مرحله', 'text' => 'توضیح' ), array( 'text' => 'textarea' ) );
}

add_action( 'save_post', 'khy_save_meta', 10, 2 );
function khy_save_meta( $post_id, $post ) {
	if ( ! isset( $_POST['khy_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['khy_meta_nonce'] ) ), 'khy_save_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$text = array(
		'khy_h1'            => '_khy_h1',
		'khy_seo_title'     => '_khy_seo_title',
		'khy_arrival'       => '_khy_arrival',
		'khy_price_summary' => '_khy_price_summary',
	);
	foreach ( $text as $field => $key ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
		}
	}
	foreach ( array( 'khy_lead' => '_khy_lead', 'khy_seo_desc' => '_khy_seo_desc' ) as $field => $key ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, $key, sanitize_textarea_field( wp_unslash( $_POST[ $field ] ) ) );
		}
	}
	foreach ( array( 'khy_price_from' => '_khy_price_from', 'khy_price_to' => '_khy_price_to' ) as $field => $key ) {
		if ( isset( $_POST[ $field ] ) ) {
			$v = absint( khy_to_ascii_digits( sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) ) );
			update_post_meta( $post_id, $key, $v ? $v : '' );
		}
	}
	if ( isset( $_POST['khy_group'] ) ) {
		$grp = sanitize_key( wp_unslash( $_POST['khy_group'] ) );
		update_post_meta( $post_id, '_khy_group', in_array( $grp, array( 'cleaning', 'plumbing' ), true ) ? $grp : 'cleaning' );
	}

	// Aliases: one path per line, stored as multi-row meta for exact-match lookups.
	if ( isset( $_POST['khy_alias'] ) ) {
		delete_post_meta( $post_id, '_khy_alias' );
		$lines = preg_split( '/\R/u', sanitize_textarea_field( wp_unslash( $_POST['khy_alias'] ) ) );
		foreach ( (array) $lines as $line ) {
			$line = trim( rawurldecode( $line ), " \t/" );
			$line = str_replace( ' ', '-', $line );
			if ( '' !== $line ) {
				add_post_meta( $post_id, '_khy_alias', $line );
			}
		}
	}

	// Repeaters (editors keep inline links / bold in answers).
	$reps = array(
		'khy_faq'        => array( '_khy_faq', array( 'q', 'a', 'g' ) ),
		'khy_price_rows' => array( '_khy_price_rows', array( 'g', 'title', 'duration', 'price', 'note', 'service' ) ),
		'khy_steps'      => array( '_khy_steps', array( 'title', 'text' ) ),
	);
	foreach ( $reps as $field => $cfg ) {
		if ( isset( $_POST[ $field ] ) ) {
			$rows = khy_sanitize_repeater( wp_unslash( $_POST[ $field ] ), $cfg[1] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			if ( $rows ) {
				update_post_meta( $post_id, $cfg[0], $rows );
			} else {
				delete_post_meta( $post_id, $cfg[0] );
			}
		}
	}

	// Neighborhood -> services, nearby.
	if ( 'neighborhood' === $post->post_type ) {
		khy_set_neighborhood_services( $post_id, isset( $_POST['khy_hood_services'] ) ? array_map( 'absint', (array) wp_unslash( $_POST['khy_hood_services'] ) ) : array() ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		delete_post_meta( $post_id, '_khy_nearby' );
		if ( isset( $_POST['khy_nearby'] ) ) {
			foreach ( array_unique( array_map( 'absint', (array) wp_unslash( $_POST['khy_nearby'] ) ) ) as $nid ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				if ( $nid && 'neighborhood' === get_post_type( $nid ) ) {
					add_post_meta( $post_id, '_khy_nearby', $nid );
				}
			}
		}
	}

	// Service -> neighborhoods (writes the other side of the relation).
	if ( 'service' === $post->post_type && ! empty( $_POST['khy_service_hoods_present'] ) ) {
		$want = isset( $_POST['khy_service_hoods'] ) ? array_map( 'absint', (array) wp_unslash( $_POST['khy_service_hoods'] ) ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$all  = get_posts( array( 'post_type' => 'neighborhood', 'posts_per_page' => 200, 'fields' => 'ids', 'post_status' => 'any', 'no_found_rows' => true ) );
		foreach ( $all as $nid ) {
			$has = in_array( $post_id, array_map( 'absint', get_post_meta( $nid, '_khy_service', false ) ), true );
			if ( in_array( $nid, $want, true ) && ! $has ) {
				add_post_meta( $nid, '_khy_service', $post_id );
			} elseif ( ! in_array( $nid, $want, true ) && $has ) {
				delete_post_meta( $nid, '_khy_service', $post_id );
			}
		}
	}
}
