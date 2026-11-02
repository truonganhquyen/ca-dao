<?php

namespace Phongdao\HaTang;

/**
 * Một mảng chức năng của plugin. dang_ky() chỉ gắn hook, không làm việc nặng lúc nạp
 * (truy vấn, gọi mạng chạy trong hook hoặc hàng đợi).
 */
interface Module {

	public function dang_ky(): void;
}
