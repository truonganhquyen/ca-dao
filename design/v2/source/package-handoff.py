"""Rebuild handoff documents, design tokens, no-JS views and social assets.
Run with Python 3 + lxml + Pillow, after node source/build.mjs.
"""
from pathlib import Path
from lxml import html as LH
from PIL import Image, ImageDraw, ImageFont
import base64, csv, gzip, hashlib, html, json, re, shutil, subprocess, zipfile

ROOT = Path(__file__).resolve().parents[1]
DIST, DOCS, EXP = ROOT/'dist', ROOT/'docs', ROOT/'exports'
BRIEF = ROOT/'brief/PHONGDAO_V2_CLAUDE_DESIGN_MASTER_PROMPT_v1.0.md'
REG = json.loads((DOCS/'screen-registry.json').read_text(encoding='utf-8'))
H = html.escape
COMMIT = 'main'
DATE = '02/11/2026'
REFLOW = json.loads((ROOT/'qa/reflow.json').read_text(encoding='utf-8')) if (ROOT/'qa/reflow.json').exists() else None
BASE = 'https://github.com/truonganhquyen/ca-dao/blob/'+COMMIT+'/'
for name in ['tokens','social','qa','docs','dist/no-js','dist/print']:
    (ROOT/name).mkdir(parents=True, exist_ok=True)

def dump(path,obj):
    path.write_text(json.dumps(obj,ensure_ascii=False,indent=2),encoding='utf-8')

def text(node):
    return ' '.join(node.text_content().split())

def table(headers, rows):
    return '<div class="table-wrap"><table><thead><tr>'+''.join('<th>'+H(str(x))+'</th>' for x in headers)+'</tr></thead><tbody>'+''.join('<tr>'+''.join('<td>'+str(x)+'</td>' for x in row)+'</tr>' for row in rows)+'</tbody></table></div>'

# Bảng tím – lục (D-092, 02/11/2026). Phải khớp khối :root và :root[data-theme=dark] đầu source/styles.css.
light={'giay':'#FEFCF8','surface':'#FFFFFF','panel':'#F6F2EA','muc':'#14120F','nhat':'#5A5652','tim':'#5E3AAE','tim-dam':'#4B2F8A','tim-nhat':'#EDE6FB','tim-vien':'#D5C8F0','tim-giay':'#F6F2FD','luc':'#1F6B4A','luc-nhat':'#E6F2E9','luc-vien':'#A9D1B8','to':'#FFF3B0','to-vien':'#F0D97A','loi':'#A1201A','vang':'#7A5A12','xanh':'#2B5580','nhan-co':'#5E5E5E','line':'#E8E0D0','control-line':'#8C8478','tren-tim':'#FFFFFF'}
dark={'giay':'#17151B','surface':'#1F1C24','panel':'#24212A','muc':'#EFEBE4','nhat':'#B4AEA6','tim':'#BCA8F4','tim-dam':'#D4C7F8','tim-nhat':'#2E2648','tim-vien':'#4A3D72','tim-giay':'#221E2E','luc':'#86CFA8','luc-nhat':'#1B3328','luc-vien':'#2F5E46','to':'#4A3F12','to-vien':'#8C7426','loi':'#F2958B','vang':'#E0BE72','xanh':'#8DB6E3','nhan-co':'#B0B0B0','line':'#38343F','control-line':'#8E8799','tren-tim':'#17151B'}
share={'nen':'#4B2F8A','chu':'#FFFFFF','phu':'#E8DDFF','duong':'#7A62B8'}
# D-093: nền khối theo vai và màu thể loại; phải khớp lớp D-093 trong source/styles.css.
light.update({'nen-vang':'#FFF8E1','nen-tim':'#F6F2FD','nen-luc':'#EFF7F1','nen-xanh':'#EEF4FB','nen-dat':'#F7F2E9','nen-hong':'#FCF0F5','vang-dam':'#8A5A00','hong':'#A3245E'})
dark.update({'nen-vang':'#2A2414','nen-tim':'#221E2E','nen-luc':'#18261F','nen-xanh':'#172231','nen-dat':'#24211C','nen-hong':'#2C1A24','vang-dam':'#E6B860','hong':'#F09ABF'})
# Ảnh để chia sẻ: 6 nền (a1 → a2 chuyển màu), chữ chính và chữ phụ; ảnh có khổ ngang 1200×630, vuông 1080×1080, dọc 1080×1350.
THEMES={'tim':{'ten':'Trống đồng','a1':'#35205F','a2':'#6A42C2','chu':'#FFFFFF','phu':'#E8DDFF'},'giay':{'ten':'Giấy dó','a1':'#FBF4E4','a2':'#EBDDBE','chu':'#2A2118','phu':'#5E3AAE'},'dem':{'ten':'Đêm sao','a1':'#12102A','a2':'#2E2560','chu':'#FFFFFF','phu':'#CFC6FF'},'luc':{'ten':'Đồng lúa','a1':'#0E3A28','a2':'#236B4C','chu':'#FFFFFF','phu':'#CDEBDC'},'nang':{'ten':'Nắng','a1':'#FFC65C','a2':'#FF8A4C','chu':'#2A1708','phu':'#5A2E00'},'xanh':{'ten':'Sông nước','a1':'#132E4A','a2':'#2F6690','chu':'#FFFFFF','phu':'#D2E6F7'}}
THEME_BY_GENRE={'ca-dao':['tim','giay','dem'],'tuc-ngu':['luc','giay','tim'],'dong-dao':['nang','giay','luc'],'ve':['xanh','giay','tim'],'cau-do':['dem','nang','tim']}
tokens={'name':'Phongdao V2','version':'design-handoff-2.1','status':'Bản chốt: mọi lựa chọn D-092, D-093 đã chốt','date':'2026-11-02','colors':{'light':light,'dark':dark,'share-image':share,'share-themes':THEMES,'share-themes-by-genre':THEME_BY_GENRE},'color_roles':{'tim':'Thương hiệu, nút chính, liên kết, mục đang chọn','luc':'Ý nghĩa và nhãn “đã đối chiếu”','to':'Nền từ có chú thích trong lời','loi':'Lỗi, cảnh báo chặn','vang':'Nháp AI chưa duyệt trong Bàn biên tập','share-image':'Ảnh chia sẻ 1200×630: nền tím đậm cố định, không đổi theo sáng/tối'},'typography':{'reading':'Literata','ui':'Be Vietnam Pro','scale_px':[15,17,22,28,36,48,64],'reading_preferences_px':[22,28,34],'reading_line_height':1.7,'ui_line_height':1.65,'font_loading':'Tự lưu trữ, không tải bên thứ ba'},'space_px':[4,8,12,16,24,32,48,64],'layout':{'reading_max':'38em','work_max_px':1040,'work_rail_px':232,'catalog_max_px':1168,'header_max_px':1248,'mobile_gutter_px':20,'tablet_gutter_px':24,'desktop_gutter_px':32,'breakpoints_px':[768,1024],'test_widths_px':[320,360,390,768,1024,1440]},'controls':{'primary_min_height_px':48,'other_min_target_px':44,'radius_px':10,'card_radius_px':14,'pill_radius':'999px','shadow':'0 1px 2px rgba(20,18,15,.04), 0 6px 20px rgba(20,18,15,.05)','focus_outline_px':3,'focus_offset_px':4},'poetry':{'indent_desktop':'1.6em','indent_mobile':'.9em','direction':'Câu lục thụt vào; câu bát ở ngoài (D-092, như sách in)','line_numbers':'Ngoài lề, aria-hidden, không chọn được; chỉ máy tính','wrap':'Giữ dòng nguồn; phần wrap thị giác có hanging indent 1em'},'motion':{'reduced_motion':'Tắt animation, transition và smooth scroll','kid_beat':'Chỉ nhịp thị giác, không tự phát âm thanh'},'classification':'Bảng tím – lục, hai font, thụt câu lục ĐÃ CHỐT (D-092); kích thước, bo góc, bóng là ĐỀ XUẤT THỂ HIỆN'}
dump(ROOT/'tokens/phongdao.tokens.json',tokens)
token_css='/* Phongdao V2: bảng tím – lục (D-092). Token kích thước là đề xuất thể hiện. */\n'
for selector,colors in [(':root',light),(':root[data-theme="dark"]',dark)]:
    token_css+=selector+'{\n'+''.join('  --'+k+': '+v+';\n' for k,v in colors.items())+'}\n'
token_css+=':root{--font-reading:Literata,Georgia,serif;--font-ui:"Be Vietnam Pro",sans-serif;--read:38em;--verse-indent:1.6em;--reading-size:22px;--radius:10px;--bo:14px;--bo-nho:10px;\n'
token_css+=''.join(f'--space-{n}:{n}px;' for n in tokens['space_px'])+'\n'+''.join(f'--text-{n}:{n}px;' for n in tokens['typography']['scale_px'])+'}\n@media(max-width:767px){:root{--verse-indent:.9em}}\n'
(ROOT/'tokens/phongdao.tokens.css').write_text(token_css)
dump(ROOT/'tokens/theme-token-map.json',{'notice':'Bảng chuyển giao, không phải theme.json production có thể cài ngay. Dev nối vào cấu trúc theme mới theo docs/26.','settings.color.palette':[{'slug':k,'color':v,'name':k} for k,v in light.items()],'settings.typography.fontSizes':[{'slug':str(n),'size':str(n)+'px','name':str(n)} for n in tokens['typography']['scale_px']],'settings.spacing.spacingSizes':[{'slug':str(n),'size':str(n)+'px','name':str(n)} for n in tokens['space_px']],'settings.layout.contentSize':'38em','settings.layout.wideSize':'1168px','styles.color':{'background':'var(--wp--preset--color--giay)','text':'var(--wp--preset--color--muc)'},'dark_mode':'CSS custom properties: bộ tối riêng trong tokens/phongdao.tokens.css (:root[data-theme=dark]); theme.json chỉ giữ bảng sáng.'})

def luminance(c):
    values=[int(c[i:i+2],16)/255 for i in (1,3,5)]
    values=[v/12.92 if v<=.04045 else ((v+.055)/1.055)**2.4 for v in values]
    return sum(x*y for x,y in zip(values,[.2126,.7152,.0722]))
def contrast(a,b):
    x,y=sorted([luminance(a),luminance(b)])
    return round((y+.05)/(x+.05),2)
contrasts=[]
for name,colors in [('light',light),('dark',dark)]:
    for fg in ['muc','nhat','tim','tim-dam','luc','loi','vang','xanh','nhan-co']:
        for bg in ['giay','panel','surface']:
            ratio=contrast(colors[fg],colors[bg]);contrasts.append({'palette':name,'foreground':fg,'background':bg,'ratio':ratio,'threshold':4.5,'pass':ratio>=4.5})
    # Chữ trên nền nhạt và chữ trên nút / nhãn đặc
    for fg,bg in [('tim','tim-nhat'),('tim','tim-giay'),('nhat','tim-giay'),('muc','tim-giay'),('luc','luc-nhat'),('muc','luc-nhat'),('nhat','luc-nhat'),('muc','to'),('tren-tim','tim'),('tren-tim','tim-dam'),('tren-tim','luc')]:
        ratio=contrast(colors[fg],colors[bg]);contrasts.append({'palette':name,'foreground':fg,'background':bg,'ratio':ratio,'threshold':4.5,'pass':ratio>=4.5})
    for bg in ['giay','panel','surface']:
        ratio=contrast(colors['control-line'],colors[bg]);contrasts.append({'palette':name,'foreground':'control-line','background':bg,'ratio':ratio,'threshold':3,'pass':ratio>=3})
for name,colors in [('light',light),('dark',dark)]:
    for bg in ['nen-vang','nen-tim','nen-luc','nen-xanh','nen-dat','nen-hong']:
        for fg in ['muc','nhat','tim']:
            ratio=contrast(colors[fg],colors[bg]);contrasts.append({'palette':name,'foreground':fg,'background':bg,'ratio':ratio,'threshold':4.5,'pass':ratio>=4.5})
    for fg,bg in [('luc','nen-luc'),('vang-dam','nen-vang'),('xanh','nen-xanh'),('hong','nen-hong'),('vang-dam','surface'),('hong','surface')]:
        ratio=contrast(colors[fg],colors[bg]);contrasts.append({'palette':name,'foreground':fg,'background':bg,'ratio':ratio,'threshold':4.5,'pass':ratio>=4.5})
for fg in ['chu','phu']:
    ratio=contrast(share[fg],share['nen']);contrasts.append({'palette':'share-image','foreground':fg,'background':'nen','ratio':ratio,'threshold':4.5,'pass':ratio>=4.5})
for key,t in THEMES.items():
    for fg in ['chu','phu']:
        for bg in ['a1','a2']:
            ratio=contrast(t[fg],t[bg]);contrasts.append({'palette':'share-'+key,'foreground':fg,'background':bg,'ratio':ratio,'threshold':4.5,'pass':ratio>=4.5})
dump(ROOT/'qa/contrast.json',contrasts)

brief=BRIEF.read_text(encoding='utf-8')
sections=re.split(r'(?m)^### ',brief)
requirements={}
for s in sections:
    m=re.match(r'([RA]\d\d)(?:–([RA]\d\d))? — (.+)\n([\s\S]*)',s)
    if m:
        start,end,title,body=m.groups();body=re.split(r'(?m)^## ',body)[0].strip()
        ids=[start] if not end else [start[0]+f'{n:02}' for n in range(int(start[1:]),int(end[1:])+1)]
        for ident in ids: requirements[ident]={'title':title,'text':body}
for line in brief.splitlines():
    m=re.match(r'\| (F\d\d) \| (.*?) \| (.*?) \|',line)
    if m:requirements[m[1]]={'title':m[2],'text':m[3]}

maps={
'R01':('front-page','loi-vao,vai,cau-hom-nay,ban-co-biet,danh-sach'),
'R02':('archive-tac_pham','bo-loc,the-bai,danh-sach'),
'R03':('taxonomy-the_loai','bo-loc,the-bai,danh-sach'),
'R04':('taxonomy-chu_de / taxonomy-vung / taxonomy-mo_tip; hinh_thuc: đối chiếu docs/26','bo-loc,the-bai,danh-sach'),
'R05':('single-tac_pham-ca-dao','loi,muc-kiem,nghia,chu-thich,di-ban'),
'R06':('single-tac_pham-tuc-ngu','loi,muc-kiem,nghia'),
'R07':('single-tac_pham-ve','loi,muc-kiem,doan,boi-canh,chu-thich'),
'R08':('single-tac_pham-dong-dao','loi,muc-kiem,nghia,thuc-hanh'),
'R09':('single-tac_pham-cau-do','loi,muc-kiem,thuc-hanh'),
'R10':('archive-tuyen_tap','tuyen-tap,bo-loc'),
'R11':('single-tuyen_tap','tuyen-tap,hanh-dong'),
'R12':('search / page /tim/ theo route plugin','tim'),
'R13':('page /tu-dien/','danh-sach,bo-loc'),
'R14':('taxonomy-muc_tu','chu-thich,danh-sach'),
'R15':('page /ban-do/','ban-do'),
'R16':('page /do-vui/','do-vui'),
'R17':('page /tro-choi/','bo-loc,danh-sach'),
'R18':('taxonomy-tro_choi','thuc-hanh,danh-sach'),
'R19':('page /hay-bi-nham/','danh-sach,the-bai'),
'R20':('page /ngau-nhien/','loi,nghia,hanh-dong'),
'R21':('page /da-luu/','the-bai; lưu local là lớp JS'),
'R22':('page hoặc đích góp hiện hành','gop'),
'R23':('single-phan_tich','loi; bài phân tích có template riêng'),
'R24':('page','nội dung tĩnh'), 'R25':('page','nội dung tĩnh theo chính sách nguồn'),
'R26':('page','nội dung tĩnh'), 'R27':('page','nội dung chính sách đã duyệt'),
'R28':('page; giữ route MVP','nội dung quyền riêng tư đã duyệt'),
'R29':('404','tim'), 'R30':('plugin maintenance / lỗi hệ thống','Không thuộc template đọc'),
'R31':('Bề mặt điều hướng mobile; route cần chọn','loi-vao; không bắt buộc tạo khối mới'),
'F01':('page /lop-hoc/ — đợt 5','danh-sach,bo-loc'),
'F02':('page /lop-hoc/<lop>/ — đợt 5','tuyen-tap,hanh-dong'),
'F03':('print stylesheet của bộ câu','loi,nghia,chu-thich,hanh-dong'),
'F04':('single-tac_pham-truyen — sau cổng','loi,doan,chu-thich,nghia'),
'F05':('single-tac_pham-{hat-ru|ho|ly} — sau cổng','loi,muc-kiem,nghia'),
'F06':('Hub/route Nghe là đề xuất','Audio sinh sẵn: tích hợp vào hành động, tên khối cần dev xác nhận'),
'F10':('Trang tải công khai là đề xuất','Xuất có phiên bản đã chốt; UI tải chưa chốt')}
gates={
'R01':'Câu hôm nay chỉ chọn L2, không lặp 30 ngày; thiếu dữ liệu ẩn khối. Vai chỉ đổi khối 4–8.',
'R03':'Thể loại 0 bài ẩn khỏi menu; không có giới thiệu thì bỏ giới thiệu.',
'R11':'Có lời dẫn do người viết và ít nhất 8 tác phẩm đã đăng. Không dùng tiêu đề tất cả có nghĩa nếu có bài thiếu.',
'R16':'Đủ 10 câu từ dữ liệu; thiếu thì chọn nhóm khác. Đáp án mặc định đóng.',
'R19':'Ít nhất 5 câu có cờ và có lý do. Trạng thái under-gate chưa là trang công khai.',
'R20':'Chỉ bài có nghĩa đã duyệt, không cờ; không lặp trong phiên.',
'R23':'Chỉ bài đã duyệt; có bút danh mới hiện Biên soạn; không làm tác phẩm tăng cấp nội bộ.',
'A05':'Công khai cần lời dẫn có người chịu trách nhiệm và ≥8 bài đã đăng. Nút Lên/Xuống thay thế kéo thả.',
'A11':'Chỉ Quản trị đổi chính sách; bắt buộc đối chiếu quyết định; không tự đổi nguồn ẩn thành công khai.',
'F01':'Đợt 5; lên menu khi ≥3 bài học có bộ câu SGK.',
'F02':'Chỉ gắn bộ sách khi có hồ sơ và trang; không suy đoán SGK từ lời.',
'F04':'Sách hết bản quyền có bản quét đã được chủ dự án kiểm quyền; không dùng bản kể hiện đại.',
'F05':'Qua cổng thể loại; không suy ra âm thanh/diễn xướng từ văn bản.',
'F06':'Bấm mới tải/phát; giọng máy đã được người nghe thử. Nghe chưa là menu ra mắt.',
'F07':'Có sách quét được phép dùng và người xác nhận từng đối chiếu; máy không tự nâng mức kiểm.',
'F08':'Lô 30 bài, người nghe Đạt/Sai và ghi lỗi; chưa sinh cả kho.',
'F09':'Mẫu qua trợ năng, ≥20 bài đăng, ≥10 L2, hồ sơ nguồn/quyền và người quyết mở.',
'F10':'Chỉ tác phẩm công khai, lớp đã duyệt, nguồn có quyền; không kho thô, nguồn ẩn, dữ liệu cá nhân.'}
flows={
'J01':(['R01','R12','R06'],'Tìm “tuc ngu mau” → kết quả → nghĩa','Đã thử UI; chưa đo ≤10 giây với người thật'),
'J02':(['R04','R21'],'Lưu 5 câu → trình chiếu','Đã thử UI: 1/5 → 2/5; chưa đo ≤3 phút'),
'J03':(['R17','R18','R08'],'4 bé / 5 tuổi / trong nhà → đồng dao → Đọc cùng bé','Đã thử UI trên fixture; chưa đo ≤1 phút'),
'J04':(['R05','R14'],'Từ chú thích → mục từ → tác phẩm; đóng trả focus','Đã thử sheet, trả focus, liên kết; fixture liên quan được chỉnh đúng từ'),
'J05':(['R05','R24','R27'],'Mức kiểm → Thông tin tư liệu → Phương pháp','Đã mở UI; mức hiểu của người đọc chưa được đo'),
'J06':(['R12'],'Chờ biên soạn → Mong có','Đã thử; xác nhận không lộ lời kho'),
'J07':(['R05'],'Trích dẫn thông dụng / APA → copy / copy tay','Đã thử preview APA và nhánh lỗi copy tay; Clipboard hệ thống phụ thuộc trình duyệt'),
'J08':(['R05','R22'],'Gửi số trang / dị bản / cảm nghĩ','Đã thử giữ nguyên chữ khi lỗi; không gửi đến dịch vụ thật'),
'J09':(['R09','R16'],'Gợi ý → đáp án → lượt 10 câu → hoàn tất','Đã thử hoàn tất 10/10, không điểm hoặc phạt'),
'J10':(['A01','A02'],'Nháp nghĩa → người xác nhận → Hoàn tác','Đã thử chặn khi chưa xác nhận và hoàn tác trong 10 giây; chưa đo ≤60 giây'),
'J11':(['A02','A04','A19','R05'],'Ứng viên → soạn → nhắc trùng → đăng → trang đọc','Đã thử sửa nghĩa, xác nhận và xem đúng chữ đã sửa; backend không được gọi'),
'J12':(['A02','A19'],'So hai bài → giữ → xác nhận gộp','Đã thử dialog hệ quả: dị bản, 301, dữ liệu dồn, thùng rác; thao tác mô phỏng'),
'J13':(['A11','R05'],'Preview khách / biên soạn theo chính sách nguồn','Đã thử chặn đổi thiếu quyết định; static scan public kiểm URL và nội dung'),
'J14':(['F01','F02','F03'],'Lớp / bộ sách → bộ câu → A4 / trình chiếu','Đã thử 1/8 và mở phiếu; chưa nghiệm thu in vật lý / hồ sơ SGK thật'),
'J16':(['R05','R11'],'Sao chép lời trong thẻ / Sao chép cả bộ','Đạt: lời giữ thụt câu lục, có đường dẫn; cả bộ 8 câu có 8 đường dẫn'),
'J17':(['R05'],'Chia sẻ → Tạo ảnh → đổi khổ dọc, nền Giấy dó, kèm ý nghĩa → Tải ảnh','Đạt: tỷ lệ 4:5, không tràn khung; tải PNG thật cần máy chủ'),
'J18':(['R05'],'Người biên soạn: Sửa ý nghĩa tại chỗ → Lưu','Đạt trên bản mẫu; REST và lịch sử do dev nối'),
'J15':(['R07'],'Mục lục → khổ cuối → nhớ chỗ → Aa lớn → in','Đã mở khổ 50 của 990 dòng; cỡ 34 không tràn 390. CSS in có mở toàn bài; chưa nghiệm thu phân trang trên máy in')}
dump(DOCS/'journeys.json',{k:{'screens':v[0],'steps':v[1],'verification':v[2]} for k,v in flows.items()})

specs=[]; issues={k:[] for k in ['h1','duplicate_ids','unlabelled','empty_links','missing_links','public_levels','external_runtime']}; total_states=0
for p in REG:
    for state in p['states']:
        total_states+=1; path=DIST/'pages'/f"{p['id']}--{state}.html"; tree=LH.fromstring(path.read_text()); mains=tree.xpath('//main'); main=mains[0]; key=p['id']+'/'+state
        if len(main.xpath('.//h1'))!=1:issues['h1'].append(key)
        ids=tree.xpath('//@id')
        if len(set(ids))!=len(ids):issues['duplicate_ids'].append(key)
        for el in tree.xpath('//input[not(@type="hidden")]|//textarea|//select'):
            ident=el.get('id');label=tree.xpath('//label[@for=$id]',id=ident) if ident else []
            if not(el.get('aria-label') or el.get('aria-labelledby') or el.xpath('ancestor::label') or label):issues['unlabelled'].append(key+':'+(ident or el.tag))
        for a in tree.xpath('//a[@href]'):
            if not text(a) and not a.get('aria-label'):issues['empty_links'].append(key+':'+a.get('href'))
            dest=a.get('href').split('?')[0].split('#')[0]
            if dest.endswith('.html') and not (path.parent/dest).is_file():issues['missing_links'].append(key+':'+dest)
        if p['id'].startswith('R') and re.search(r'\bL[0-3]\b',text(main)):issues['public_levels'].append(key)
        if tree.xpath('//script[starts-with(@src,"http")]|//link[@rel="stylesheet" and starts-with(@href,"http")]'):issues['external_runtime'].append(key)
    tree=LH.fromstring((DIST/'pages'/f"{p['id']}.html").read_text()); main=tree.xpath('//main')[0]
    isadmin=p['id'].startswith('A') or p['id'] in ['F07','F08','F09']
    template,blocks=maps.get(p['id'],('Phongdao admin React trong WordPress; trang nghiệp vụ '+p['id'],'Dùng thư viện WordPress; không dùng Gutenberg cho tác phẩm') if isadmin else ('Công cụ duyệt thiết kế, không là màn production','Không ánh xạ vào theme'))
    if p['id'] in ['R05','R06','R07','R08','R09','F04','F05']: blocks+=',hanh-dong,tu-lieu,bien-soan,thuoc-tuyen-tap,doc-tiep,gop'
    source=BASE+('docs/28-ban-bien-tap-v2.md' if isadmin else 'docs/27-san-pham-v2.md')
    headings=[text(el) for el in main.xpath('.//h1|.//h2|.//h3|.//summary')]
    interactions=list(dict.fromkeys([text(el) or el.get('aria-label','') for el in main.xpath('.//button|.//a[@data-route]|.//summary')]))
    req=requirements.get(p['id'],{'text':'Công cụ so và kiểm hệ thống thiết kế theo brief §5, §12, §16.'})['text']
    spec={**p,'objective':requirements.get(p['id'],{}).get('title',p['title']),'requirement_source':source,'requirement_verbatim_from_user_brief':req,'block_order_rendered':headings,'default_state':p['states'][0],'interaction_labels':interactions,'wordpress_template':template,'wordpress_blocks':blocks,'responsive':'390: một cột, lề 20, vùng chạm 44/48; 1440: cột đọc 38em / danh sách 1168; admin rail đổi bottom nav dưới 768. 320 dùng wrap, không cắt dòng nguồn.','empty_error':'Xem các biến thể trong states. Nhãn thiếu/lỗi chỉ khi áp dụng; không sinh nháp văn hoá để lấp chỗ.','no_js':'Quản trị cần JavaScript; giữ buffer và báo lỗi phiên. Không có fallback Gutenberg.' if isadmin else 'Có bản HTML cơ bản tại dist/no-js. Lời/nghĩa/đáp án/chú thích là HTML; form tìm cần SSR khi làm WordPress. Nguyên mẫu tĩnh không có backend tìm không-JS.','print':'A4 nền trắng; ẩn điều hướng/công cụ; mở nội dung details trước in, có nguồn. Kiểm thực tế máy in là bước nghiệm thu tiếp.' if p['id'] in ['R05','R06','R07','R08','R09','R11','F02','F03','F04','F05'] else 'CSS in cơ bản; màn này không được thiết kế như tài liệu in chính.','permissions':'Nội bộ theo capability; role trong prototype chỉ mô phỏng, không là cơ chế bảo mật.' if isadmin else 'Khách chỉ nhận tác phẩm/lớp được phép công khai. Không cần tài khoản người đọc.','visibility_gate':gates.get(p['id'],'Chỉ nội dung đủ điều kiện công khai; bỏ cả khối rỗng. Số lượt đọc ≥10, chia sẻ/trích dẫn ≥3; không hiện số 0.' if not isadmin else 'Do người có quyền quyết định; máy chỉ gợi ý/nháp. Không tự công khai từ kho.'),'journeys':[k for k,v in flows.items() if p['id'] in v[0]],'verification':'Static: tất cả trạng thái. Reflow: mặc định 320 / 390 / 1440 đã đo ở 65 mẫu (D02 so đôi kiểm riêng). Hành trình theo bảng J; chưa chứng nhận WCAG/hiệu năng production.','limitations':'Dữ liệu tổng hợp, không nội dung văn hoá thật. Dịch vụ gửi, xin phép, lấy nguồn, gộp, job, auth đều mô phỏng.'}
    specs.append(spec)
dump(DOCS/'screen-specs.json',specs)
dump(ROOT/'qa/static-checks.json',{'screen_count':len(REG),'product_screens':sum(p['id'][0] in 'RAF' for p in REG),'state_count':total_states,'issues':issues})

shared=[
('C01','Phần cuối tác phẩm','R05–R09','Chủ đề/vùng → thuộc tuyển tập → tư liệu → đọc tiếp → góp → cảm nghĩ; cùng hợp đồng khối','Static + mở tư liệu'),
('C02','Chú thích từ','R05, R14','Desktop popover; mobile sheet; Esc/Đóng trả focus; link đến mục từ','UI đã thử focus và liên kết'),
('C03','Dị bản','R05/variants','Giữ chữ nguồn; đánh dấu khác bằng ins/del; không gọi đúng/sai','Static + bố cục'),
('C04','Nguồn theo lớp','R05, R24, A11','Ẩn / tại chỗ / trong tư liệu; public không nhận URL nội bộ','UI guard + static public'),
('C05','Trích dẫn','R05/clipboard-error','Thông dụng / APA; chỉ lời; kèm đáp án tùy chọn; preview; copy tay','UI preview APA + nhánh lỗi'),
('C06','Lưu / chia sẻ / Aa','R21, R05','LocalStorage có nhánh chặn; Web Share fallback; cỡ 22/28/34; sáng/tối/hệ thống','UI lưu 5, Aa 34; chia sẻ hệ thống cần thiết bị thật'),
('C07','Trình chiếu','R21, R11, F02','Một bài / danh sách; trước/tiếp; Esc/Thoát; nguồn và cờ theo bài','UI 1/5→2/5 và 1/8'),
('C08','Đọc cùng bé','R08','Chữ lớn, từng dòng; không audio tự phát; reduced motion tắt nhịp','UI đã mở; CSS reduced-motion kiểm tĩnh'),
('C09','In / chia sẻ ảnh','F03, D04','A4 nền trắng; SVG/PNG 1200×630; bốn loại; cờ rõ trên ảnh nghi','Xuất asset + kiểm file; in vật lý chưa thực hiện'),
('C10','Mỗi câu một dòng','R01, R05–R11, chế độ trình chiếu','Co chữ vừa khung, sàn 15 / 14 px, cùng cỡ trong một bài','Đo 205 trạng thái × 5 khổ'),
('C11','Ảnh để chia sẻ','R05–R09, D04','Khổ, nền, kèm ý nghĩa; không lộ đáp án','Luồng tương tác đạt; PNG thật do máy chủ sinh')]
with (DOCS/'coverage.csv').open('w',encoding='utf-8-sig',newline='') as f:
    out=csv.writer(f);out.writerow(['ID','Nhóm','Màn','Trạng thái','Mobile','Desktop','Luồng','Cách kiểm','Giới hạn'])
    for p in specs:out.writerow([p['id'],p['group'],p['title'],' | '.join(p['states']),'Responsive 320–390','Responsive 768–1440',','.join(p['journeys']),p['verification'],p['limitations']])
    for ident,title,where,behavior,check in shared:out.writerow([ident,'Dùng chung',title,where,'Có','Có',behavior,check,'Xem giới hạn J và ghi chú trong handoff'])

# Static readable fallbacks: no scripts; data-route links remain ordinary local links.
nojsindex=[]
for p in REG:
    if p['id'].startswith('R') or p['id'] in ['F01','F02','F03','F04','F05','F06','F10']:
        for state in p['states']:
            path=DIST/'pages'/f"{p['id']}--{state}.html"; tree=LH.fromstring(path.read_text())
            for el in tree.xpath('//script|//button[@data-action]'):el.getparent().remove(el)
            for el in tree.xpath('//*[@hidden]'):
                if 'quiz-item' in el.get('class',''):el.attrib.pop('hidden',None)
            for a in tree.xpath('//a[@href]'):
                m=re.match(r'([RF]\d\d)\.html\?state=([\w-]+)$',a.get('href'))
                if m:a.set('href',m[1]+'--'+m[2]+'.html')
            banner=LH.fromstring('<div class="notice">Bản đọc cơ bản không JavaScript. Tìm tương tác, lưu trên máy, gửi form và trình chiếu cần bản nguyên mẫu chính; tìm production phải được render phía server.</div>')
            tree.xpath('//main')[0].insert(0,banner)
            result='<!doctype html>\n'+LH.tostring(tree,encoding='unicode',method='html')
            (DIST/'no-js'/path.name).write_text(result)
            if state==p['states'][0]:(DIST/'no-js'/f"{p['id']}.html").write_text(result)
        nojsindex.append('<li><a href="'+p['id']+'.html">'+p['id']+' · '+H(p['title'])+'</a></li>')
(DIST/'no-js/index.html').write_text('<!doctype html><html lang="vi"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="../assets/styles.css"><title>Bản đọc không JavaScript</title><main class="page"><h1>Bản đọc cơ bản</h1><p>Các tệp này đã bỏ script, giữ nội dung và disclosure gốc.</p><ul>'+''.join(nojsindex)+'</ul></main></html>')
for ident,state in [('R07','lines220'),('R07','lines990'),('R11','thirty'),('F03','one'),('F03','long')]:
    tree=LH.fromstring((DIST/'pages'/f'{ident}--{state}.html').read_text())
    for el in tree.xpath('//script|//header|//footer|//nav|//button|//*[contains(concat(" ",normalize-space(@class)," ")," prototype-note ")]'):el.getparent().remove(el)
    for el in tree.xpath('//details'):el.set('open','')
    tree.xpath('//head')[0].append(LH.fromstring('<style>html,body{background:white!important;color:#22201c!important}body{padding:0}main{max-width:180mm!important;margin:10mm auto!important}.reading .verse{font-size:20px}.no-print,.actions,.related-list,#gop{display:none!important}@page{size:A4;margin:16mm}a{color:inherit}</style>'))
    (DIST/'print'/f'{ident}--{state}.html').write_text('<!doctype html>'+LH.tostring(tree,encoding='unicode',method='html'))

# Original text-only social graphics. No external photos, no invented cultural content.
fontdir=DIST/'assets'
font_b64=base64.b64encode((fontdir/'literata.woff').read_bytes()).decode()
socials={
'ca-dao':('CA DAO · DỮ LIỆU THỬ','tim',['Dòng mẫu một của bài này','Dòng mẫu hai dài hơn chút cho vừa','Dòng mẫu ba của bài này','Dòng mẫu bốn dài hơn chút cho xong']),
'tuc-ngu':('TỤC NGỮ – THÀNH NGỮ · DỮ LIỆU THỬ','luc',['Tục ngữ mẫu ngắn một dòng']),
'chung':('THƯ VIỆN VĂN HỌC DÂN GIAN VIỆT NAM','tim',['Đọc một câu.','Hiểu một đời sống.']),
'co-nghi':('NGHI KHÔNG PHẢI DÂN GIAN · DỮ LIỆU THỬ','dem',['Dòng mẫu một của bài này','Dòng mẫu hai dài hơn chút cho vừa'])}
import math
def drum(cx,cy):
    # Hoạ tiết mặt trống đồng: sao 14 cánh + vòng tròn đồng tâm + vạch quanh vành (vẽ bằng mã, không ảnh ngoài)
    pts=[]
    for i in range(28):
        r=70 if i%2==0 else 25;a=math.pi*i/14-math.pi/2;pts.append((cx+r*math.cos(a),cy+r*math.sin(a)))
    return pts,[100,135,170,205,240],[(cx+185*math.cos(i*math.pi/18),cy+185*math.sin(i*math.pi/18),cx+198*math.cos(i*math.pi/18),cy+198*math.sin(i*math.pi/18)) for i in range(36)]
for name,(label,theme,lines) in socials.items():
    t=THEMES[theme];uiw=ImageFont.truetype(str(fontdir/'bevietnam.woff'),22)
    # Mỗi câu một dòng: cỡ chữ lớn nhất ≤ 52 sao cho dòng dài nhất (kể cả thụt câu lục) vừa 1072 px và cả khối vừa chiều cao
    fs=52
    while fs>20:
        f_=ImageFont.truetype(str(fontdir/'literata.woff'),fs)
        if max(f_.getlength(l)+(40 if name=='ca-dao' and i%2==0 else 0) for i,l in enumerate(lines))<=1072 and len(lines)*fs*1.45<=330:break
        fs-=2
    lh=int(fs*1.45);top=150+(330-len(lines)*lh)//2;ys=[top+i*lh+fs for i in range(len(lines))]
    pts,rings,ticks=drum(1040,500)
    motif=''.join(f'<circle cx="1040" cy="500" r="{r}"/>' for r in rings)+'<polygon points="'+' '.join(f'{x:.1f},{y:.1f}' for x,y in pts)+'"/>'+''.join(f'<path d="M{a:.1f} {b_:.1f}L{c_:.1f} {d:.1f}"/>' for a,b_,c_,d in ticks)
    parts=['<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="630" viewBox="0 0 1200 630" role="img" aria-labelledby="title desc"><title>'+H(label)+'</title><desc>'+H(' / '.join(lines))+'</desc><defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="'+t['a1']+'"/><stop offset="1" stop-color="'+t['a2']+'"/></linearGradient><style>@font-face{font-family:Literata;src:url(data:font/woff;base64,'+font_b64+')}text{font-family:Literata,serif}</style></defs><rect width="1200" height="630" fill="url(#g)"/><g fill="none" stroke="'+t['chu']+'" stroke-width="2" opacity=".18">'+motif+'</g>',f'<text x="64" y="104" fill="{t["phu"]}" font-size="22" letter-spacing="2">{H(label)}</text>']
    for i,line in enumerate(lines):parts.append(f'<text x="{104 if name=="ca-dao" and i%2==0 else 64}" y="{ys[i]}" fill="{t["chu"]}" font-size="{fs}">{H(line)}</text>')
    parts+=[f'<rect x="64" y="532" width="44" height="44" rx="11" fill="{t["chu"]}"/><text x="76" y="565" fill="{t["a1"]}" font-size="26" font-weight="700">P</text><text x="124" y="553" fill="{t["chu"]}" font-size="24">Thư viện Văn học Dân gian Việt Nam</text><text x="124" y="584" fill="{t["phu"]}" font-size="18">phongdao.com · Bản lưu truyền phổ biến · dữ liệu thử</text></svg>']
    (ROOT/'social'/f'{name}-1200x630.svg').write_text(''.join(parts),encoding='utf-8')
    a1=Image.new('RGB',(1200,630),t['a1']);a2=Image.new('RGB',(1200,630),t['a2']);mask=Image.blend(Image.linear_gradient('L').rotate(90).transpose(Image.FLIP_LEFT_RIGHT).resize((1200,630)),Image.linear_gradient('L').resize((1200,630)),.5)
    image=Image.composite(a2,a1,mask).convert('RGBA');over=Image.new('RGBA',(1200,630),(0,0,0,0));od=ImageDraw.Draw(over);rgb=tuple(int(t['chu'][k:k+2],16) for k in (1,3,5));ink=rgb+(46,)
    for r in rings:od.ellipse((1040-r,500-r,1040+r,500+r),outline=ink,width=2)
    od.polygon(pts,outline=ink,width=2)
    for a_,b_,c_,d in ticks:od.line((a_,b_,c_,d),fill=ink,width=2)
    image=Image.alpha_composite(image,over).convert('RGB');draw=ImageDraw.Draw(image);serif=ImageFont.truetype(str(fontdir/'literata.woff'),fs)
    draw.text((64,82),label,font=uiw,fill=t['phu'])
    for i,line in enumerate(lines):draw.text((104 if name=='ca-dao' and i%2==0 else 64,ys[i]-fs),line,font=serif,fill=t['chu'])
    draw.rounded_rectangle((64,532,108,576),radius=11,fill=t['chu']);draw.text((76,535),'P',font=ImageFont.truetype(str(fontdir/'literata.woff'),28),fill=t['a1'])
    draw.text((124,529),'Thư viện Văn học Dân gian Việt Nam',font=ImageFont.truetype(str(fontdir/'literata.woff'),24),fill=t['chu']);draw.text((124,566),'phongdao.com · Bản lưu truyền phổ biến · dữ liệu thử',font=ImageFont.truetype(str(fontdir/'bevietnam.woff'),18),fill=t['phu'])
    image.save(ROOT/'social'/f'{name}-1200x630.png')

assets=[
('Literata','The Literata Project Authors, 2017','SIL OFL 1.1','https://github.com/googlefonts/literata','assets/literata-OFL.txt; subset Latin + Vietnamese; giữ font gốc để tái xuất'),
('Be Vietnam Pro','The Be Vietnam Pro Project Authors, 2021','SIL OFL 1.1','https://github.com/bettergui/BeVietnamPro','assets/bevietnampro-OFL.txt; Regular + SemiBold'),
('Bản đồ Việt Nam SVG','Tài sản có sẵn trong repo ca-dao tại commit ghim','Chưa thấy metadata tác giả/giấy phép riêng trong asset đã nhận',BASE+'wordpress/plugins/ca-dao/assets/ban-do-vn.svg','Giữ nguyên hình bản đồ, Hoàng Sa và Trường Sa; không vẽ lại; cần xác nhận ghi công trước production'),
('Nội dung thử','Fixture của dự án + chuỗi tổng hợp bổ sung nhận diện rõ','Chỉ dữ liệu thử, không là tư liệu dân gian',BASE+'wordpress/tests/mau-giao-dien.php','Giữ lời mẫu; không gán sách/thời gian/tác giả thật'),
('Icon và social graphic','Tạo bằng SVG và mã bố cục trong gói này','Tài sản thiết kế của gói bàn giao','source/common.mjs; source/package-handoff.py','Không ảnh kho bên thứ ba, không ảnh AI; social PNG có SVG chỉnh sửa được')]
with (DOCS/'asset-credits.csv').open('w',encoding='utf-8-sig',newline='') as f:
    w=csv.writer(f);w.writerow(['Tài sản','Tác giả/nguồn','Giấy phép/trạng thái','URL','Ghi chú']);w.writerows(assets)

components=[
('Khung site','publicHeader / publicFooter / bottom / bread','Desktop nav; mobile 4 đích; menu details; skip link; active route'),
('Ô tìm / kết quả','runSearch / resultCard','Idle, debounce, match, groups, typo fixture, pending, error; aria-live; Enter submit'),
('Bộ lọc / phân trang','catalogue / filters','Chip bỏ lọc, xoá tất cả, sort, trang cuối, rỗng; select native'),
('Lời / thơ / tục ngữ','verse / level / meaning','Dòng nguồn span.line; lục thụt; tục ngữ nghĩa sớm; vè 60/220/990'),
('Mức kiểm / cờ','level / flag','Câu chữ có căn cứ; nhãn + chữ, không chỉ màu; cờ đi theo bài'),
('Chú thích / dị bản','dialog / details / .variant','Popover desktop, sheet mobile; Esc; focus; ins/del, không đúng/sai'),
('Tư liệu / nguồn','materials / A11 sourcePreview','Theo từng lớp; 3 chính sách; public/admin tách từ server khi production'),
('Tuyển tập','collectionItem / A05','Dùng lại lời/nghĩa; 8/30; missing; cờ; ngưỡng công khai'),
('Trích dẫn','cite / citation / copy','Normal/APA; lời-only; notes; answer opt-in; preview/readonly; status + fallback'),
('Lưu / share / Aa','saved / aa / applyTheme','Local; blocked; removed; 22/28/34; hệ thống/sáng/tối; share native hoặc link'),
('Trình chiếu / cùng bé','openMode / renderMode','Prev/Next; tiến độ; focus trap; Esc; reduced motion; không âm thanh tự phát'),
('Đố / trò chơi','quiz / gameParams / gameSteps','10 câu, đáp án details đóng; tuổi/số trẻ/nơi/đồ dùng; không điểm'),
('Đóng góp','contributionForm','6 loại, consent, validation, pending/success, network/rate-limit; giữ buffer'),
('Thẻ việc','A02 / review','Human-approved rõ; reject reason; defer; conflict; Undo 10 giây'),
('Editor','A04 / editorData / publish','10 vùng cấu trúc; manual save; diff trước/sau; nhắc không chặn; mất phiên/mạng'),
('Trạng thái / điều khiển','btn / chip / notice / empty','Normal, hover, focus-visible, selected, expanded, loading, disabled, error/success'),
('Mỗi câu một dòng','verse / fit.js','data-fit, data-nhom (cùng cỡ), data-max (px, mode, kid, w-tỷ lệ), data-san; theo vế; thụt treo dự phòng'),
('Sao chép / Ý nghĩa','copyBtn / meaning / editorStrip','Chép lời có thụt và nguồn; Sửa / Thêm ý nghĩa tại chỗ, có hỗ trợ AI, lưu / huỷ'),
('Ảnh để chia sẻ','shareBlock / .acs','3 khổ, 3 nền theo thể loại, kèm ý nghĩa, câu đố ẩn đáp án; chạy được khi tắt JavaScript cho khổ (CSS :has)')]
dump(DOCS/'components.json',[dict(zip(['component','implementation','variants'],row)) for row in components])

sizes={n:{'bytes':(EXP/n).stat().st_size,'gzip_bytes':len(gzip.compress((EXP/n).read_bytes()))} for n in ['PHONGDAO_V2_Prototype.html']}
sizes['public_runtime']={'bytes':(DIST/'assets/runtime.js').stat().st_size,'gzip_bytes':len(gzip.compress((DIST/'assets/runtime.js').read_bytes()))}
sizes['styles']={'bytes':(DIST/'assets/styles.css').stat().st_size,'gzip_bytes':len(gzip.compress((DIST/'assets/styles.css').read_bytes()))}
sizes['woff_total_bytes']=sum(p.stat().st_size for p in (DIST/'assets').glob('*.woff'))
dump(ROOT/'qa/size-report.json',sizes)

doccss='''@font-face{font-family:Literata;src:url(data:font/woff;base64,FONT)}*{box-sizing:border-box}html{scroll-behavior:smooth}body{background:#FEFCF8;color:#14120F;font:16px/1.65 system-ui,sans-serif;margin:0}header,main,footer{max-width:1160px;margin:auto;padding:36px 28px}header{border-bottom:1px solid #e8e0d0}h1,h2,h3{font-family:Literata,Georgia,serif;line-height:1.35}h1{font-size:48px;font-weight:500;margin:16px 0}h2{font-size:32px;margin:56px 0 20px}h3{font-size:23px}a{color:#5E3AAE;text-underline-offset:3px}p{max-width:80ch}.eyebrow{letter-spacing:.08em;color:#5E3AAE;font-size:14px}.lede{font-size:20px}.notice{border-left:4px solid #5E3AAE;border-radius:0 10px 10px 0;background:#f6f2fd;padding:18px 24px}.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}.metric{padding:24px;border:1px solid #e8e0d0;border-radius:14px;background:#fff}.metric strong{font:42px Literata,serif;color:#5E3AAE;display:block}nav{display:flex;flex-wrap:wrap;gap:10px 24px;padding:24px 0}details{border-top:1px solid #e8e0d0;padding:18px 0}summary{cursor:pointer;font-weight:650;padding:8px 0}details[open]>summary{color:#5E3AAE}.spec-body{padding:12px 0 20px}.spec-body p{margin:10px 0}.table-wrap{overflow:auto}table{border-collapse:collapse;width:100%;font-size:14px}th,td{text-align:left;vertical-align:top;padding:14px 12px;border-bottom:1px solid #e8e0d0}th{background:#f6f2ea;min-width:105px}code{font-family:ui-monospace,monospace;font-size:13px;overflow-wrap:anywhere}pre{white-space:pre-wrap;font:14px/1.7 system-ui,sans-serif;background:#f6f2ea;padding:18px;max-width:100%;overflow-wrap:anywhere}dt{font-weight:650;margin-top:14px}dd{margin:4px 0 12px}.small{font-size:14px;color:#5A5652}.swatches{display:flex;flex-wrap:wrap;gap:12px}.swatch{padding:16px;border:1px solid #8C8478;min-width:160px;border-radius:10px}img{max-width:100%;height:auto}footer{border-top:1px solid #e8e0d0}input{font:inherit;padding:12px;border:1px solid #8C8478;background:#ffffff;max-width:100%;width:460px}button{font:inherit;padding:10px 16px;min-height:44px;border-radius:10px;border:1px solid #8C8478;background:#f6f2ea;cursor:pointer}summary:focus-visible,a:focus-visible,button:focus-visible,input:focus-visible{outline:3px solid #5E3AAE;outline-offset:3px}@media(max-width:767px){header,main,footer{padding:24px 20px}h1{font-size:36px}.grid{grid-template-columns:1fr}h2{font-size:28px}}@media print{body{background:#fff}nav,input,button{display:none}header,main,footer{max-width:none;padding:10px 0}h2{break-after:avoid}details{break-inside:auto}details::details-content{content-visibility:visible;display:contents}@page{size:A4;margin:16mm}}'''.replace('FONT',font_b64)
body='''<header><p class="eyebrow">PHONGDAO V2 / BÀN GIAO UX–UI / BẢN CHỐT 2.1 / 02.11.2026</p><h1>Một hệ thống để đọc,<br>hiểu và biên soạn.</h1><p class="lede">Bộ nguyên mẫu chỉnh sửa được, làm chuẩn cho theme WordPress V2 và Bàn biên tập.</p><div class="notice"><strong>Trạng thái: bản chốt.</strong> Mọi lựa chọn đã chốt ngày 02/11/2026: bảng tím – lục, bỏ “Đọc theo tâm trạng”, thụt câu lục, nơi lưu (D-092); mỗi câu một dòng, “Ý nghĩa”, nhãn mức kiểm, nút Sao chép, Ảnh để chia sẻ, “Lời này từ đâu?”, Khám phá là trang riêng, nền khối và nút gọn (D-093). Tất cả tư liệu trong bản mẫu là dữ liệu thử; chưa triển khai WordPress, chưa công khai site.</div><nav><a href="#changes">Điều chỉnh 02/11</a><a href="#changes-2">Chốt lần 2</a><a href="#start">Mở bộ thiết kế</a><a href="#system">Hệ thống</a><a href="#architecture">Nối WordPress</a><a href="#specs">62 đặc tả</a><a href="#coverage">Bảng phủ</a><a href="#qa">Kết quả kiểm</a><a href="#choices">Lựa chọn</a><a href="#credits">Ghi công</a></nav></header><main>'''
body+='<section id="changes"><h2>Điều chỉnh 02/11/2026 (D-092)</h2><p>Bản 1.0 (28/10) giữ nguyên danh mục 66 màn, 203 trạng thái, luồng và lằn ranh. Bản 2.0 đổi lớp nhìn theo hai bản mẫu HTML người sáng lập cung cấp: <em>Phongdao-V2.html</em> (bảng màu, bố cục) và <em>Ca-dao-tham-khao.html</em> (cách sắp nội dung trang bài).</p>'+table(['Phần','Bản cuối','Lấy từ'],[['Bảng màu','Tím #5E3AAE (thương hiệu, nút chính, liên kết), lục #1F6B4A (nghĩa, “đã đối chiếu”), tô vàng #FFF3B0 (từ có chú thích), giấy #FEFCF8; bộ tối riêng. Bỏ bảng A/B.','Phongdao-V2.html'],['Hình khối','Thẻ trắng bo 14 px, bóng nhẹ; nút và ô nhập bo 10 px; nhãn, chip tròn hẳn; logo chữ P trong ô vuông tím.','Phongdao-V2.html'],['Khung site','Thêm thanh thể loại dạng viên thuốc dưới đầu trang (Trang chủ · Tất cả · Ca dao · Tục ngữ – Thành ngữ · Đồng dao · Vè · Câu đố · Tuyển tập), trượt ngang trên điện thoại. Menu Đọc · Khám phá · Tra cứu và thanh dưới 4 mục giữ nguyên.','Phongdao-V2.html'],['Trang chủ','Hai cột: tên, khẩu hiệu, ô tìm lớn, “Bạn là” bên trái; 3 thẻ lối tìm (vài chữ, vùng, chủ đề) bên phải. Câu hôm nay thành thẻ giữa trang; thêm lưới “Đọc theo dịp”, tuyển tập nổi bật có số câu, thể loại dạng ô. Không có “Đọc theo tâm trạng”.','Phongdao-V2.html'],['Trang tác phẩm','Đầu trang căn giữa có nhãn; thẻ lời (số dòng ngoài lề trên máy tính), dòng mức kiểm dạng nhãn, Hiểu nhanh nền lục; cột công cụ dính bên phải (Trích dẫn, Lưu, Chia sẻ, Trình chiếu, cỡ chữ Vừa / Lớn / Rất lớn), trên điện thoại thành hàng 4 nút dưới lời.','Phongdao-V2.html'],['Khối nội dung','Từ khó dạng ô; Dị bản thu gọn; tục ngữ có Giải nghĩa hai ô Nghĩa đen | Nghĩa bóng, Dùng khi, Câu gần nghĩa · trái nghĩa mở sẵn; thêm Trích dẫn nhanh (hiện sẵn câu trích, nút sao chép) và Thẻ chia sẻ (xem trước ảnh 1200×630, Tải ảnh); Đọc tiếp dạng thẻ có lý do.','Ca-dao-tham-khao.html'],['Ảnh chia sẻ','Nền tím đậm #4B2F8A cố định, chữ trắng, logo P; giữ thể loại, 4 dòng đầu, dòng mức kiểm, phongdao.com.','Bản cuối'],['Sửa lỗi','Từ có chú thích không còn đè chữ bên cạnh (text-indent của dòng thơ không lan vào từ); vùng chạm 44 px mở rộng bằng lớp vô hình, không làm giãn dòng.','Bản cuối']])+'<h3>Không lấy từ hai bản mẫu</h3><ul><li>Dòng “chỉ văn bản đã đối chiếu”, nhãn “Đã kiểm chứng”, “Phổ biến cao”: khẳng định không đúng dữ liệu; mức kiểm do máy tính từ dữ liệu.</li><li>Số lượt lưu: site không biết ai lưu gì. Tên thật người góp, người duyệt: chỉ bút danh.</li><li>Đăng ký bản tin qua email, “Tạo bộ sưu tập của riêng bạn”: cần thu email hoặc tài khoản người đọc.</li><li>Phông Google, React/Tailwind, emoji làm biểu tượng: phông tự lưu trữ, JavaScript trang bài ≤ 21 KB, biểu tượng SVG đồng bộ.</li><li>Nhãn kỹ thuật (“Scheme: 6-8”, mã bài, số phiên bản), nút Nghe (đợt 5), “Đọc theo tâm trạng”, thể loại Thơ.</li></ul></section>'
body+='<section id="changes-2"><h2>Chốt 02/11/2026, lần 2 (D-093)</h2><p>Người sáng lập yêu cầu thêm; agent đề xuất phần còn mở và tự rà soát độc lập trong vai chủ site và người đọc.</p>'+table(['Phần','Bản chốt'],[['Mỗi câu một dòng','fit.js co cỡ chữ lời vừa khung ở trang bài, Câu hôm nay, tuyển tập, ảnh chia sẻ, trình chiếu, Đọc cùng bé; trần = cỡ người đọc chọn; sàn 15 px (14 px dưới 360 px); dưới sàn tục ngữ ngắt theo vế, thể khác thụt treo; các khổ một bài cùng cỡ. Khi tắt JavaScript, CSS ước lượng theo số ký tự dòng dài nhất (--dai). Lề điện thoại 16 px.'],['Ý nghĩa','“Hiểu nhanh” hiển thị là “Ý nghĩa”. Người biên soạn đăng nhập thấy dải nhắc vai và nút Sửa / Thêm ý nghĩa ngay trên trang đọc.'],['Nhãn mức kiểm','“Bản lưu truyền phổ biến ⓘ” hoặc “Đã đối chiếu: <sách>, tr. N ⓘ”; ⓘ giải thích và mời gửi số trang; chi tiết “chưa đối chiếu với sách in” trong “Lời này từ đâu?”.'],['Sao chép','Bỏ khối Trích dẫn nhanh. Nút Sao chép ở hàng đầu thẻ lời: chép lời giữ thụt câu lục + thể loại + tên thư viện + đường dẫn. Tuyển tập có Sao chép cả bộ.'],['Ảnh để chia sẻ','Xem trước trực tiếp; khổ ngang / vuông / dọc; 3 nền theo thể loại (Trống đồng, Giấy dó, Đêm sao, Đồng lúa, Nắng, Sông nước) với hoạ tiết SVG; Kèm ý nghĩa; câu đố không lộ đáp án. Nút Chia sẻ mở hộp: liên kết · ứng dụng · tạo ảnh.'],['Nền và nút','Khối có nền nhạt theo vai; thẻ theo màu thể loại; nút 44 px, chữ 15 px, nhãn không xuống dòng; hàng nút là lưới cột đều; Bạn muốn góp gì? là lưới 6 nút.'],['Điều hướng','Bỏ menu “Đọc ▾” (thanh thể loại thay); Khám phá trên điện thoại là trang riêng; liên kết tới khối thu gọn tự mở khối.'],['Rà soát độc lập','Đọc tiếp lên trước Ảnh để chia sẻ; trình chiếu rộng tới 1240 px; thẻ lối vào dạng hàng ngang trên điện thoại; nhãn Bàn biên tập đổi theo “Ý nghĩa”; sửa tiêu điểm khi đóng hộp Chia sẻ.']])+'</section>'
body+='<section id="start"><h2>Mở và duyệt</h2><div class="grid"><div class="metric"><strong>62</strong>Mẫu sản phẩm: 31 người đọc, 21 quản trị, 10 đợt 5</div><div class="metric"><strong>'+str(total_states)+'</strong>Biến thể màn hình, tính cả 4 màn hệ thống thiết kế</div><div class="metric"><strong>15</strong>Hành trình để kiểm; tiêu chí thời gian cần thử người thật</div></div><ol><li>Mở <code>exports/PHONGDAO_V2_Prototype.html</code> bằng trình duyệt (sinh bằng <code>node source/build.mjs</code>). Không cần cài phụ thuộc để xem.</li><li>Chọn tám màn ★ trước; chuyển khung 390 / 1440, trạng thái, sáng/tối. Bấm “So sáng / tối” để đặt hai chế độ cạnh nhau.</li><li>D01 là token và component. D03 hướng dẫn duyệt. D04 là bốn ảnh chia sẻ.</li><li><code>source/</code> là mã chỉnh sửa được. <code>tokens/</code> chứa CSS/JSON. <code>docs/coverage.csv</code> và <code>screen-specs.json</code> để rà từng yêu cầu.</li><li><code>dist/no-js/index.html</code> là bản đọc đã loại script. <code>dist/print/</code> có phiếu A4 và mẫu vè/tuyển tập dài đã mở toàn nội dung.</li></ol><p>Công cụ trên cùng chỉ dành cho duyệt thiết kế. Đổi màn/trạng thái trong công cụ sẽ nạp lại bản mẫu; thay đổi chưa lưu trong vùng soạn có thể mất. Đi bằng liên kết của sản phẩm có cảnh báo phần đang sửa. Không dùng trình quản lý prototype làm giao diện người đọc production.</p></section>'
body+='<section id="system"><h2>Hệ thống thiết kế</h2><p><strong>ĐÃ CHỐT:</strong> bảng tím – lục sáng và tối (D-092), Literata / Be Vietnam Pro tự lưu trữ, thụt câu lục, cột đọc 38em, lời ≥22 px, UI ≥17 px, phụ chú ≥15 px. <strong>ĐỀ XUẤT THỂ HIỆN:</strong> kích thước chi tiết, bo góc, bóng, gutter, vị trí cột công cụ và wrap dòng. <strong>KHÔNG CÒN ĐIỂM CHỜ CHỌN</strong> (D-093). Lời mỗi câu một dòng: cỡ ưu tiên 22 px, tự co vừa khung tới 15 px (14 px khi màn hẹp hơn 360 px).</p><div class="swatches">'+''.join('<div class="swatch" style="background:'+light[k]+';color:'+(light['muc'] if k in ['giay','to'] else '#FFFFFF')+'">'+n+'<br>'+light[k]+'</div>' for k,n in [('giay','Giấy'),('muc','Mực'),('tim','Tím'),('luc','Lục'),('to','Tô vàng'),('loi','Lỗi')])+'</div>'
body+=table(['Token','Giá trị','Hợp đồng'],[['Chữ','15 / 17 / 22 / 28 / 36 / 48 / 64 px','Aa lời 22 / 28 / 34 px; giữ dấu tiếng Việt, không cắt chữ'],['Khoảng cách','4 / 8 / 12 / 16 / 24 / 32 / 48 / 64 px','Lề mobile20, tablet24, desktop32; breakpoint768/1024'],['Đọc','38em; line-height 1.7; trang tác phẩm 1040 px gồm cột công cụ 232 px','Lục thụt 1.6em, mobile .9em. Số dòng ngoài lề trên máy tính, ẩn trên điện thoại. Wrap thị giác hanging 1em; không sửa dòng nguồn'],['Điều khiển','48px chính / 44px khác','Focus 3px, offset 4; không dựa vào màu; nút thay kéo thả'],['Ảnh / icon','SVG nét 1.6, khung 24, thường hiển thị 20','Có nhãn; thiếu ảnh thì bỏ cả khối; không tải bản đồ ngoài; không dùng emoji làm biểu tượng'],['Màu','Tím #5E3AAE / lục #1F6B4A / tô #FFF3B0 / giấy #FEFCF8','Tím: thương hiệu, nút chính, liên kết. Lục: nghĩa, “đã đối chiếu” (luôn kèm chữ và biểu tượng). Tô vàng: từ có chú thích. Bộ tối riêng; ảnh chia sẻ nền #4B2F8A cố định'],['Hình khối','Bo 10 / 14 px / tròn; bóng 0 6px 20px 5%','Thẻ trắng trên giấy; viên thuốc cho nhãn, chip, thanh thể loại'],['Motion','Reduced motion tắt chuyển động','Không âm thanh tự phát; không animation làm điều kiện hiểu nội dung']])
body+='<h3>Thành phần và biến thể</h3>'+table(['Nhóm','Nguồn chỉnh sửa','Biến thể / hành vi'],components)+'</section>'
body+='''<section id="architecture"><h2>Nối với WordPress V2</h2><p>Đích production theo docs/26: theme khối độc lập, <code>theme.json</code>, khối động render phía server; JavaScript thuần tối thiểu ở trang công khai. Bàn biên tập React dùng thư viện WordPress có sẵn. Tác phẩm dùng editor cấu trúc, không có Gutenberg dự phòng.</p><p>Namespace khối là <code>cadao/*</code>. Bảng bên dưới giữ tên nghiệp vụ hiện hành; template cụ thể và permalink phải được dev đối chiếu lại trong repo trước triển khai. Không đưa framework của công cụ duyệt vào sản phẩm.</p>'''
body+=table(['Nhóm thiết kế','Template / bề mặt','Khối'],[['R01','front-page','cadao/loi-vao, vai, cau-hom-nay, ban-co-biet, danh-sach'],['R05–09','single-tac_pham-{the-loai}; single-tac_pham fallback','loi, muc-kiem, nghia, chu-thich, di-ban, boi-canh, doan, thuc-hanh'],['Cuối tác phẩm','Thành phần dùng chung','hanh-dong, tu-lieu, bien-soan, thuoc-tuyen-tap, doc-tiep, gop'],['Kho / taxonomy','archive-tac_pham; taxonomy-the_loai/chu_de/vung/mo_tip/muc_tu/tro_choi','bo-loc, the-bai, danh-sach'],['Tuyển tập','archive-tuyen_tap; single-tuyen_tap','tuyen-tap, hanh-dong'],['Tìm / bản đồ / đố','search hoặc page theo route plugin','tim, ban-do, do-vui'],['Tĩnh / phân tích / lỗi','page; single-phan_tich;404; plugin maintenance','Giữ chính sách và quyền của nội dung'],['A01–A21','Phongdao trong wp-admin, capabilities phía server','Các màn React nghiệp vụ; không phát minh khối Gutenberg cho editor'],['F01–F10','Đợt 5 có điều kiện','Hub Nghe, Kể cho bé và UI tải dữ liệu vẫn là đề xuất']])
body+='''<h3>Hợp đồng dữ liệu và quyền</h3><ul><li>Tác phẩm là nguồn dùng lại cho tuyển tập, bộ câu, kết quả và trình chiếu. Nghĩa không nhân bản thành nhiều bản độc lập.</li><li>Public serializer chỉ xuất lớp đã được phép công khai. Nguồn ẩn phải bị loại ở server trước HTML/JSON; không gửi rồi ẩn bằng CSS. Nội bộ cần capability và nonce; prototype không thay kiểm quyền.</li><li>Cấp L0–L3 do dữ liệu tính trong quản trị. Chất lượng nghĩa khác mức đối chiếu lời. Không biến L2 thành “đã xác minh”.</li><li>Quyết định đăng/duyệt/gộp do người bấm. “AI, chưa duyệt” không thể xuất hiện trong lớp nghĩa public khi chưa có xác nhận. Đăng và lưu dùng response thành công thật; lỗi giữ buffer.</li><li>Lưu bài, cỡ chữ, sáng/tối, vị trí đọc, vai và tiến độ đố ở localStorage; bộ nhớ chặn phải báo rõ. Không đo vai/lưu/độ tuổi.</li><li>Chỉ năm sự kiện được đặc tả: <code>tim_khong_ket_qua</code>, <code>trich_dan_sao_chep</code>, <code>chu_giai_mo</code>, <code>chia_se</code>, <code>mong_co_cau</code>, cùng lượt đọc. Nguyên mẫu không gửi telemetry.</li><li>Undo 10 giây là UI hợp đồng; backend phải có khả năng khôi phục và lịch sử. Gộp cần xác nhận bài giữ, 301, dữ liệu dồn và bản chuyển vào thùng rác.</li></ul><h3>Giới hạn và chuẩn bị production</h3><p>Prototype chứa cả 66 màn và dữ liệu mô phỏng để duyệt offline. Nó không đạt ngân sách một trang đọc production bằng cách giữ nguyên runtime: runtime thô '''+str(sizes['public_runtime']['bytes'])+''' byte, gzip '''+str(sizes['public_runtime']['gzip_bytes'])+''' byte; tổng font WOFF '''+str(sizes['woff_total_bytes'])+''' byte; HTML một tệp '''+str(sizes['PHONGDAO_V2_Prototype.html']['bytes'])+''' byte.</p><p>Dev cần tách module theo tính năng/trang, bỏ viewer/admin khỏi public, subset WOFF2 phù hợp và tải italic khi cần. Đo riêng trang tác phẩm ≤200KB, JS ≤21KB, tuyển tập30 không ảnh ≤300KB; CLS≤0.1, LCP p75 ≤ 2.5s, INP p75 ≤ 200ms trên triển khai thật. Chưa có số đo field hoặc chứng nhận đạt các chỉ số đó.</p></section>'''
body+='<section id="specs"><h2>Đặc tả từng màn hình</h2><p>Mỗi mục có route, nguồn, thứ tự khối thực tế, trạng thái, tương tác, responsive, không JavaScript, in, quyền và điều kiện. Phần yêu cầu trích nguyên từ brief để dev đối chiếu trực tiếp; đó là tiêu chí, không phải chứng nhận mọi hành vi backend đã được làm.</p><label for="spec-filter">Lọc theo mã hoặc tên</label><br><input id="spec-filter" placeholder="R05, nguồn, lớp học…"><p class="small">Các mục D là công cụ thiết kế. 62 mục R/A/F là phạm vi sản phẩm.</p>'
for s in specs:
    body+='<details class="screen-spec" id="'+s['id']+'"><summary>'+s['id']+' · '+H(s['title'])+'</summary><div class="spec-body"><dl>'
    rows=[('Route / đích nối',s['route']),('Mục tiêu',s['objective']),('Mặc định / trạng thái',s['default_state']+' / '+', '.join(s['states'])),('Thứ tự khối trong bản mặc định',' → '.join(s['block_order_rendered'])),('Điều khiển / lối đi',' · '.join(s['interaction_labels'])),('Mobile / desktop',s['responsive']),('Thiếu / trống / lỗi',s['empty_error']),('Không JavaScript',s['no_js']),('In',s['print']),('Quyền xem',s['permissions']),('Điều kiện xuất hiện',s['visibility_gate']),('WordPress',s['wordpress_template']),('Khối',s['wordpress_blocks']),('Hành trình',', '.join(s['journeys']) or 'Màn bổ trợ / quản lý'),('Cách kiểm / giới hạn',s['verification']+' '+s['limitations'])]
    body+=''.join('<dt>'+H(k)+'</dt><dd>'+H(v)+'</dd>' for k,v in rows)+'</dl><p><a href="'+s['requirement_source']+'">Nguồn dự án ở commit ghim</a> · Brief: '+s['id']+'</p><details><summary>Yêu cầu gốc của màn từ tệp được giao</summary><pre>'+H(s['requirement_verbatim_from_user_brief'])+'</pre></details></div></details>'
body+='</section><section id="coverage"><h2>Phủ màn và chế độ dùng chung</h2><p>62 mẫu sản phẩm đã có nội dung thiết kế, không chỉ tên trong chỉ mục. Các trạng thái dùng chung một template khi hợp lý; dữ liệu chưa có ảnh/audio/scan dùng bố cục chỉ chữ hoặc thông báo thiếu, không giả tài sản đã tồn tại.</p>'+table(['Mã','Chế độ','Ở đâu','Hợp đồng','Đã kiểm'],shared)
body+='<h3>Hành trình J01–J15</h3>'+table(['ID','Các màn','Thao tác','Bằng chứng / giới hạn'],[(k,' → '.join(v[0]),v[1],v[2]) for k,v in flows.items()])+'</section>'
reflow_text=(f"Đo lại {REFLOW['date']} sau D-093: {REFLOW['measurements']} lần mở trang ở {REFLOW['screens']} mẫu / {REFLOW['states']} trạng thái × {' / '.join(str(w) for w in REFLOW['widths'])}: {REFLOW['failures']} trang tràn ngang; {REFLOW.get('verse_wraps',0)} câu lời xuống dòng ngoài ý muốn, {REFLOW.get('verse_fallback_wrap',0)} khối phải dùng phương án dự phòng; {REFLOW.get('button_wraps',0)} nhãn nút gãy dòng, {REFLOW.get('button_clipped',0)} nhãn bị cắt; {REFLOW.get('script_errors',0)} lỗi JavaScript. Thanh thể loại trượt ngang trong khung riêng." if REFLOW else 'Chưa đo lại sau D-092.')
body+='<section id="qa"><h2>Kết quả kiểm tra thực tế</h2><p>Ngày kiểm: '+DATE+' (Edge/Chromium, bản xem thử nội bộ); kiểm tĩnh và tương phản sinh lại bằng tệp này. Không có cuộc thử người dùng chính thức, kiểm máy in vật lý, kiểm screen reader đầy đủ, hay số đo performance WordPress.</p>'+table(['Hạng mục','Kết quả'],[['Kiểm file',f'{len(REG)} mẫu / {total_states} trạng thái sinh được; parse JS bằng node --check; kiểm HTML nhãn, H1, ID và link nội bộ. Chi tiết trong qa/static-checks.json.'],['Reflow mặc định',reflow_text+' Đây không thay kiểm zoom 200%/bàn phím ảo trên thiết bị thật.'],['Hành trình','Kết quả từng J ở bảng trên; các thời gian là mục tiêu chờ đo, không là thành tích đã đạt.'],['Tương phản','Đo màu chữ, chữ trên nút và nhãn đặc, chữ trên nền nhạt tím / lục / tô vàng và control-line của bảng tím – lục sáng và tối, cùng chữ trên ảnh chia sẻ. qa/contrast.json lưu từng tỷ lệ.'],['Trợ năng','Native details, dialog, label, skip link, focus, Esc/return-focus, aria-live, reduced-motion được thiết kế/kiểm có chọn lọc. Chưa tuyên bố WCAG 2.2 AA toàn bộ.'],['Không JavaScript','Xuất bản không script. Nội dung và details vẫn là HTML. Tìm/gửi cần server production; bản tĩnh không giả kết quả server.'],['In','Có CSS A4, bản in đã mở details, không lặp mục lục trong phần lời. Chưa kiểm phân trang vật lý trên mọi trình duyệt / máy in.'],['Mạng / quyền riêng tư','Font và map tại chỗ. Không scripts/fonts remote. Không API key, telemetry hoặc nội dung kho thật.']])
failed=[x for x in contrasts if not x['pass']]
body+='<p class="notice">Màu kiểm không đạt ngưỡng: '+str(len(failed))+'. Lỗi static còn ghi nhận: '+str(sum(len(v) for v in issues.values()))+'. Kết quả này được sinh lại từ file bàn giao, không là điểm chất lượng UX.</p>'
body+='''<h3>Kế hoạch thử với 5 người</h3><p>Hai giáo viên, một phụ huynh, hai học sinh/người trẻ; 15 phút/người. Dùng J01–J05 cho tất cả, J02/J14 cho giáo viên, J03 cho phụ huynh; quản trị J10–J13 do chủ dự án/người biên soạn thử riêng. Không gợi ý vị trí nút trước khi người thử tự tìm.</p><ol><li>Ghi bắt đầu/kết thúc, hoàn thành không trợ giúp, số lần đi sai, câu chữ người thử không hiểu.</li><li>J01 ≤ 10 giây; J02 ≤ 3 phút; J03 ≤ 1 phút; J10 ≤ 60 giây/thẻ. Mục tiêu chung≥85% tác vụ.</li><li>Sau J05 hỏi: lời theo đâu, nghĩa do ai viết, phần nào chưa đối chiếu? Chấm hiểu đúng bằng câu trả lời, không chỉ có click.</li><li>Lục bát đã chốt thụt câu lục (D-092, đóng Q-006); chỉ ghi lại nếu người thử đọc khó ở 390 / chữ 34.</li><li>Ghi vấn đề theo mức chặn tác vụ / gây hiểu sai / chậm thao tác / thị giác; sửa rồi chạy lại tác vụ liên quan.</li></ol>'''
body+='''<h3>Phần còn cần đầu vào hoặc nghiệm thu</h3><ul><li>Corpus thật, hồ sơ nguồn/quyền, trang sách, SGK, bút danh, ảnh đáp án có ghi công, audio và scan được phép dùng. Mẫu có ảnh/audio/scan hiện nêu đúng tình trạng thiếu tài sản.</li><li>Tìm full-text/fuzzy thật, đặc biệt ca “ba co doi gai” trong brief; prototype mới có tìm không dấu và sửa một chữ fixture “tuc ngu mauu”.</li><li>Chính sách công khaiR27/R28 cần chủ dự án thay bằng nội dung hiện hành đã duyệt; bản mẫu không phải bản tư vấn pháp lý.</li><li>Ghi công riêng của SVG bản đồ cần xác nhận. Font đã kèm OFL.</li><li>WordPress REST/auth/capabilities, gửi biểu mẫu, Turnstile, giới hạn gửi, tác vụ lấy nguồn và migration cần dev triển khai/kiểm thật.</li><li>Voice/scans và hub Nghe, Kể cho bé, dữ liệu mở chỉ thiết kế đợt 5; chưa được mở menu ra mắt.</li></ul></section>'''
body+='<section id="choices"><h2>Lựa chọn của chủ dự án</h2>'+table(['Điểm','Bản chốt','Quyết định'],[['Màu','Tím – lục, sáng và tối; nền khối theo vai; màu thể loại','D-092, D-093'],['Đọc theo tâm trạng','Không làm','D-092'],['Lục bát','Thụt câu lục, như sách in','D-092'],['Mỗi câu một dòng','Co chữ vừa khung, sàn 15 px (14 px dưới 360 px); dưới sàn ngắt theo vế / thụt treo','D-093'],['Tên khối nguồn gốc','Lời này từ đâu?','D-093'],['Khám phá trên điện thoại','Trang riêng /kham-pha/','D-093'],['Hiểu nhanh','Hiển thị là “Ý nghĩa”; người biên soạn sửa tại chỗ','D-093'],['Nhãn mức kiểm','“Bản lưu truyền phổ biến ⓘ” / “Đã đối chiếu: <sách>, tr. N ⓘ”','D-093'],['Trích dẫn nhanh','Bỏ; nút Sao chép trong thẻ lời','D-093'],['Ảnh để chia sẻ','3 khổ × 3 nền theo thể loại, hoạ tiết SVG của dự án','D-093'],['Nơi lưu bộ thiết kế','design/v2/: mã nguồn, token, đặc tả','D-092'],['Đợt 5','Tách khỏi ra mắt','Chốt khi tới đợt 5']])+'</section>'
body+='<section id="credits"><h2>Nguồn và ghi công</h2>'+table(['Tài sản','Tác giả / xuất xứ','Giấy phép / trạng thái','Nguồn','Ghi chú'],[[H(x[0]),H(x[1]),H(x[2]),('<a href="'+x[3]+'">Nguồn</a>') if x[3].startswith('http') else H(x[3]),H(x[4])] for x in assets])
body+='<h3>Tài liệu đã đọc</h3><p>Bản 1.0 đối chiếu từ các tệp ghim commit <code>44f34b0</code>; bản 2.0 đọc thêm docs/01 (D-092) trên nhánh <code>'+COMMIT+'</code> và hai bản mẫu HTML người sáng lập cung cấp (không lưu vào repo): AGENTS; docs/00,01,26,27,28,29; token và phần kế thừa docs/20; backlog docs22; fixture PHP. Bản tóm lược brief của người dùng là cơ sở đối chiếu đầy đủ phạm vi. Nghiên cứuUX và docs/25 được brief dẫn; không tuyên bố đã kiểm toàn bộ từng đoạn nếu không có bản tải trong gói.</p><p>Ưu tiên: yêu cầu phiên → quyết định V2 mới nhất → docs/00/26–29 → phần còn hiệu lực docs/20/25 → nghiên cứu/MVP. Không đổi trạng thái task READY thành đã triển khai. Không sửa repo hoặc site thật.</p></section></main><footer><p>Phongdao V2 · Bộ thiết kế bản chốt 2.1 · 02/11/2026 · bản 1.0 ngày 28/10/2026</p><p class="small">Định dạng bàn giao thực tế: HTML/CSS/JavaScript + JSON/CSV + SVG/PNG. Không có tệp Figma giả. Có thể chỉnh sửa trực tiếp và tái build bằng Node.</p></footer>'
doc='<!doctype html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Phongdao V2 — Đặc tả và bàn giao UX/UI</title><style>'+doccss+'</style></head><body>'+body+'<script>document.querySelector("#spec-filter").addEventListener("input",e=>{const q=e.target.value.toLocaleLowerCase("vi");document.querySelectorAll(".screen-spec").forEach(d=>d.hidden=!d.querySelector("summary").textContent.toLocaleLowerCase("vi").includes(q))});window.addEventListener("beforeprint",()=>document.querySelectorAll("details").forEach(d=>d.open=true));</script></body></html>'
(EXP/'PHONGDAO_V2_Handoff.html').write_text(doc,encoding='utf-8')
(DOCS/'handoff.html').write_text(doc,encoding='utf-8')
(DIST/'handoff.html').write_text(doc,encoding='utf-8')

readme='''PHONGDAO V2 — BỘ THIẾT KẾ UX/UI BẢN CHỐT 2.1 — 02/11/2026
Bản 1.0 (28/10/2026) đổi lớp nhìn theo D-092 (bảng tím – lục, bỏ "Đọc theo tâm trạng", thụt câu lục) và chốt theo D-093 (mỗi câu một dòng, Ý nghĩa, nhãn mức kiểm, Sao chép, Ảnh để chia sẻ, Khám phá là trang riêng).

DỰNG VÀ MỞ
Chạy từ thư mục design/v2 (Node, không cần npm install):
  node source/build.mjs                 sinh dist/ và exports/PHONGDAO_V2_Prototype.html
  python -X utf8 source/package-handoff.py
                                        sinh đặc tả, token, no-JS, bản in, ảnh chia sẻ, qa/ và exports/PHONGDAO_V2_Handoff.html (cần lxml, Pillow)
  PD_EARLY=1 node source/build.mjs      bản tám màn ★ (sau đó dựng lại bản đủ)
dist/, exports/, social/ do máy sinh, không lưu vào repo.
Mở exports/PHONGDAO_V2_Prototype.html bằng Chrome/Edge/Firefox: chọn màn, trạng thái, 390 / 1440, sáng/tối, "So sáng / tối".

CHỈNH SỬA
source/data.mjs — registry, fixture, tên trạng thái
source/common.mjs — thành phần chung: khung site, thanh thể loại, lời, mức kiểm, cột công cụ, khối cuối bài
source/views-core.mjs — trang chủ, ca dao, tục ngữ, đồng dao, tuyển tập, tìm, Hôm nay, Duyệt nhanh, D01–D03
source/views-extra.mjs — phần còn lại R/A/F, D04
source/styles.css — token sáng/tối ở đầu tệp; lớp giao diện tím – lục ở cuối tệp
source/runtime.js, extra-runtime.js — tương tác mô phỏng
source/fit.js — mỗi câu một dòng: co cỡ chữ lời vừa khung (D-093); dùng lại được cho theme production
source/viewer.html — công cụ duyệt, chỉ dùng ở prototype
assets/ — phông WOFF tự lưu trữ (OFL), bản đồ SVG
brief/ — brief gốc của bản 1.0

GÓI TÀI LIỆU
tokens/ — CSS/JSON và bảng chuyển sang theme.json (không phải theme production).
docs/handoff.html — đặc tả, ánh xạ WordPress, giới hạn, lựa chọn.
docs/screen-registry.json, screen-specs.json, components.json, journeys.json, coverage.csv, review-decisions.csv, asset-credits.csv.
qa/ — kết quả kiểm tĩnh, tương phản, kích thước, tràn ngang; không phải chứng nhận WCAG/production.

RANH GIỚI
Không triển khai, không gửi form ra mạng, không ghi WordPress.
Role/capability trong prototype là mô phỏng; không coi là bảo mật. Không đưa bundle gồm admin lên site công khai.
Audio, scan, ảnh có quyền và corpus thật chưa được bàn giao. Mục đợt 5 chưa được bật cho ra mắt.
Chưa có thử người dùng, đo thời gian chính thức, in vật lý hoặc số đo field performance.
'''
(ROOT/'README.txt').write_text(readme)
dump(ROOT/'qa/build-summary.json',{'screens':len(REG),'states':total_states,'static_issue_count':sum(len(x) for x in issues.values()),'contrast_fail_count':len(failed),'artifacts':['PHONGDAO_V2_Prototype.html','PHONGDAO_V2_8_Man_Xem_Som.html','PHONGDAO_V2_Handoff.html'],'date':'2026-11-02'})
print(json.dumps({'screens':len(REG),'states':total_states,'issues':{k:len(v) for k,v in issues.items()},'contrast_failures':failed,'handoff_bytes':len(doc.encode())},ensure_ascii=False,indent=2))

# Trên Windows write_text ghi CRLF; repo giữ LF (.gitattributes). Chỉ chạm tệp văn bản do tệp này sinh.
for folder,pattern in [('tokens','*.*'),('docs','*.*'),('qa','*.json')]:
    for path in (ROOT/folder).glob(pattern):
        if path.suffix in ('.css','.json','.csv','.html','.txt'):
            data=path.read_bytes()
            if b'\r\n' in data:path.write_bytes(data.replace(b'\r\n',b'\n'))
readme_path=ROOT/'README.txt';data=readme_path.read_bytes();readme_path.write_bytes(data.replace(b'\r\n',b'\n'))
