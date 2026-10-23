# Kho tàng Ca dao Tục ngữ Việt Nam — Bộ tài liệu triển khai v1.2

**Đọc một câu. Hiểu một đời sống.**

Bộ tài liệu này đủ để bắt đầu xây dựng dự án: định hướng sản phẩm, đặc tả dữ liệu, hướng dẫn biên soạn, đặc tả giao diện và kỹ thuật, backlog có tiêu chí chấp nhận, kế hoạch kiểm thử, cùng một **công cụ kiểm tra dữ liệu đã chạy được**. Nó được viết cho cả dev là người và AI agent.

Ngày: 06/10/2026 (v1.2, thêm quy trình thực thi Codex → Claude) · Nguồn gốc: hợp nhất 4 bản ý tưởng (Meta AI, GPT-5.6 Sol, Qwen3.8, Opus 5.5) và 1 bản review song song.

---

## Bắt đầu trong 5 phút

```bash
npm install
npm run check:data      # kiểm 3 hồ sơ khung: 0 lỗi, vài cảnh báo (đúng như mong đợi ở trạng thái nháp)
npm run test:fixtures   # 13 bộ dữ liệu mẫu: 1 hợp lệ, 12 lỗi phải bị bắt đúng mã
npm run health          # báo cáo sức khỏe dữ liệu: còn nợ gì, cần xử lý gì
npm run trang-thai     # xem phase/cổng/trạng thái hiện tại
npm run viec-tiep-theo # đề xuất task kỹ thuật READY tiếp theo
npm run kiem-tra       # cổng kiểm tra bắt buộc cho agent và CI
```

Cần Node.js ≥ 20. Lần `npm install` đầu tiên sẽ sinh `package-lock.json`; hãy commit lockfile để Codex Cloud, máy local và CI dùng cùng cây phụ thuộc.

## Đọc theo thứ tự cần dùng

| Giai đoạn | Đọc | Để làm ra |
|---|---|---|
| **Ngay bây giờ** | `docs/00-tai-lieu-nen.md` → `docs/01-nhat-ky-quyet-dinh.md` | Hiểu dự án; chốt 3 câu hỏi Q-001..Q-003 |
| **GĐ0 · Chứng minh mô hình dữ liệu** | `docs/10` dữ liệu · `docs/11` biên soạn · `docs/12` nguồn · `docs/13` ưu tiên | 10 hồ sơ thật, thời gian đo theo 5 phần, schema **đóng băng v1** |
| **GĐ0.5 · Lát cắt dọc** | `docs/20` giao diện · `docs/21` kỹ thuật · `docs/22` (T-010) | 3 hồ sơ chạy trọn từ YAML tới bản xem trước |
| **GĐ1 · Lõi sản phẩm + phản hồi sớm** | `docs/22` backlog · `docs/23` kiểm thử · `docs/24` preview và beta | P0-Core; preview ở 15–20 hồ sơ; beta ở 25–30; mở rộng tới 60; P0.5 |
| **Trước ra mắt công khai (~120 hồ sơ)** | `docs/30` vận hành · `docs/50` trang công khai | Sao lưu, sổ tay, các trang Phương pháp, Chính sách AI, Giấy phép… |
| **Trước khi mở đóng góp, ghi âm** | `docs/40` đóng góp · `docs/41` ghi âm · `docs/42` cố vấn | Form, quy trình duyệt, mẫu đồng ý, hội đồng cố vấn |

**Dev là người:** bắt đầu từ `docs/00`, rồi xem `docs/02` và backlog trong `docs/22`.
**Codex Cloud:** đọc `AGENTS.md` → `docs/02` → `docs/03` → `docs/04`, sau đó tự chọn task `READY`.
**Claude Code:** đọc `CLAUDE.md` và review độc lập branch/PR; mặc định không sửa code.

## Cấu trúc

```text
.
├── README.md                 tệp này
├── AGENTS.md                 luật cho AI agent (dev người cũng nên đọc)
├── CLAUDE.md                 trỏ tới AGENTS.md
├── package.json              lệnh dữ liệu + trạng thái + cổng kiểm tra
├── docs/
│   ├── 00-tai-lieu-nen.md            tầm nhìn, nguyên tắc, phạm vi, MVP, lộ trình, chỉ số, rủi ro
│   ├── 01-nhat-ky-quyet-dinh.md      nhật ký quyết định + câu hỏi còn mở
│   ├── 02-trang-thai-hien-tai.md     dashboard trạng thái cho người và agent
│   ├── 03-quy-trinh-thuc-thi.md      Codex tự chọn/phân rã/thực hiện task
│   ├── 04-cong-chat-luong.md         cổng chuyển giai đoạn + Human Gate
│   ├── 10-dac-ta-du-lieu.md          schema, vòng đời, ba trục độc lập, quy tắc R01–R19
│   ├── 11-huong-dan-bien-soan.md     quy trình 7 bước, văn phong, quy tắc AI, checklist
│   ├── 12-chinh-sach-nguon.md        thứ bậc nguồn, bản quyền, danh sách tư liệu cần xác minh
│   ├── 13-danh-sach-uu-tien.md       4 tầng ưu tiên cho 300 hồ sơ đầu
│   ├── 20-dac-ta-giao-dien.md        đọc theo lớp, luật giao diện, thành phần, màu (đã đo tương phản), WCAG 2.2
│   ├── 21-kien-truc-ky-thuat.md      stack, môi trường, tìm kiếm tiếng Việt, đo lường, SEO, sao lưu, điều kiện kích hoạt
│   ├── 22-backlog-trien-khai.md      47 task (Core / 0.5 / P1 / P2) có phụ thuộc + tiêu chí chấp nhận, 17 user story
│   ├── 23-ke-hoach-kiem-thu.md       dữ liệu, giao diện, trợ năng, tìm kiếm, hiệu năng
│   ├── 24-ke-hoach-beta.md           preview 15–20 + beta 25–30, nhiệm vụ thử, tiêu chí Cổng 2
│   ├── 30-so-tay-van-hanh.md         nhịp tuần, sao lưu, sự cố, tài khoản, tiếp quản
│   ├── 40-quy-trinh-dong-gop.md      form, so khớp, duyệt, nâng mức, mẫu thư
│   ├── 41-ghi-am-va-dong-y.md        quy chuẩn ghi âm, mẫu đồng ý, gỡ bỏ, chiến dịch
│   ├── 42-quy-che-co-van.md          hội đồng cố vấn
│   └── 50-ban-nhap-trang-cong-khai.md  Phương pháp, Chính sách AI, Giấy phép, Quyền riêng tư, Về dự án
├── taxonomy/tu-vung-kiem-soat.yaml   20 nhóm từ vựng kiểm soát
├── templates/                mẫu YAML cho 7 loại tệp + lệnh AI soạn nháp
├── content/                  3 hồ sơ khung (nháp) + tệp liên quan
├── data/                     CSV: danh sách ưu tiên, 41 tình huống khởi đầu, 30 truy vấn thử
├── config/lich-tinh-huong.yaml   thứ tự tình huống theo dịp
├── du-an/trang-thai.json     trạng thái dự án dạng máy đọc
├── scripts/                  validator, fixture, trạng thái, chọn việc tiếp theo
├── .github/                  CI nền + mẫu Pull Request
└── tests/fixtures/           13 bộ dữ liệu kiểm thử
```

## Quy ước trạng thái tài liệu

- **Ổn định** — dùng được ngay; đổi thì ghi vào `docs/01`.
- **Sống** — cập nhật thường xuyên (nhật ký quyết định, backlog).
- **Nháp** — cần người sáng lập duyệt hoặc chờ một câu hỏi mở.

## Lưu ý quan trọng về dữ liệu mẫu

3 hồ sơ trong `content/tac-pham/` là **khung ở trạng thái nháp**. Lời chép theo dạng phổ biến, **chưa đối chiếu bản in**; mọi chuỗi do AI soạn mang dấu `NHAP_AI`; mọi thông tin còn thiếu mang dấu `CAN_BO_SUNG`. Công cụ kiểm tra sẽ **chặn** việc chuyển chúng sang "chờ kiểm định" cho tới khi người biên soạn đối chiếu nguồn và viết lại. Thông tin thư mục trong `content/nguon/` cũng chưa xác minh.

## Thay đổi ở v1.2 (agent-driven execution)

- GitHub là control plane; Codex Cloud là planner + implementer chính; Claude Code là reviewer độc lập (D-033).
- Thêm `docs/02-trang-thai-hien-tai.md`, `docs/03-quy-trinh-thuc-thi.md`, `docs/04-cong-chat-luong.md`.
- Thêm `du-an/trang-thai.json` để trạng thái không phụ thuộc lịch sử chat.
- Backlog dùng trạng thái `READY / IN_PROGRESS / REVIEW / BLOCKED / DONE / DEFERRED`; agent tự chọn việc trong phase hiện tại.
- Thêm `npm run trang-thai`, `npm run viec-tiep-theo`, `npm run kiem-tra` (D-034).
- Thêm CI nền và Pull Request template; T-004 sẽ mở rộng CI sau khi Astro được khởi tạo.
- `CLAUDE.md` trở thành hợp đồng review độc lập; chỉ Human Gate mới bắt người sáng lập quyết định.

### Luồng làm việc mặc định

```text
GitHub repo
   ↓
Codex Cloud — tự chọn task READY → code → kiểm tra → PR
   ↓
GitHub CI — npm run kiem-tra
   ↓
Claude Code — review độc lập
   ↓
APPROVE / REQUEST CHANGES / HUMAN DECISION REQUIRED
   ↓
Người sáng lập merge hoặc quyết định Human Gate
```

Prompt Codex thường ngày chỉ cần: **“Tiếp tục dự án theo quy trình thực thi trong repo.”**

Prompt Claude thường ngày chỉ cần: **“Review thay đổi hiện tại theo CLAUDE.md. Không sửa file.”**

## Thay đổi ở v1.1 (review trước triển khai)

- Chú giải khai bằng `cum_tu`; máy tự tìm vị trí, báo lỗi khi mơ hồ (D-020).
- GĐ0 đo thời gian theo 5 phần; schema đóng băng v1 sau 10 hồ sơ thật (D-021).
- Thêm lát cắt dọc (T-010) và hai vòng thử sớm: preview 15–20, beta 25–30 (D-022, D-023).
- MVP tách P0-Core / P0.5; trang tác phẩm đọc theo lớp; đo lường còn 7 sự kiện (D-024..D-026).
- Kiểm tra dữ liệu thêm R18 (quyền với tư liệu điền dã), R19 (nghi trùng), báo cáo `npm run health` (D-027, D-028).
- WCAG 2.2 AA; giao diện công khai không dùng React/shadcn; Admin, nạp tư liệu, monorepo có điều kiện kích hoạt rõ (D-029..D-031).
- Dấu vết AI qua dòng `AI-Agent:` trong commit (D-032).

## Ba việc đầu tiên

1. Chạy `npm install`, commit `package-lock.json`, rồi chạy `npm run trang-thai && npm run viec-tiep-theo && npm run kiem-tra`.
2. Người sáng lập chốt Q-001, Q-002, Q-003; song song Codex có thể bắt đầu **T-002** vì task này không bị ba Q trên chặn.
3. Sau khi T-002 qua PR/CI/Claude review, tiếp tục bằng câu lệnh **“Tiếp tục dự án theo quy trình thực thi trong repo.”**; repo tự xác định task kỹ thuật kế tiếp.
