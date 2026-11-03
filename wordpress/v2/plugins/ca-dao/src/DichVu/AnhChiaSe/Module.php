<?php

namespace Phongdao\DichVu\AnhChiaSe;

use Phongdao\DichVu\DungTrang\DocBai;
use Phongdao\HaTang\KhaiBao;

defined( 'ABSPATH' ) || exit;

/**
 * Ảnh chia sẻ (docs/27 mục 10.1; thay MVP anh-chia-se.php):
 * - thẻ og:image: ảnh ngang, nền mặc định của thể loại, lưu ở uploads/phongdao-anh/<id>-<dấu băm>.png — dấu băm đổi
 *   khi lời, ý nghĩa, thể loại, đường dẫn đổi nên ảnh tự sinh lại, ảnh cũ của bài bị xoá; trang chủ, danh sách: ảnh
 *   chung của thư viện;
 * - nút "Tải ảnh" của khối Ảnh để chia sẻ: /?phongdao_anh=<id> kèm khổ, nền, "Kèm ý nghĩa" → tệp PNG tải về; ảnh đã
 *   vẽ đệm ở uploads/phongdao-anh/tai/ (giữ 300 tệp mới nhất, không đầy ổ đĩa khi bị dò hết các kiểu). Chỉ tác phẩm
 *   đã đăng; câu đố không lộ đáp án.
 * Máy không vẽ được chữ thì không có ảnh: trang vẫn chạy, không in thẻ og:image, nút Tải ảnh không hiện.
 */
final class Module implements \Phongdao\HaTang\Module {

	public const THAM_SO = 'phongdao_anh';

	private const THU_MUC = 'phongdao-anh';

	/** Số ảnh tải về giữ trong đệm. */
	private const DEM = 300;

	public function dang_ky(): void {
		add_action( 'template_redirect', array( self::class, 'tai' ), 0 );
		add_action( 'before_delete_post', array( self::class, 'xoa' ) );
	}

	/** @return array{url:string,rong:int,cao:int}|null ảnh og:image của một tác phẩm đã đăng */
	public static function og( \WP_Post $bai ): ?array {
		if ( 'publish' !== $bai->post_status || post_password_required( $bai ) || ! VeAnh::duoc() ) {
			return null;
		}
		$tp = DocBai::cho_anh( $bai );
		if ( ! $tp ) {
			return null;
		}
		$nd  = VeAnh::noi_dung( $tp );
		$nen = VeAnh::nen_cua( (string) $tp['the_loai'] )[0];
		return self::luu( (string) $bai->ID, $nd, $nen );
	}

	/** @return array{url:string,rong:int,cao:int}|null ảnh chung của thư viện (trang chủ, danh sách) */
	public static function og_chung(): ?array {
		if ( ! VeAnh::duoc() ) {
			return null;
		}
		$host = (string) wp_parse_url( home_url(), PHP_URL_HOST );
		$nd   = array(
			'the_loai' => 'Thư viện',
			'dong'     => array( 'Ca dao, tục ngữ, đồng dao,', 'vè, câu đố Việt Nam' ),
			'luc_bat'  => false,
			'cau'      => false,
			'y_nghia'  => '',
			'cau_do'   => false,
			'ten_mien' => $host,
			'duong'    => $host,
		);
		return self::luu( 'chung', $nd, 'tim' );
	}

	/** Lưu ảnh ngang (đã có cùng dấu băm thì dùng lại; ảnh cũ cùng tên đầu bị xoá). */
	private static function luu( string $dau, array $nd, string $nen ): ?array {
		[ $thu_muc, $url ] = self::thu_muc();
		$ten               = $dau . '-' . substr( md5( wp_json_encode( $nd ) . $nen . VeAnh::PHIEN_BAN ), 0, 10 ) . '.png';
		if ( ! file_exists( "$thu_muc/$ten" ) ) {
			$png = VeAnh::ve( $nd, 'ngang', $nen );
			if ( null === $png || ! wp_mkdir_p( $thu_muc ) ) {
				return null;
			}
			foreach ( glob( "$thu_muc/$dau-*.png" ) ?: array() as $cu ) {
				wp_delete_file( $cu );
			}
			if ( false === file_put_contents( "$thu_muc/$ten", $png ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions -- tệp ảnh trong uploads
				return null;
			}
		}
		[ $rong, $cao ] = VeAnh::KHO['ngang'];
		return array( 'url' => "$url/$ten", 'rong' => $rong, 'cao' => $cao );
	}

	/**
	 * Tải ảnh: đọc tham số (tên ô theo mã bài, như form của khối), trả [mã HTTP, đầu trả lời, thân].
	 *
	 * @param array<string,mixed> $q tham số GET
	 * @return array{0:int,1:array<string,string>,2:string}
	 */
	public static function phan_hoi( array $q ): array {
		$id  = absint( $q[ self::THAM_SO ] ?? 0 );
		$bai = $id ? get_post( $id ) : null;
		$tp  = $bai && KhaiBao::TAC_PHAM === $bai->post_type && 'publish' === $bai->post_status && ! post_password_required( $bai ) ? DocBai::cho_anh( $bai ) : null;
		$chu = array( 'Content-Type' => 'text/plain; charset=utf-8', 'X-Robots-Tag' => 'noindex' );
		if ( ! $tp ) {
			return array( 404, $chu, 'Không có bài này.' );
		}
		$kho = sanitize_key( (string) ( $q[ "kho-$id" ] ?? 'ngang' ) );
		$kho = isset( VeAnh::KHO[ $kho ] ) ? $kho : 'ngang';
		$ds  = VeAnh::nen_cua( (string) $tp['the_loai'] );
		$nen = sanitize_key( (string) ( $q[ "nen-$id" ] ?? '' ) );
		$png = self::dem( $id, VeAnh::noi_dung( $tp ), $kho, in_array( $nen, $ds, true ) ? $nen : $ds[0], ! empty( $q[ "yn-$id" ] ) );
		if ( null === $png ) {
			return array( 503, $chu, 'Máy chủ chưa vẽ được ảnh. Hãy chụp màn hình phần xem trước.' );
		}
		return array(
			200,
			array(
				'Content-Type'        => 'image/png',
				'Content-Disposition' => 'attachment; filename="phongdao-' . sanitize_file_name( $bai->post_name ) . '-' . $kho . '.png"',
				'Content-Length'      => (string) strlen( $png ),
				'Cache-Control'       => 'public, max-age=86400',
				'X-Robots-Tag'        => 'noindex',
			),
			$png,
		);
	}

	/** Ảnh tải về: lấy trong đệm, chưa có thì vẽ rồi đệm (quá DEM tệp thì xoá tệp cũ nhất). */
	private static function dem( int $id, array $nd, string $kho, string $nen, bool $kem_y ): ?string {
		$thu_muc = self::thu_muc()[0] . '/tai';
		$tep     = "$thu_muc/$id-$kho-$nen-" . (int) $kem_y . '-' . substr( md5( wp_json_encode( $nd ) . VeAnh::PHIEN_BAN ), 0, 10 ) . '.png';
		if ( is_readable( $tep ) ) {
			return (string) file_get_contents( $tep ); // phpcs:ignore WordPress.WP.AlternativeFunctions -- tệp ảnh trong uploads
		}
		$png = VeAnh::ve( $nd, $kho, $nen, $kem_y );
		if ( null !== $png && wp_mkdir_p( $thu_muc ) && false !== file_put_contents( $tep, $png ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions
			$ds = glob( "$thu_muc/*.png" ) ?: array();
			if ( count( $ds ) > self::DEM ) {
				usort( $ds, static fn( string $a, string $b ): int => filemtime( $a ) <=> filemtime( $b ) );
				array_map( 'wp_delete_file', array_slice( $ds, 0, count( $ds ) - self::DEM ) );
			}
		}
		return $png;
	}

	public static function tai(): void {
		// phpcs:ignore WordPress.Security.NonceVerification -- tải ảnh công khai, chỉ đọc
		if ( ! isset( $_GET[ self::THAM_SO ] ) ) {
			return;
		}
		[ $ma, $dau, $than ] = self::phan_hoi( wp_unslash( $_GET ) ); // phpcs:ignore WordPress.Security.NonceVerification
		status_header( $ma );
		if ( 200 !== $ma ) {
			nocache_headers();
		}
		foreach ( $dau as $k => $v ) {
			header( "$k: $v" );
		}
		echo $than; // phpcs:ignore WordPress.Security.EscapeOutput -- ảnh PNG hoặc chữ thường
		exit;
	}

	/** Tác phẩm bị xoá hẳn: xoá ảnh đã lưu. */
	public static function xoa( int $id ): void {
		if ( KhaiBao::TAC_PHAM === get_post_type( $id ) ) {
			foreach ( array_merge( glob( self::thu_muc()[0] . '/' . $id . '-*.png' ) ?: array(), glob( self::thu_muc()[0] . '/tai/' . $id . '-*.png' ) ?: array() ) as $cu ) {
				wp_delete_file( $cu );
			}
		}
	}

	/** @return array{0:string,1:string} thư mục, địa chỉ lưu ảnh */
	private static function thu_muc(): array {
		$u = wp_upload_dir( null, false );
		return array( trailingslashit( $u['basedir'] ) . self::THU_MUC, trailingslashit( $u['baseurl'] ) . self::THU_MUC );
	}
}
