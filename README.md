# Kho tàng Ca dao Tục ngữ Việt Nam — Bộ tài liệu triển khai v2

**Đọc một câu. Hiểu một đời sống.**
**Mỗi câu ghi rõ lấy từ đâu, và đã được kiểm đến mức nào.**

Kho mã này chứa định hướng sản phẩm, đặc tả dữ liệu, hướng dẫn biên soạn, đặc tả giao diện và kỹ thuật, backlog theo mốc, cùng **công cụ kiểm tra dữ liệu đã chạy được**. Nó được viết cho cả người sáng lập, dev là người và AI agent.

Ngày: 23/10/2026 (v2 — hướng "Trung thực hơn hoàn hảo", D-035..D-047). Bản v1.2 (06/10/2026) giữ trong lịch sử Git và `docs/luu-tru/`.

---

## Hướng v2 trong 6 dòng

1. **Xuất bản sớm, nhãn trung thực, cải tiến dần** — không còn cổng đếm hồ sơ; 3 mốc trải nghiệm A/B/C, mở công khai lặng lẽ trước Tết Đinh Mùi 2027.
2. **Nội dung MVP thu thập có chọn lọc từ internet** — nguồn đầu mối chính là Ca dao Mẹ (cadao.me); lấy toàn bộ lời vào kho thô riêng tư, xuất bản chọn lọc 100–150 bài, ghi công từng bài.
3. **Nhãn bằng chứng do máy tính** từ nguồn: Đã đối chiếu tư liệu · Ghi từ người kể · Cộng đồng gửi · Lời phổ biến – chưa đối chiếu.
4. **Ba lằn ranh máy chặn:** AI không viết lời/nguồn; nhãn luôn khớp dữ liệu; dữ liệu cá nhân phải có đồng ý.
5. **Người sáng lập không dùng Git/YAML** — biên soạn bằng CMS (Sveltia) trên điện thoại, duyệt thay đổi bằng bản xem trước.
6. **UX cho điện thoại và giáo viên:** thanh điều hướng dưới, nguồn gốc ở lớp 1, trích dẫn một chạm, chia sẻ Zalo, trình chiếu, bản in.

## Lệnh

```bash
npm install            # Node.js ≥ 20
npm run kiem-tra       # cổng kiểm tra bắt buộc: dữ liệu + fixture + nhất quán trạng thái
npm run check:data     # kiểm dữ liệu R01–R22 (LỖI chặn build, NHẮC không chặn)
npm run test:fixtures  # 26 bộ dữ liệu mẫu: 5 hợp lệ, 21 lỗi phải bị bắt đúng mã
npm run health         # báo cáo sức khỏe dữ liệu (nguồn dữ liệu của Bàn biên tập)
npm run trang-thai     # giai đoạn, mốc, số bài, câu hỏi mở
npm run viec-tiep-theo # đề xuất task kỹ thuật READY tiếp theo
```

## Đọc theo vai trò

| Ai | Đọc |
|---|---|
| **Người sáng lập** | `docs/00` (tài liệu nền) → `docs/02` (đang ở đâu) → `docs/11` (biên soạn trong CMS) |
| **Codex Cloud** (implementer) | `AGENTS.md` → `docs/02` → `docs/03` → `docs/22`; tự chọn task `READY` |
| **Claude Code** (reviewer) | `CLAUDE.md`; review độc lập branch/PR, mặc định không sửa file |
| **Dev là người** | `AGENTS.md`, `docs/00`, `docs/10`, `docs/21`, `docs/22` |

## Cấu trúc

```text
.
├── AGENTS.md  CLAUDE.md  README.md
├── docs/
│   ├── 00-tai-lieu-nen.md            tầm nhìn, nguyên tắc, ba lằn ranh, phạm vi, mốc, chỉ số, rủi ro
│   ├── 01-nhat-ky-quyet-dinh.md      quyết định D-001..D-047 + câu hỏi còn mở
│   ├── 02-trang-thai-hien-tai.md     đang ở đâu
│   ├── 03-quy-trinh-thuc-thi.md      agent tự chọn/phân rã/thực hiện task
│   ├── 10-dac-ta-du-lieu.md          schema 2.0, nhãn tự tính, quy tắc R01–R22
│   ├── 11-huong-dan-bien-soan.md     duyệt bài trong CMS, chọn bài MVP, viết lại chú thích, quy tắc AI
│   ├── 12-chinh-sach-nguon.md        nguồn web, Ca dao Mẹ, bản quyền, giấy phép, thư xin phép
│   ├── 20-dac-ta-giao-dien.md        UX v2, nhãn, nguồn gốc, thành phần, trợ năng
│   ├── 21-kien-truc-ky-thuat.md      Astro 6, CMS, Cloudflare, tìm kiếm, kho thô, đường ống thu thập
│   ├── 22-backlog-trien-khai.md      task T-031..T-069, mốc và điều kiện xong
│   ├── 23-ke-hoach-kiem-thu.md       fixture, giao diện, trợ năng, tìm kiếm, tiêu chí theo mốc
│   └── luu-tru/                      tài liệu v1.2 không còn dùng hằng ngày (xem README trong đó)
├── taxonomy/tu-vung-kiem-soat.yaml   từ vựng kiểm soát v2
├── templates/                        mẫu YAML + lệnh AI soạn nháp
├── content/                          dữ liệu CÔNG KHAI (3 hồ sơ khung nháp + nguồn Ca dao Mẹ)
├── data/                             CSV: tình huống khởi đầu, truy vấn thử
├── config/lich-tinh-huong.yaml       thứ tự tình huống
├── du-an/trang-thai.json             trạng thái máy đọc
├── scripts/                          validator, dữ liệu suy ra, fixture, trạng thái, kiểm nhất quán
├── .github/                          CI + mẫu Pull Request
└── tests/fixtures/                   26 bộ dữ liệu kiểm thử
```

Kho thô (toàn bộ lời thu thập từ nguồn web) nằm ở **một repo riêng tư khác**, không bao giờ trong kho này (D-045).

## Lưu ý về dữ liệu mẫu

3 hồ sơ trong `content/tac-pham/` là **khung ở trạng thái nháp** từ v1: lời chép theo dạng phổ biến, chưa có nguồn gốc, nên công cụ kiểm tra **chặn** việc công khai chúng (R21) cho tới khi có nguồn. Nội dung thật đến từ đường ống thu thập (T-039).

## Luồng làm việc

```text
Người sáng lập ── CMS trên điện thoại ─────────────► main (nội dung)
                └─ mở link xem trước, bấm gộp ◄─────┐
GitHub repo ── Codex Cloud: tự chọn task READY → code → PR → CI (npm run kiem-tra)
            └─ Claude Code: review độc lập → APPROVE / REQUEST CHANGES / HUMAN DECISION REQUIRED
```

Prompt Codex thường ngày: **"Tiếp tục dự án theo quy trình thực thi trong repo."**
Prompt Claude thường ngày: **"Review thay đổi hiện tại theo CLAUDE.md. Không sửa file."**

## Việc tiếp theo

1. **Người sáng lập — T-032:** tạo tài khoản GitHub (repo công khai cho kho này + repo riêng tư cho kho thô) và Cloudflare; đẩy kho mã lên.
2. **Codex:** sau T-032, làm T-035 (Astro + CI + triển khai) và T-038 (kho thô) song song.
3. **Người sáng lập — T-034:** gửi thư cho Ca dao Mẹ (bản nháp ở `docs/12` mục 7) trước khi mở công khai.
