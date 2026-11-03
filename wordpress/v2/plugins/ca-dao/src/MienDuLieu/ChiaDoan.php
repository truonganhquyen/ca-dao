<?php

namespace Phongdao\MienDuLieu;

/**
 * Chia lời thành đoạn để đọc (docs/27 mục 6; chuyển từ cadao_chia_doan của theme MVP, T-090). PHP thuần.
 *
 * - Bài có phần "Đoạn" (người biên soạn đặt, hoặc tiêu đề phụ của bài MVP nhiều phần): đúng các đoạn đó, có tiêu đề;
 *   dòng nằm ngoài mọi đoạn thành đoạn không tiêu đề, đoạn chồng nhau hoặc vượt quá lời thì cắt lại — không mất dòng.
 * - Không có: bài dài (từ 41 dòng) chia theo khổ (dòng trống); khổ dài quá 40 dòng chia tiếp mỗi đoạn khoảng 20 dòng.
 * Dòng đếm theo dòng có chữ, từ 1. Bài dài: các đoạn bắt đầu trong 30 dòng đầu hiện sẵn, còn lại sau "Đọc toàn bài".
 */
final class ChiaDoan {

	/** Bài dài: từ 41 dòng có chữ. */
	public const DAI = 40;

	/** Bài dài hiện sẵn các đoạn bắt đầu trong 30 dòng đầu. */
	public const HIEN = 30;

	/** Khổ dài quá DAI dòng: mỗi đoạn khoảng 20 dòng. */
	public const MOI = 20;

	/**
	 * @param string[]                                              $dong lời (dòng trống = ngắt khổ)
	 * @param list<array{tieu_de?:string,tu_dong:int,den_dong:int}> $doan các mục của phần "Đoạn" (có thể rỗng)
	 * @return array{dai:bool,so_dong:int,phut:int,con_lai:int,doan:list<array{so:int,tieu_de:string,tu:int,den:int,kho:list<string[]>,sau:bool}>}|null
	 *         null = bài ngắn không đặt đoạn: hiện như thường
	 */
	public static function chia( array $dong, array $doan = array() ): ?array {
		$dong = array_values( array_map( 'strval', $dong ) );
		$chu  = array_keys( array_filter( $dong, static fn( string $d ): bool => '' !== trim( $d ) ) );
		$n    = count( $chu );
		$dai  = $n > self::DAI;
		$moc  = $n ? self::theo_phan( $doan, $n ) : array();
		if ( ! $moc ) {
			if ( ! $dai ) {
				return null;
			}
			$moc = self::theo_kho( $dong );
		}

		$ra   = array();
		$hien = 0;
		$am   = 0;
		foreach ( $moc as $i => [ $tieu_de, $tu ] ) {
			$den  = ( $moc[ $i + 1 ][1] ?? $n + 1 ) - 1;
			$kho  = array();
			$cho  = array();
			foreach ( array_slice( $dong, $chu[ $tu - 1 ], $chu[ $den - 1 ] - $chu[ $tu - 1 ] + 1 ) as $d ) {
				if ( '' === trim( $d ) ) {
					if ( $cho ) {
						$kho[] = $cho;
					}
					$cho = array();
					continue;
				}
				$cho[] = $d;
			}
			if ( $cho ) {
				$kho[] = $cho;
			}
			$ra[] = array(
				'so'      => $i + 1,
				'tieu_de' => $tieu_de,
				'tu'      => $tu,
				'den'     => $den,
				'kho'     => $kho,
				'sau'     => $dai && $tu > self::HIEN,
			);
			if ( $tu <= self::HIEN ) {
				$hien = $den;
			}
			$am += count( preg_split( '/\s+/u', trim( implode( ' ', array_merge( ...$kho ) ) ), -1, PREG_SPLIT_NO_EMPTY ) );
		}
		return array(
			'dai'     => $dai,
			'so_dong' => $n,
			'phut'    => max( 1, (int) ceil( $am / 200 ) ), // khoảng 200 âm tiết mỗi phút
			'con_lai' => $dai ? $n - $hien : 0,
			'doan'    => $ra,
		);
	}

	/**
	 * Mốc đầu đoạn theo phần "Đoạn": [tiêu đề, dòng đầu]; khoảng hở thành đoạn không tiêu đề.
	 *
	 * @return list<array{0:string,1:int}>
	 */
	private static function theo_phan( array $doan, int $n ): array {
		$doan = array_values( array_filter( $doan, static fn( $d ): bool => is_array( $d ) && isset( $d['tu_dong'], $d['den_dong'] ) ) );
		usort( $doan, static fn( $a, $b ): int => (int) $a['tu_dong'] <=> (int) $b['tu_dong'] );
		$moc = array();
		$het = 0;
		foreach ( $doan as $d ) {
			$tu  = max( (int) $d['tu_dong'], $het + 1 );
			$den = min( (int) $d['den_dong'], $n );
			if ( $tu > $den ) {
				continue;
			}
			if ( $tu > $het + 1 ) {
				$moc[] = array( '', $het + 1 );
			}
			$moc[] = array( trim( (string) ( $d['tieu_de'] ?? '' ) ), $tu );
			$het   = $den;
		}
		if ( $moc && $het < $n ) {
			$moc[] = array( '', $het + 1 );
		}
		return $moc;
	}

	/**
	 * Mốc đầu đoạn theo khổ, đúng cadao_chia_doan của MVP.
	 *
	 * @return list<array{0:string,1:int}>
	 */
	private static function theo_kho( array $dong ): array {
		$dai_kho = array();
		$kho     = 0;
		foreach ( $dong as $k => $d ) {
			if ( '' === trim( $d ) ) {
				$kho = $k + 1;
				continue;
			}
			$dai_kho[ $kho ] = ( $dai_kho[ $kho ] ?? 0 ) + 1;
		}
		$moc   = array();
		$thu   = 0;
		$moi   = true;
		$trong = 0;
		$kho   = 0;
		foreach ( $dong as $k => $d ) {
			if ( '' === trim( $d ) ) {
				$moi = true;
				$kho = $k + 1;
				continue;
			}
			++$thu;
			if ( $moi || ( $dai_kho[ $kho ] > self::DAI && $trong >= self::MOI ) ) {
				$moc[] = array( '', $thu );
				$trong = 0;
				$moi   = false;
			}
			++$trong;
		}
		return $moc;
	}
}
