<?php

namespace Phongdao\GiaoTiep\Khoi;

defined( 'ABSPATH' ) || exit;

/**
 * Trang tác phẩm (docs/27 mục 6, bộ thiết kế R05–R09): đầu trang căn giữa → thẻ lời (lời, nhãn mức kiểm, ý nghĩa)
 * + công cụ đọc → từ khó, dị bản, (tục ngữ: giải nghĩa chi tiết, dùng khi, câu gần nghĩa) → chủ đề / vùng / tuyển tập
 * → Lời này từ đâu? → Ảnh để chia sẻ. Khối không có dữ liệu thì không hiện, kể cả tiêu đề (docs/27 mục 1).
 * Đọc tiếp, Bạn muốn góp gì?, Cảm nghĩ, Trình chiếu làm khi có dữ liệu thật.
 */
final class TacPham {

	/** @param array<string,mixed> $tp xem DichVu\DungTrang\DuLieuMau */
	public static function hien( array $tp ): string {
		$tp['url'] = $tp['url'] ?? ( get_permalink() ?: home_url( '/' ) );
		$tu        = array_values( array_filter( array_column( $tp['chu_thich'], 'tu' ) ) );
		$tuc_ngu   = 'proverb' === $tp['kieu'];
		$nhan      = array( array( trim( $tp['ten_the_loai'] . ( $tp['hinh_thuc'] ? ' · ' . $tp['hinh_thuc'] : '' ) ) ) );
		if ( $tp['di_ban'] ) {
			$nhan[] = array( 'Bản tham chiếu · ' . count( $tp['di_ban'] ) . ' dị bản', true, '#di-ban' );
		}
		$tieu_de = $tuc_ngu
			? str_replace( array( '<div', '</div>' ), array( '<span', '</span>' ), Ve::loi( $tp['loi'], 'proverb', array(), array( 'max' => '48' ) ) )
			: esc_html( $tp['tieu_de'] );
		$y = empty( $tp['y_nghia']['chu'] )
			? '<div class="notice">Bài đang được bổ sung ý nghĩa. Bạn biết thêm? ' . Ve::lien_ket( 'gop', 'Góp ý' ) . '</div>'
			: Ve::y_nghia( $tp['y_nghia']['chu'], ! empty( $tp['y_nghia']['ai'] ) );
		$cau_do = 'cau-do' === $tp['the_loai'];
		if ( $cau_do ) {
			// Câu đố: đáp án thu gọn sau nút "Hiện đáp án", tắt JavaScript vẫn mở được (D-087)
			$y = ( empty( $tp['goi_y'] ) ? '' : '<details class="disclosure"><summary>Gợi ý</summary><div class="detail-body"><p>' . esc_html( $tp['goi_y'] ) . '</p></div></details>' )
				. ( empty( $tp['dap_an'] ) ? '' : '<details class="answer"><summary>Hiện đáp án</summary><div class="detail-body"><h2>Đáp án</h2><p>' . esc_html( $tp['dap_an'] ) . '</p></div></details>' )
				. ( empty( $tp['y_nghia']['chu'] ) ? '' : Ve::y_nghia( $tp['y_nghia']['chu'], ! empty( $tp['y_nghia']['ai'] ) ) );
		}
		$than_loi = $tuc_ngu
			? Ve::muc_kiem( $tp['doi_chieu'] ) . $y
			: Ve::loi( $tp['loi'], $tp['kieu'], $tu, array( 'so_dong' => ! $cau_do, 'nhom' => $tp['ma'] ) ) . Ve::muc_kiem( $tp['doi_chieu'] ) . $y;

		$sau = '';
		if ( $tp['chu_thich'] ) {
			$o = '';
			foreach ( $tp['chu_thich'] as $c ) {
				// Nguồn người biên soạn điền thì hiện (D-081); nguồn của chú thích máy chép ẩn theo chính sách (D-068)
				$nguon = empty( $c['nguon'] ) ? '' : '<br><span class="small">Nguồn: ' . ( empty( $c['nguon']['url'] ) ? esc_html( $c['nguon']['chu'] ) : '<a href="' . esc_url( $c['nguon']['url'] ) . '" rel="nofollow">' . esc_html( $c['nguon']['chu'] ) . '</a>' ) . '</span>';
				$o    .= '<div class="term"><dt>' . esc_html( '' !== $c['tu'] ? $c['tu'] : 'Chú thích chung' ) . '</dt><dd>' . esc_html( $c['giai'] ) . $nguon . '</dd></div>';
			}
			$sau .= Ve::khoi( 'Từ khó', '<dl class="terms">' . $o . '</dl>', 'tint-vang', 'chu-thich' );
		}
		if ( $tp['di_ban'] ) {
			$o = '<p class="small muted">Các cách lưu truyền đặt cạnh Bản tham chiếu. Chữ gạch dưới là phần khác.</p>';
			foreach ( $tp['di_ban'] as $i => $d ) {
				$v = Ve::loi( $d['loi'], $tp['kieu'], array(), array( 'nhom' => 'di-ban' ) );
				foreach ( $d['khac'] as $k ) {
					$v = str_replace( '>' . esc_html( $k ), '><ins>' . esc_html( $k ) . '</ins>', str_replace( ' ' . esc_html( $k ), ' <ins>' . esc_html( $k ) . '</ins>', $v ) );
				}
				$noi = array_filter( array( $d['noi'], $d['nguoi_ke'], $d['nam'] ?? '' ) );
				$o  .= '<div class="variant"><span class="tag">' . sprintf( '%02d', $i + 1 ) . ' · Dị bản</span>' . $v . '<p class="small muted">' . ( $noi ? esc_html( implode( ' · ', $noi ) ) : 'Chưa ghi nơi, người kể hoặc năm.' ) . '</p></div>';
			}
			$sau .= Ve::khoi_gon( 'Dị bản · ' . count( $tp['di_ban'] ), $o, 'di-ban', 'tim' );
		}
		if ( $tuc_ngu && ! empty( $tp['nghia_den'] ) ) {
			$sau .= Ve::khoi( 'Giải nghĩa chi tiết', '<div class="two-col"><div class="mini"><h3>Nghĩa đen</h3><p>' . esc_html( $tp['nghia_den'] ) . '</p></div><div class="mini"><h3>Nghĩa bóng</h3><p>' . esc_html( $tp['nghia_bong'] ?? '' ) . '</p></div></div>', 'tint-luc' );
		}
		if ( ! empty( $tp['dung_khi'] ) ) {
			$sau .= Ve::khoi( 'Dùng khi', '<ul class="uses">' . implode( '', array_map( static fn( string $x ): string => '<li>' . esc_html( $x ) . '</li>', $tp['dung_khi'] ) ) . '</ul>', 'tint-xanh' );
		}
		$nhan_cuoi = '';
		foreach ( $tp['chu_de'] as $c ) {
			$nhan_cuoi .= self::nhan_lien_ket( $c, 'chu-de', 'Chủ đề: ', 'tag-pill' );
		}
		$vung       = implode( ', ', array_map( static fn( $v ): string => is_array( $v ) ? (string) $v[0] : (string) $v, $tp['vung'] ) );
		$nhan_cuoi .= '<span class="tag-pill plain">Nơi lưu truyền: ' . ( '' !== $vung ? esc_html( $vung ) : 'chưa ghi nhận' ) . '</span>';
		foreach ( $tp['tuyen_tap'] as $t ) {
			$nhan_cuoi .= self::nhan_lien_ket( $t, 'tuyen-tap', 'Có trong tuyển tập: ', 'tag-pill plain' );
		}
		$sau .= '<div class="work-tags">' . $nhan_cuoi . '</div>' . self::tu_lieu( $tp ) . AnhChiaSe::hien( $tp );

		return '<nav aria-label="Đường dẫn" class="breadcrumbs">' . Ve::lien_ket( 'trang-chu', 'Trang chủ' ) . '<span aria-hidden="true">/</span>' . Ve::lien_ket( $tp['the_loai'], esc_html( $tp['ten_the_loai'] ) ) . '<span aria-hidden="true">/</span><span>' . esc_html( $tp['tieu_de'] ) . '</span></nav>'
			. '<header class="work-head">' . Ve::nhan_dau( $nhan ) . '<h1 class="read-title">' . $tieu_de . '</h1><p class="read-sub">' . Ve::so_lieu( $tp['so_lieu'] ) . '</p></header>'
			. '<div class="work-layout"><div class="work-main">' . Ve::the_loi( $than_loi, $tp['url'], $tuc_ngu ? 'Ý nghĩa' : 'Lời' ) . '</div>' . Ve::cong_cu( $tp ) . '<div class="work-rest">' . $sau . '</div></div>';
	}

	/** Nhãn dẫn tới trang phân loại: $muc là tên (dữ liệu mẫu) hoặc [tên, đường dẫn]. */
	private static function nhan_lien_ket( string|array $muc, string $ma, string $dau, string $lop ): string {
		[ $ten, $url ] = is_array( $muc ) ? array( (string) $muc[0], (string) ( $muc[1] ?? '' ) ) : array( $muc, '' );
		return '' === $url
			? Ve::lien_ket( $ma, esc_html( $dau . $ten ), $lop )
			: '<a class="' . esc_attr( $lop ) . '" href="' . esc_url( $url ) . '">' . esc_html( $dau . $ten ) . '</a>';
	}

	/** "Lời này từ đâu?" (D-085, D-093): nguồn đã ẩn không có trong HTML gửi người đọc. */
	private static function tu_lieu( array $tp ): string {
		$loi = null === $tp['doi_chieu']
			? 'Bản lưu truyền phổ biến, chưa đối chiếu với sách in. ' . Ve::lien_ket( 'gop', 'Có sách? Gửi số trang' )
			: 'Đã đối chiếu với ' . esc_html( $tp['doi_chieu']['sach'] ) . '.';
		$dong = '<dt>Lời</dt><dd>' . $loi . '</dd>';
		if ( ! empty( $tp['y_nghia']['chu'] ) ) {
			$dong .= '<dt>Ý nghĩa</dt><dd>Người biên soạn của thư viện viết' . ( empty( $tp['y_nghia']['ai'] ) ? '' : '; có hỗ trợ của AI, đã duyệt' ) . '.</dd>';
		}
		if ( $tp['chu_thich'] ) {
			// Dữ liệu mẫu không ghi người làm: coi như máy chép (nguồn ẩn)
			$may  = count( array_filter( $tp['chu_thich'], static fn( array $c ): bool => $c['may'] ?? true ) );
			$viet = count( $tp['chu_thich'] ) - $may;
			$o    = array();
			if ( $may ) {
				$o[] = $may . ' mục chép từ nguồn tham khảo · nguồn đã ẩn theo ' . Ve::lien_ket( 'chinh-sach', 'Chính sách thu thập dữ liệu' );
			}
			if ( $viet ) {
				$o[] = $viet . ' mục người biên soạn viết' . ( array_filter( array_column( $tp['chu_thich'], 'nguon' ) ) ? ', nguồn ghi tại chỗ' : '' );
			}
			$dong .= '<dt>Chú thích</dt><dd>' . implode( '<br>', $o ) . '</dd>';
		}
		$dong .= '<dt>Cách làm</dt><dd>' . Ve::lien_ket( 'phuong-phap', 'Cách chúng tôi biên soạn' ) . '</dd>';
		return Ve::khoi_gon( Ve::icon( 'info' ) . 'Lời này từ đâu?', '<dl class="metadata">' . $dong . '</dl>', 'tu-lieu', 'dat' );
	}
}
