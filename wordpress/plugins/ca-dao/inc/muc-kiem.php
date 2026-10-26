<?php
/**
 * Dòng "lời và mức kiểm" ngay dưới lời (D-072, T-119) — giữ lời hứa D-039 "Mỗi câu ghi rõ lấy từ đâu, và đã được
 * kiểm đến mức nào" mà không quay lại huy hiệu 4 mức (D-054). Máy tính từ phần "Lời đã đối chiếu với" của hộp Nguồn:
 * - người biên soạn chưa điền (mặc định, mọi bài tự động): "Lời theo bản lưu truyền phổ biến · chưa đối chiếu sách";
 * - người biên soạn tự điền: "Lời đã đối chiếu với <tên>, tr. N" (có địa chỉ thì tên là liên kết).
 * Máy không bao giờ tự điền phần đối chiếu. Phần "Nguồn" cũ của hộp (nguồn của chú thích chép) không tính là đối chiếu
 * lời. Liên kết "Vì sao?" tới mục #muc-kiem của trang Phương pháp, chỉ khi trang đó đã đăng.
 */

defined( 'ABSPATH' ) || exit;

const CADAO_MUC_KIEM_CHUA = 'Lời theo bản lưu truyền phổ biến · chưa đối chiếu sách';
const CADAO_DOI_CHIEU     = array( 'doi_chieu_ten', 'doi_chieu_trang', 'doi_chieu_url' );

add_action(
	'init',
	static function () {
		foreach ( CADAO_DOI_CHIEU as $khoa ) {
			register_post_meta(
				CADAO_LOAI_BAI,
				$khoa,
				array(
					'type'              => 'string',
					'single'            => true,
					'show_in_rest'      => true,
					'sanitize_callback' => 'doi_chieu_url' === $khoa ? 'esc_url_raw' : 'sanitize_text_field',
					'auth_callback'     => static fn() => current_user_can( 'edit_posts' ),
				)
			);
		}
	}
);

/**
 * Mức kiểm của lời một bài.
 *
 * @return array{da_doi_chieu: bool, ten: string, trang: string, url: string, ngan: string}
 */
function cadao_muc_kiem( int $id ): array {
	$ten   = trim( (string) get_post_meta( $id, 'doi_chieu_ten', true ) );
	$trang = trim( (string) get_post_meta( $id, 'doi_chieu_trang', true ) );
	$url   = (string) get_post_meta( $id, 'doi_chieu_url', true );
	if ( '' === $ten && '' !== $url ) {
		$ten = (string) wp_parse_url( $url, PHP_URL_HOST );
	}
	return array(
		'da_doi_chieu' => '' !== $ten,
		'ten'          => $ten,
		'trang'        => '' !== $ten ? $trang : '',
		'url'          => '' !== $ten ? $url : '',
		'ngan'         => '' !== $ten ? 'đã đối chiếu' : 'chưa đối chiếu sách',
	);
}

/** Nơi đã đối chiếu, dạng chữ: "Tên, tr. N" ('' khi chưa đối chiếu). Dùng trong trích dẫn. */
function cadao_chu_doi_chieu( int $id ): string {
	$mk = cadao_muc_kiem( $id );
	return $mk['da_doi_chieu'] ? $mk['ten'] . ( '' !== $mk['trang'] ? ', tr. ' . $mk['trang'] : '' ) : '';
}

/** Địa chỉ trang Phương pháp khi đã đăng, '' nếu chưa. */
function cadao_url_phuong_phap(): string {
	$p = get_page_by_path( 'phuong-phap', OBJECT, 'page' );
	return $p && 'publish' === $p->post_status ? (string) get_permalink( $p ) : '';
}

/** HTML của dòng mức kiểm. */
function cadao_html_muc_kiem( int $id ): string {
	$mk = cadao_muc_kiem( $id );
	if ( $mk['da_doi_chieu'] ) {
		$ten = '' !== $mk['url']
			? sprintf( '<a href="%s" rel="noopener">%s <span aria-hidden="true">↗</span></a>', esc_url( $mk['url'] ), esc_html( $mk['ten'] ) )
			: esc_html( $mk['ten'] );
		$chu = 'Lời đã đối chiếu với ' . $ten . ( '' !== $mk['trang'] ? ', tr. ' . esc_html( $mk['trang'] ) : '' );
		$hoi = 'Đối chiếu thế nào?';
	} else {
		$chu = esc_html( CADAO_MUC_KIEM_CHUA );
		$hoi = 'Vì sao?';
	}
	$pp = cadao_url_phuong_phap();
	if ( '' !== $pp ) {
		$chu .= ' · <a href="' . esc_url( $pp . '#muc-kiem' ) . '">' . esc_html( $hoi ) . '<span class="screen-reader-text"> Cách dự án ghi lời lấy từ đâu và đã kiểm đến đâu</span></a>';
	}
	return '<p class="cadao-muc-kiem' . ( $mk['da_doi_chieu'] ? ' cadao-muc-kiem-da' : '' ) . '">' . $chu . '</p>';
}

/** Chèn dòng ngay sau lời (khối Thơ đầu tiên); không có khối Thơ thì giữ nguyên. */
function cadao_chen_muc_kiem( string $noi_dung, string $dong ): string {
	$vt = strpos( $noi_dung, '</pre>' );
	return false === $vt ? $noi_dung : substr_replace( $noi_dung, '</pre>' . $dong, $vt, strlen( '</pre>' ) );
}

// Sau "Hiểu nhanh" của theme (ưu tiên 20, cũng chèn ngay sau </pre>) nên dòng này đứng giữa lời và Hiểu nhanh
add_filter(
	'the_content',
	static function ( $noi_dung ) {
		if ( ! is_singular( CADAO_LOAI_BAI ) || get_the_ID() !== get_queried_object_id() || doing_filter( 'get_the_excerpt' ) ) {
			return $noi_dung;
		}
		return cadao_chen_muc_kiem( $noi_dung, cadao_html_muc_kiem( get_the_ID() ) );
	},
	25
);

// Hộp "Nguồn": phần "Lời đã đối chiếu với" (sau phần nguồn của chú thích)
add_action(
	'add_meta_boxes_' . CADAO_LOAI_BAI,
	static function () {
		add_meta_box( 'cadao_doi_chieu', 'Lời đã đối chiếu với (tùy chọn)', 'cadao_hop_doi_chieu', CADAO_LOAI_BAI, 'side' );
	}
);

function cadao_hop_doi_chieu( WP_Post $bai ): void {
	wp_nonce_field( 'cadao_luu_doi_chieu', 'cadao_doi_chieu_nonce' );
	$o = static fn( $k ) => esc_attr( (string) get_post_meta( $bai->ID, $k, true ) );
	?>
	<p class="description">Chỉ điền khi <strong>chính bạn</strong> đã so lời trên trang với sách hoặc trang web này. Để trống thì dưới lời hiện "<?php echo esc_html( CADAO_MUC_KIEM_CHUA ); ?>".</p>
	<p><label for="cadao_doi_chieu_ten">Tên sách hoặc trang web</label><br>
		<input class="widefat" id="cadao_doi_chieu_ten" name="doi_chieu_ten" value="<?php echo $o( 'doi_chieu_ten' ); ?>" placeholder="Ví dụ: Tục ngữ phong dao (1928)"></p>
	<p><label for="cadao_doi_chieu_trang">Trang (sách)</label><br>
		<input class="widefat" id="cadao_doi_chieu_trang" name="doi_chieu_trang" value="<?php echo $o( 'doi_chieu_trang' ); ?>" inputmode="numeric" placeholder="Ví dụ: 45"></p>
	<p><label for="cadao_doi_chieu_url">Địa chỉ (bản quét, trang web — nếu có)</label><br>
		<input class="widefat" id="cadao_doi_chieu_url" name="doi_chieu_url" type="url" value="<?php echo $o( 'doi_chieu_url' ); ?>" placeholder="https://"></p>
	<?php
}

add_action(
	'save_post_' . CADAO_LOAI_BAI,
	static function ( $id ) {
		if ( ! isset( $_POST['cadao_doi_chieu_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['cadao_doi_chieu_nonce'] ), 'cadao_luu_doi_chieu' ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $id ) ) {
			return;
		}
		update_post_meta( $id, 'doi_chieu_ten', sanitize_text_field( wp_unslash( $_POST['doi_chieu_ten'] ?? '' ) ) );
		update_post_meta( $id, 'doi_chieu_trang', sanitize_text_field( wp_unslash( $_POST['doi_chieu_trang'] ?? '' ) ) );
		update_post_meta( $id, 'doi_chieu_url', esc_url_raw( wp_unslash( $_POST['doi_chieu_url'] ?? '' ) ) );
	}
);

// ---------- Trang Phương pháp: mục "Dòng nhỏ dưới lời nói gì" (#muc-kiem) ----------
const CADAO_PP_LOI_CU = "<!-- wp:paragraph -->\n<p>Phần lớn lời được máy chép nguyên văn từ trang <a href=\"https://cadao.me\">Ca dao Mẹ</a> — không qua AI, không sửa chữ, không đổi cách bỏ dấu. Người biên soạn cũng nhập tay những câu mình biết. Lời ca dao, tục ngữ là của cộng đồng nên không ghi nguồn dưới lời.</p>\n<!-- /wp:paragraph -->";
const CADAO_PP_LOI    = "<!-- wp:paragraph -->\n<p>Phần lớn lời được máy chép nguyên văn từ trang <a href=\"https://cadao.me\">Ca dao Mẹ</a> — không qua AI, không sửa chữ, không đổi cách bỏ dấu. Người biên soạn cũng nhập tay những câu mình biết. Lời ca dao, tục ngữ là của cộng đồng nên dưới lời không ghi trang web đã chép.</p>\n<!-- /wp:paragraph -->";

/** Mục giải thích dòng mức kiểm (khối WordPress), đặt sau đoạn "lời lấy từ đâu". */
function cadao_pp_muc_kiem(): string {
	return "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\" id=\"muc-kiem\">Dòng nhỏ dưới lời nói gì</h2>\n<!-- /wp:heading -->\n\n"
		. "<!-- wp:paragraph -->\n<p>Ngay dưới lời mỗi bài có một dòng cho biết lời đã được kiểm đến đâu. Dòng này do máy tính từ những gì người biên soạn đã ghi; không ai đặt tay cho đẹp.</p>\n<!-- /wp:paragraph -->\n\n"
		. "<!-- wp:list -->\n<ul class=\"wp-block-list\">"
		. "<!-- wp:list-item -->\n<li><strong>" . CADAO_MUC_KIEM_CHUA . '</strong>: lời được chép nguyên văn từ một trang web hoặc do người biên soạn nhập, nhưng chưa ai so với một cuốn sách in. Lời vẫn có thể đúng — dòng này chỉ nói thật là chưa so.</li>' . "\n<!-- /wp:list-item -->"
		. "<!-- wp:list-item -->\n<li><strong>Lời đã đối chiếu với …</strong>: người biên soạn đã tự so lời với cuốn sách (kèm số trang) hoặc trang web được nêu tên. Máy không bao giờ tự ghi dòng này.</li>\n<!-- /wp:list-item -->"
		. "</ul>\n<!-- /wp:list -->\n\n"
		. "<!-- wp:paragraph -->\n<p>Bạn có sách in câu này? Dùng nút <strong>Gửi số trang</strong> ở cuối bài để giúp chúng tôi đối chiếu.</p>\n<!-- /wp:paragraph -->";
}

/** Bản nháp trang Phương pháp do máy soạn (chưa đăng, còn đoạn cũ): thêm mục #muc-kiem. Trang đã đăng thì không đụng. */
function cadao_pp_them_muc_kiem(): void {
	if ( wp_installing() ) {
		return;
	}
	$pp = get_page_by_path( 'phuong-phap', OBJECT, 'page' );
	if ( $pp && 'publish' !== $pp->post_status && str_contains( $pp->post_content, CADAO_PP_LOI_CU ) && ! str_contains( $pp->post_content, 'id="muc-kiem"' ) ) {
		wp_update_post( array( 'ID' => $pp->ID, 'post_content' => wp_slash( str_replace( CADAO_PP_LOI_CU, CADAO_PP_LOI . "\n\n" . cadao_pp_muc_kiem(), $pp->post_content ) ) ) );
	}
}
add_action( 'init', 'cadao_pp_them_muc_kiem', 33 );
