<?php
/**
 * Gợi ý vùng (T-089) và chủ đề (T-091) cho từng bài — máy gợi ý, người sáng lập duyệt hàng loạt (docs/25 mục 4.2,
 * 4.4, 4.5; D-061 B). Không gắn gì khi chưa duyệt. Chủ đề là bộ của dự án (taxonomy/tu-vung-kiem-soat.yaml và các
 * nhóm đã duyệt ở docs/25 mục 4.4); chủ đề của nguồn chỉ dùng nội bộ qua bảng ánh xạ do người sáng lập duyệt — không
 * bao giờ hiện ra trang.
 *
 * Vùng: từ lời giải của chú thích (ghi phương ngữ, tỉnh, miền) và tọa độ của địa danh đã xác nhận trên Wikidata.
 * Chủ đề: chủ đề của nguồn qua bảng ánh xạ; lối hát (hát ru, hò, lý) theo lời; nhóm kinh nghiệm của tục ngữ theo từ
 * khóa; loại câu đố theo phân loại của nguồn qua bảng ánh xạ.
 */

defined( 'ABSPATH' ) || exit;

const CADAO_TRANG_GOI_Y = 'cadao-goi-y';

/** Vùng của dự án (taxonomy/tu-vung-kiem-soat.yaml). */
const CADAO_VUNG = array(
	'bac-bo'         => 'Bắc Bộ',
	'bac-trung-bo'   => 'Bắc Trung Bộ',
	'trung-trung-bo' => 'Trung Trung Bộ',
	'nam-trung-bo'   => 'Nam Trung Bộ',
	'tay-nguyen'     => 'Tây Nguyên',
	'nam-bo'         => 'Nam Bộ',
	'toan-quoc'      => 'Phổ biến toàn quốc',
);

/** Từ khóa trong lời giải của chú thích → vùng. */
const CADAO_VUNG_TU_KHOA = array(
	'nam-bo'         => '/Nam Bộ|miền Nam|Nam Kỳ|Lục tỉnh|Sài Gòn|Gia Định|Đồng Nai|Cần Thơ|Bến Tre|Vĩnh Long|Mỹ Tho|Châu Đốc|Cà Mau|Bạc Liêu|Sóc Trăng|Long An|Tây Ninh|An Giang|Đồng Tháp|Kiên Giang|Rạch Giá|Hậu Giang|Trà Vinh|Biên Hòa|Biên Hoà|Bà Rịa/u',
	'bac-trung-bo'   => '/Nghệ|Hà Tĩnh|Thanh Hóa|Thanh Hoá|xứ Thanh|Quảng Bình|Quảng Trị/u',
	'trung-trung-bo' => '/Huế|Thừa Thiên|Quảng Nam|Đà Nẵng|Quảng Ngãi|Hội An/u',
	'nam-trung-bo'   => '/Bình Định|Phú Yên|Khánh Hòa|Khánh Hoà|Nha Trang|Ninh Thuận|Bình Thuận|Phan Thiết/u',
	'tay-nguyen'     => '/Tây Nguyên|Gia Lai|Kon Tum|Đắk Lắk|Đắc Lắc|Lâm Đồng|Đà Lạt|Buôn Ma Thuột/u',
	'bac-bo'         => '/Bắc Bộ|miền Bắc|Bắc Kỳ|Hà Nội|Thăng Long|Kinh Bắc|Bắc Ninh|Nam Định|Thái Bình|Hải Dương|Hải Phòng|Hưng Yên|Hà Nam|Ninh Bình|Phú Thọ|Sơn Tây/u',
);

/** Chủ đề của dự án: nhóm → [slug → tên] (slug theo mã từ vựng, gạch nối). */
const CADAO_CHU_DE = array(
	''          => array(
		'gia-dinh'              => 'Gia đình',
		'tinh-yeu-hon-nhan'     => 'Tình yêu, hôn nhân',
		'que-huong-dat-nuoc'    => 'Quê hương, đất nước',
		'lao-dong-san-xuat'     => 'Lao động, sản xuất',
		'thien-nhien-thoi-tiet' => 'Thiên nhiên, thời tiết',
		'ung-xu-dao-duc'        => 'Ứng xử, đạo đức',
		'than-phan-con-nguoi'   => 'Thân phận con người',
		'phe-phan-xa-hoi'       => 'Phê phán xã hội',
		'hai-huoc-cham-biem'    => 'Hài hước, châm biếm',
		'hoc-tap-thay-tro'      => 'Học tập, thầy trò',
		'dia-danh-lich-su'      => 'Địa danh, lịch sử',
	),
	'kinh-nghiem' => array( // tục ngữ kinh nghiệm (ý 28)
		'kn-thoi-tiet'   => 'Kinh nghiệm thời tiết',
		'kn-mua-vu'      => 'Kinh nghiệm mùa vụ',
		'kn-chan-nuoi'   => 'Kinh nghiệm chăn nuôi',
		'kn-an-uong'     => 'Ăn uống, sức khỏe',
		'kn-doi-nhan'    => 'Đối nhân xử thế',
	),
	'loi-hat'     => array( // lối hát (ý 36)
		'hat-ru'  => 'Hát ru',
		'ho'      => 'Hò',
		'ly'      => 'Lý',
		'hat-doi' => 'Hát đối',
	),
	'loai-cau-do' => array( // loại câu đố (ý 35)
		'do-do-vat'    => 'Đố đồ vật',
		'do-con-vat'   => 'Đố con vật',
		'do-cay-coi'   => 'Đố cây cối, hoa quả',
		'do-thien-nhien' => 'Đố thiên nhiên',
		'do-con-nguoi' => 'Đố con người, sinh hoạt',
	),
);
const CADAO_NHOM_CHU_DE = array( 'kinh-nghiem' => 'Kinh nghiệm (tục ngữ)', 'loi-hat' => 'Lối hát', 'loai-cau-do' => 'Loại câu đố' );

/** Bảng ánh xạ ĐỀ XUẤT từ chủ đề của nguồn (Ca dao Mẹ) → chủ đề của dự án; người sáng lập duyệt, sửa ở trang Gợi ý. */
const CADAO_ANH_XA_DE_XUAT = array(
	'Tình yêu đôi lứa'             => 'tinh-yeu-hon-nhan',
	'Quê hương đất nước'           => 'que-huong-dat-nuoc',
	'Tình cảm gia đình, bạn bè'    => 'gia-dinh',
	'Than thân trách phận'         => 'than-phan-con-nguoi',
	'Trào phúng, phê phán đả kích' => 'phe-phan-xa-hoi',
	'Đấu tranh, phản kháng'        => 'phe-phan-xa-hoi',
	'Lịch sử'                      => 'dia-danh-lich-su',
	'Lao động sản xuất'            => 'lao-dong-san-xuat',
	'Quan hệ thiên nhiên'          => 'thien-nhien-thoi-tiet',
	'Ru con'                       => 'hat-ru',
	'Đồ vật'                       => 'do-do-vat',
	'Con vật'                      => 'do-con-vat',
	'Cây cối - hoa quả - rau củ'   => 'do-cay-coi',
	'Thiên nhiên'                  => 'do-thien-nhien',
	'Con người và sinh hoạt'       => 'do-con-nguoi',
	'Vũ trụ, con người và xã hội'  => '',
	'Khác'                         => '',
);

/** Từ khóa (trong lời) của nhóm kinh nghiệm — chỉ xét với tục ngữ, thành ngữ. */
const CADAO_KINH_NGHIEM_TU_KHOA = array(
	// Chặn chữ cái hai bên thay cho \b (\b của PCRE không hiểu chữ có dấu)
	'kn-thoi-tiet' => '/(?<![\p{L}\p{M}])(mưa|nắng|gió|bão|trời|mây|sấm|chớp|cầu vồng|rét|nồm)(?![\p{L}\p{M}])/u',
	'kn-mua-vu'    => '/(?<![\p{L}\p{M}])(lúa|mạ|cấy|gặt|ruộng|mùa|thóc|cày|bừa|phân|giống)(?![\p{L}\p{M}])/u',
	'kn-chan-nuoi' => '/(?<![\p{L}\p{M}])(trâu|bò|lợn|heo|gà|vịt|chó|mèo|ngựa|cá)(?![\p{L}\p{M}])/u',
	'kn-an-uong'   => '/(?<![\p{L}\p{M}])(ăn|uống|cơm|canh|no|đói|bệnh|ốm|thuốc|ngủ)(?![\p{L}\p{M}])/u',
);

// Tạo các chủ đề, vùng của dự án (một lần, khi nâng cấp)
add_action(
	'init',
	static function () {
		if ( 1 === (int) get_option( 'cadao_bo_chu_de' ) ) {
			return;
		}
		foreach ( CADAO_VUNG as $slug => $ten ) {
			if ( ! term_exists( $slug, 'vung' ) ) {
				wp_insert_term( $ten, 'vung', array( 'slug' => $slug ) );
			}
		}
		foreach ( CADAO_CHU_DE as $nhom => $ds ) {
			$cha = 0;
			if ( '' !== $nhom ) {
				$co  = term_exists( $nhom, 'chu_de' );
				$cha = (int) ( $co ? $co['term_id'] : ( wp_insert_term( CADAO_NHOM_CHU_DE[ $nhom ], 'chu_de', array( 'slug' => $nhom ) )['term_id'] ?? 0 ) );
			}
			foreach ( $ds as $slug => $ten ) {
				if ( ! term_exists( $slug, 'chu_de' ) ) {
					wp_insert_term( $ten, 'chu_de', array( 'slug' => $slug, 'parent' => $cha ) );
				}
			}
		}
		update_option( 'cadao_bo_chu_de', 1 );
	},
	12
);

/** Vùng gần đúng theo tọa độ (chỉ để gợi ý). */
function cadao_vung_theo_toa_do( float $lat, float $lon ): string {
	if ( $lat >= 20.3 ) {
		return 'bac-bo';
	}
	if ( $lat >= 16.6 ) {
		return 'bac-trung-bo';
	}
	if ( $lat >= 14.6 ) {
		return 'trung-trung-bo';
	}
	if ( $lat >= 11.4 ) {
		return $lon >= 108.6 ? 'nam-trung-bo' : 'tay-nguyen';
	}
	return $lon >= 107.8 && $lat >= 10.5 ? 'nam-trung-bo' : 'nam-bo';
}

/** Bảng ánh xạ đang dùng: bảng người sáng lập đã lưu, chưa lưu thì bảng đề xuất. */
function cadao_anh_xa_chu_de(): array {
	$luu = get_option( 'cadao_anh_xa_chu_de' );
	return is_array( $luu ) ? $luu : CADAO_ANH_XA_DE_XUAT;
}

/**
 * Gợi ý cho một bài.
 *
 * @return array{vung: array<string, string>, chu_de: array<string, string>} slug → lý do
 */
function cadao_goi_y_bai( int $id ): array {
	global $wpdb;
	$bai = get_post( $id );
	$ra  = array( 'vung' => array(), 'chu_de' => array() );
	if ( ! $bai ) {
		return $ra;
	}
	// Vùng từ lời giải của chú thích, và từ tọa độ địa danh đã xác nhận
	foreach ( cadao_chu_thich_cua_bai( $bai->post_content ) as $ct ) {
		foreach ( CADAO_VUNG_TU_KHOA as $vung => $mau ) {
			if ( preg_match( $mau, $ct['noi_dung'], $m ) && ! isset( $ra['vung'][ $vung ] ) ) {
				$ra['vung'][ $vung ] = 'chú thích "' . $ct['tu'] . '" nhắc "' . $m[0] . '"';
			}
		}
		$t  = cadao_muc_tu( $ct, false );
		$wd = $t ? cadao_wd_cua_muc_tu( (int) $t->term_id ) : null;
		if ( ! empty( $wd['toa_do'] ) ) {
			$vung = cadao_vung_theo_toa_do( (float) $wd['toa_do'][0], (float) $wd['toa_do'][1] );
			$ra['vung'][ $vung ] ??= 'địa danh "' . $ct['tu'] . '" (đã xác nhận) ở ' . CADAO_VUNG[ $vung ];
		}
	}
	// Chủ đề của nguồn (nội bộ) qua bảng ánh xạ
	$anh_xa = cadao_anh_xa_chu_de();
	$muc    = $wpdb->get_row( $wpdb->prepare( 'SELECT du_lieu FROM ' . cadao_kho_bang() . ' WHERE post_id = %d ORDER BY id LIMIT 1', $id ) );
	foreach ( (array) ( $muc ? ( json_decode( $muc->du_lieu, true )['chu_de_goc'] ?? array() ) : array() ) as $cg ) {
		$slug = $anh_xa[ $cg ] ?? '';
		if ( '' !== $slug ) {
			$ra['chu_de'][ $slug ] ??= 'nguồn xếp vào "' . $cg . '"';
		}
	}
	// Lối hát theo lời
	$loi = cadao_loi_cac_khoi_tho( $bai->post_content )[0] ?? '';
	$dau = mb_strtolower( (string) strtok( $loi, "\n" ) );
	if ( preg_match( '/(^|\s)(à ơi|ầu ơ|ạ ời|ví dầu|ru con|con ơi con ngủ)/u', mb_strtolower( $loi ) ) ) {
		$ra['chu_de']['hat-ru'] ??= 'lời ru ("à ơi", "ầu ơ", "ru con"…)';
	}
	if ( preg_match( '/(^|\s)hò(\s|,|$)/u', $dau ) ) {
		$ra['chu_de']['ho'] ??= 'dòng đầu có "hò"';
	}
	if ( preg_match( '/^lý\s/u', mb_strtolower( get_the_title( $bai ) ) ) ) {
		$ra['chu_de']['ly'] ??= 'tên bài bắt đầu bằng "Lý"';
	}
	// Kinh nghiệm: chỉ tục ngữ, thành ngữ
	if ( has_term( array( 'tuc-ngu', 'thanh-ngu' ), 'the_loai', $bai ) ) {
		foreach ( CADAO_KINH_NGHIEM_TU_KHOA as $slug => $mau ) {
			if ( preg_match( $mau, mb_strtolower( $loi ), $m ) ) {
				$ra['chu_de'][ $slug ] ??= 'lời có "' . $m[0] . '"';
			}
		}
	}
	// Bỏ những gì bài đã có
	foreach ( array( 'vung' => 'vung', 'chu_de' => 'chu_de' ) as $k => $tax ) {
		foreach ( wp_get_object_terms( $id, $tax, array( 'fields' => 'slugs' ) ) as $co ) {
			unset( $ra[ $k ][ $co ] );
		}
	}
	return $ra;
}

/** Tính lại gợi ý của bài (khi lưu, khi làm dần). */
function cadao_ghi_goi_y( int $id ): void {
	if ( get_post_meta( $id, '_cadao_goi_y_xong', true ) ) {
		return; // người sáng lập đã duyệt bài này
	}
	$g = cadao_goi_y_bai( $id );
	if ( $g['vung'] || $g['chu_de'] ) {
		update_post_meta( $id, '_cadao_goi_y', $g );
	} else {
		delete_post_meta( $id, '_cadao_goi_y' );
	}
}

add_action(
	'save_post_' . CADAO_LOAI_BAI,
	static function ( $id ) {
		if ( ! wp_is_post_revision( $id ) && ! wp_is_post_autosave( $id ) ) {
			cadao_ghi_goi_y( (int) $id );
		}
	},
	27
);

// Bài đã có: tính gợi ý một lần (làm dần, mỗi lượt 200 bài); bảng ánh xạ đổi thì tính lại
add_action(
	'cadao_thu_thap',
	static function () {
		global $wpdb;
		$sau = (int) get_option( 'cadao_goi_y_sau', 0 );
		if ( $sau < 0 ) {
			return;
		}
		$ds = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_status NOT IN ('trash','auto-draft','inherit') AND ID > %d ORDER BY ID LIMIT 200", CADAO_LOAI_BAI, $sau ) );
		foreach ( $ds as $id ) {
			cadao_ghi_goi_y( (int) $id );
		}
		update_option( 'cadao_goi_y_sau', $ds ? (int) end( $ds ) : -1, false );
	},
	10
);

/** Duyệt gợi ý của một bài: gắn các mục đã chọn (thêm vào, không bỏ cái bài đang có); bài rời hàng chờ. */
function cadao_duyet_goi_y( int $id, array $vung, array $chu_de ): void {
	$vung   = array_values( array_intersect( $vung, array_keys( CADAO_VUNG ) ) );
	$chu_de = array_values( array_filter( $chu_de, static fn( $s ) => (bool) term_exists( $s, 'chu_de' ) ) );
	if ( $vung ) {
		wp_set_object_terms( $id, $vung, 'vung', true );
	}
	if ( $chu_de ) {
		wp_set_object_terms( $id, $chu_de, 'chu_de', true );
	}
	update_post_meta( $id, '_cadao_goi_y_xong', 1 );
	delete_post_meta( $id, '_cadao_goi_y' );
}

// ---------- Trang "Gợi ý vùng, chủ đề" ----------

add_action(
	'admin_menu',
	static function () {
		add_submenu_page( 'edit.php?post_type=' . CADAO_LOAI_BAI, 'Gợi ý vùng, chủ đề', 'Gợi ý vùng, chủ đề', 'manage_options', CADAO_TRANG_GOI_Y, 'cadao_trang_goi_y' );
	},
	14
);

function cadao_ten_chu_de( string $slug ): string {
	foreach ( CADAO_CHU_DE as $nhom => $ds ) {
		if ( isset( $ds[ $slug ] ) ) {
			return ( '' !== $nhom ? CADAO_NHOM_CHU_DE[ $nhom ] . ' → ' : '' ) . $ds[ $slug ];
		}
	}
	return $slug;
}

function cadao_trang_goi_y(): void {
	global $wpdb;
	echo '<div class="wrap"><h1>Gợi ý vùng, chủ đề</h1>';
	cadao_hien_bao();
	echo '<p>Máy gợi ý <strong>vùng</strong> (từ lời giải của chú thích và địa danh đã xác nhận) và <strong>chủ đề</strong> của dự án cho từng bài. <strong>Không gắn gì khi bạn chưa duyệt.</strong> Gợi ý đã đánh dấu sẵn; bỏ dấu những cái không đúng rồi bấm "Đồng ý". Bài đã duyệt rời danh sách.</p>';
	// Bảng ánh xạ chủ đề của nguồn
	$anh_xa = cadao_anh_xa_chu_de();
	$da_luu = is_array( get_option( 'cadao_anh_xa_chu_de' ) );
	$dem    = array();
	foreach ( $wpdb->get_col( 'SELECT du_lieu FROM ' . cadao_kho_bang() . " WHERE du_lieu LIKE '%chu_de_goc%'" ) as $dl ) {
		foreach ( (array) ( json_decode( $dl, true )['chu_de_goc'] ?? array() ) as $cg ) {
			$dem[ $cg ] = ( $dem[ $cg ] ?? 0 ) + 1;
		}
	}
	arsort( $dem );
	echo '<details' . ( $da_luu ? '' : ' open' ) . ' class="card" style="max-width:none;padding:8px 16px"><summary><strong>Bảng ánh xạ chủ đề của nguồn → chủ đề của dự án</strong> ' . ( $da_luu ? '(bạn đã duyệt)' : '(máy đề xuất — xem rồi bấm Lưu để duyệt)' ) . '</summary>';
	echo '<p class="description">Chủ đề của nguồn chỉ dùng nội bộ để gợi ý; không hiện ra trang.</p><form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	wp_nonce_field( 'cadao_anh_xa' );
	echo '<input type="hidden" name="action" value="cadao_anh_xa"><table class="widefat striped"><thead><tr><th>Chủ đề của nguồn</th><th>Số câu</th><th>Chủ đề của dự án</th></tr></thead><tbody>';
	foreach ( array_unique( array_merge( array_keys( $dem ), array_keys( $anh_xa ) ) ) as $i => $cg ) {
		$chon = '<option value="">— không dùng —</option>';
		foreach ( CADAO_CHU_DE as $ds ) {
			foreach ( $ds as $slug => $ten ) {
				$chon .= '<option value="' . esc_attr( $slug ) . '" ' . selected( $anh_xa[ $cg ] ?? '', $slug, false ) . '>' . esc_html( cadao_ten_chu_de( $slug ) ) . '</option>';
			}
		}
		echo '<tr><td>' . esc_html( $cg ) . '<input type="hidden" name="nguon[' . $i . ']" value="' . esc_attr( $cg ) . '"></td><td>' . (int) ( $dem[ $cg ] ?? 0 ) . '</td><td><select name="du_an[' . $i . ']" aria-label="' . esc_attr( 'Chủ đề của dự án cho ' . $cg ) . '">' . $chon . '</select></td></tr>';
	}
	echo '</tbody></table>';
	submit_button( 'Lưu bảng ánh xạ', 'secondary' );
	echo '</form></details>';

	// Danh sách bài có gợi ý
	$ds = get_posts( array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => array( 'publish', 'pending', 'draft' ), 'numberposts' => 50, 'meta_key' => '_cadao_goi_y', 'orderby' => 'ID', 'order' => 'ASC' ) );
	$con = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s", '_cadao_goi_y' ) );
	echo '<h2>Bài có gợi ý (' . $con . ')</h2>';
	if ( ! $ds ) {
		echo '<p>Không có bài nào đang chờ duyệt gợi ý.</p></div>';
		return;
	}
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	wp_nonce_field( 'cadao_duyet_goi_y' );
	echo '<input type="hidden" name="action" value="cadao_duyet_goi_y"><p>';
	submit_button( 'Đồng ý các gợi ý đang đánh dấu (cả trang)', 'primary', 'submit', false );
	echo '</p><table class="widefat striped"><thead><tr><th>Bài</th><th>Vùng</th><th>Chủ đề</th></tr></thead><tbody>';
	foreach ( $ds as $b ) {
		$g = get_post_meta( $b->ID, '_cadao_goi_y', true ) ?: array( 'vung' => array(), 'chu_de' => array() );
		$o = static function ( string $loai, array $muc, callable $ten ) use ( $b ) {
			$ra = '';
			foreach ( $muc as $slug => $ly_do ) {
				$ra .= '<label style="display:block;margin:2px 0"><input type="checkbox" name="' . $loai . '[' . (int) $b->ID . '][]" value="' . esc_attr( $slug ) . '" checked> <strong>' . esc_html( $ten( $slug ) ) . '</strong><br><span class="description">' . esc_html( $ly_do ) . '</span></label>';
			}
			return $ra ?: '<span class="description">—</span>';
		};
		echo '<tr><td><a href="' . esc_url( (string) get_edit_post_link( $b->ID ) ) . '"><strong>' . esc_html( get_the_title( $b ) ) . '</strong></a><br><span class="description">' . esc_html( cadao_ten_the_loai( $b ) ) . '</span><input type="hidden" name="bai[]" value="' . (int) $b->ID . '"></td>';
		echo '<td>' . $o( 'vung', (array) $g['vung'], static fn( $s ) => CADAO_VUNG[ $s ] ?? $s ) . '</td><td>' . $o( 'chu_de', (array) $g['chu_de'], 'cadao_ten_chu_de' ) . '</td></tr>';
	}
	echo '</tbody></table></form></div>';
}

add_action(
	'admin_post_cadao_anh_xa',
	static function () {
		cadao_kiem_quyen( 'cadao_anh_xa' );
		$nguon = array_map( 'sanitize_text_field', (array) wp_unslash( $_POST['nguon'] ?? array() ) );
		$du_an = array_map( 'sanitize_key', (array) wp_unslash( $_POST['du_an'] ?? array() ) );
		$bang  = array();
		foreach ( $nguon as $i => $cg ) {
			$bang[ $cg ] = $du_an[ $i ] ?? '';
		}
		update_option( 'cadao_anh_xa_chu_de', $bang, false );
		update_option( 'cadao_goi_y_sau', 0, false ); // tính lại gợi ý theo bảng mới
		cadao_ve( CADAO_TRANG_GOI_Y, 'Đã lưu bảng ánh xạ. Máy tính lại gợi ý trong vài lượt 5 phút.' );
	}
);

add_action(
	'admin_post_cadao_duyet_goi_y',
	static function () {
		cadao_kiem_quyen( 'cadao_duyet_goi_y' );
		$vung   = (array) wp_unslash( $_POST['vung'] ?? array() );
		$chu_de = (array) wp_unslash( $_POST['chu_de'] ?? array() );
		$so     = 0;
		foreach ( array_map( 'intval', (array) wp_unslash( $_POST['bai'] ?? array() ) ) as $id ) {
			if ( current_user_can( 'edit_post', $id ) ) {
				cadao_duyet_goi_y( $id, array_map( 'sanitize_key', (array) ( $vung[ $id ] ?? array() ) ), array_map( 'sanitize_key', (array) ( $chu_de[ $id ] ?? array() ) ) );
				++$so;
			}
		}
		cadao_ve( CADAO_TRANG_GOI_Y, "Đã duyệt gợi ý của $so bài." );
	}
);
