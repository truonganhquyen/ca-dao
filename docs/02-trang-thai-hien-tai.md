# 02 · Trạng thái hiện tại

| | |
|---|---|
| Phiên bản | 2.3 · 26/10/2026 |
| Mục đích | Trang tổng quan ngắn cho người sáng lập và AI agent |
| Nguồn máy đọc | `du-an/trang-thai.json` (`npm run trang-thai`) |

> Tệp này mô tả **thực tế hiện tại**, không phải kế hoạch tương lai. Sau mỗi task đã gộp, agent cập nhật tệp này cùng `du-an/trang-thai.json` và trạng thái trong `docs/22-backlog-trien-khai.md`. `npm run kiem-nhat-quan` báo lỗi khi các nguồn này lệch nhau.

## Giai đoạn hiện tại

**Đang chuyển sang WordPress** (D-056, `docs/24-wordpress.md`): WordPress, plugin, theme chạy trên VPS tại **phongdao.com** (D-070; T-075 xong 25/10/2026; sao lưu chưa bật — điều kiện mở duy nhất còn lại ngoài quyết định mở, D-073, D-077), chưa mở cho Google; máy chủ tự cập nhật sau mỗi PR gộp (T-078). **Gói nâng cấp đã duyệt 25/10/2026** (D-059..D-065): 51 mục (gồm 4 ý chống trùng), kế hoạch 5 đợt ở `docs/25-nang-cap-giao-dien-noi-dung.md`, task T-080..T-111. Từ D-054 không còn chặn đăng vì lý do biên tập, nguồn không bắt buộc; từ D-055 chép chú thích có ghi công được phép. **Kế hoạch tới Mốc C sau báo cáo review 26/10/2026** (D-072, D-073): dòng "lời và mức kiểm" dưới lời, điều hướng và danh sách bài, SEO cơ bản, trợ giúp viết Hiểu nhanh. **Từ D-077 (26/10/2026):** việc biên tập của người sáng lập (thư cho Ca dao Mẹ, 5 người thử, 50 Hiểu nhanh, số bài, bản quét sách) nên làm nhưng không chặn mở; mở lại mô-típ, câu một dòng, đố vui, bối cảnh vè, giọng sinh sẵn. Tục ngữ và thành ngữ gộp thành một mục "Tục ngữ – Thành ngữ" (D-079).

**Mốc B · Thử nhỏ** — hướng v2 "Trung thực hơn hoàn hảo" (D-039). Mốc A xong 25/10/2026: WordPress chạy trên VPS, người sáng lập đăng bài từ điện thoại, nguồn tự động đổ bài Chờ duyệt mỗi ngày, trang bài có lời, dị bản, chú thích, trích dẫn, chống trùng; nội dung cũ đã chuyển, site Astro ngừng (T-076). Đang làm tiếp các tính năng của gói nâng cấp (D-077). Điều kiện xong Mốc B ở `docs/22` mục 2. Mốc tiếp theo: **C · Mở lặng lẽ**.

## Đã có

- Gói quyết định D-035..D-049 trong `docs/01`.
- Tài liệu v2: `AGENTS.md`, `docs/00`–`03`, `10`–`12`, `20`–`23`; tài liệu v1.2 không còn dùng ở `docs/luu-tru/`.
- Validator hai tầng (LỖI/NHẮC), quy tắc R01–R22; nhãn bằng chứng do máy tính (`scripts/lib/suy-ra.mjs`); 26 bộ fixture.
- Nguồn đầu mối `content/nguon/src-ca-dao-me.yaml`; 3 hồ sơ khung ở trạng thái `nhap` (chưa có nguồn gốc, không công khai được).
- **Site Astro cũ (T-035, T-040) — đã ngừng (T-076, 25/10/2026):** địa chỉ `https://ca-dao.truonganhquyen.workers.dev` chuyển hướng 301 sang phongdao.com; 2 bài công khai đã chuyển vào WordPress. Mã Astro đã gỡ khỏi repo (T-117).
- **Plugin WordPress (T-071):** loại bài Ca dao – Tục ngữ (lời không ghi nguồn; "Nguồn: …" chỉ dưới chú thích chép từ nguồn — D-057), trang **Nguồn tự động**, **Kho thu thập**, tự tạo bài **Chờ duyệt**, ô Chờ duyệt trên Bảng tin (`wordpress/`, `docs/24`). Bộ đọc Ca dao Mẹ đầy đủ (T-073): ca dao, thành ngữ–tục ngữ, đồng dao, vè, câu đố, kèm dị bản, chú thích, đáp án; bài chưa sửa tay tự được bổ sung.
- **Trích dẫn, chia sẻ, lượt đọc (T-042, T-081, 25/10/2026):** hộp Trích dẫn (thông dụng, APA 7; "Sao chép kèm nguồn" / "Chỉ sao chép lời"), nút Chia sẻ; lượt đọc tự đếm không cookie, không lưu IP, "Đọc nhiều tuần này" ở trang chủ, ô lượt đọc 7 ngày trên Bảng tin.
- **Số liệu của bài (T-115, 26/10/2026):** dưới tiêu đề mỗi bài có 👁 lượt đọc · ↗ lượt chia sẻ · ❝ lượt trích dẫn khi đủ ngưỡng (đọc từ 10, chia sẻ và trích dẫn từ 3); danh sách bài có 👁.
- **Chính sách thu thập dữ liệu (D-068, T-114, 26/10/2026):** chú thích, đáp án chép từ cadao.me không còn dòng "Nguồn" dưới từng mục; các trang đã thu thập được ghi nhận ở `/chinh-sach-du-lieu/` (đã đăng, liên kết ở chân trang). Ghi công ảnh Commons, Wikipedia vẫn tại chỗ. **Nguồn đã ẩn (D-078):** cuối bài người đọc thấy dòng nghiêng nhỏ "[N nguồn tham khảo đã ẩn]"; người biên soạn đăng nhập thấy thêm liên kết về từng trang gốc.
- **Sửa mục từ (T-113, 26/10/2026):** trang sửa mục từ có ô sửa nguồn của lời giải và đổi ảnh (chọn từ Thư viện ảnh, ghi công) hoặc ẩn ảnh lấy từ Wikidata.
- **Bàn biên tập (T-047, 25/10/2026):** một trang (mục đầu của menu Ca dao – Tục ngữ) và ô "việc chờ bạn" trên Bảng tin gom mọi việc đang chờ người sáng lập, có số đếm và nút mở đúng chỗ.
- **Lời vào khối Thơ (T-112, 25/10/2026):** bài dán lời vào khối Đoạn văn được máy nhận ra; trình soạn có nút đưa lời vào khối Thơ, bài cũ có trang riêng để chuyển (không đổi chữ, hoàn tác được).
- **Đồng dao, bản đồ ca dao (T-103, T-104, 25/10/2026):** bài đồng dao có khối Cách chơi của trò chơi (người sáng lập viết, hoặc bấm nút chép từ Wikipedia kèm nguồn) và chế độ "Đọc cùng bé" (một dòng, chữ rất to, vỗ tay theo chữ, không âm thanh); trang Bản đồ ca dao (`/ban-do/`) chấm các địa danh đã xác nhận, chạm ra bài. Xong phần máy làm được của đợt 5 trong `docs/25` mục 6.
- **Đã lưu, đo lường (T-051, T-052, 25/10/2026):** người đọc bấm ☆ Lưu để giữ bài trên máy mình (trang Đã lưu, site không biết ai lưu gì); site đếm ba việc để biết nên làm gì tiếp — câu tìm không ra, sao chép trích dẫn, từ được chạm xem nghĩa — không cookie, không IP. Ô "người đọc cần gì" trên Bảng tin.
- **Gộp hai bài đã đăng bị trùng (T-101, 25/10/2026):** trong trang Có thể trùng, cặp hai bài đã đăng có nút chọn bài giữ lại; bài kia thành dị bản, đường dẫn cũ tự chuyển về bài giữ lại, lượt đọc, cảm nghĩ, bài phân tích dồn sang, bài kia vào thùng rác (khôi phục được). Mỗi đêm máy so lại các bài đã đăng.
- **Người đọc góp phần (T-045, T-097, 25/10/2026):** cuối mỗi bài có Góp ý (ở quê nói khác, góp ý chú thích, báo lỗi, gửi số trang), Cảm nghĩ, và "Viết cảm nhận" (bài phân tích); tất cả vào Chờ duyệt, không bắt email hay tên thật, không cookie; chống rác bằng ô bẫy và giới hạn mỗi giờ (Turnstile khi có khóa). Xong đợt 4 của `docs/25` mục 6, trừ các việc chờ người sáng lập.
- **Trang chủ, Bạn có biết?, trang thể loại, tình huống (T-043, T-093, T-094, 25/10/2026):** trang chủ mới (Câu hôm nay, Bạn có biết?, Hôm nay bạn đang…, Đọc nhiều, Vừa bổ sung, theo thể loại, vùng); "Bạn có biết?" máy ghép từ chú thích có sẵn, người sáng lập duyệt; trang thể loại có giới thiệu do người sáng lập viết và bài tiêu biểu; trang tình huống khi đủ 2 bài.
- **Tìm kiếm (T-041, 25/10/2026):** trang Tìm (`/tim/`): kết quả hiện khi gõ, gõ không dấu vẫn ra, tìm cả trong chú thích và từ điển; chỉ mục tự làm trong WordPress (D-067), không phải cài thêm gì.
- **Vùng, chủ đề (T-089, T-091, 25/10/2026):** máy gợi ý vùng (từ chú thích nhắc miền, tỉnh và địa danh đã xác nhận) và chủ đề của dự án (qua bảng ánh xạ chủ đề của nguồn, lời ru, hò, lý, nhóm kinh nghiệm của tục ngữ); người sáng lập duyệt hàng loạt. Xong đợt 3 của `docs/25` mục 6.
- **Từ điển và địa danh (T-044, T-086..T-088, 25/10/2026):** trang Từ điển (`/tu-dien/`) gom chú thích thành mục từ, mỗi mục liệt kê các bài có từ đó, lọc theo vùng, từ cổ, Hán Việt; máy tìm thực thể Wikidata cho từ được chú thích và đáp án câu đố, người sáng lập xác nhận; thực thể đã xác nhận có ảnh Commons (ghi tác giả, giấy phép) và bản đồ nhỏ (có Hoàng Sa, Trường Sa).
- **Đọc dễ hơn, phần 2 (T-085, T-090, 25/10/2026):** cỡ chữ, sáng / tối, trình chiếu chữ lớn cho lớp học, nghe bằng giọng có sẵn của máy; bài dài chia đoạn, có mục lục, số dòng, "Đọc toàn bài", nhớ chỗ đang đọc. Xong đợt 2 của `docs/25` mục 6.
- **Đọc dễ hơn, phần 1 (T-082..T-084, 25/10/2026):** chạm vào từ có chú thích để xem nghĩa ngay trong lời; dị bản đánh số, tô chữ khác lời chính; máy nhận thể thơ (lục bát, song thất lục bát, câu 3–5 chữ, một dòng, tự do), lọc danh sách theo thể.
- **Chống trùng (T-109..T-111, 25/10/2026):** máy so cả lời của câu mới lấy từ nguồn và của bài viết tay với các bài, câu đã có: giống hệt, cùng thể loại thì tự gắn vào bài có sẵn; trùng đúng một dị bản đã ghi thì tự gắn làm dị bản (D-074, 26/10/2026) (Kho thu thập → Đã gộp, "Tách ra" được); còn lại vào trang **Có thể trùng** chờ người sáng lập quyết; trình soạn có ô "Có thể trùng" (chỉ báo, không chặn đăng).
- **Theme WordPress (T-072):** theme con của Twenty Twenty-Five theo `docs/20`: màu, phông Literata / Be Vietnam Pro tự lưu trữ, sáng/tối theo máy, trang bài (lời lớn, lục bát thụt lề câu lục, Hiểu nhanh dưới lời, dị bản, chú thích); quét trợ năng 0 lỗi.
- **Trang tác phẩm (T-040):** đọc theo lớp, nhãn tự tính, nguồn gốc ở lớp 1, chú giải chạm-là-hiện (tắt JavaScript vẫn đọc được), sáng/tối, bản in, thanh dưới; quét trợ năng tự động. Chưa có bài `cong_khai` thật: bản xem trước có 3 trang mẫu giao diện dữ liệu giả (D-050).
- **Trợ giúp viết Hiểu nhanh (T-100, 26/10/2026):** trang **Viết Hiểu nhanh** (bài chưa có, đọc nhiều trước, đếm X/50); trong trình soạn có hộp hiện chú thích của bài, nháp máy (nếu có, ghi rõ "chưa duyệt") với nút "Dùng nháp này", và ô "Phần giải nghĩa có hỗ trợ của AI" do bạn tự đánh dấu. Agent soạn nháp theo lô khi bạn nhờ, nạp lên sau khi bạn đồng ý (`docs/huong-dan/viet-hieu-nhanh.md`).
- **SEO cơ bản, ảnh chia sẻ (T-050, 26/10/2026):** mỗi bài có ảnh 1200×630 (thể loại, 4 dòng đầu, dòng mức kiểm, phongdao.com) hiện khi dán link vào Zalo, Facebook; mô tả cho Google, dữ liệu có cấu trúc; sitemap chỉ gồm trang được lập chỉ mục (bài có Hiểu nhanh hoặc bài phân tích đã duyệt); trang mỏng, danh sách đã lọc không lập chỉ mục. Site vẫn chặn Google tới khi bạn quyết định mở.
- **Điều hướng, danh sách, cuối bài (T-120, T-121, 26/10/2026):** menu "Tất cả · Thể loại ▾ · Từ điển · Tìm" (thể loại chưa có bài tự ẩn); điện thoại có thanh dưới Trang chủ · Tìm · Tất cả · Đã lưu. Trang Tất cả xếp A–Z (hoặc Mới thêm), lọc bằng chip theo thể loại, thể thơ, vùng, chủ đề, mỗi bài có 2 dòng đầu. Cuối bài: Chủ đề, Vùng ngay sau chú thích, rồi một khối "Bạn muốn góp gì?" thay cho ba form rời. Tìm ghi tổng số bài. Bàn biên tập chia "Làm trước" và "Khi rảnh".
- **Bốn việc trước khi mở (T-124..T-127, D-076, 26/10/2026):** **cửa quản trị** khoá chặt hơn — XML-RPC tắt hẳn, không lộ tên đăng nhập, một câu báo chung khi đăng nhập sai, sai 5 lần trong 15 phút thì tạm khoá (không thu IP), không ai chèn được chữ vào trang bài qua địa chỉ; **tục ngữ, thành ngữ** vào hàng Chờ duyệt (máy lấy xoay vòng theo thể loại, câu bạn đã chọn loại đi trước; Bàn biên tập có dòng "Kho đang có"); **danh sách và tìm**: nhóm lọc Vùng, Chủ đề có mũi tên, 30 bài mỗi trang, số trang to, trang "Không tìm thấy" riêng, kết quả tìm hiện tên bài và dòng khớp, bài chỉ có chữ rải rác xếp cuối ở nhóm riêng; **canonical** cho trang chủ, danh sách.
- **Một mục "Tục ngữ – Thành ngữ" (T-128, D-079, 26/10/2026):** không tách tục ngữ với thành ngữ nữa — menu, trang thể loại, đường dẫn `/tuc-ngu/` chung; bài "Thành ngữ" cũ (nếu có) chuyển sang, đường dẫn cũ tự chuyển; không còn trang "Tách thành ngữ – tục ngữ" phải duyệt.
- **"Đọc tiếp" cuối bài (T-122, D-075, 26/10/2026):** sau Chủ đề, Vùng có 3–5 bài nên đọc tiếp — cùng chủ đề, cùng vùng (đã duyệt), thiếu thì cùng thể loại; mỗi bài có tên, hai dòng đầu và nhãn vì sao. Duyệt chủ đề, vùng càng nhiều thì gợi ý càng sát. Mô-típ, trang mô-típ vẫn tạm dừng.
- **Dòng "lời và mức kiểm" (T-119, D-072, 26/10/2026):** ngay dưới lời mỗi bài: "Lời theo bản lưu truyền phổ biến · chưa đối chiếu sách", hoặc "Lời đã đối chiếu với <sách>, tr. N" khi người biên soạn tự điền hộp **Lời đã đối chiếu với** trong trang sửa bài (máy không bao giờ tự điền). Kèm "Vì sao?" tới trang Phương pháp (khi đã đăng); có trong kết quả Tìm và trích dẫn.
- **An toàn vận hành (T-118, D-073, 26/10/2026):** máy chủ chặn cùng một máy gửi lặp lượt đọc, chia sẻ, trích dẫn, sự kiện đo lường (không lưu IP, chỉ dấu băm tạm một ngày) nên "Đọc nhiều tuần này" và số dưới tiêu đề không bị thổi phồng; chỉ tin IP do Cloudflare báo khi yêu cầu thật sự đi qua Cloudflare; không còn lộ phiên bản WordPress; máy chủ ghi các bản đã lên site, có hướng dẫn quay lại bản trước (`docs/huong-dan/quay-lai-ban-truoc.md`).
- **Gỡ site Astro và CMS cũ (T-117, D-071, 26/10/2026):** đã xóa khỏi repo phần Astro, CMS Sveltia (`/admin`) và các công cụ chỉ phục vụ nó; luật và tài liệu viết lại theo WordPress.
- GitHub (24/10/2026): repo công khai `truonganhquyen/ca-dao` (`main` = hướng v2; PR #1–#3 đã gộp) và repo riêng tư `truonganhquyen/ca-dao-kho-tho` (mã thu thập T-038; dữ liệu đổ dần bằng GitHub Actions hằng ngày sau khi PR của repo đó gộp). Commit dùng email ẩn của GitHub.

## Đang chờ người sáng lập

Mở **Ca dao – Tục ngữ → Bàn biên tập** (hoặc ô "việc chờ bạn" trên Bảng tin): mọi hàng chờ dưới đây có số đếm và nút mở đúng trang; phần **"Làm trước"** là đủ cho mỗi tuần, phần "Khi rảnh" không gấp.

- **An toàn tài khoản quản trị — làm ngay** (báo cáo khảo sát E2E 26/10/2026): đổi mật khẩu quản trị (mật khẩu dài, lưu trong trình quản lý mật khẩu — mật khẩu cũ đã đi qua kênh chữ thường); bật xác thực hai lớp; Hồ sơ → "Tên hiển thị công khai" chọn tên khác tên đăng nhập; FlashPanel → PHP bật OPcache; xóa bài mẫu "Hello world!" và bình luận mẫu. Nếu đăng nhập sai 5 lần, site tạm khoá tài khoản 15 phút — đợi, hoặc bấm "Quên mật khẩu?".
- **Sao lưu FlashPanel — làm ngay** (điều kiện mở, D-073): FlashPanel → *Sao Lưu*, chọn hằng ngày, gồm site và cơ sở dữ liệu cadao, lưu ra ngoài VPS (Google Drive…); rồi thử khôi phục một lần. Quay lại bản trước chỉ trả lại mã, không trả lại dữ liệu (`docs/huong-dan/quay-lai-ban-truoc.md`).
- **Gửi thư cho Ca dao Mẹ (T-034) — nên gửi tuần này, không chặn mở (D-077):** bản nháp ở `docs/12` mục 7. Từ khi bỏ dòng Nguồn dưới chú thích chép (D-068), thư càng cần trước khi mở cho Google.
- **Viết Hiểu nhanh** (mục tiêu ≥ 50 bài, khoảng 10 bài mỗi tuần — không chặn mở từ D-077, nhưng bài chưa có Hiểu nhanh thì Google chưa lập chỉ mục): Ca dao – Tục ngữ → **Viết Hiểu nhanh**; ưu tiên câu trong SGK Ngữ văn và câu theo dịp (Tết, Vu Lan, 20/11). Muốn nhanh hơn: nhờ agent soạn nháp cho 10 bài, bạn chỉ đọc, sửa — `docs/huong-dan/viet-hieu-nhanh.md`. Bài chưa có Hiểu nhanh thì Google không lập chỉ mục.
- **Chuyển hướng cadao.sdigi.net** (nếu chưa làm, D-070): Cloudflare → thêm bản ghi cho cadao.sdigi.net và quy tắc chuyển hướng 301 sang `https://phongdao.com/$1`.
- **Ba trang Phương pháp, Nguồn, Về dự án** (Trang → Bản nháp): máy đã soạn theo đúng cách site đang làm (Phương pháp có thêm mục "Dòng nhỏ dưới lời nói gì" — liên kết "Vì sao?" dưới lời chỉ hiện khi trang này đã đăng); bạn đọc, sửa câu chữ rồi bấm Đăng — chân trang tự có liên kết.
- **Đọc lại trang Chính sách thu thập dữ liệu** (Trang → Chính sách thu thập dữ liệu): máy đã soạn và đăng (vì đây là nơi ghi nhận nguồn); sửa câu chữ nếu muốn.
- **Chuyển 16 bài vè vừa nhập** (Ca dao – Tục ngữ → Lời chưa vào khối Thơ): lời đang nằm trong khối Đoạn văn nên trang thiếu nút Trích dẫn, Lưu, Trình chiếu và tìm theo lời chưa ra; xem đề xuất rồi bấm "Chuyển các bài đã chọn". Về sau khi dán lời, nếu ô bên cạnh báo "Lời đang nằm trong khối Đoạn văn" thì bấm "Đưa lời vào khối Thơ".
- **Trò chơi cho đồng dao** (Ca dao – Tục ngữ → Trò chơi): thêm trò chơi (ví dụ Rồng rắn lên mây), bấm vào tên rồi bấm "Lấy mục Cách chơi từ Wikipedia" (hoặc tự viết), điền số người, độ tuổi, đồ dùng; trong trình soạn bài đồng dao, chọn trò chơi ở ô "Trò chơi".
- **Đọc số liệu mỗi tuần** (Bảng tin → ô "người đọc cần gì"; khoảng 5 phút): câu tìm không ra → thêm bài; bài được sao chép nhiều → kiểm lại; từ được chạm nhiều → viết nghĩa kỹ hơn — `docs/huong-dan/doc-so-lieu.md`.
- **Duyệt đóng góp của người đọc:** Ca dao – Tục ngữ → Góp ý của người đọc; Cảm nhận – phân tích (bài Chờ duyệt → Đăng); Bình luận (cảm nghĩ chờ duyệt → Chấp thuận).
- **Khóa Turnstile** (tùy chọn, chặn tin rác chắc hơn): Cloudflare → Turnstile → Add widget cho phongdao.com, dán hai khóa vào Ca dao – Tục ngữ → Chống rác. Không gửi khóa cho ai, kể cả agent.
- **Bạn có biết?** (Ca dao – Tục ngữ → Bạn có biết?): máy ghép câu từ chú thích, bạn sửa cho tự nhiên rồi chọn "Duyệt"; trang chủ hiện 4 câu đã duyệt mỗi tuần.
- **Giới thiệu thể loại** (Ca dao – Tục ngữ → Thể loại → Sửa từng thể loại, ô "Mô tả"): vài dòng giới thiệu, cách đọc — hiện ở đầu trang thể loại.
- **Tình huống** (khi rảnh): trong trình soạn, gắn bài vào tình huống (ô "Tình huống") và viết một câu "Hợp khi…"; tình huống có từ 2 bài thì có trang và thẻ trên trang chủ.
- **Gợi ý vùng, chủ đề** (Ca dao – Tục ngữ → Gợi ý vùng, chủ đề): trước hết xem **bảng ánh xạ** ở đầu trang (chủ đề của Ca dao Mẹ → chủ đề của dự án; máy đã đề xuất), sửa nếu cần rồi bấm "Lưu"; sau đó duyệt gợi ý từng trang (bỏ dấu cái sai, bấm "Đồng ý").
- **Xác nhận Wikidata** (Ca dao – Tục ngữ → Xác nhận Wikidata; máy thêm dần): mỗi mục bấm Đúng / Sai / Bỏ qua. Chỉ mục "Đúng" mới hiện ảnh, bản đồ trên trang.
- **Quyết các cặp "Có thể trùng"** (menu Ca dao – Tục ngữ → Có thể trùng; Bảng tin báo số cặp): câu trùng đúng một dị bản đã ghi máy đã tự gắn (xem Kho thu thập → Đã gộp, bấm "Tách ra" nếu sai); mỗi cặp còn lại chọn "Gộp: là cùng một bài" / "Gộp làm dị bản" / "Là bài khác" / "Để sau"; cặp hai bài đã đăng thì chọn "Gộp: giữ …" bài muốn giữ lại. Câu đang chờ thì máy chưa tạo thành bài.
- **Việc của người sáng lập trong gói nâng cấp** (`docs/25` mục 6): duyệt hàng loạt thể loại tách, xác nhận Wikidata, duyệt bộ chủ đề và vùng; tạo khóa Turnstile khi tới đợt đó; nghe thử 30 bài giọng đọc; tìm *Tục ngữ phong dao* (T-107).
- **Thử biên soạn trên WordPress** (Ca dao – Tục ngữ → Thêm mới, trên điện thoại): tạo một bài thử, dán lời, bấm Lưu nháp rồi xem trước; xóa bài thử sau khi thử.
- **T-034** · Gửi thư cho Ca dao Mẹ — nên gửi trước khi mở công khai, không chặn (D-077).

## Công việc kỹ thuật tiếp theo

Chạy `npm run viec-tiep-theo` — máy suy ra từ backlog (`docs/22`), luôn đúng. Mục này **không liệt kê task bằng tay** để các PR không sửa chung một dòng (xem `docs/03` mục 7).

## Quyết định đang mở

Q-006 (trình bày lục bát), Q-008 (tài trợ). Q-005 (tên miền) đã chốt: phongdao.com (D-070). Q-007 (đồng dao) đã chốt: làm ngay (D-059). Không câu nào chặn task kỹ thuật hiện tại.

## Lệnh điều hành

```bash
npm run trang-thai
npm run viec-tiep-theo
npm run kiem-tra
```
