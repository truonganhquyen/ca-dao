"""Kiểm Meili thật với 100 bài công khai; toàn bộ lời/truy vấn chỉ nằm trong bộ nhớ.
MEILI_EXE và PHP_BIN trỏ binary sẵn có. Output riêng chỉ chứa số đo/mã ca.
Không đánh giá ý nghĩa hay quyết định biên tập từ ca sinh tự động.
"""
import collections,hashlib,json,os,pathlib,re,secrets,subprocess,tempfile,time,unicodedata,urllib.request,urllib.error,xml.etree.ElementTree as ET
from concurrent.futures import ThreadPoolExecutor
from html.parser import HTMLParser

ROOT=pathlib.Path(__file__).resolve().parents[2]
UA={'User-Agent':'Phongdao isolated search PoC; public read only'}
PHP=os.environ['PHP_BIN'];EXE=os.environ['MEILI_EXE'];OUT=pathlib.Path(os.environ['POC_OUTPUT'])
KEY=secrets.token_hex(24);BASE='http://127.0.0.1:17771'

def fetch(url):
    with urllib.request.urlopen(urllib.request.Request(url,headers=UA),timeout=30) as r:return r.read().decode('utf-8')

class Verses(HTMLParser):
    VOID={'area','base','br','col','embed','hr','img','input','link','meta','param','source','track','wbr'}
    def __init__(self):super().__init__();self.stack=[];self.current=None;self.line=None;self.depth=0;self.skip=0;self.parts=[]
    def handle_starttag(self,tag,attrs):
        attrs=dict(attrs)
        if tag not in self.VOID:self.stack.append(tag)
        if 'verse' in attrs.get('class','').split() and self.current is None:self.current={'kind':attrs.get('data-nhom',''),'depth':len(self.stack),'lines':[],'tag':tag}
        if self.current and tag=='span' and 'line' in attrs.get('class','').split():self.line=[];self.depth=len(self.stack)
        if self.line is not None and tag=='sup':self.skip+=1
    def handle_data(self,data):
        if self.line is not None and not self.skip:self.line.append(data)
    def handle_endtag(self,tag):
        if tag=='sup' and self.skip:self.skip-=1
        if self.line is not None and tag=='span' and len(self.stack)==self.depth:self.current['lines'].append(''.join(self.line).strip());self.line=None
        if self.current and tag==self.current['tag'] and len(self.stack)==self.current['depth']:
            if not self.current['kind'].startswith('acs-') and self.current['lines']:self.parts.append(self.current['lines'])
            self.current=None
        if tag in self.stack:
            while self.stack:
                if self.stack.pop()==tag:break

def php(mode,rows):
    p=subprocess.run([PHP,str(ROOT/'scripts/poc-search/meili-bridge.php')],input=json.dumps({'mode':mode,'rows':rows},ensure_ascii=False),encoding='utf-8',stdout=subprocess.PIPE,stderr=subprocess.PIPE,timeout=180)
    if p.returncode:raise RuntimeError('PHP bridge failure')
    return json.loads(p.stdout)

def req(method,path,data=None):
    body=None if data is None else json.dumps(data,ensure_ascii=False).encode()
    r=urllib.request.Request(BASE+path,data=body,method=method,headers={'Authorization':'Bearer '+KEY,'Content-Type':'application/json'})
    try:
        with urllib.request.urlopen(r,timeout=30) as v:return json.load(v)
    except urllib.error.HTTPError as e:raise RuntimeError('Engine HTTP '+str(e.code)) from None

def write(method,path,data=None):
    uid=req(method,path,data)['taskUid'];end=time.monotonic()+60
    while time.monotonic()<end:
        s=req('GET','/tasks/'+str(uid))['status']
        if s=='succeeded':return
        if s in ['failed','canceled']:raise RuntimeError('Engine task failed')
        time.sleep(.05)
    raise TimeoutError('Engine task timeout')

def main():
    OUT.mkdir(parents=True,exist_ok=True)
    core_file=ROOT/'wordpress/v2/plugins/ca-dao/src/MienDuLieu/SoKhopLoi.php'
    core_hash=hashlib.sha256(core_file.read_bytes()).hexdigest()
    urls=[x.text for x in ET.fromstring(fetch('https://phongdao.com/wp-sitemap-posts-tac_pham-1.xml')).iter() if x.tag.endswith('loc')]
    groups=collections.defaultdict(list)
    for u in urls:groups[u.split('/')[3]].append(u)
    picked=[]
    for g in sorted(groups):picked+=sorted(groups[g],key=lambda u:hashlib.sha256(u.encode()).hexdigest())[:20]
    rest=sorted(set(urls)-set(picked),key=lambda u:hashlib.sha256(u.encode()).hexdigest())
    picked=(picked+rest)[:100]
    def read(u):
        p=Verses();p.feed(fetch(u));return u,p.parts
    sources=list(ThreadPoolExecutor(max_workers=3).map(read,picked))
    docs=[];cases=[];stats=collections.Counter();parts_count=0
    for i,(u,parts) in enumerate(sources):
        if not parts:raise RuntimeError('No verse at source '+str(i))
        parent='p'+str(i);genre=u.split('/')[3];stats[genre]+=1
        for k,lines in enumerate(parts):docs.append({'id':parent+'_'+str(k),'parent':parent,'text':'\n'.join(lines)});parts_count+=1
        lines=parts[0];text='\n'.join(lines);tokens=re.findall(r'[^\W_]+',text,re.UNICODE);line_tokens=re.findall(r'[^\W_]+',lines[0],re.UNICODE)
        variants=[('whole',text),('nfd',unicodedata.normalize('NFD',text)),('accentless',''.join(c for c in unicodedata.normalize('NFD',text.lower()) if unicodedata.category(c)!='Mn').replace('đ','d').replace('ð','d')),('spaces','\u202f'.join(tokens)),('unicode_d',text.translate(str.maketrans({'Đ':'Ð','đ':'ð'}))),('line',lines[0]),('tail',' '.join(tokens[-6:]))]
        if len(line_tokens)>=5:
            changed=line_tokens[:];changed[len(changed)//2]='fixtureunknown'
            omitted=line_tokens[:];del omitted[len(omitted)//2]
            inserted=line_tokens[:];inserted.insert(len(inserted)//2,'fixtureunknown')
            variants += [('substitution',' '.join(changed)),('omission',' '.join(omitted)),('insertion',' '.join(inserted))]
        for kind,q in variants:cases.append({'id':parent+'_'+kind,'kind':kind,'q':q,'expected':parent})
    prepared=php('prepare',[d['text'] for d in docs]);lookup={d['id']:d for d in docs}
    indexed=[{'id':d['id'],'parent':d['parent'],**{k:p[k] for k in ['loi','loi_kd','bam','bam_kd']}} for d,p in zip(docs,prepared)]
    qplans=php('prepare',[c['q'] for c in cases]);dataset_hash=hashlib.sha256(json.dumps([docs,cases],ensure_ascii=False,sort_keys=True).encode()).hexdigest()
    tmp=tempfile.mkdtemp(prefix='phongdao-meili-accept-');proc=None
    try:
        flags=subprocess.CREATE_NO_WINDOW if os.name=='nt' else 0
        proc=subprocess.Popen([EXE,'--http-addr','127.0.0.1:17771','--master-key',KEY,'--db-path',str(pathlib.Path(tmp)/'data'),'--no-analytics'],stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL,creationflags=flags)
        end=time.monotonic()+45
        while True:
            try:req('GET','/health');break
            except Exception:
                if time.monotonic()>end:raise
                time.sleep(.2)
        version=req('GET','/version')
        write('POST','/indexes',{'uid':'public','primaryKey':'id'})
        write('PATCH','/indexes/public/settings',{'searchableAttributes':['loi','loi_kd'],'filterableAttributes':['bam','bam_kd']})
        write('POST','/indexes/public/documents',indexed)
        metrics=[]
        for ix,(c,p) in enumerate(zip(cases,qplans)):
            queries=[dict(n,indexUid='public',limit=30) for n in p['plan']]
            start=time.perf_counter();result=req('POST','/multi-search',{'queries':queries});hits={h['id']:h for r in result['results'] for h in r['hits']}
            ids=list(hits);scores=php('score',[[c['q'],lookup[i]['text']] for i in ids])
            ranked=sorted([(not s['khop'],-s['diem'],i,s) for i,s in zip(ids,scores) if s],key=lambda x:x[:3]);parents=[]
            for _,_,ident,s in ranked:
                parent=lookup[ident]['parent']
                if parent not in parents:parents.append(parent)
            metrics.append({'id':c['id'],'kind':c['kind'],'hit1':c['expected'] in parents[:1],'hit5':c['expected'] in parents[:5],'candidates':len(ids),'elapsed_ms':round((time.perf_counter()-start)*1000,2),'branches':len(queries),'http_search_calls':1})
            if ix%100==0:print(json.dumps({'processed':ix,'total':len(cases)}),flush=True)
        # Negative query never exists in either public source or technical fixtures.
        neg=req('POST','/multi-search',{'queries':[{'indexUid':'public','q':'zzzzprivateunique987 zzzunknown654','limit':30}]})
        summary={k:{'cases':sum(m['kind']==k for m in metrics),'hit1':sum(m['hit1'] for m in metrics if m['kind']==k),'hit5':sum(m['hit5'] for m in metrics if m['kind']==k)} for k in sorted({m['kind'] for m in metrics})}
        if hashlib.sha256(core_file.read_bytes()).hexdigest()!=core_hash:raise RuntimeError('Core changed during benchmark; discard this run')
        report={'status':'complete','version':version,'core_sha256':core_hash,'works':len(sources),'parts':parts_count,'genres':dict(stats),'sha256':dataset_hash,'queries':len(cases),'summary':summary,'negative_no_hits':not neg['results'][0]['hits'],'metrics':metrics,'raw_content_saved':False,'limits':['100 public works selected from sitemap, not full corpus','Mechanically transformed queries, no independent human relevance labels','One-word ranking is not evaluated','Timing includes PHP process startup and is not engine latency or concurrency test','No WordPress deployment acceptance']}
        (OUT/'metrics.json').write_text(json.dumps(report,ensure_ascii=False,indent=2),encoding='utf-8')
        print(json.dumps({k:v for k,v in report.items() if k not in ['metrics','limits']},ensure_ascii=False),flush=True)
    finally:
        if proc:proc.terminate();proc.wait(timeout=15)
        # Temp folder contains this process's index only; keep outside repo for reproduction, never artifact it.

if __name__=='__main__':main()
