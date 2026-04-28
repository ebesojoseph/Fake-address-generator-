// admin/app.jsx — Complete React Admin Dashboard v2
const { useState, useEffect, useCallback, useRef, createContext, useContext } = React;

// Reads BASE_URL injected by admin/index.php
const API_BASE = window.__APP__?.apiBase || (window.location.origin + '/admin/api');

async function api(endpoint, options = {}) {
  const res  = await fetch(`${API_BASE}/${endpoint}`, { credentials:'include', ...options, headers:{...(options.headers||{})} });
  const data = await res.json().catch(() => ({}));
  if (!res.ok) throw new Error(data.error || `HTTP ${res.status}`);
  return data;
}
async function apiJson(endpoint, method, body) {
  return api(endpoint, { method, headers:{'Content-Type':'application/json'}, body:JSON.stringify(body) });
}

const AuthCtx  = createContext(null);
const ToastCtx = createContext(null);
function useAuth()  { return useContext(AuthCtx); }
function useToast() { return useContext(ToastCtx); }

// ── Toasts ────────────────────────────────────────────────
function ToastProvider({ children }) {
  const [toasts, setToasts] = useState([]);
  const add = useCallback((msg, type='success') => {
    const id = Date.now();
    setToasts(t => [...t, { id, msg, type }]);
    setTimeout(() => setToasts(t => t.filter(x => x.id !== id)), 3500);
  }, []);
  return (
    <ToastCtx.Provider value={add}>
      {children}
      <div style={{position:'fixed',bottom:24,right:24,display:'flex',flexDirection:'column',gap:8,zIndex:9999}}>
        {toasts.map(t => (
          <div key={t.id} style={{padding:'12px 18px',borderRadius:8,minWidth:260,maxWidth:360,
            background:t.type==='success'?'#166534':t.type==='error'?'#991b1b':'#1e3a5f',
            color:'#fff',fontSize:13,boxShadow:'0 4px 12px rgba(0,0,0,.2)',animation:'slideIn .25s ease'}}>
            {t.type==='success'?'✓ ':t.type==='error'?'✕ ':'ℹ '}{t.msg}
          </div>
        ))}
      </div>
    </ToastCtx.Provider>
  );
}

// ── CSS helpers ───────────────────────────────────────────
const css = {
  card:  { background:'#fff',borderRadius:8,border:'1px solid #e2e8f0',boxShadow:'0 1px 3px rgba(0,0,0,.1)' },
  input: { width:'100%',padding:'8px 12px',border:'1px solid #e2e8f0',borderRadius:6,fontSize:13,fontFamily:'inherit',outline:'none',background:'#fff' },
  label: { display:'block',fontSize:11,fontWeight:600,marginBottom:4,color:'#64748b',textTransform:'uppercase',letterSpacing:'.05em' },
  btn:   (v='primary',s='md') => ({
    display:'inline-flex',alignItems:'center',gap:6,cursor:'pointer',border:'none',
    borderRadius:6,fontWeight:600,fontSize:s==='sm'?12:13,fontFamily:'inherit',
    padding:s==='sm'?'5px 12px':'8px 16px',
    background:v==='primary'?'#5d83f1':v==='danger'?'#ef4444':v==='success'?'#22c55e':v==='ghost'?'transparent':'#e2e8f0',
    color:v==='ghost'?'#64748b':v==='secondary'?'#1e293b':'#fff',
    opacity:1,transition:'opacity .15s',
  }),
};

function Btn({ children, variant='primary', size='md', onClick, type='button', disabled, style={} }) {
  return <button type={type} onClick={onClick} disabled={disabled} style={{...css.btn(variant,size),opacity:disabled?.6:1,...style}}>{children}</button>;
}
function Input({ label, ...props }) {
  return <div style={{marginBottom:16}}>{label&&<label style={css.label}>{label}</label>}<input {...props} style={{...css.input,...(props.style||{})}} /></div>;
}
function Textarea({ label, ...props }) {
  return <div style={{marginBottom:16}}>{label&&<label style={css.label}>{label}</label>}<textarea {...props} style={{...css.input,minHeight:100,resize:'vertical',...(props.style||{})}} /></div>;
}
function Select({ label, children, ...props }) {
  return <div style={{marginBottom:16}}>{label&&<label style={css.label}>{label}</label>}<select {...props} style={{...css.input,...(props.style||{})}}>{children}</select></div>;
}
function Badge({ children, color='blue' }) {
  const c = {blue:{bg:'#dbeafe',color:'#1d4ed8'},green:{bg:'#dcfce7',color:'#15803d'},red:{bg:'#fee2e2',color:'#b91c1c'},yellow:{bg:'#fef9c3',color:'#a16207'},gray:{bg:'#f1f5f9',color:'#475569'}}[color]||{bg:'#f1f5f9',color:'#475569'};
  return <span style={{...c,padding:'2px 8px',borderRadius:20,fontSize:11,fontWeight:600}}>{children}</span>;
}
function Spinner() { return <span style={{display:'inline-block',width:16,height:16,border:'2px solid rgba(255,255,255,.4)',borderTopColor:'#fff',borderRadius:'50%',animation:'spin .7s linear infinite'}} />; }
function StatCard({ label, value, icon, color='#5d83f1' }) {
  return (
    <div style={{...css.card,padding:20,display:'flex',alignItems:'center',gap:16}}>
      <div style={{width:48,height:48,borderRadius:12,background:color+'20',display:'flex',alignItems:'center',justifyContent:'center',fontSize:22}}>{icon}</div>
      <div><div style={{fontSize:24,fontWeight:700}}>{value?.toLocaleString?.()??value}</div><div style={{fontSize:12,color:'#64748b',fontWeight:500}}>{label}</div></div>
    </div>
  );
}
function Modal({ open, title, onClose, children, width=600 }) {
  if (!open) return null;
  return (
    <div style={{position:'fixed',inset:0,background:'rgba(0,0,0,.5)',zIndex:1000,display:'flex',alignItems:'center',justifyContent:'center',padding:16}} onClick={onClose}>
      <div style={{background:'#fff',borderRadius:12,width:'100%',maxWidth:width,maxHeight:'90vh',overflow:'auto',boxShadow:'0 20px 60px rgba(0,0,0,.3)'}} onClick={e=>e.stopPropagation()}>
        <div style={{display:'flex',justifyContent:'space-between',alignItems:'center',padding:'16px 20px',borderBottom:'1px solid #e2e8f0'}}>
          <h3 style={{fontSize:16}}>{title}</h3>
          <button onClick={onClose} style={{background:'none',border:'none',fontSize:20,cursor:'pointer',color:'#64748b'}}>×</button>
        </div>
        <div style={{padding:20}}>{children}</div>
      </div>
    </div>
  );
}
function Confirm({ open, message, onConfirm, onCancel }) {
  return (
    <Modal open={open} title="Confirm" onClose={onCancel} width={400}>
      <p style={{marginBottom:20,color:'#64748b'}}>{message}</p>
      <div style={{display:'flex',gap:8,justifyContent:'flex-end'}}>
        <Btn variant="secondary" onClick={onCancel}>Cancel</Btn>
        <Btn variant="danger" onClick={onConfirm}>Delete</Btn>
      </div>
    </Modal>
  );
}
function DeleteBtn({ onConfirm }) {
  const [open, setOpen] = useState(false);
  return (<><Btn size="sm" variant="danger" onClick={()=>setOpen(true)}>Delete</Btn><Confirm open={open} message="Delete this item? This cannot be undone." onConfirm={()=>{onConfirm();setOpen(false);}} onCancel={()=>setOpen(false)} /></>);
}

// ── Quill Editor ──────────────────────────────────────────
function QuillEditor({ value, onChange, height=300 }) {
  const ref = useRef(null); const q = useRef(null);
  useEffect(() => {
    if (!ref.current || q.current) return;
    q.current = new Quill(ref.current, { theme:'snow', modules:{ toolbar:[[{header:[1,2,3,false]}],['bold','italic','underline','strike'],['link','image','blockquote','code-block'],[{list:'ordered'},{list:'bullet'}],['clean']] } });
    q.current.on('text-change', () => onChange(q.current.root.innerHTML));
    if (value) q.current.root.innerHTML = value;
  }, []);
  return <div ref={ref} style={{height,fontSize:14}} />;
}

// ── Sidebar ───────────────────────────────────────────────
const NAV = [
  {id:'dashboard',    label:'Dashboard',        icon:'📊'},
  {id:'posts',        label:'Blog Posts',        icon:'📝'},
  {id:'categories',   label:'Categories',        icon:'🏷️'},
  {id:'faqs',         label:'FAQs',              icon:'❓'},
  {id:'content',      label:'Content Sections',  icon:'📄'},
  {id:'footer_links', label:'Footer Links',      icon:'🔗'},
  {id:'settings',     label:'Settings',          icon:'⚙️'},
];

function Sidebar({ active, onNav }) {
  const auth = useAuth();
  return (
    <aside style={{width:'var(--sidebar-w,240px)',background:'#0f172a',height:'100vh',position:'fixed',left:0,top:0,display:'flex',flexDirection:'column',zIndex:100}}>
      <div style={{padding:'20px 16px',borderBottom:'1px solid rgba(255,255,255,.08)'}}>
        <div style={{color:'#5d83f1',fontWeight:700,fontSize:15}}>⚡ FAG Admin</div>
        <div style={{color:'rgba(255,255,255,.4)',fontSize:11,marginTop:2}}>{window.__APP__?.siteName||'Fake Address Generator'}</div>
      </div>
      <nav style={{flex:1,padding:'12px 8px',overflowY:'auto'}}>
        {NAV.map(item => (
          <button key={item.id} onClick={()=>onNav(item.id)}
            style={{display:'flex',alignItems:'center',gap:10,width:'100%',padding:'9px 12px',marginBottom:2,
              background:active===item.id?'rgba(93,131,241,.2)':'transparent',
              color:active===item.id?'#fff':'rgba(255,255,255,.55)',
              border:'none',borderRadius:6,cursor:'pointer',fontSize:13,fontWeight:active===item.id?600:400,
              borderLeft:active===item.id?'3px solid #5d83f1':'3px solid transparent'}}>
            <span>{item.icon}</span><span>{item.label}</span>
          </button>
        ))}
      </nav>
      <div style={{padding:16,borderTop:'1px solid rgba(255,255,255,.08)'}}>
        <div style={{color:'rgba(255,255,255,.6)',fontSize:12,marginBottom:8}}>👤 {auth?.user?.username}</div>
        <Btn variant="ghost" size="sm" style={{color:'rgba(255,255,255,.5)',width:'100%',justifyContent:'center'}} onClick={auth?.logout}>Sign Out</Btn>
      </div>
    </aside>
  );
}

// ── Login ─────────────────────────────────────────────────
function LoginScreen({ onLogin }) {
  const [form, setForm]     = useState({username:'',password:''});
  const [loading, setLoading] = useState(false);
  const [error, setError]   = useState('');
  const submit = async e => {
    e.preventDefault(); setLoading(true); setError('');
    try { const d = await apiJson('auth.php?action=login','POST',form); onLogin(d.user); }
    catch(err) { setError(err.message); } finally { setLoading(false); }
  };
  return (
    <div style={{minHeight:'100vh',display:'flex',alignItems:'center',justifyContent:'center',background:'linear-gradient(135deg,#0f172a,#1e3a5f)'}}>
      <div style={{...css.card,width:380,padding:40,textAlign:'center'}}>
        <div style={{fontSize:36,marginBottom:8}}>⚡</div>
        <h1 style={{fontSize:22,marginBottom:4}}>Admin Login</h1>
        <p style={{color:'#64748b',fontSize:13,marginBottom:24}}>Fake Address Generator CMS</p>
        {error && <div style={{background:'#fee2e2',color:'#b91c1c',padding:'10px 14px',borderRadius:6,marginBottom:16,fontSize:13}}>{error}</div>}
        <form onSubmit={submit}>
          <Input label="Username or Email" value={form.username} onChange={e=>setForm(f=>({...f,username:e.target.value}))} placeholder="admin" required />
          <Input label="Password" type="password" value={form.password} onChange={e=>setForm(f=>({...f,password:e.target.value}))} placeholder="••••••••" required />
          <Btn type="submit" variant="primary" disabled={loading} style={{width:'100%',justifyContent:'center',padding:'10px 0'}}>
            {loading?<><Spinner/> Signing in…</>:'Sign In'}
          </Btn>
        </form>
        <p style={{marginTop:16,fontSize:11,color:'#94a3b8'}}>Default: admin / Admin@1234</p>
      </div>
    </div>
  );
}

// ── Dashboard ─────────────────────────────────────────────
function DashboardView() {
  const [stats, setStats] = useState(null);
  const [top, setTop]     = useState([]);
  const [byL, setByL]     = useState([]);
  const [loading, setL]   = useState(true);
  useEffect(() => {
    Promise.all([
      api('data.php?resource=analytics&type=overview'),
      api('data.php?resource=analytics&type=top_posts'),
      api('data.php?resource=analytics&type=generations_by_locale'),
    ]).then(([s,p,l]) => { setStats(s.data); setTop(p.data); setByL(l.data); }).finally(()=>setL(false));
  }, []);
  if (loading) return <div style={{padding:40,textAlign:'center',color:'#64748b'}}>Loading…</div>;
  return (
    <div>
      <h2 style={{marginBottom:20}}>Dashboard</h2>
      <div style={{display:'grid',gridTemplateColumns:'repeat(auto-fill,minmax(200px,1fr))',gap:16,marginBottom:28}}>
        <StatCard label="Total Generations" value={stats?.total_generations}  icon="🗺️" color="#5d83f1" />
        <StatCard label="Today's Generations" value={stats?.today_generations} icon="📍" color="#8cc63f" />
        <StatCard label="Published Posts"   value={stats?.published_posts}    icon="📝" color="#f59e0b" />
        <StatCard label="Total Post Views"  value={stats?.total_post_views}   icon="👁️" color="#ec4899" />
      </div>
      <div style={{display:'grid',gridTemplateColumns:'1fr 1fr',gap:20}}>
        <div style={css.card}>
          <div style={{padding:'14px 18px',borderBottom:'1px solid #e2e8f0',fontWeight:600}}>🏆 Top Posts</div>
          <div style={{padding:16}}>
            {top.map((p,i)=>(
              <div key={p.id} style={{display:'flex',justifyContent:'space-between',padding:'7px 0',borderBottom:'1px solid #f1f5f9',fontSize:13}}>
                <span style={{color:'#64748b',marginRight:8}}>#{i+1}</span>
                <span style={{flex:1,overflow:'hidden',textOverflow:'ellipsis',whiteSpace:'nowrap'}}>{p.title}</span>
                <Badge color="blue">{Number(p.views).toLocaleString()}</Badge>
              </div>
            ))}
            {top.length===0&&<p style={{color:'#64748b',fontSize:13}}>No posts yet.</p>}
          </div>
        </div>
        <div style={css.card}>
          <div style={{padding:'14px 18px',borderBottom:'1px solid #e2e8f0',fontWeight:600}}>🌍 Top Locales</div>
          <div style={{padding:16}}>
            {byL.map(r=>(
              <div key={r.locale} style={{display:'flex',alignItems:'center',gap:8,padding:'7px 0',borderBottom:'1px solid #f1f5f9',fontSize:13}}>
                <span style={{fontFamily:'monospace',fontWeight:600,color:'#5d83f1',minWidth:70}}>{r.locale||'—'}</span>
                <div style={{flex:1,height:6,background:'#f1f5f9',borderRadius:3,overflow:'hidden'}}>
                  <div style={{width:`${Math.min(100,r.count/Math.max(...byL.map(x=>x.count))*100)}%`,height:'100%',background:'#5d83f1',borderRadius:3}} />
                </div>
                <span>{Number(r.count).toLocaleString()}</span>
              </div>
            ))}
            {byL.length===0&&<p style={{color:'#64748b',fontSize:13}}>No data yet.</p>}
          </div>
        </div>
      </div>
    </div>
  );
}

// ── Posts List ────────────────────────────────────────────
function PostsView() {
  const toast = useToast();
  const [posts, setPosts]     = useState([]);
  const [cats, setCats]       = useState([]);
  const [total, setTotal]     = useState(0);
  const [loading, setLoading] = useState(true);
  const [page, setPage]       = useState(1);
  const [search, setSearch]   = useState('');
  const [statusF, setStatusF] = useState('');
  const [editing, setEditing] = useState(null);
  const PER = 15;

  const load = useCallback(async () => {
    setLoading(true);
    const p = new URLSearchParams({page,per_page:PER,search,status:statusF});
    const d = await api(`posts.php?${p}`);
    setPosts(d.data); setTotal(d.total); setLoading(false);
  }, [page, search, statusF]);

  useEffect(()=>{ load(); },[load]);
  useEffect(()=>{ api('data.php?resource=categories').then(d=>setCats(d.data)); },[]);

  const del = async id => { try { await api(`posts.php?id=${id}`,{method:'DELETE'}); toast('Post deleted'); load(); } catch(e){toast(e.message,'error');} };

  if (editing !== null) return <PostEditor postId={editing==='new'?null:editing} cats={cats} onBack={()=>{setEditing(null);load();}} />;

  return (
    <div>
      <div style={{display:'flex',justifyContent:'space-between',alignItems:'center',marginBottom:20}}>
        <h2>Blog Posts</h2>
        <Btn onClick={()=>setEditing('new')}>+ New Post</Btn>
      </div>
      <div style={{display:'flex',gap:10,marginBottom:16,flexWrap:'wrap'}}>
        <input placeholder="Search…" value={search} onChange={e=>{setSearch(e.target.value);setPage(1);}} style={{...css.input,width:240}} />
        <select value={statusF} onChange={e=>{setStatusF(e.target.value);setPage(1);}} style={{...css.input,width:140}}>
          <option value="">All Status</option><option value="published">Published</option><option value="draft">Draft</option>
        </select>
      </div>
      <div style={css.card}>
        <table style={{width:'100%',borderCollapse:'collapse'}}>
          <thead>
            <tr style={{borderBottom:'1px solid #e2e8f0',background:'#f8fafc'}}>
              {['Thumb','Title','Category','Status','Featured','Views','Date','Actions'].map(h=>(
                <th key={h} style={{padding:'10px 14px',textAlign:'left',fontSize:11,fontWeight:600,color:'#64748b',textTransform:'uppercase'}}>{h}</th>
              ))}
            </tr>
          </thead>
          <tbody>
            {loading&&<tr><td colSpan={8} style={{textAlign:'center',padding:40,color:'#64748b'}}>Loading…</td></tr>}
            {!loading&&posts.length===0&&<tr><td colSpan={8} style={{textAlign:'center',padding:40,color:'#64748b'}}>No posts found.</td></tr>}
            {posts.map(p=>(
              <tr key={p.id} style={{borderBottom:'1px solid #e2e8f0'}}>
                <td style={{padding:'10px 14px'}}>{p.thumbnail&&<img src={`${window.__APP__?.baseUrl||''}/${p.thumbnail}`} alt="" style={{width:48,height:36,objectFit:'cover',borderRadius:4}} />}</td>
                <td style={{padding:'10px 14px',fontWeight:500,maxWidth:240}}>
                  <div style={{overflow:'hidden',textOverflow:'ellipsis',whiteSpace:'nowrap'}}>{p.title}</div>
                  <div style={{fontSize:11,color:'#94a3b8',fontFamily:'monospace'}}>/blog/{p.slug}</div>
                </td>
                <td style={{padding:'10px 14px',fontSize:12,color:'#64748b'}}>{p.category_name||'—'}</td>
                <td style={{padding:'10px 14px'}}><Badge color={p.status==='published'?'green':'gray'}>{p.status}</Badge></td>
                <td style={{padding:'10px 14px',textAlign:'center'}}>{p.is_featured?'⭐':'—'}</td>
                <td style={{padding:'10px 14px',fontSize:12}}>{Number(p.views).toLocaleString()}</td>
                <td style={{padding:'10px 14px',fontSize:12,color:'#64748b',whiteSpace:'nowrap'}}>{p.published_at?new Date(p.published_at).toLocaleDateString():'Draft'}</td>
                <td style={{padding:'10px 14px'}}><div style={{display:'flex',gap:6}}><Btn size="sm" variant="secondary" onClick={()=>setEditing(p.id)}>Edit</Btn><DeleteBtn onConfirm={()=>del(p.id)} /></div></td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
      {total > PER && (
        <div style={{display:'flex',gap:8,justifyContent:'center',marginTop:16}}>
          {page>1&&<Btn variant="secondary" size="sm" onClick={()=>setPage(p=>p-1)}>← Prev</Btn>}
          <span style={{padding:'5px 12px',fontSize:13,color:'#64748b'}}>Page {page} of {Math.ceil(total/PER)}</span>
          {page<Math.ceil(total/PER)&&<Btn variant="secondary" size="sm" onClick={()=>setPage(p=>p+1)}>Next →</Btn>}
        </div>
      )}
    </div>
  );
}

// ── Post Editor ───────────────────────────────────────────
function PostEditor({ postId, cats, onBack }) {
  const toast = useToast();
  const isNew = !postId;
  const [form, setForm]     = useState({title:'',slug:'',excerpt:'',content:'',category_id:'',status:'draft',is_featured:0,meta_title:'',meta_description:'',meta_keywords:''});
  const [thumb, setThumb]   = useState(null);
  const [preview, setPrev]  = useState('');
  const [loading, setLoading] = useState(!isNew);
  const [saving, setSaving] = useState(false);
  const [tab, setTab]       = useState('content');

  useEffect(() => {
    if (!isNew) api(`posts.php?id=${postId}`).then(d=>{ setForm(f=>({...f,...d.data})); if(d.data.thumbnail) setPrev(`${window.__APP__?.baseUrl||''}/${d.data.thumbnail}`); }).finally(()=>setLoading(false));
  },[postId]);

  const slug = t => t.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');
  const set  = (k,v) => setForm(f=>({...f,[k]:v}));

  const save = async status => {
    if (!form.title.trim())  { toast('Title required','error'); return; }
    if (!form.content||form.content==='<p><br></p>') { toast('Content required','error'); return; }
    if (isNew && !thumb) { toast('Thumbnail required','error'); return; }
    setSaving(true);
    try {
      const fd = new FormData();
      Object.entries({...form,status:status||form.status}).forEach(([k,v])=>fd.append(k,v));
      if (thumb) fd.append('thumbnail', thumb);
      const url = isNew ? `${API_BASE}/posts.php` : `${API_BASE}/posts.php?id=${postId}`;
      if (!isNew) fd.append('_method','PUT');
      const res  = await fetch(url, {method:'POST',credentials:'include',body:fd});
      const data = await res.json();
      if (!res.ok) throw new Error(data.error||'Save failed');
      toast(isNew?'Post created!':'Post updated!');
      onBack();
    } catch(e) { toast(e.message,'error'); } finally { setSaving(false); }
  };

  if (loading) return <div style={{padding:40,textAlign:'center',color:'#64748b'}}>Loading…</div>;

  return (
    <div>
      <div style={{display:'flex',alignItems:'center',gap:12,marginBottom:24}}>
        <Btn variant="ghost" onClick={onBack} style={{color:'#64748b'}}>← Back</Btn>
        <h2>{isNew?'New Post':'Edit Post'}</h2>
        <div style={{marginLeft:'auto',display:'flex',gap:8}}>
          <Btn variant="secondary" onClick={()=>save('draft')} disabled={saving}>Save Draft</Btn>
          <Btn variant="success" onClick={()=>save('published')} disabled={saving}>{saving?<><Spinner/> Saving…</>:'✓ Publish'}</Btn>
        </div>
      </div>
      <div style={{display:'grid',gridTemplateColumns:'1fr 300px',gap:20,alignItems:'start'}}>
        <div>
          <Input label="Title" value={form.title} onChange={e=>{set('title',e.target.value);if(isNew||!form.slug)set('slug',slug(e.target.value));}} placeholder="Post title…" />
          <Input label="Slug" value={form.slug} onChange={e=>set('slug',e.target.value)} placeholder="url-friendly-slug" />
          <Textarea label="Excerpt" value={form.excerpt} onChange={e=>set('excerpt',e.target.value)} style={{height:80}} />
          <div style={{display:'flex',gap:0,marginBottom:12,borderBottom:'1px solid #e2e8f0'}}>
            {['content','seo'].map(t=>(
              <button key={t} onClick={()=>setTab(t)} style={{padding:'8px 16px',background:'none',border:'none',cursor:'pointer',fontSize:13,fontWeight:tab===t?600:400,color:tab===t?'#5d83f1':'#64748b',borderBottom:tab===t?'2px solid #5d83f1':'2px solid transparent',marginBottom:-1}}>
                {t==='content'?'📝 Content':'🔍 SEO'}
              </button>
            ))}
          </div>
          {tab==='content'&&<div style={{border:'1px solid #e2e8f0',borderRadius:6,overflow:'hidden'}}><QuillEditor value={form.content} onChange={v=>set('content',v)} height={420} /></div>}
          {tab==='seo'&&(
            <div style={css.card}>
              <div style={{padding:20}}>
                <Input label="Meta Title" value={form.meta_title} onChange={e=>set('meta_title',e.target.value)} />
                <Textarea label="Meta Description" value={form.meta_description} onChange={e=>set('meta_description',e.target.value)} style={{height:80}} />
                <Input label="Keywords" value={form.meta_keywords} onChange={e=>set('meta_keywords',e.target.value)} />
                {form.meta_title&&(
                  <div style={{border:'1px solid #e2e8f0',borderRadius:6,padding:14,background:'#f8fafc'}}>
                    <div style={{fontSize:11,color:'#64748b',marginBottom:4}}>SERP Preview</div>
                    <div style={{color:'#1a0dab',fontSize:16}}>{form.meta_title}</div>
                    <div style={{color:'#006621',fontSize:12}}>example.com/blog/{form.slug}</div>
                    <div style={{color:'#545454',fontSize:13}}>{form.meta_description}</div>
                  </div>
                )}
              </div>
            </div>
          )}
        </div>
        <div style={{display:'flex',flexDirection:'column',gap:16}}>
          <div style={css.card}>
            <div style={{padding:'12px 16px',borderBottom:'1px solid #e2e8f0',fontWeight:600,fontSize:13}}>🖼️ Thumbnail <span style={{color:'#ef4444'}}>*</span></div>
            <div style={{padding:16}}>
              {preview&&<img src={preview} alt="" style={{width:'100%',aspectRatio:'16/9',objectFit:'cover',borderRadius:6,marginBottom:12}} />}
              <label style={{display:'block',padding:'8px 12px',background:'#f8fafc',border:'2px dashed #e2e8f0',borderRadius:6,textAlign:'center',cursor:'pointer',fontSize:12,color:'#64748b'}}>
                {thumb||preview?'🔄 Change':'📸 Upload Thumbnail'}
                <input type="file" accept="image/*" style={{display:'none'}} onChange={e=>{const f=e.target.files[0];if(f){setThumb(f);setPrev(URL.createObjectURL(f));}}} />
              </label>
              {!preview&&!thumb&&<p style={{fontSize:11,color:'#ef4444',marginTop:6}}>Required — cannot publish without thumbnail</p>}
            </div>
          </div>
          <div style={css.card}>
            <div style={{padding:'12px 16px',borderBottom:'1px solid #e2e8f0',fontWeight:600,fontSize:13}}>📋 Details</div>
            <div style={{padding:16}}>
              <Select label="Category" value={form.category_id} onChange={e=>set('category_id',e.target.value)}>
                <option value="">— Uncategorized —</option>
                {cats.map(c=><option key={c.id} value={c.id}>{c.name}</option>)}
              </Select>
              <Select label="Status" value={form.status} onChange={e=>set('status',e.target.value)}>
                <option value="draft">Draft</option><option value="published">Published</option>
              </Select>
              <label style={{display:'flex',alignItems:'center',gap:8,cursor:'pointer'}}>
                <input type="checkbox" checked={!!form.is_featured} onChange={e=>set('is_featured',e.target.checked?1:0)} style={{width:16,height:16}} />
                <span style={{fontSize:13}}>⭐ Featured Post</span>
              </label>
              <p style={{fontSize:11,color:'#64748b',marginTop:4}}>Max 5 featured posts</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

// ── Categories ────────────────────────────────────────────
function CategoriesView() {
  const toast = useToast();
  const [cats, setCats]     = useState([]);
  const [modal, setModal]   = useState(false);
  const [editing, setEditing] = useState(null);
  const [form, setForm]     = useState({name:'',description:''});
  const load = () => api('data.php?resource=categories').then(d=>setCats(d.data));
  useEffect(()=>{ load(); },[]);
  const save = async () => {
    try {
      editing ? await apiJson(`data.php?resource=categories&id=${editing}`,'PUT',form) : await apiJson('data.php?resource=categories','POST',form);
      toast(editing?'Updated':'Created'); setModal(false); setEditing(null); setForm({name:'',description:''}); load();
    } catch(e){toast(e.message,'error');}
  };
  const del = async id => { try { await api(`data.php?resource=categories&id=${id}`,{method:'DELETE'}); toast('Deleted'); load(); } catch(e){toast(e.message,'error');} };
  return (
    <div>
      <div style={{display:'flex',justifyContent:'space-between',alignItems:'center',marginBottom:20}}>
        <h2>Categories</h2>
        <Btn onClick={()=>{setEditing(null);setForm({name:'',description:''});setModal(true);}}>+ New</Btn>
      </div>
      <div style={css.card}>
        <table style={{width:'100%',borderCollapse:'collapse'}}>
          <thead><tr style={{borderBottom:'1px solid #e2e8f0',background:'#f8fafc'}}>{['Name','Slug','Description','Actions'].map(h=><th key={h} style={{padding:'10px 14px',textAlign:'left',fontSize:11,fontWeight:600,color:'#64748b',textTransform:'uppercase'}}>{h}</th>)}</tr></thead>
          <tbody>{cats.map(c=>(
            <tr key={c.id} style={{borderBottom:'1px solid #e2e8f0'}}>
              <td style={{padding:'10px 14px',fontWeight:500}}>{c.name}</td>
              <td style={{padding:'10px 14px',fontFamily:'monospace',fontSize:12,color:'#64748b'}}>{c.slug}</td>
              <td style={{padding:'10px 14px',fontSize:13,color:'#64748b'}}>{c.description||'—'}</td>
              <td style={{padding:'10px 14px'}}><div style={{display:'flex',gap:6}}><Btn size="sm" variant="secondary" onClick={()=>{setEditing(c.id);setForm({name:c.name,description:c.description||''});setModal(true);}}>Edit</Btn><DeleteBtn onConfirm={()=>del(c.id)} /></div></td>
            </tr>
          ))}</tbody>
        </table>
      </div>
      <Modal open={modal} title={editing?'Edit Category':'New Category'} onClose={()=>setModal(false)} width={440}>
        <Input label="Name" value={form.name} onChange={e=>setForm(f=>({...f,name:e.target.value}))} />
        <Textarea label="Description" value={form.description} onChange={e=>setForm(f=>({...f,description:e.target.value}))} style={{height:80}} />
        <div style={{display:'flex',justifyContent:'flex-end',gap:8}}><Btn variant="secondary" onClick={()=>setModal(false)}>Cancel</Btn><Btn onClick={save}>Save</Btn></div>
      </Modal>
    </div>
  );
}

// ── FAQs ──────────────────────────────────────────────────
function FaqsView() {
  const toast = useToast();
  const [faqs, setFaqs]     = useState([]);
  const [modal, setModal]   = useState(false);
  const [editing, setEditing] = useState(null);
  const [form, setForm]     = useState({question:'',answer:'',sort_order:0,is_active:1});
  const [saving, setSaving] = useState(false);

  const load = () => api('data.php?resource=faqs').then(d => setFaqs(d.data));
  useEffect(()=>{ load(); },[]);

  const openNew  = () => { setEditing(null); setForm({question:'',answer:'',sort_order:faqs.length+1,is_active:1}); setModal(true); };
  const openEdit = f  => { setEditing(f.id); setForm({question:f.question,answer:f.answer,sort_order:f.sort_order,is_active:f.is_active}); setModal(true); };

  const save = async () => {
    if (!form.question.trim()) { toast('Question required','error'); return; }
    if (!form.answer.trim())   { toast('Answer required','error');   return; }
    setSaving(true);
    try {
      editing ? await apiJson(`data.php?resource=faqs&id=${editing}`,'PUT',form) : await apiJson('data.php?resource=faqs','POST',form);
      toast(editing?'FAQ updated':'FAQ created'); setModal(false); load();
    } catch(e){ toast(e.message,'error'); } finally { setSaving(false); }
  };

  const del = async id => { try { await api(`data.php?resource=faqs&id=${id}`,{method:'DELETE'}); toast('Deleted'); load(); } catch(e){toast(e.message,'error');} };

  const toggle = async faq => {
    try { await apiJson(`data.php?resource=faqs&id=${faq.id}`,'PUT',{...faq,is_active:faq.is_active?0:1}); load(); }
    catch(e){toast(e.message,'error');}
  };

  return (
    <div>
      <div style={{display:'flex',justifyContent:'space-between',alignItems:'center',marginBottom:20}}>
        <h2>FAQs</h2>
        <Btn onClick={openNew}>+ New FAQ</Btn>
      </div>
      <p style={{color:'#64748b',fontSize:13,marginBottom:16}}>FAQs render as an accordion on the homepage. Click the status badge to toggle visibility.</p>
      <div style={css.card}>
        <table style={{width:'100%',borderCollapse:'collapse'}}>
          <thead><tr style={{borderBottom:'1px solid #e2e8f0',background:'#f8fafc'}}>{['#','Question','Status','Order','Actions'].map(h=><th key={h} style={{padding:'10px 14px',textAlign:'left',fontSize:11,fontWeight:600,color:'#64748b',textTransform:'uppercase'}}>{h}</th>)}</tr></thead>
          <tbody>
            {faqs.length===0&&<tr><td colSpan={5} style={{textAlign:'center',padding:40,color:'#64748b'}}>No FAQs yet. Click + New FAQ.</td></tr>}
            {faqs.map((f,i)=>(
              <tr key={f.id} style={{borderBottom:'1px solid #e2e8f0'}}>
                <td style={{padding:'12px 14px',color:'#64748b',fontSize:13}}>{i+1}</td>
                <td style={{padding:'12px 14px',maxWidth:500}}>
                  <div style={{fontWeight:500,marginBottom:3}}>{f.question}</div>
                  <div style={{fontSize:12,color:'#64748b',overflow:'hidden',textOverflow:'ellipsis',whiteSpace:'nowrap',maxWidth:460}}>{f.answer}</div>
                </td>
                <td style={{padding:'12px 14px'}}><span style={{cursor:'pointer'}} onClick={()=>toggle(f)}><Badge color={f.is_active?'green':'gray'}>{f.is_active?'Active':'Hidden'}</Badge></span></td>
                <td style={{padding:'12px 14px',fontSize:13,color:'#64748b'}}>{f.sort_order}</td>
                <td style={{padding:'12px 14px'}}><div style={{display:'flex',gap:6}}><Btn size="sm" variant="secondary" onClick={()=>openEdit(f)}>Edit</Btn><DeleteBtn onConfirm={()=>del(f.id)} /></div></td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
      <Modal open={modal} title={editing?'Edit FAQ':'New FAQ'} onClose={()=>setModal(false)} width={560}>
        <div style={{marginBottom:16}}><label style={css.label}>Question</label><input value={form.question} onChange={e=>setForm(f=>({...f,question:e.target.value}))} placeholder="e.g. Are these addresses real?" style={css.input} /></div>
        <div style={{marginBottom:16}}><label style={css.label}>Answer</label><textarea value={form.answer} onChange={e=>setForm(f=>({...f,answer:e.target.value}))} placeholder="Write the answer here…" style={{...css.input,minHeight:120,resize:'vertical'}} /></div>
        <div style={{display:'flex',gap:16,marginBottom:16}}>
          <div style={{flex:1}}><label style={css.label}>Sort Order</label><input type="number" value={form.sort_order} onChange={e=>setForm(f=>({...f,sort_order:parseInt(e.target.value)||0}))} style={css.input} /></div>
          <div style={{display:'flex',alignItems:'flex-end',paddingBottom:4}}><label style={{display:'flex',alignItems:'center',gap:8,cursor:'pointer'}}><input type="checkbox" checked={!!form.is_active} onChange={e=>setForm(f=>({...f,is_active:e.target.checked?1:0}))} style={{width:16,height:16}} /><span style={{fontSize:13}}>Active (visible on homepage)</span></label></div>
        </div>
        <div style={{display:'flex',justifyContent:'flex-end',gap:8}}><Btn variant="secondary" onClick={()=>setModal(false)}>Cancel</Btn><Btn onClick={save} disabled={saving}>{saving?<><Spinner/> Saving…</>:'Save FAQ'}</Btn></div>
      </Modal>
    </div>
  );
}

// ── Content Sections ──────────────────────────────────────
function ContentView() {
  const toast = useToast();
  const [sections, setSections] = useState([]);
  const [modal, setModal]       = useState(false);
  const [editing, setEditing]   = useState(null);
  const [form, setForm]         = useState({section_key:'',title:'',body:'',sort_order:0,is_active:1});
  const load = () => api('data.php?resource=content_sections').then(d=>setSections(d.data));
  useEffect(()=>{ load(); },[]);
  const save = async () => {
    try {
      editing ? await apiJson(`data.php?resource=content_sections&id=${editing}`,'PUT',form) : await apiJson('data.php?resource=content_sections','POST',form);
      toast('Saved'); setModal(false); load();
    } catch(e){toast(e.message,'error');}
  };
  const del = async id => { try{await api(`data.php?resource=content_sections&id=${id}`,{method:'DELETE'});toast('Deleted');load();}catch(e){toast(e.message,'error');} };
  return (
    <div>
      <div style={{display:'flex',justifyContent:'space-between',alignItems:'center',marginBottom:20}}>
        <h2>Content Sections</h2>
        <Btn onClick={()=>{setEditing(null);setForm({section_key:'',title:'',body:'',sort_order:0,is_active:1});setModal(true);}}>+ New Section</Btn>
      </div>
      {sections.map(s=>(
        <div key={s.id} style={{...css.card,marginBottom:14,padding:0,overflow:'hidden'}}>
          <div style={{display:'flex',justifyContent:'space-between',alignItems:'center',padding:'12px 18px',borderBottom:'1px solid #e2e8f0',background:'#f8fafc'}}>
            <div><span style={{fontWeight:600}}>{s.title}</span><span style={{marginLeft:10,fontSize:11,fontFamily:'monospace',color:'#64748b'}}>{s.section_key}</span></div>
            <div style={{display:'flex',gap:8,alignItems:'center'}}>
              <Badge color={s.is_active?'green':'gray'}>{s.is_active?'Active':'Hidden'}</Badge>
              <Btn size="sm" variant="secondary" onClick={()=>{setEditing(s.id);setForm({section_key:s.section_key,title:s.title,body:s.body,sort_order:s.sort_order,is_active:s.is_active});setModal(true);}}>Edit</Btn>
              <DeleteBtn onConfirm={()=>del(s.id)} />
            </div>
          </div>
          <div style={{padding:'10px 18px',fontSize:13,color:'#64748b'}} dangerouslySetInnerHTML={{__html:(s.body||'').substring(0,180)+'…'}} />
        </div>
      ))}
      <Modal open={modal} title={editing?'Edit Section':'New Section'} onClose={()=>setModal(false)} width={700}>
        <Input label="Section Key" value={form.section_key} onChange={e=>setForm(f=>({...f,section_key:e.target.value}))} disabled={!!editing} />
        <Input label="Title" value={form.title} onChange={e=>setForm(f=>({...f,title:e.target.value}))} />
        <div style={{marginBottom:16}}><label style={css.label}>Content</label><div style={{border:'1px solid #e2e8f0',borderRadius:6,overflow:'hidden'}}><QuillEditor value={form.body} onChange={v=>setForm(f=>({...f,body:v}))} height={240} /></div></div>
        <div style={{display:'flex',gap:16,marginBottom:16}}>
          <div style={{flex:1}}><label style={css.label}>Sort Order</label><input type="number" value={form.sort_order} onChange={e=>setForm(f=>({...f,sort_order:parseInt(e.target.value)||0}))} style={css.input} /></div>
          <div style={{display:'flex',alignItems:'flex-end',paddingBottom:4}}><label style={{display:'flex',alignItems:'center',gap:8,cursor:'pointer'}}><input type="checkbox" checked={!!form.is_active} onChange={e=>setForm(f=>({...f,is_active:e.target.checked?1:0}))} style={{width:16,height:16}} /><span style={{fontSize:13}}>Active</span></label></div>
        </div>
        <div style={{display:'flex',justifyContent:'flex-end',gap:8}}><Btn variant="secondary" onClick={()=>setModal(false)}>Cancel</Btn><Btn onClick={save}>Save</Btn></div>
      </Modal>
    </div>
  );
}

// ── Footer Links ──────────────────────────────────────────
function FooterLinksView() {
  const toast = useToast();
  const [links, setLinks]   = useState([]);
  const [modal, setModal]   = useState(false);
  const [editing, setEditing] = useState(null);
  const [form, setForm]     = useState({label:'',url:'',row_number:1,sort_order:0,is_active:1});
  const load = () => api('data.php?resource=footer_links').then(d=>setLinks(d.data));
  useEffect(()=>{ load(); },[]);
  const save = async () => {
    try {
      editing ? await apiJson(`data.php?resource=footer_links&id=${editing}`,'PUT',form) : await apiJson('data.php?resource=footer_links','POST',form);
      toast('Saved'); setModal(false); load();
    } catch(e){toast(e.message,'error');}
  };
  const del = async id => { try{await api(`data.php?resource=footer_links&id=${id}`,{method:'DELETE'});toast('Deleted');load();}catch(e){toast(e.message,'error');} };
  const grouped = links.reduce((a,l)=>{ a[l.row_number]??=[];a[l.row_number].push(l);return a; },{});
  return (
    <div>
      <div style={{display:'flex',justifyContent:'space-between',alignItems:'center',marginBottom:20}}>
        <h2>Footer Links</h2>
        <Btn onClick={()=>{setEditing(null);setForm({label:'',url:'',row_number:1,sort_order:0,is_active:1});setModal(true);}}>+ Add Link</Btn>
      </div>
      <p style={{color:'#64748b',fontSize:13,marginBottom:16}}>Row 1 = top of funnel (most links). Higher rows = fewer links, narrowing to a single contact line.</p>
      {Object.entries(grouped).map(([row,rowLinks])=>(
        <div key={row} style={{marginBottom:18}}>
          <div style={{fontSize:12,fontWeight:600,color:'#64748b',textTransform:'uppercase',letterSpacing:'.06em',marginBottom:8}}>Row {row} — {rowLinks.length} link{rowLinks.length!==1?'s':''}</div>
          <div style={css.card}>
            {rowLinks.map((l,i)=>(
              <div key={l.id} style={{display:'flex',alignItems:'center',gap:12,padding:'10px 16px',borderBottom:i<rowLinks.length-1?'1px solid #e2e8f0':'none'}}>
                <Badge color={l.is_active?'green':'gray'}>{l.is_active?'On':'Off'}</Badge>
                <span style={{fontWeight:500,flex:1}}>{l.label}</span>
                <span style={{fontFamily:'monospace',fontSize:12,color:'#64748b',maxWidth:220,overflow:'hidden',textOverflow:'ellipsis'}}>{l.url}</span>
                <div style={{display:'flex',gap:6}}><Btn size="sm" variant="secondary" onClick={()=>{setEditing(l.id);setForm({label:l.label,url:l.url,row_number:l.row_number,sort_order:l.sort_order,is_active:l.is_active});setModal(true);}}>Edit</Btn><DeleteBtn onConfirm={()=>del(l.id)} /></div>
              </div>
            ))}
          </div>
        </div>
      ))}
      <Modal open={modal} title={editing?'Edit Link':'Add Link'} onClose={()=>setModal(false)} width={480}>
        <Input label="Label" value={form.label} onChange={e=>setForm(f=>({...f,label:e.target.value}))} placeholder="US Address Generator" />
        <Input label="URL" value={form.url} onChange={e=>setForm(f=>({...f,url:e.target.value}))} placeholder="/fake-address/english-united-states" />
        <div style={{display:'flex',gap:16}}>
          <div style={{flex:1}}><label style={css.label}>Row</label><input type="number" min={1} max={10} value={form.row_number} onChange={e=>setForm(f=>({...f,row_number:parseInt(e.target.value)||1}))} style={css.input} /></div>
          <div style={{flex:1}}><label style={css.label}>Sort Order</label><input type="number" value={form.sort_order} onChange={e=>setForm(f=>({...f,sort_order:parseInt(e.target.value)||0}))} style={css.input} /></div>
        </div>
        <label style={{display:'flex',alignItems:'center',gap:8,cursor:'pointer',margin:'12px 0'}}><input type="checkbox" checked={!!form.is_active} onChange={e=>setForm(f=>({...f,is_active:e.target.checked?1:0}))} style={{width:16,height:16}} /><span style={{fontSize:13}}>Active</span></label>
        <div style={{display:'flex',justifyContent:'flex-end',gap:8}}><Btn variant="secondary" onClick={()=>setModal(false)}>Cancel</Btn><Btn onClick={save}>Save</Btn></div>
      </Modal>
    </div>
  );
}

// ── Settings ──────────────────────────────────────────────
function SettingsView() {
  const toast = useToast();
  const [settings, setSettings] = useState([]);
  const [form, setForm]         = useState({});
  const [saving, setSaving]     = useState(false);
  const [pw, setPw]             = useState({current_password:'',new_password:'',confirm_password:''});
  useEffect(()=>{ api('data.php?resource=settings').then(d=>{ setSettings(d.data); const m={}; d.data.forEach(s=>m[s.setting_key]=s.setting_value||''); setForm(m); }); },[]);
  const save = async () => { setSaving(true); try{await apiJson('data.php?resource=settings','POST',{settings:form});toast('Settings saved');}catch(e){toast(e.message,'error');}finally{setSaving(false);} };
  const changePw = async () => {
    if (pw.new_password!==pw.confirm_password){toast('Passwords do not match','error');return;}
    if (pw.new_password.length<8){toast('Min 8 characters','error');return;}
    try{await apiJson('auth.php?action=change_password','POST',pw);toast('Password changed');setPw({current_password:'',new_password:'',confirm_password:''});}catch(e){toast(e.message,'error');}
  };
  const renderField = key => {
    const s = settings.find(x=>x.setting_key===key); if(!s) return null;
    if(s.setting_type==='html') return <div key={key} style={{marginBottom:20}}><label style={css.label}>{s.label}</label><textarea value={form[key]||''} onChange={e=>setForm(f=>({...f,[key]:e.target.value}))} style={{...css.input,minHeight:120,fontFamily:'monospace',fontSize:12}} /></div>;
    return <div key={key} style={{marginBottom:20}}><label style={css.label}>{s.label}</label><input value={form[key]||''} onChange={e=>setForm(f=>({...f,[key]:e.target.value}))} style={css.input} /></div>;
  };
  return (
    <div>
      <h2 style={{marginBottom:24}}>Settings</h2>
      <div style={{display:'grid',gridTemplateColumns:'1fr 1fr',gap:20,alignItems:'start'}}>
        <div style={css.card}><div style={{padding:'14px 20px',borderBottom:'1px solid #e2e8f0',fontWeight:600}}>🌐 Site Settings</div><div style={{padding:20}}>{['site_name','site_tagline','contact_email','ga_tracking_id'].map(renderField)}<Btn onClick={save} disabled={saving}>{saving?<><Spinner/> Saving…</>:'Save Settings'}</Btn></div></div>
        <div style={css.card}><div style={{padding:'14px 20px',borderBottom:'1px solid #e2e8f0',fontWeight:600}}>💻 Header / Footer Scripts</div><div style={{padding:20}}>{['header_scripts','footer_scripts'].map(renderField)}<Btn onClick={save} disabled={saving}>{saving?<><Spinner/> Saving…</>:'Save Scripts'}</Btn></div></div>
        <div style={css.card}><div style={{padding:'14px 20px',borderBottom:'1px solid #e2e8f0',fontWeight:600}}>🔐 Change Password</div><div style={{padding:20}}><Input label="Current Password" type="password" value={pw.current_password} onChange={e=>setPw(p=>({...p,current_password:e.target.value}))} /><Input label="New Password" type="password" value={pw.new_password} onChange={e=>setPw(p=>({...p,new_password:e.target.value}))} /><Input label="Confirm New" type="password" value={pw.confirm_password} onChange={e=>setPw(p=>({...p,confirm_password:e.target.value}))} /><Btn onClick={changePw}>Update Password</Btn></div></div>
      </div>
    </div>
  );
}

// ── App Shell ─────────────────────────────────────────────
function App() {
  const [user, setUser]       = useState(null);
  const [checking, setCheck]  = useState(true);
  const [view, setView]       = useState('dashboard');
  useEffect(()=>{ api('auth.php?action=me').then(d=>setUser(d.user)).catch(()=>{}).finally(()=>setCheck(false)); },[]);
  const logout = async () => { await apiJson('auth.php?action=logout','POST',{}); setUser(null); };
  if (checking) return <div style={{display:'flex',alignItems:'center',justifyContent:'center',height:'100vh',color:'#64748b'}}>Loading…</div>;
  if (!user)    return <LoginScreen onLogin={setUser} />;
  const VIEWS = { dashboard:<DashboardView/>, posts:<PostsView/>, categories:<CategoriesView/>, faqs:<FaqsView/>, content:<ContentView/>, footer_links:<FooterLinksView/>, settings:<SettingsView/> };
  return (
    <AuthCtx.Provider value={{user,logout}}>
      <ToastProvider>
        <div style={{display:'flex',minHeight:'100vh'}}>
          <Sidebar active={view} onNav={setView} />
          <main style={{marginLeft:240,flex:1,padding:32,minHeight:'100vh',overflowX:'hidden'}}>
            <div style={{maxWidth:1100,margin:'0 auto'}}>{VIEWS[view]||<DashboardView/>}</div>
          </main>
        </div>
      </ToastProvider>
    </AuthCtx.Provider>
  );
}

ReactDOM.createRoot(document.getElementById('root')).render(<App/>);
