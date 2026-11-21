"""PoC chỉ đọc nguồn công khai; không in hay lưu lời tác phẩm vào artifact."""
import hashlib,json,os,pathlib,re,secrets,subprocess,time,unicodedata,urllib.error,urllib.parse,urllib.request
from html.parser import HTMLParser

ROOT=pathlib.Path(os.environ.get('POC_OUTPUT','poc-output'))
URLS={'a':'https://phongdao.com/ca-dao/ve-thoi-dua-non-anh-cam/','b':'https://phongdao.com/ca-dao/ai-ve-dap-da-que-cha/'}
IMAGES={'meili':'getmeili/meilisearch:v1.57.0','typesense':'typesense/typesense:30.2'}
PORTS={'meili':17770,'typesense':18108}
KEY=secrets.token_hex(24)
RUN=secrets.token_hex(5)

class Verses(HTMLParser):
    VOID={'area','base','br','col','embed','hr','img','input','link','meta','param','source','track','wbr'}
    def __init__(self):
        super().__init__();self.stack=[];self.current=None;self.line=None;self.lines_depth=0;self.skip=0;self.verses=[]
    def handle_starttag(self,tag,attrs):
        attrs=dict(attrs)
        if tag not in self.VOID:self.stack.append(tag)
        if 'verse' in attrs.get('class','').split() and self.current is None:
            self.current={'kind':attrs.get('data-nhom',''),'depth':len(self.stack),'lines':[],'tag':tag}
        if self.current and tag=='span' and 'line' in attrs.get('class','').split():self.line=[];self.lines_depth=len(self.stack)
        if self.line is not None and tag=='sup':self.skip+=1
    def handle_data(self,data):
        if self.line is not None and not self.skip:self.line.append(data)
    def handle_endtag(self,tag):
        if tag=='sup' and self.skip:self.skip-=1
        if self.line is not None and tag=='span' and len(self.stack)==self.lines_depth:
            self.current['lines'].append(''.join(self.line).strip());self.line=None
        if self.current and tag==self.current['tag'] and len(self.stack)==self.current['depth']:
            self.verses.append(self.current);self.current=None
        if tag in self.stack:
            while self.stack:
                if self.stack.pop()==tag:break

def read_source(url):
    req=urllib.request.Request(url,headers={'User-Agent':'Phongdao isolated search PoC; public read only'})
    with urllib.request.urlopen(req,timeout=30) as r:
        if r.status!=200:raise RuntimeError('Source HTTP failure')
        parser=Verses();parser.feed(r.read().decode('utf-8'))
    return [v for v in parser.verses if not v['kind'].startswith('acs-')]

def norm(s,fold=False):
    s=unicodedata.normalize('NFC',s).lower().translate(str.maketrans({'ð':'đ','\ufeff':'','\u00ad':''}))
    s=re.sub(r'\s+',' ',s).strip()
    if fold:s=''.join(c for c in unicodedata.normalize('NFD',s) if unicodedata.category(c)!='Mn').replace('đ','d')
    return s

def words(s):return re.findall(r'[^\W_]+',s,re.UNICODE)
def toks(s):return words(norm(s,True))
def local_cost(q,s):
    a,b=toks(q),toks(s);prev=[0]*(len(b)+1)
    for i,x in enumerate(a,1):
        cur=[i]
        for j,y in enumerate(b,1):cur.append(min(prev[j]+1,cur[-1]+1,prev[j-1]+(x!=y)))
        prev=cur
    return min(prev)/max(1,len(a))

def fixtures():
    src={k:read_source(url) for k,url in URLS.items()}
    a=src['a'][0]['lines'];b=src['b'][0]['lines'];v=next(x['lines'] for x in src['b'] if x['kind']=='di-ban')
    assert len(a)==len(b)==len(v)==2,'Source structure changed'
    aw,bw,vw=words(' '.join(a)),words(' '.join(b)),words(' '.join(v))
    assert len(aw)==len(bw)==len(vw)==14,'Source token structure changed'
    changed=words(a[0]);changed[4]=bw[9];remembered=' '.join(changed)
    fragment=words(b[0]);fragment[0]=aw[4]
    full=bw[:];full[0]=aw[4];full[11]=vw[11]
    full=' '.join(full[:6])+'\n'+' '.join(full[6:])+'.'
    docs=[]
    for ident,parent,part,lines in [('a_main','a','main',a),('b_main','b','main',b),('b_variant','b','variant',v)]:
        docs.append(dict(id=ident,parent=parent,part=part,title=(a if parent=='a' else b)[0],loi='\n'.join(lines),notes='',visibility='public'))
    for i in range(1000):
        note='technical fixture only'
        if i<30:note=' '.join(w+' '+('fixture%04d '%i)*12 for w in words(remembered))
        docs.append(dict(id=f'tech{i}',parent=f'tech{i}',part='fixture',title=f'Technical fixture {i}',loi=f'fixture{i:04d} zqa zqb zqc zqd zqe zqf',notes=note,visibility='public'))
    docs.append(dict(id='private_fixture',parent='private_fixture',part='fixture',title='Private fixture',loi='privateonlytoken zzzprivate',notes='',visibility='draft'))
    soft=words(a[0]);soft[3]=soft[3][:-1]+'\u00ad'+soft[3][-1]
    queries=[('exact',a[0],'a'),('one_word_changed',remembered,'a'),('accentless_changed',norm(remembered,True),'a'),('case',a[0].upper(),'a'),('nfd',unicodedata.normalize('NFD',a[0]),'a'),('nbsp','\u00a0'.join(words(a[0])),'a'),('soft_hyphen',' '.join(soft),'a'),('partial',' '.join(words(a[0])[2:]),'a'),('tail',' '.join(words(a[1])[-4:]),'a'),('single_word',words(a[0])[3],'a'),('unicode_d',b[0].translate(str.maketrans({'Ð':'Đ','ð':'đ'})),'b'),('remembered_fragment',' '.join(fragment),'b'),('full_user',full,'b'),('joined_user',full.replace('\n',' '),'b'),('variant',v[0],'b'),('private_exclusion','privateonlytoken zzzprivate',None),('unrelated','zzzxunknown987 zzzxunknown654',None)]
    extra=[('narrow_nbsp','\u202f'.join(words(a[0])),'a'),('bom_prefix','\ufeff'+a[0],'a'),('mixed_diacritics',' '.join([norm(words(a[0])[0],True)]+words(a[0])[1:]),'a'),('query_deletion',' '.join(words(a[0])[:2]+words(a[0])[3:]),'a'),('query_insertion',' '.join(words(a[0])[:2]+[bw[9]]+words(a[0])[2:]),'a'),('variant_full','\n'.join(v),'b'),('b_accentless',norm(b[0],True),'b')]
    return docs,[(k,q,e,'core') for k,q,e in queries]+[(k,q,e,'extension') for k,q,e in extra]

class Engine:
    def __init__(self,kind):self.kind=kind;self.base=f'http://127.0.0.1:{PORTS[kind]}';self.calls=0
    def req(self,method,path,data=None):
        body=None if data is None else json.dumps(data,ensure_ascii=False).encode()
        header={'Content-Type':'application/json'}
        header.update({'Authorization':'Bearer '+KEY} if self.kind=='meili' else {'X-TYPESENSE-API-KEY':KEY})
        req=urllib.request.Request(self.base+path,data=body,method=method,headers=header)
        self.calls+=1
        try:
            with urllib.request.urlopen(req,timeout=30) as r:return json.load(r)
        except urllib.error.HTTPError as err:
            # Never include request text or source content in logs.
            raise RuntimeError(f'{self.kind}: HTTP {err.code} at {path.split("?")[0]}') from None
    def wait(self,d):
        deadline=time.monotonic()+90
        while time.monotonic()<deadline:
            r=self.req('GET','/tasks/'+str(d['taskUid']))
            if r['status']=='succeeded':return
            if r['status']=='failed':raise RuntimeError('Meili indexing failed: '+str((r.get('error') or {}).get('code')))
            time.sleep(.1)
        raise TimeoutError('Meili indexing timeout')
    def index(self,name,docs):
        if self.kind=='meili':
            self.wait(self.req('POST','/indexes',{'uid':name,'primaryKey':'id'}))
            self.wait(self.req('PATCH',f'/indexes/{name}/settings',{'searchableAttributes':['title','loi','notes','loi_kd'],'filterableAttributes':['visibility'],'pagination':{'maxTotalHits':2000}}))
            self.wait(self.req('POST',f'/indexes/{name}/documents',docs))
        else:
            fields=[{'name':k,'type':'string',**({'facet':True} if k=='visibility' else {})} for k in ['parent','part','title','loi','notes','loi_kd','visibility']]
            self.req('POST','/collections',{'name':name,'fields':fields})
            for d in docs:self.req('POST',f'/collections/{name}/documents',d)
    def search(self,name,q,field=None,all_words=False):
        if self.kind=='meili':
            args={'q':q,'filter':'visibility = public','limit':20}
            if field:args.update(attributesToSearchOn=[field],matchingStrategy='all' if all_words else 'frequency')
            return self.req('POST',f'/indexes/{name}/search',args)['hits']
        args={'q':q,'query_by':field or 'title,loi,notes,loi_kd','filter_by':'visibility:=public','per_page':20}
        if all_words:args.update(drop_tokens_threshold=0,typo_tokens_threshold=0,num_typos=0,prefix='false')
        r=self.req('GET',f'/collections/{name}/documents/search?'+urllib.parse.urlencode(args))
        return [h['document'] for h in r.get('hits',[])]

def docker(*args,capture=True):
    r=subprocess.run(['docker',*args],stdout=subprocess.PIPE if capture else subprocess.DEVNULL,stderr=subprocess.PIPE,encoding='utf-8',timeout=300)
    if r.returncode:raise RuntimeError('Docker operation failed: '+args[0])
    return r.stdout.strip() if capture else ''

def main():
    ROOT.mkdir(parents=True,exist_ok=True);names=[];report={'status':'running','engines':{},'sources':URLS,'raw_content_saved':False}
    try:
        docs,queries=fixtures();original={d['id']:d for d in docs}
        frozen=json.dumps([docs,queries],ensure_ascii=False,sort_keys=True).encode()
        report.update(documents=len(docs),core_queries=17,extension_queries=7,data_sha256=hashlib.sha256(frozen).hexdigest())
        for kind,image in IMAGES.items():
            docker('pull',image,capture=False)
            name='phongdao-poc-'+kind+'-'+RUN;names.append(name)
            args=['run','-d','--name',name,'--label','phongdao.poc='+RUN,'--mount','type=volume,destination=/data','-p',f'127.0.0.1:{PORTS[kind]}:'+('7700' if kind=='meili' else '8108')]
            if kind=='meili':args+=['-e','MEILI_MASTER_KEY='+KEY,'-e','MEILI_NO_ANALYTICS=true','-e','MEILI_DB_PATH=/data/meili','-e','MEILI_MAX_INDEXING_THREADS=2','-e','MEILI_MAX_INDEXING_MEMORY=512MiB',image,'meilisearch','--http-addr','0.0.0.0:7700','--no-analytics']
            else:args+=[image,'--data-dir=/data','--api-key='+KEY,'--listen-address=0.0.0.0']
            docker(*args)
            engine=Engine(kind);deadline=time.monotonic()+60
            while True:
                try:engine.req('GET','/health');break
                except Exception:
                    if time.monotonic()>deadline:raise TimeoutError(kind+' startup timeout')
                    time.sleep(.25)
            ver=engine.req('GET','/version' if kind=='meili' else '/debug')
            results=[]
            for index in ['raw','normalized']:
                records=[]
                for source in docs:
                    d=dict(source)
                    if index=='normalized':
                        for key in ['title','loi','notes']:d[key]=norm(d[key])
                    d['loi_kd']=norm(source['loi'],True) if index=='normalized' else ''
                    records.append(d)
                engine.index(index,records)
            for case,q,expected,group in queries:
                row={'case':case,'group':group,'expected_parent':expected,'profiles':{}}
                for profile,index,query in [('raw_engine','raw',q),('normalized_engine','normalized',norm(q)),('adapter','normalized',norm(q))]:
                    start=time.perf_counter();before=engine.calls
                    if profile!='adapter':hits=engine.search(index,query)
                    else:
                        hits=engine.search(index,norm(q),'loi')+engine.search(index,norm(q,True),'loi_kd')
                        qt=toks(q)
                        if 4<=len(qt)<=16:
                            for omit in range(len(qt)):
                                hits+=engine.search(index,' '.join(qt[:omit]+qt[omit+1:]),'loi_kd',True)
                        hits=list({h['id']:h for h in hits}.values())
                        hits.sort(key=lambda h:(local_cost(q,original[h['id']]['loi']),h['id']))
                    parents=[]
                    for h in hits:
                        if h['parent'] not in parents:parents.append(h['parent'])
                    row['profiles'][profile]={'top5':parents[:5],'hit1':expected is not None and expected in parents[:1],'hit5':expected is not None and expected in parents[:5],'no_hits':not hits,'calls':engine.calls-before,'elapsed_ms':round((time.perf_counter()-start)*1000,3)}
                results.append(row)
            summary={}
            for group in ['core','extension']:
                positive=[r for r in results if r['group']==group and r['expected_parent'] is not None]
                summary[group]={p:{'hit1':sum(r['profiles'][p]['hit1'] for r in positive),'hit5':sum(r['profiles'][p]['hit5'] for r in positive),'cases':len(positive)} for p in ['raw_engine','normalized_engine','adapter']}
            report['engines'][kind]={'image':image,'image_id':docker('image','inspect',image,'--format','{{.Id}}'),'version':ver,'summary':summary,'cases':results}
            print(json.dumps({'engine':kind,'summary':summary},ensure_ascii=False),flush=True)
        report['status']='complete'
        report['limits']=['Small diagnostic set, two public works only','Extension cases were not tuned separately, but same work families as core','No production or concurrency benchmark','Adapter prototype is not final duplicate confirmation logic','No claim of corpus-wide accuracy']
    except Exception as error:
        report['status']='failed';report['error_type']=type(error).__name__;report['error']=str(error)
        raise
    finally:
        for name in names:
            try:
                label=docker('inspect',name,'--format','{{ index .Config.Labels "phongdao.poc" }}')
                if label==RUN:docker('rm','-f','-v',name,capture=False)
            except Exception:pass
        (ROOT/'metrics.json').write_text(json.dumps(report,ensure_ascii=False,indent=2),encoding='utf-8')
        print('Only metrics saved; owned containers cleanup requested.',flush=True)
if __name__=='__main__':main()