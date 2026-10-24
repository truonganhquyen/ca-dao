// Giao diện CMS (T-037, docs/21 mục 12): nhãn tiếng Việt, gợi ý trong từng ô, thứ tự ô, ô thu gọn, giá trị mặc định,
// bộ "Thêm nhanh". scripts/sinh-tu-schema.mjs ghép tệp này với scripts/lib/schema.mjs để sinh public/admin/config.yml;
// ô nào trong schema thiếu nhãn ở đây thì bộ sinh báo lỗi — hai tệp không lệch nhau được.
// Câu chữ dự án tự viết, đặt dấu theo SGK (D-038). Không dùng lời ca dao làm ví dụ.

export const CMS = {
  site_url: 'https://ca-dao.truonganhquyen.workers.dev',
  base_url: 'https://sveltia-cms-auth.truonganhquyen.workers.dev', // Worker đăng nhập (docs/huong-dan/dang-nhap-cms.md)
  // Sveltia bắt buộc có thư mục tệp; dự án không đăng ảnh (D-046) nên thư mục này để trống.
  media_folder: 'public/tep',
  commit_messages: {
    create: 'Nội dung: thêm {{collection}} {{slug}}',
    update: 'Nội dung: sửa {{collection}} {{slug}}',
    delete: 'Nội dung: xóa {{collection}} {{slug}}',
    uploadMedia: 'Nội dung: tải tệp {{path}}',
    deleteMedia: 'Nội dung: xóa tệp {{path}}',
  },
};

/** Tên bộ sưu tập trong CMS. */
export const COLLECTION = {
  'tac-pham': { nhan: 'Tác phẩm', mot: 'tác phẩm', mo_ta: 'Ca dao, tục ngữ, thành ngữ… Mỗi bài một tệp. Lời chỉ lấy từ nguồn có ghi rõ, không sửa khi chưa có nguồn khác.' },
  'muc-tu': { nhan: 'Từ điển (mục từ)', mot: 'mục từ', mo_ta: 'Từ khó dùng chung cho mọi bài. Giải nghĩa viết bằng lời của dự án.' },
  'mo-tip': { nhan: 'Mô-típ', mot: 'mô-típ', mo_ta: 'Hình ảnh, cách nói lặp lại ở nhiều bài.' },
  'nguon': { nhan: 'Nguồn', mot: 'nguồn', mo_ta: 'Sách, trang web, tư liệu điền dã. Chỉ ghi điều đã thấy tận mắt trên nguồn.' },
  'tinh-huong': { nhan: 'Tình huống', mot: 'tình huống', mo_ta: 'Nhóm bài theo hoàn cảnh dùng. Thêm bài vào tình huống ở đây (danh sách "Bài trong tình huống").' },
  'ghi-am': { nhan: 'Ghi âm', mot: 'ghi âm', mo_ta: 'Chỉ thêm khi đã có hồ sơ đồng ý của người được ghi âm.' },
  'nguoi-dong-gop': { nhan: 'Người đóng góp', mot: 'người đóng góp', mo_ta: 'Không ghi email, số điện thoại, địa chỉ, tên thật của người chọn ẩn danh.' },
};

// Nhãn dùng chung theo tên ô (áp ở mọi nơi tên đó xuất hiện, trừ khi RIENG ghi khác)
export const CHUNG = {
  id: { nhan: 'Mã', goi_y: 'Không đổi sau khi đã tạo. Trùng tên tệp.' },
  slug: { nhan: 'Đường dẫn', goi_y: 'Chữ thường không dấu, nối bằng gạch ngang. Ví dụ: `cong-cha-nghia-me`.' },
  tieu_de: { nhan: 'Tiêu đề' },
  vung: { nhan: 'Vùng', goi_y: 'Vùng văn hóa theo nguồn. Chưa biết thì chọn "Chưa rõ vùng".' },
  nguon: { nhan: 'Nguồn', goi_y: 'Mỗi dòng một nguồn đã dùng.' },
  ref: { nhan: 'Nguồn', goi_y: 'Chọn từ danh sách Nguồn. Chưa có thì tạo trong bộ "Nguồn" trước.' },
  trang: { nhan: 'Số trang', goi_y: 'Với sách, tạp chí, luận văn, bản quét. Ví dụ: `123` hoặc `45–46`.' },
  url: { nhan: 'Địa chỉ trang web', goi_y: 'Địa chỉ của **đúng trang** đã lấy (không phải trang chủ). Bắt đầu bằng `https://`.' },
  ngay_truy_cap: { nhan: 'Ngày truy cập', goi_y: 'Ngày bạn mở trang đó.' },
  ban_luu_tru: { nhan: 'Bản lưu trữ', goi_y: 'Bản lưu của trang trên web.archive.org (nếu có).' },
  cach_dung: { nhan: 'Cách dùng nguồn', goi_y: '"Tham khảo" = đã viết lại bằng lời của mình. "Trích nguyên văn" chỉ khi nguồn cho phép bằng văn bản, hoặc định nghĩa ≤ 15 từ.' },
  ghi_chu: { nhan: 'Ghi chú' },
  nhap_ai_da_duyet: { nhan: 'Tôi đã đọc và đồng ý mọi phần do AI soạn', goi_y: 'Chỉ đánh dấu khi đã đọc hết các câu bắt đầu bằng NHAP_AI trong tệp này và thấy đúng. Trang sẽ ghi "có hỗ trợ của AI, đã được người biên soạn duyệt".' },
  muc: { nhan: 'Mục' },
};

// Nhãn riêng theo đường dẫn trong từng bộ (ô con viết bằng dấu chấm; ô trong danh sách không cần [])
export const RIENG = {
  'tac-pham': {
    the_loai: { nhan: 'Thể loại' },
    the_tho: { nhan: 'Thể thơ', goi_y: 'Với ca dao. Lục bát được thụt lề câu sáu chữ.' },
    tieu_de: { nhan: 'Tiêu đề', goi_y: 'Thường là dòng đầu của bản tham chiếu.' },
    di_ban: { nhan: 'Lời (các dị bản)', goi_y: 'Mỗi cách nói là một dị bản. Bản đầu tiên thường là v1.' },
    'di_ban.id': { nhan: 'Mã dị bản', goi_y: '`v1`, `v2`, `v3`… không trùng trong bài.' },
    'di_ban.loi': { nhan: 'Lời', goi_y: 'Mỗi dòng thơ một dòng. Gõ hoặc dán **đúng như nguồn**; không sửa chữ khi chưa có nguồn khác. Nghi sai chữ thì ghi vào "Ghi chú".' },
    'di_ban.dia_danh': { nhan: 'Địa danh', goi_y: 'Đúng như nguồn hoặc người kể gọi, kể cả tên cũ.' },
    'di_ban.nguon': { nhan: 'Lời lấy từ đâu', goi_y: 'Bắt buộc trước khi công khai (ít nhất một nguồn, hoặc mã đóng góp).' },
    'di_ban.nguoi_ke': { nhan: 'Người kể', goi_y: 'Không ghi tên thật.' },
    'di_ban.nguoi_ke.the_he': { nhan: 'Thế hệ', goi_y: 'Ví dụ: sinh khoảng 1940.' },
    'di_ban.nguoi_ke.quan_he': { nhan: 'Quan hệ', goi_y: 'Ví dụ: bà nội của người đóng góp.' },
    'di_ban.dong_gop': { nhan: 'Mã đóng góp', goi_y: 'Dạng `sub-2026-0001`, từ bảng tính đóng góp riêng tư.' },
    'di_ban.ghi_chu': { nhan: 'Ghi chú về lời', goi_y: 'Ví dụ: chỗ nghi sai chữ, khác với nguồn khác.' },
    ban_dai_dien: { nhan: 'Bản tham chiếu', goi_y: 'Mã của dị bản hiện đầu trang, thường `v1`.' },
    ly_do_chon: { nhan: 'Vì sao chọn bản này', goi_y: 'Một câu.' },
    nghia_ngan: { nhan: 'Hiểu trong 10 giây', goi_y: 'Một câu, tối đa 40 từ (nên ≤ 25). Hiện ngay dưới lời.' },
    dien_giai: { nhan: 'Nghĩa và các cách hiểu' },
    'dien_giai.goc_nhin': { nhan: 'Góc nhìn', goi_y: 'Ví dụ: Nghĩa phổ thông; Cách hiểu khác.' },
    'dien_giai.noi_dung': { nhan: 'Nội dung', goi_y: 'Viết bằng lời của mình. Cách một dòng trống để xuống đoạn.' },
    'dien_giai.nguon': { nhan: 'Căn cứ' },
    nghia_den: { nhan: 'Nghĩa đen', goi_y: 'Với tục ngữ.' },
    nghia_bong: { nhan: 'Nghĩa bóng', goi_y: 'Với tục ngữ.' },
    linh_vuc: { nhan: 'Lĩnh vực', goi_y: 'Với tục ngữ.' },
    chu_giai: { nhan: 'Chú giải từ khó', goi_y: 'Từ khó với học sinh lớp 7. Chạm vào từ trên trang sẽ hiện nghĩa.' },
    'chu_giai.di_ban': { nhan: 'Trong dị bản', goi_y: 'Mã dị bản chứa từ, thường `v1`.' },
    'chu_giai.cum_tu': { nhan: 'Cụm từ', goi_y: 'Gõ đúng như trong lời. Máy tự tìm vị trí.' },
    'chu_giai.dong': { nhan: 'Dòng số', goi_y: 'Chỉ điền khi máy báo cụm từ xuất hiện ở nhiều dòng.' },
    'chu_giai.lan': { nhan: 'Lần thứ', goi_y: 'Chỉ điền khi cụm từ lặp trong cùng một dòng.' },
    'chu_giai.muc_tu': { nhan: 'Mục từ', goi_y: 'Chọn từ Từ điển. Chưa có thì tạo trong bộ "Từ điển" trước.' },
    'chu_giai.nghia_tai_cho': { nhan: 'Nghĩa riêng trong bài', goi_y: 'Chỉ điền khi trong bài này từ có nghĩa khác nghĩa chung. Tối đa 40 từ.' },
    mo_ta_tinh_huong: { nhan: 'Dùng khi nào', goi_y: 'Câu đời thường, nói với "bạn". Ít nhất hai câu.' },
    cam_xuc: { nhan: 'Cảm xúc', goi_y: 'Tối đa 5.' },
    chu_de: { nhan: 'Chủ đề', goi_y: 'Tối đa 3.' },
    mo_tip: { nhan: 'Mô-típ' },
    lien_quan: { nhan: 'Bài liên quan', goi_y: 'Bạn tự chọn; máy không tự gợi ý.' },
    sgk: { nhan: 'Có trong sách giáo khoa' },
    'sgk.bo_sach': { nhan: 'Bộ sách' },
    'sgk.lop': { nhan: 'Lớp' },
    'sgk.bai': { nhan: 'Bài' },
    canh_bao_noi_dung: { nhan: 'Cảnh báo nội dung', goi_y: 'Bài phản ánh định kiến xưa: giải thích bối cảnh. Bài có cảnh báo không được đưa vào tình huống.' },
    nhap_ai_da_duyet: CHUNG.nhap_ai_da_duyet,
    trang_thai: { nhan: 'Trạng thái', goi_y: '"Công khai" để lên trang. "Ẩn" để gỡ ngay khi phát hiện sai.' },
    am_luat: { nhan: 'Âm luật (nâng cao)', goi_y: 'Chỉ điền khi tự kiểm được.' },
    'am_luat.nhip': { nhan: 'Nhịp' },
    'am_luat.van': { nhan: 'Vần' },
    'am_luat.ghi_chu': { nhan: 'Ghi chú' },
    dien_xuong: { nhan: 'Diễn xướng (nâng cao)', goi_y: 'Không rõ thì để trống, không đoán.' },
    'dien_xuong.hinh_thuc': { nhan: 'Hình thức' },
    'dien_xuong.nguoi_the_hien': { nhan: 'Người thể hiện' },
    'dien_xuong.boi_canh': { nhan: 'Bối cảnh' },
    goc_kiem_chung: { nhan: 'Góc kiểm chứng (nâng cao)', goi_y: 'Với tục ngữ kinh nghiệm (thời tiết, nông nghiệp…).' },
    'goc_kiem_chung.ket_luan': { nhan: 'Kết luận' },
    'goc_kiem_chung.noi_dung': { nhan: 'Nội dung' },
    'goc_kiem_chung.nguon': { nhan: 'Căn cứ' },
    xac_thuc_dan_gian: { nhan: 'Có phải dân gian? (nâng cao)', goi_y: 'Khác "Bình thường" thì phải ghi căn cứ.' },
    can_cu_xac_thuc_dan_gian: { nhan: 'Căn cứ', goi_y: 'Cụ thể: tác giả, lần xuất hiện đầu…' },
    tranh_luan: { nhan: 'Đang có tranh luận (nâng cao)' },
    ghi_chu_tranh_luan: { nhan: 'Tranh luận về điều gì', goi_y: 'Các bên dẫn nguồn nào.' },
    id: { nhan: 'Mã bài', goi_y: 'Tiền tố theo thể loại + 4 chữ số chưa dùng: `cd-` ca dao, `tn-` tục ngữ, `tng-` thành ngữ, `dd-` đồng dao, `ve-` vè, `cdo-` câu đố. Ví dụ: `cd-0004`.' },
    bien_soan: { nhan: 'Biên soạn' },
    'bien_soan.nguoi': { nhan: 'Người biên soạn' },
    'bien_soan.ngay_tao': { nhan: 'Ngày tạo' },
  },
  'muc-tu': {
    tu: { nhan: 'Từ', goi_y: 'Dạng hiển thị.' },
    bien_the: { nhan: 'Cách viết khác' },
    loai: { nhan: 'Loại' },
    vung: { nhan: 'Vùng', goi_y: 'Bắt buộc với phương ngữ.' },
    giai_nghia: { nhan: 'Giải nghĩa', goi_y: 'Hiện trong ô chú giải. Tối đa 40 từ, viết bằng lời của mình.' },
    giai_nghia_day_du: { nhan: 'Giải nghĩa đầy đủ', goi_y: 'Cho trang Từ điển. Không chép nguyên văn từ nguồn chưa cho phép.' },
    xem_them: { nhan: 'Xem thêm' },
    id: { nhan: 'Mã mục từ', goi_y: '`lx-` + từ không dấu nối gạch. Ví dụ: `lx-thai-son`.' },
  },
  'mo-tip': {
    ten: { nhan: 'Tên' },
    mo_ta: { nhan: 'Mô tả' },
    id: { nhan: 'Mã mô-típ', goi_y: '`mt-` + tên không dấu nối gạch.' },
  },
  'nguon': {
    loai: { nhan: 'Loại nguồn' },
    tac_gia: { nhan: 'Tác giả', goi_y: 'Ghi "(chủ biên)" nếu có. Trang web: tên nhóm hoặc ban quản trị.' },
    ten: { nhan: 'Tên sách / trang' },
    nxb: { nhan: 'Nhà xuất bản', goi_y: 'Bắt buộc với sách. Chưa biết thì gõ CAN_BO_SUNG.' },
    nam: { nhan: 'Năm xuất bản' },
    noi_xb: { nhan: 'Nơi xuất bản' },
    tap: { nhan: 'Tập' },
    tai_ban: { nhan: 'Lần in', goi_y: 'Lần in thực sự đang dùng.' },
    isbn: { nhan: 'ISBN' },
    url: { nhan: 'Địa chỉ', goi_y: 'Trang web: trang chủ. Bản quét: link tới bản quét.' },
    ban_quyen: { nhan: 'Bản quyền', goi_y: 'Chưa rõ thì chọn "Chưa rõ" (xử lý như có bản quyền).' },
    da_xac_minh_ban_in: { nhan: 'Đã đối chiếu thông tin trên bản in', goi_y: 'Chỉ đánh dấu khi chính bạn đã xem bản in hoặc bản quét. Trang web: luôn để trống.' },
    id: { nhan: 'Mã nguồn', goi_y: '`src-` + tên ngắn không dấu nối gạch.' },
  },
  'tinh-huong': {
    nhom: { nhan: 'Nhóm' },
    tieu_de: { nhan: 'Tiêu đề', goi_y: 'Ví dụ: Khi nhớ mẹ.' },
    mo_dau: { nhan: 'Mở đầu', goi_y: '50–100 từ, giọng gần gũi.' },
    tac_pham: { nhan: 'Bài trong tình huống', goi_y: 'Đây là nơi duy nhất ghi bài nào thuộc tình huống nào. Trang chỉ hiện khi có từ 2 bài công khai.' },
    'tac_pham.ref': { nhan: 'Bài' },
    'tac_pham.hop_khi': { nhan: 'Hợp khi…', goi_y: 'Ví dụ: Hợp khi bạn muốn nói lời cảm ơn cha mẹ.' },
    tu_khoa: { nhan: 'Từ khóa tìm kiếm', goi_y: 'Từ đồng nghĩa người đọc hay gõ.' },
    id: { nhan: 'Mã tình huống', goi_y: '`sit-` + tên không dấu nối gạch.' },
  },
  'ghi-am': {
    tac_pham: { nhan: 'Tác phẩm' },
    di_ban: { nhan: 'Dị bản', goi_y: 'Mã dị bản trong tác phẩm, ví dụ `v2`.' },
    loai: { nhan: 'Loại ghi âm' },
    nguoi_the_hien: { nhan: 'Người thể hiện' },
    'nguoi_the_hien.kieu_hien_thi': { nhan: 'Cách hiển thị tên' },
    'nguoi_the_hien.ten_hien_thi': { nhan: 'Tên hiển thị', goi_y: 'Bắt buộc nếu không ẩn danh.' },
    'nguoi_the_hien.the_he': { nhan: 'Thế hệ' },
    'nguoi_the_hien.vung': { nhan: 'Vùng' },
    'nguoi_the_hien.dia_danh': { nhan: 'Địa danh' },
    nguoi_ghi: { nhan: 'Người ghi âm' },
    ngay_ghi: { nhan: 'Ngày ghi' },
    dia_diem: { nhan: 'Địa điểm' },
    tep: { nhan: 'Tệp âm thanh' },
    'tep.url_phat': { nhan: 'Địa chỉ để phát' },
    'tep.url_luu_tru': { nhan: 'Địa chỉ lưu trữ' },
    'tep.thoi_luong': { nhan: 'Thời lượng (giây)' },
    giay_phep: { nhan: 'Giấy phép' },
    ma_dong_y: { nhan: 'Mã hồ sơ đồng ý', goi_y: 'Mã trong kho riêng tư. Không có hồ sơ đồng ý thì không thêm ghi âm.' },
    ban_chep: { nhan: 'Bản chép lời', goi_y: 'Chép đúng như nghe.' },
    id: { nhan: 'Mã ghi âm', goi_y: '`au-` + 4 chữ số.' },
  },
  'nguoi-dong-gop': {
    ten_hien_thi: { nhan: 'Tên hiển thị' },
    kieu_hien_thi: { nhan: 'Cách hiển thị tên' },
    vai_tro: { nhan: 'Vai trò' },
    gioi_thieu: { nhan: 'Giới thiệu', goi_y: 'Không ghi thông tin liên lạc.' },
    id: { nhan: 'Mã', goi_y: '`ctb-` + 4 chữ số.' },
  },
};

// Thứ tự ô cấp trên cùng. Tác phẩm theo docs/21 mục 12: Lời → Nguồn → Nghĩa → Chú giải → Tình huống → Nâng cao
// (nguồn nằm trong từng dị bản, ngay dưới lời). Ô không có trong danh sách: bộ sinh báo lỗi.
export const THU_TU = {
  'tac-pham': [
    'tieu_de', 'the_loai', 'the_tho',
    'di_ban', 'ban_dai_dien', 'ly_do_chon',
    'nghia_ngan', 'dien_giai', 'nghia_den', 'nghia_bong',
    'chu_giai',
    'mo_ta_tinh_huong', 'chu_de', 'cam_xuc', 'linh_vuc', 'mo_tip', 'lien_quan', 'sgk',
    'canh_bao_noi_dung', 'nhap_ai_da_duyet', 'trang_thai',
    'am_luat', 'dien_xuong', 'goc_kiem_chung', 'xac_thuc_dan_gian', 'can_cu_xac_thuc_dan_gian', 'tranh_luan', 'ghi_chu_tranh_luan',
    'id', 'slug', 'bien_soan',
  ],
  'muc-tu': ['tu', 'bien_the', 'loai', 'vung', 'giai_nghia', 'giai_nghia_day_du', 'nguon', 'xem_them', 'nhap_ai_da_duyet', 'id'],
  'mo-tip': ['ten', 'mo_ta', 'nguon', 'nhap_ai_da_duyet', 'id'],
  'nguon': ['ten', 'loai', 'tac_gia', 'nxb', 'nam', 'noi_xb', 'tap', 'tai_ban', 'isbn', 'url', 'ban_quyen', 'da_xac_minh_ban_in', 'ghi_chu', 'id'],
  'tinh-huong': ['tieu_de', 'nhom', 'mo_dau', 'tac_pham', 'tu_khoa', 'nhap_ai_da_duyet', 'id', 'slug'],
  'ghi-am': ['tac_pham', 'di_ban', 'loai', 'nguoi_the_hien', 'nguoi_ghi', 'ngay_ghi', 'dia_diem', 'tep', 'giay_phep', 'ma_dong_y', 'ban_chep', 'id'],
  'nguoi-dong-gop': ['ten_hien_thi', 'kieu_hien_thi', 'vai_tro', 'gioi_thieu', 'id'],
};

/** Đối tượng thu gọn mặc định (phần nâng cao). */
export const THU_GON = new Set(['tac-pham:am_luat', 'tac-pham:dien_xuong', 'tac-pham:goc_kiem_chung', 'tac-pham:di_ban.nguoi_ke']);

/**
 * Giá trị điền sẵn. LƯU Ý: Sveltia áp giá trị mặc định cả cho bài ĐÃ CÓ mà thiếu ô đó (mở rồi lưu là ghi vào).
 * Vì vậy không đặt mặc định cho thông tin nguồn gốc (ngày truy cập, số trang, url…) ở các bộ sửa bài cũ —
 * CMS không được tự "điền cho đủ" nguồn gốc. Mặc định ở đây chỉ là giá trị trung tính (chưa duyệt, nháp, chưa rõ vùng).
 * `{{now}}` = hôm nay (ô ngày).
 */
export const MAC_DINH = {
  'tac-pham': {
    the_loai: 'ca_dao', ban_dai_dien: 'v1', trang_thai: 'nhap', nhap_ai_da_duyet: false, tranh_luan: false,
    xac_thuc_dan_gian: 'binh_thuong', 'di_ban.vung': ['khong_ro'], 'di_ban.nguon.ref': 'src-ca-dao-me',
    'bien_soan.nguoi': 'ctb-0001', 'bien_soan.ngay_tao': '{{now}}',
  },
  'muc-tu': { nhap_ai_da_duyet: false },
  'mo-tip': { nhap_ai_da_duyet: false },
  'nguon': { da_xac_minh_ban_in: false, ban_quyen: 'chua_ro' },
  'tinh-huong': { nhap_ai_da_duyet: false },
  'ghi-am': { giay_phep: 'cc_by_4_0' },
  'nguoi-dong-gop': {},
};

/**
 * Bộ "Thêm nhanh" (docs/11 mục 1): tiêu đề, lời, vùng, nguồn — lưu thành bài nháp trong content/tac-pham/.
 * Ô ẩn điền sẵn phần bắt buộc còn lại. Bộ này không liệt kê bài nào (mở lại bài trong bộ "Tác phẩm").
 */
export const THEM_NHANH = {
  name: 'them-nhanh',
  nhan: 'Thêm nhanh (bài nháp)',
  mot: 'bài nháp',
  mo_ta: 'Lưu nhanh một bài vừa nghe hoặc vừa đọc: tiêu đề, lời, vùng, nguồn. Bài lưu ở trạng thái Nháp, chưa lên trang; làm giàu sau trong bộ "Tác phẩm".',
  thu_tu: ['tieu_de', 'the_loai', 'di_ban', 'id', 'slug', 'bien_soan'],
  // Ô con của dị bản trong bộ Thêm nhanh
  di_ban: ['loi', 'vung', 'nguon', 'nguoi_ke', 'ghi_chu'],
  nguon: ['ref', 'url', 'ngay_truy_cap', 'trang'],
  an: { ban_dai_dien: 'v1', trang_thai: 'nhap', 'di_ban.id': 'v1' },
  // Chỉ bộ này (luôn là bài mới) mới điền sẵn ngày truy cập = hôm nay
  mac_dinh: { 'di_ban.nguon.ngay_truy_cap': '{{now}}' },
};
