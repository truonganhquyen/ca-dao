<?php
/**
 * Kho thử cho phép đo tìm kiếm (docs/22 T-181): bài tổng hợp từ âm tiết tiếng Việt rời rạc, tần suất kiểu Zipf,
 * cùng hạt giống thì cùng kho (đo lại so được). KHÔNG phải lời thật của nguồn nào (repo này công khai) và không
 * bao giờ đăng: chỉ nằm trong bảng chỉ mục tìm của site thử (Playground hoặc CSDL đo riêng).
 */

declare(strict_types=1);

/** Âm tiết của lời, dùng nhiều trước (thứ tự là thứ hạng tần suất). */
const DO_TIM_LOI = 'anh em người con có không một ai đi về ra cho mà thì là nhà mẹ cha trời đất sông núi nước non lòng thương nhớ '
	. 'đâu bao nhiêu chẳng như ơi hỡi ngày đêm trăng gió mưa nắng đồng ruộng lúa gạo cơm ăn uống làm chồng vợ duyên tình '
	. 'chung thủy nghĩa cây hoa lá cành chim cò sáo bướm đò bến thuyền chợ làng quê xứ đường xa gần lên xuống qua lại năm '
	. 'tháng mai chiều sớm tối khi xưa nay mới cũ già trẻ thầy bạn bè vua quan dân giàu nghèo sang hèn khôn dại tốt xấu '
	. 'đẹp hay dở ngọt đắng cay chua mặn nhạt trắng đen đỏ xanh vàng tím hồng biển hồ ao đầm giếng cầu đình chùa miếu phố '
	. 'kinh thành nam bắc đông tây trên dưới trong ngoài trước sau giữa bên cạnh đầu cuối tay chân mắt miệng tóc má lưng '
	. 'bụng ruột gan dạ tim vai lời câu hát ru hò chuyện tiếng nói cười khóc buồn vui giận hờn sầu mong chờ đợi gặp cách '
	. 'yêu ghét sợ tin ngờ biết hiểu học hành cày cấy gặt hái trồng chăn nuôi buôn bán đánh bắt cá tôm cua ốc trâu bò '
	. 'lợn gà vịt chó mèo ngựa voi hổ rồng phượng rùa tre trúc đào sen cúc lan huệ bèo bông cau trầu rượu chè thuốc áo '
	. 'quần khăn nón yếm dải lụa vải tơ kim chỉ dao kéo cuốc võng chiếu giường mâm bát đũa nồi niêu chum vại ma mạ mã '
	. 'ca cà cả di côn sao nuôi rau dưa muối tương cà mắm canh xôi chè bánh chưng giầy đa đá đà dã khoai sắn ngô đậu vừng '
	. 'lạc mía chuối cam quýt bưởi mít ổi nhãn vải hồng đèn nến lửa khói tro than củi rơm rạ';

/** Âm tiết chỉ có trong phần giải thích (ý nghĩa, chú thích). */
const DO_TIM_GIAI = 'câu khuyên ta phải sống chỉ việc ý ví hình ảnh ẩn dụ so sánh phê phán ca ngợi răn dạy kinh nghiệm lao động sản xuất '
	. 'phong tục tập quán đạo lý gia đình xã hội thời những kẻ';

/** Kho thử n bài (id 1..n): [id, chinh (tên + lời + dị bản, như ChiMucTim::ghi gộp), phu (ý nghĩa + chú thích)]. */
function do_tim_kho( int $n, int $hat = 20261110 ): array {
	$loi  = explode( ' ', DO_TIM_LOI );
	$giai = array_merge( explode( ' ', DO_TIM_GIAI ), array_slice( $loi, 0, 120 ) );
	// Bảng tích luỹ Zipf lệch 10 (âm tiết đầu ~3 % số âm tiết, như "anh", "em" trong ca dao)
	$tich = static function ( array $tu ): array {
		$c = array();
		$s = 0.0;
		foreach ( array_keys( $tu ) as $i ) {
			$s  += 1 / ( $i + 10 );
			$c[] = $s;
		}
		return array( $c, $s );
	};
	[ $c_loi, $t_loi ]   = $tich( $loi );
	[ $c_giai, $t_giai ] = $tich( $giai );
	mt_srand( $hat, MT_RAND_MT19937 );
	$rut  = static function ( array $tu, array $c, float $t ): string {
		$x = mt_rand() / mt_getrandmax() * $t;
		$a = 0;
		$b = count( $c ) - 1;
		while ( $a < $b ) { // tìm nhị phân trên bảng tích luỹ
			$g = intdiv( $a + $b, 2 );
			if ( $c[ $g ] < $x ) {
				$a = $g + 1;
			} else {
				$b = $g;
			}
		}
		return $tu[ $a ];
	};
	$dong = static fn( int $so ): string => implode( ' ', array_map( static fn() => $rut( $loi, $c_loi, $t_loi ), range( 1, $so ) ) );
	$cau  = static fn( int $so ): string => implode( ' ', array_map( static fn() => $rut( $giai, $c_giai, $t_giai ), range( 1, $so ) ) );
	$kho  = array();
	for ( $id = 1; $id <= $n; $id++ ) {
		$x = mt_rand( 1, 100 );
		if ( $x <= 45 ) { // tục ngữ – thành ngữ: một dòng
			$lines = array( $dong( mt_rand( 4, 10 ) ) );
		} elseif ( $x <= 85 ) { // ca dao lục bát: 2–8 dòng
			$lines = array();
			for ( $i = 0, $so = 2 * mt_rand( 1, 4 ); $i < $so; $i++ ) {
				$lines[] = $dong( 0 === $i % 2 ? 6 : 8 );
			}
		} elseif ( $x <= 95 ) { // đồng dao: 4–12 dòng bốn chữ
			$lines = array_map( static fn() => $dong( 4 ), range( 1, mt_rand( 4, 12 ) ) );
		} else { // vè: 20–80 dòng
			$lines = array_map( static fn() => $dong( mt_rand( 4, 8 ) ), range( 1, mt_rand( 20, 80 ) ) );
		}
		$chinh = $lines[0] . "\n" . implode( "\n", $lines );
		if ( mt_rand( 1, 10 ) === 1 ) { // dị bản: đổi một âm tiết
			$tu                                  = explode( ' ', $lines[0] );
			$tu[ mt_rand( 0, count( $tu ) - 1 ) ] = $rut( $loi, $c_loi, $t_loi );
			$chinh                              .= "\n" . implode( ' ', $tu );
		}
		$phu = array();
		if ( mt_rand( 1, 100 ) <= 35 ) { // ý nghĩa
			$phu[] = $cau( mt_rand( 15, 50 ) );
		}
		if ( mt_rand( 1, 100 ) <= 25 ) { // chú thích: từ + lời giải
			for ( $i = 0, $so = mt_rand( 1, 3 ); $i < $so; $i++ ) {
				$phu[] = $rut( $loi, $c_loi, $t_loi ) . ' ' . $cau( mt_rand( 8, 20 ) );
			}
		}
		$kho[] = array( $id, $chinh, implode( "\n", $phu ) );
	}
	return $kho;
}

/**
 * Tập truy vấn đại diện cho kho n bài: chữ phổ biến, cụm lấy từ bài mới nhất (id lớn — cách cũ chặn 500 ứng viên
 * đầu bảng hay bỏ sót), gõ dở, chữ hiếm, chữ chỉ có ở phần giải thích, gõ sai, không có.
 *
 * @return array{ds:array<string,string>,moi:int} nhãn => truy vấn; moi = id bài mới nhất dùng làm mẫu
 */
function do_tim_truy_van( array $kho ): array {
	$loi = explode( ' ', DO_TIM_LOI );
	$kd  = static fn( string $s ): string => \Phongdao\MienDuLieu\ChiMuc::khong_dau( \Phongdao\MienDuLieu\ChiMuc::chu( $s ) );
	// Bài mới nhất có ít nhất hai dòng lời 6–8 chữ
	for ( $i = count( $kho ) - 1; $i >= 0; $i-- ) {
		$dong = explode( "\n", $kho[ $i ][1] );
		if ( count( $dong ) >= 3 && count( explode( ' ', $dong[2] ) ) >= 6 ) {
			break;
		}
	}
	$moi  = (int) $kho[ $i ][0];
	$tu   = explode( ' ', $dong[2] );
	$cum3 = implode( ' ', array_slice( $tu, 0, 3 ) );
	$sai  = $tu;
	$sai[1] .= 'q'; // không âm tiết nào kết thúc bằng q: chắc chắn 0 kết quả
	// Chữ hiếm: âm tiết cuối bảng (ít dùng nhất) mà bỏ dấu không trùng âm tiết khác ("rạ" trùng "ra" thì bỏ qua)
	$dem_kd = array_count_values( array_map( $kd, $loi ) );
	for ( $j = count( $loi ) - 1; $j > 0 && $dem_kd[ $kd( $loi[ $j ] ) ] > 1; $j-- ) {
		continue;
	}
	return array(
		'moi' => $moi,
		'ds'  => array(
			'Một chữ cái (vừa gõ)'           => 'a',
			'Chữ phổ biến nhất, không dấu'   => $kd( $loi[0] ),
			'Chữ phổ biến, có dấu'           => $loi[2],
			'Hai chữ phổ biến'               => $kd( $loi[0] . ' ' . $loi[1] ),
			'Ba chữ của bài mới, không dấu'  => $kd( $cum3 ),
			'Ba chữ của bài mới, có dấu'     => $cum3,
			'Cả dòng của bài mới'            => $kd( $dong[2] ),
			'Đang gõ dở chữ thứ ba'          => $kd( $tu[0] . ' ' . $tu[1] ) . ' ' . mb_substr( $kd( $tu[2] ), 0, 1 ),
			'Chữ hiếm'                       => $kd( $loi[ $j ] ),
			'Chỉ có ở phần giải thích'       => 'ẩn dụ',
			'Gõ sai một chữ cái'             => $kd( implode( ' ', array_slice( $sai, 0, 3 ) ) ),
			'Không có trong kho'             => 'xyz qwv',
		),
	);
}
