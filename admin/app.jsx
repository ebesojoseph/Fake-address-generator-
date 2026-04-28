// admin/app.jsx — Complete React Admin Dashboard
const { useState, useEffect, useCallback, useRef, createContext, useContext } = React;

// ─────────────────────────────────────────────────────────
// API Helper
// ─────────────────────────────────────────────────────────
const API_BASE = BASE_URL+'/admin/api';

async function api(endpoint, options = {}) {
  const res = await fetch(`${API_BASE}/${endpoint}`, {
    credentials: 'include',
    ...options,
    headers: { ...(options.headers || {}) },
  });
  const data = await res.json().catch(() => ({}));
  if (!res.ok) throw new Error(data.error || `HTTP ${res.status}`);
  return data;
}

async function apiJson(endpoint, method, body) {
  return api(endpoint, {
    method,
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(body),
  });
}

// ─────────────────────────────────────────────────────────
// Auth Context
// ─────────────────────────────────────────────────────────
const AuthCtx = createContext(null);

function useAuth() { return useContext(AuthCtx); }

// ─────────────────────────────────────────────────────────
// Toast notifications
// ─────────────────────────────────────────────────────────
const ToastCtx = createContext(null);
function useToast() { return useContext(ToastCtx); }

function ToastProvider({ children }) {
  const [toasts, setToasts] = useState([]);
  const add = useCallback((msg, type = 'success') => {
    const id = Date.now();
    setToasts(t => [...t, { id, msg, type }]);
    setTimeout(() => setToasts(t => t.filter(x => x.id !== id)), 3500);
  }, []);
  return (
    <ToastCtx.Provider value={add}>
      {children}
      <div style={{ position:'fixed', bottom:24, right:24, display:'flex', flexDirection:'column', gap:8, zIndex:9999 }}>
        {toasts.map(t => (
          <div key={t.id} style={{
            padding:'12px 18px', borderRadius:8, minWidth:260, maxWidth:360,
            background: t.type==='success'?'#166534': t.type==='error'?'#991b1b':'#1e3a5f',
            color:'#fff', fontSize:13, boxShadow:'0 4px 12px rgba(0,0,0,.2)',
            animation:'slideIn .25s ease',
          }}>
            {t.type==='success'?'✓ ':t.type==='error'?'✕ ':'ℹ '}{t.msg}
          </div>
        ))}
      </div>
      <style>{`@keyframes slideIn{from{transform:translateX(40px);opacity:0}to{transform:none;opacity:1}}`}</style>
    </ToastCtx.Provider>
  );
}

// ─────────────────────────────────────────────────────────
// Design Tokens & Reusable Components
// ─────────────────────────────────────────────────────────
const css = {
  card:    { background:'var(--card)', borderRadius:'var(--radius)', border:'1px solid var(--border)', boxShadow:'var(--shadow)' },
  input:   { width:'100%', padding:'8px 12px', border:'1px solid var(--border)', borderRadius:6, fontSize:13, fontFamily:'inherit', outline:'none', background:'#fff' },
  label:   { display:'block', fontSize:12, fontWeight:600, marginBottom:4, color:'var(--text-muted)', textTransform:'uppercase', letterSpacing:'.05em' },
  btn:     (variant='primary',size='md') => ({
    display:'inline-flex', alignItems:'center', gap:6, cursor:'pointer', border:'none',
    borderRadius:6, fontWeight:600, fontSize:size==='sm'?12:13, fontFamily:'inherit',
    padding: size==='sm'?'5px 12px':'8px 16px',
    background: variant==='primary'?'var(--brand)': variant==='danger'?'var(--danger)': variant==='success'?'var(--success)': variant==='ghost'?'transparent':'var(--border)',
    color: variant==='ghost'?'var(--text-muted)': variant==='secondary'?'var(--text)':'#fff',
    transition:'opacity .15s, box-shadow .15s',
  }),
};

function Btn({ children, variant='primary', size='md', onClick, type='button', disabled, style={} }) {
  return (
    <button type={type} onClick={onClick} disabled={disabled} style={{ ...css.btn(variant,size), opacity:disabled?.6:1, ...style }}>
      {children}
    </button>
  );
}

function Input({ label, ...props }) {
  return (
    <div style={{ marginBottom:16 }}>
      {label && <label style={css.label}>{label}</label>}
      <input {...props} style={{ ...css.input, ...(props.style||{}) }} />
    </div>
  );
}

function Textarea({ label, ...props }) {
  return (
    <div style={{ marginBottom:16 }}>
      {label && <label style={css.label}>{label}</label>}
      <textarea {...props} style={{ ...css.input, minHeight:100, resize:'vertical', ...(props.style||{}) }} />
    </div>
  );
}

function Select({ label, children, ...props }) {
  return (
    <div style={{ marginBottom:16 }}>
      {label && <label style={css.label}>{label}</label>}
      <select {...props} style={{ ...css.input, ...(props.style||{}) }}>{children}</select>
    </div>
  );
}

function Badge({ children, color='blue' }) {
  const colors = {
    blue:   { bg:'#dbeafe', color:'#1d4ed8' },
    green:  { bg:'#dcfce7', color:'#15803d' },
    red:    { bg:'#fee2e2', color:'#b91c1c' },
    yellow: { bg:'#fef9c3', color:'#a16207' },
    gray:   { bg:'#f1f5f9', color:'#475569' },
  };
  const c = colors[color] || colors.gray;
  return <span style={{ ...c, padding:'2px 8px', borderRadius:20, fontSize:11, fontWeight:600 }}>{children}</span>;
}

function Modal({ open, title, onClose, children, width=600 }) {
  if (!open) return null;
  return (
    <div style={{ position:'fixed', inset:0, background:'rgba(0,0,0,.5)', zIndex:1000, display:'flex', alignItems:'center', justifyContent:'center', padding:16 }} onClick={onClose}>
      <div style={{ background:'#fff', borderRadius:12, width:'100%', maxWidth:width, maxHeight:'90vh', overflow:'auto', boxShadow:'0 20px 60px rgba(0,0,0,.3)' }} onClick={e => e.stopPropagation()}>
        <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center', padding:'16px 20px', borderBottom:'1px solid var(--border)' }}>
          <h3 style={{ fontSize:16 }}>{title}</h3>
          <button onClick={onClose} style={{ background:'none', border:'none', fontSize:20, cursor:'pointer', color:'var(--text-muted)', lineHeight:1 }}>×</button>
        </div>
        <div style={{ padding:20 }}>{children}</div>
      </div>
    </div>
  );
}

function Confirm({ open, message, onConfirm, onCancel }) {
  return (
    <Modal open={open} title="Confirm Action" onClose={onCancel} width={400}>
      <p style={{ marginBottom:20, color:'var(--text-muted)' }}>{message}</p>
      <div style={{ display:'flex', gap:8, justifyContent:'flex-end' }}>
        <Btn variant="secondary" onClick={onCancel}>Cancel</Btn>
        <Btn variant="danger" onClick={onConfirm}>Confirm</Btn>
      </div>
    </Modal>
  );
}

function Spinner() {
  return <span className="spin" style={{ display:'inline-block', width:16, height:16, border:'2px solid rgba(255,255,255,.4)', borderTopColor:'#fff', borderRadius:'50%' }} />;
}

function StatCard({ label, value, icon, color='#5d83f1' }) {
  return (
    <div style={{ ...css.card, padding:20, display:'flex', alignItems:'center', gap:16 }}>
      <div style={{ width:48, height:48, borderRadius:12, background:color+'20', display:'flex', alignItems:'center', justifyContent:'center', fontSize:22 }}>{icon}</div>
      <div>
        <div style={{ fontSize:24, fontWeight:700 }}>{value?.toLocaleString?.() ?? value}</div>
        <div style={{ fontSize:12, color:'var(--text-muted)', fontWeight:500 }}>{label}</div>
      </div>
    </div>
  );
}

// ─────────────────────────────────────────────────────────
// Sidebar Navigation
// ─────────────────────────────────────────────────────────
const NAV_ITEMS = [
  { id:'dashboard',       label:'Dashboard',       icon:'📊' },
  { id:'posts',           label:'Blog Posts',      icon:'📝' },
  { id:'categories',      label:'Categories',      icon:'🏷️' },
  { id:'content',         label:'Content Sections',icon:'📄' },
  { id:'footer_links',    label:'Footer Links',    icon:'🔗' },
  { id:'settings',        label:'Settings',        icon:'⚙️' },
];

function Sidebar({ active, onNav }) {
  const auth = useAuth();
  return (
    <aside style={{ width:'var(--sidebar-w)', background:'var(--sidebar-bg)', height:'100vh', position:'fixed', left:0, top:0, display:'flex', flexDirection:'column', zIndex:100 }}>
      <div style={{ padding:'20px 16px', borderBottom:'1px solid rgba(255,255,255,.08)' }}>
        <div style={{ color:'var(--brand)', fontWeight:700, fontSize:15, letterSpacing:'.02em' }}>⚡ FAG Admin</div>
        <div style={{ color:'rgba(255,255,255,.4)', fontSize:11, marginTop:2 }}>Fake Address Generator</div>
      </div>
      <nav style={{ flex:1, padding:'12px 8px', overflowY:'auto' }}>
        {NAV_ITEMS.map(item => (
          <button key={item.id} onClick={() => onNav(item.id)}
            style={{ display:'flex', alignItems:'center', gap:10, width:'100%', padding:'9px 12px', marginBottom:2,
              background: active===item.id ? 'rgba(93,131,241,.2)':'transparent',
              color: active===item.id ? '#fff':'rgba(255,255,255,.55)',
              border:'none', borderRadius:6, cursor:'pointer', fontSize:13, fontWeight:active===item.id?600:400,
              borderLeft: active===item.id ? '3px solid var(--brand)':'3px solid transparent',
              transition:'all .15s',
            }}>
            <span>{item.icon}</span><span>{item.label}</span>
          </button>
        ))}
      </nav>
      <div style={{ padding:16, borderTop:'1px solid rgba(255,255,255,.08)' }}>
        <div style={{ color:'rgba(255,255,255,.6)', fontSize:12, marginBottom:8 }}>
          👤 {auth?.user?.username}
          <Badge color="blue" style={{ marginLeft:6 }}>{auth?.user?.role}</Badge>
        </div>
        <Btn variant="ghost" size="sm" style={{ color:'rgba(255,255,255,.5)', width:'100%', justifyContent:'center' }} onClick={auth?.logout}>
          Sign Out
        </Btn>
      </div>
    </aside>
  );
}

// ─────────────────────────────────────────────────────────
// Login Screen
// ─────────────────────────────────────────────────────────
function LoginScreen({ onLogin }) {
  const [form, setForm]   = useState({ username:'', password:'' });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const submit = async e => {
    e.preventDefault();
    setLoading(true); setError('');
    try {
      const data = await apiJson('auth.php?action=login', 'POST', form);
      onLogin(data.user);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div style={{ minHeight:'100vh', display:'flex', alignItems:'center', justifyContent:'center', background:'linear-gradient(135deg,#0f172a 0%,#1e3a5f 100%)' }}>
      <div style={{ ...css.card, width:380, padding:40, textAlign:'center' }}>
        <div style={{ fontSize:36, marginBottom:8 }}>⚡</div>
        <h1 style={{ fontSize:22, marginBottom:4 }}>Admin Login</h1>
        <p style={{ color:'var(--text-muted)', fontSize:13, marginBottom:24 }}>Fake Address Generator CMS</p>
        {error && <div style={{ background:'#fee2e2', color:'#b91c1c', padding:'10px 14px', borderRadius:6, marginBottom:16, fontSize:13 }}>{error}</div>}
        <form onSubmit={submit}>
          <Input label="Username or Email" value={form.username} onChange={e => setForm(f=>({...f,username:e.target.value}))} placeholder="admin" required />
          <Input label="Password" type="password" value={form.password} onChange={e => setForm(f=>({...f,password:e.target.value}))} placeholder="••••••••" required />
          <Btn type="submit" variant="primary" disabled={loading} style={{ width:'100%', justifyContent:'center', padding:'10px 0' }}>
            {loading ? <><Spinner /> Signing in…</> : 'Sign In'}
          </Btn>
        </form>
        <p style={{ marginTop:16, fontSize:11, color:'var(--text-muted)' }}>
          Default: admin / Admin@1234 — change on first login
        </p>
      </div>
    </div>
  );
}

// ─────────────────────────────────────────────────────────
// Dashboard View
// ─────────────────────────────────────────────────────────
function DashboardView() {
  const [stats, setStats]   = useState(null);
  const [topPosts, setTop]  = useState([]);
  const [byCo, setByCo]     = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    Promise.all([
      api('data.php?resource=analytics&type=overview'),
      api('data.php?resource=analytics&type=top_posts'),
      api('data.php?resource=analytics&type=generations_by_country'),
    ]).then(([s,p,c]) => {
      setStats(s.data); setTop(p.data); setByCo(c.data);
    }).finally(() => setLoading(false));
  }, []);

  if (loading) return <div style={{ padding:40, textAlign:'center', color:'var(--text-muted)' }}>Loading dashboard…</div>;

  return (
    <div>
      <h2 style={{ marginBottom:20 }}>Dashboard</h2>
      <div style={{ display:'grid', gridTemplateColumns:'repeat(auto-fill,minmax(200px,1fr))', gap:16, marginBottom:28 }}>
        <StatCard label="Total Generations" value={stats?.total_generations}  icon="🗺️" color="#5d83f1" />
        <StatCard label="Today's Generations" value={stats?.today_generations} icon="📍" color="#8cc63f" />
        <StatCard label="Published Posts"   value={stats?.published_posts}    icon="📝" color="#f59e0b" />
        <StatCard label="Total Post Views"  value={stats?.total_post_views}   icon="👁️" color="#ec4899" />
      </div>

      <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr', gap:20 }}>
        <div style={css.card}>
          <div style={{ padding:'16px 20px', borderBottom:'1px solid var(--border)', fontWeight:600 }}>🏆 Top Posts by Views</div>
          <div style={{ padding:16 }}>
            {topPosts.length === 0 && <p style={{ color:'var(--text-muted)', fontSize:13 }}>No posts yet.</p>}
            {topPosts.map((p,i) => (
              <div key={p.id} style={{ display:'flex', justifyContent:'space-between', padding:'8px 0', borderBottom:'1px solid #f1f5f9', fontSize:13 }}>
                <span style={{ color:'var(--text-muted)', marginRight:10 }}>#{i+1}</span>
                <span style={{ flex:1, overflow:'hidden', textOverflow:'ellipsis', whiteSpace:'nowrap' }}>{p.title}</span>
                <Badge color="blue">{p.views?.toLocaleString()} views</Badge>
              </div>
            ))}
          </div>
        </div>

        <div style={css.card}>
          <div style={{ padding:'16px 20px', borderBottom:'1px solid var(--border)', fontWeight:600 }}>🌍 Generations by Country</div>
          <div style={{ padding:16 }}>
            {byCo.length === 0 && <p style={{ color:'var(--text-muted)', fontSize:13 }}>No data yet.</p>}
            {byCo.map(row => (
              <div key={row.country} style={{ display:'flex', justifyContent:'space-between', padding:'8px 0', borderBottom:'1px solid #f1f5f9', fontSize:13, alignItems:'center' }}>
                <span style={{ textTransform:'uppercase', fontWeight:600, color:'var(--brand)' }}>{row.country || 'N/A'}</span>
                <div style={{ flex:1, margin:'0 12px', height:6, background:'#f1f5f9', borderRadius:3, overflow:'hidden' }}>
                  <div style={{ width:`${Math.min(100,row.count/Math.max(...byCo.map(r=>r.count))*100)}%`, height:'100%', background:'var(--brand)', borderRadius:3 }} />
                </div>
                <span>{row.count?.toLocaleString()}</span>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}

// ─────────────────────────────────────────────────────────
// Quill Editor Component
// ─────────────────────────────────────────────────────────
function QuillEditor({ value, onChange, height=300 }) {
  const containerRef = useRef(null);
  const quillRef     = useRef(null);

  useEffect(() => {
    if (!containerRef.current || quillRef.current) return;
    quillRef.current = new Quill(containerRef.current, {
      theme: 'snow',
      modules: {
        toolbar: [
          [{ header: [1,2,3,false] }],
          ['bold','italic','underline','strike'],
          ['link','image','blockquote','code-block'],
          [{ list:'ordered' },{ list:'bullet' }],
          ['clean'],
        ],
      },
    });
    quillRef.current.on('text-change', () => {
      onChange(quillRef.current.root.innerHTML);
    });
    if (value) quillRef.current.root.innerHTML = value;
  }, []);

  return <div ref={containerRef} style={{ height, fontSize:14 }} />;
}

// ─────────────────────────────────────────────────────────
// Posts View
// ─────────────────────────────────────────────────────────
function PostsView() {
  const toast = useToast();
  const [posts, setPosts]       = useState([]);
  const [cats, setCats]         = useState([]);
  const [loading, setLoading]   = useState(true);
  const [search, setSearch]     = useState('');
  const [statusF, setStatusF]   = useState('');
  const [page, setPage]         = useState(1);
  const [total, setTotal]       = useState(0);
  const [editing, setEditing]   = useState(null); // null = list, 'new' = new, {id} = edit
  const PER_PAGE = 15;

  const fetchPosts = useCallback(async () => {
    setLoading(true);
    try {
      const params = new URLSearchParams({ page, per_page:PER_PAGE, search, status:statusF });
      const data = await api(`posts.php?${params}`);
      setPosts(data.data); setTotal(data.total);
    } finally { setLoading(false); }
  }, [page, search, statusF]);

  useEffect(() => { fetchPosts(); }, [fetchPosts]);
  useEffect(() => { api('data.php?resource=categories').then(d => setCats(d.data)); }, []);

  const deletePost = async id => {
    try {
      await api(`posts.php?id=${id}`, { method:'DELETE' });
      toast('Post deleted'); fetchPosts();
    } catch (e) { toast(e.message, 'error'); }
  };

  if (editing !== null) {
    return <PostEditor postId={editing==='new'?null:editing} cats={cats} onBack={() => { setEditing(null); fetchPosts(); }} />;
  }

  return (
    <div>
      <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center', marginBottom:20 }}>
        <h2>Blog Posts</h2>
        <Btn onClick={() => setEditing('new')}>+ New Post</Btn>
      </div>

      <div style={{ display:'flex', gap:10, marginBottom:16, flexWrap:'wrap' }}>
        <input placeholder="Search posts…" value={search} onChange={e=>{setSearch(e.target.value);setPage(1);}} style={{ ...css.input, width:240 }} />
        <select value={statusF} onChange={e=>{setStatusF(e.target.value);setPage(1);}} style={{ ...css.input, width:140 }}>
          <option value="">All Status</option>
          <option value="published">Published</option>
          <option value="draft">Draft</option>
        </select>
      </div>

      <div style={css.card}>
        <table style={{ width:'100%', borderCollapse:'collapse' }}>
          <thead>
            <tr style={{ borderBottom:'1px solid var(--border)', background:'#f8fafc' }}>
              {['Thumbnail','Title','Category','Status','Featured','Views','Date','Actions'].map(h => (
                <th key={h} style={{ padding:'10px 14px', textAlign:'left', fontSize:11, fontWeight:600, color:'var(--text-muted)', textTransform:'uppercase', letterSpacing:'.05em' }}>{h}</th>
              ))}
            </tr>
          </thead>
          <tbody>
            {loading && (
              <tr><td colSpan={8} style={{ textAlign:'center', padding:40, color:'var(--text-muted)' }}>Loading…</td></tr>
            )}
            {!loading && posts.length === 0 && (
              <tr><td colSpan={8} style={{ textAlign:'center', padding:40, color:'var(--text-muted)' }}>No posts found.</td></tr>
            )}
            {posts.map(p => (
              <tr key={p.id} style={{ borderBottom:'1px solid var(--border)', transition:'background .1s' }}
                  onMouseEnter={e=>e.currentTarget.style.background='#f8fafc'}
                  onMouseLeave={e=>e.currentTarget.style.background=''}>
                <td style={{ padding:'10px 14px' }}>
                  {p.thumbnail && <img src={'/' + p.thumbnail} alt="" style={{ width:48, height:36, objectFit:'cover', borderRadius:4 }} />}
                </td>
                <td style={{ padding:'10px 14px', fontWeight:500, maxWidth:240 }}>
                  <div style={{ overflow:'hidden', textOverflow:'ellipsis', whiteSpace:'nowrap' }}>{p.title}</div>
                  <div style={{ fontSize:11, color:'var(--text-muted)', fontFamily:'monospace' }}>/blog/{p.slug}</div>
                </td>
                <td style={{ padding:'10px 14px', fontSize:12, color:'var(--text-muted)' }}>{p.category_name || '—'}</td>
                <td style={{ padding:'10px 14px' }}>
                  <Badge color={p.status==='published'?'green':'gray'}>{p.status}</Badge>
                </td>
                <td style={{ padding:'10px 14px', textAlign:'center' }}>{p.is_featured ? '⭐' : '—'}</td>
                <td style={{ padding:'10px 14px', fontSize:12 }}>{Number(p.views).toLocaleString()}</td>
                <td style={{ padding:'10px 14px', fontSize:12, color:'var(--text-muted)', whiteSpace:'nowrap' }}>
                  {p.published_at ? new Date(p.published_at).toLocaleDateString() : 'Draft'}
                </td>
                <td style={{ padding:'10px 14px' }}>
                  <div style={{ display:'flex', gap:6 }}>
                    <Btn size="sm" variant="secondary" onClick={() => setEditing(p.id)}>Edit</Btn>
                    <DeleteBtn onConfirm={() => deletePost(p.id)} />
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      {/* Pagination */}
      {total > PER_PAGE && (
        <div style={{ display:'flex', gap:8, justifyContent:'center', marginTop:16 }}>
          {page > 1 && <Btn variant="secondary" size="sm" onClick={() => setPage(p=>p-1)}>← Prev</Btn>}
          <span style={{ padding:'5px 12px', fontSize:13, color:'var(--text-muted)' }}>Page {page} of {Math.ceil(total/PER_PAGE)}</span>
          {page < Math.ceil(total/PER_PAGE) && <Btn variant="secondary" size="sm" onClick={() => setPage(p=>p+1)}>Next →</Btn>}
        </div>
      )}
    </div>
  );
}

function DeleteBtn({ onConfirm }) {
  const [open, setOpen] = useState(false);
  return (
    <>
      <Btn size="sm" variant="danger" onClick={() => setOpen(true)}>Delete</Btn>
      <Confirm open={open} message="Are you sure you want to delete this post? This cannot be undone." onConfirm={() => { onConfirm(); setOpen(false); }} onCancel={() => setOpen(false)} />
    </>
  );
}

// ─────────────────────────────────────────────────────────
// Post Editor
// ─────────────────────────────────────────────────────────
function PostEditor({ postId, cats, onBack }) {
  const toast   = useToast();
  const isNew   = !postId;
  const [form, setForm]     = useState({ title:'', slug:'', excerpt:'', content:'', category_id:'', status:'draft', is_featured:0, meta_title:'', meta_description:'', meta_keywords:'' });
  const [thumb, setThumb]   = useState(null);
  const [preview, setPreview] = useState('');
  const [loading, setLoading] = useState(!isNew);
  const [saving, setSaving] = useState(false);
  const [tab, setTab]       = useState('content'); // 'content' | 'seo'

  useEffect(() => {
    if (!isNew) {
      api(`posts.php?id=${postId}`).then(d => {
        setForm(f => ({ ...f, ...d.data }));
        if (d.data.thumbnail) setPreview('/' + d.data.thumbnail);
      }).finally(() => setLoading(false));
    }
  }, [postId]);

  const autoSlug = title => title.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');

  const set = (k, v) => setForm(f => ({ ...f, [k]: v }));

  const save = async status => {
    if (!form.title.trim()) { toast('Title is required', 'error'); return; }
    if (!form.content || form.content === '<p><br></p>') { toast('Content is required', 'error'); return; }
    if (isNew && !thumb) { toast('Thumbnail is required to save a post', 'error'); return; }

    setSaving(true);
    try {
      const fd = new FormData();
      Object.entries({ ...form, status: status || form.status }).forEach(([k,v]) => fd.append(k, v));
      if (thumb) fd.append('thumbnail', thumb);

      if (isNew) {
        await fetch('/admin/api/posts.php', { method:'POST', credentials:'include', body:fd });
        toast('Post created!');
      } else {
        fd.append('_method', 'PUT');
        await fetch(`/admin/api/posts.php?id=${postId}`, { method:'POST', credentials:'include', body:fd });
        toast('Post updated!');
      }
      onBack();
    } catch (e) {
      toast(e.message || 'Save failed', 'error');
    } finally {
      setSaving(false);
    }
  };

  if (loading) return <div style={{ padding:40, textAlign:'center', color:'var(--text-muted)' }}>Loading post…</div>;

  return (
    <div>
      <div style={{ display:'flex', alignItems:'center', gap:12, marginBottom:24 }}>
        <Btn variant="ghost" onClick={onBack} style={{ color:'var(--text-muted)' }}>← Back</Btn>
        <h2>{isNew ? 'New Post' : 'Edit Post'}</h2>
        <div style={{ marginLeft:'auto', display:'flex', gap:8 }}>
          <Btn variant="secondary" onClick={() => save('draft')} disabled={saving}>Save Draft</Btn>
          <Btn variant="success" onClick={() => save('published')} disabled={saving}>
            {saving ? <><Spinner /> Saving…</> : '✓ Publish'}
          </Btn>
        </div>
      </div>

      <div style={{ display:'grid', gridTemplateColumns:'1fr 300px', gap:20, alignItems:'start' }}>
        {/* Main editing area */}
        <div>
          <Input label="Post Title" value={form.title} onChange={e => { set('title',e.target.value); if(isNew||!form.slug) set('slug',autoSlug(e.target.value)); }} placeholder="Enter post title…" />
          <div style={{ display:'flex', gap:10, marginBottom:16 }}>
            <div style={{ flex:1 }}>
              <label style={css.label}>Slug</label>
              <input value={form.slug} onChange={e=>set('slug',e.target.value)} style={css.input} placeholder="auto-generated-slug" />
            </div>
          </div>
          <Textarea label="Excerpt" value={form.excerpt} onChange={e=>set('excerpt',e.target.value)} placeholder="Short description for blog listings…" style={{ height:80 }} />

          {/* Tab switcher */}
          <div style={{ display:'flex', gap:0, marginBottom:12, borderBottom:'1px solid var(--border)' }}>
            {['content','seo'].map(t => (
              <button key={t} onClick={()=>setTab(t)} style={{ padding:'8px 16px', background:'none', border:'none', cursor:'pointer', fontSize:13, fontWeight:tab===t?600:400, color:tab===t?'var(--brand)':'var(--text-muted)', borderBottom:tab===t?'2px solid var(--brand)':'2px solid transparent', marginBottom:-1 }}>
                {t === 'content' ? '📝 Content' : '🔍 SEO'}
              </button>
            ))}
          </div>

          {tab === 'content' && (
            <div style={{ border:'1px solid var(--border)', borderRadius:6, overflow:'hidden' }}>
              <QuillEditor value={form.content} onChange={v => set('content',v)} height={400} />
            </div>
          )}

          {tab === 'seo' && (
            <div style={css.card}>
              <div style={{ padding:20 }}>
                <Input label="Meta Title" value={form.meta_title} onChange={e=>set('meta_title',e.target.value)} placeholder="SEO title (defaults to post title)" />
                <Textarea label="Meta Description" value={form.meta_description} onChange={e=>set('meta_description',e.target.value)} placeholder="Brief description for search engines (150–160 chars)" style={{ height:80 }} />
                <Input label="Keywords" value={form.meta_keywords} onChange={e=>set('meta_keywords',e.target.value)} placeholder="keyword1, keyword2, keyword3" />
                {form.meta_title && (
                  <div style={{ border:'1px solid var(--border)', borderRadius:6, padding:14, background:'#f8fafc' }}>
                    <div style={{ fontSize:11, color:'var(--text-muted)', marginBottom:4 }}>SERP Preview</div>
                    <div style={{ color:'#1a0dab', fontSize:16, marginBottom:2 }}>{form.meta_title}</div>
                    <div style={{ color:'#006621', fontSize:12, marginBottom:2 }}>example.com/blog/{form.slug}</div>
                    <div style={{ color:'#545454', fontSize:13 }}>{form.meta_description}</div>
                  </div>
                )}
              </div>
            </div>
          )}
        </div>

        {/* Sidebar options */}
        <div style={{ display:'flex', flexDirection:'column', gap:16 }}>
          {/* Thumbnail */}
          <div style={css.card}>
            <div style={{ padding:'12px 16px', borderBottom:'1px solid var(--border)', fontWeight:600, fontSize:13 }}>🖼️ Thumbnail <span style={{ color:'var(--danger)' }}>*</span></div>
            <div style={{ padding:16 }}>
              {preview && <img src={preview} alt="thumb" style={{ width:'100%', aspectRatio:'16/9', objectFit:'cover', borderRadius:6, marginBottom:12 }} />}
              <label style={{ display:'block', padding:'8px 12px', background:'var(--bg)', border:'2px dashed var(--border)', borderRadius:6, textAlign:'center', cursor:'pointer', fontSize:12, color:'var(--text-muted)' }}>
                {thumb || preview ? '🔄 Change Thumbnail' : '📸 Upload Thumbnail'}
                <input type="file" accept="image/*" style={{ display:'none' }} onChange={e => {
                  const f = e.target.files[0];
                  if (f) { setThumb(f); setPreview(URL.createObjectURL(f)); }
                }} />
              </label>
              {!preview && !thumb && <p style={{ fontSize:11, color:'var(--danger)', marginTop:6 }}>Required — cannot publish without thumbnail</p>}
            </div>
          </div>

          {/* Category & Status */}
          <div style={css.card}>
            <div style={{ padding:'12px 16px', borderBottom:'1px solid var(--border)', fontWeight:600, fontSize:13 }}>📋 Details</div>
            <div style={{ padding:16 }}>
              <Select label="Category" value={form.category_id} onChange={e=>set('category_id',e.target.value)}>
                <option value="">— Uncategorized —</option>
                {cats.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
              </Select>
              <Select label="Status" value={form.status} onChange={e=>set('status',e.target.value)}>
                <option value="draft">Draft</option>
                <option value="published">Published</option>
              </Select>
              <div style={{ display:'flex', alignItems:'center', gap:8 }}>
                <input type="checkbox" id="featured" checked={!!form.is_featured} onChange={e=>set('is_featured',e.target.checked?1:0)} style={{ width:16, height:16 }} />
                <label htmlFor="featured" style={{ fontSize:13, cursor:'pointer' }}>⭐ Featured Post</label>
              </div>
              <p style={{ fontSize:11, color:'var(--text-muted)', marginTop:4 }}>Max 5 featured posts allowed</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

// ─────────────────────────────────────────────────────────
// Categories View
// ─────────────────────────────────────────────────────────
function CategoriesView() {
  const toast = useToast();
  const [cats, setCats]     = useState([]);
  const [modal, setModal]   = useState(false);
  const [editing, setEditing] = useState(null);
  const [form, setForm]     = useState({ name:'', description:'' });

  const fetch_ = () => api('data.php?resource=categories').then(d=>setCats(d.data));
  useEffect(() => { fetch_(); }, []);

  const save = async () => {
    try {
      if (editing) {
        await apiJson(`data.php?resource=categories&id=${editing}`, 'PUT', form);
        toast('Category updated');
      } else {
        await apiJson('data.php?resource=categories', 'POST', form);
        toast('Category created');
      }
      setModal(false); setForm({ name:'', description:'' }); setEditing(null); fetch_();
    } catch (e) { toast(e.message,'error'); }
  };

  const del = async id => {
    try { await api(`data.php?resource=categories&id=${id}`, { method:'DELETE' }); toast('Deleted'); fetch_(); }
    catch (e) { toast(e.message,'error'); }
  };

  return (
    <div>
      <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center', marginBottom:20 }}>
        <h2>Categories</h2>
        <Btn onClick={() => { setEditing(null); setForm({name:'',description:''}); setModal(true); }}>+ New Category</Btn>
      </div>
      <div style={css.card}>
        <table style={{ width:'100%', borderCollapse:'collapse' }}>
          <thead>
            <tr style={{ borderBottom:'1px solid var(--border)', background:'#f8fafc' }}>
              {['Name','Slug','Description','Actions'].map(h=><th key={h} style={{ padding:'10px 14px', textAlign:'left', fontSize:11, fontWeight:600, color:'var(--text-muted)', textTransform:'uppercase' }}>{h}</th>)}
            </tr>
          </thead>
          <tbody>
            {cats.map(c=>(
              <tr key={c.id} style={{ borderBottom:'1px solid var(--border)' }}>
                <td style={{ padding:'10px 14px', fontWeight:500 }}>{c.name}</td>
                <td style={{ padding:'10px 14px', fontFamily:'monospace', fontSize:12, color:'var(--text-muted)' }}>{c.slug}</td>
                <td style={{ padding:'10px 14px', fontSize:13, color:'var(--text-muted)' }}>{c.description||'—'}</td>
                <td style={{ padding:'10px 14px' }}>
                  <div style={{ display:'flex', gap:6 }}>
                    <Btn size="sm" variant="secondary" onClick={()=>{ setEditing(c.id); setForm({name:c.name,description:c.description||''}); setModal(true); }}>Edit</Btn>
                    <DeleteBtn onConfirm={()=>del(c.id)} />
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      <Modal open={modal} title={editing?'Edit Category':'New Category'} onClose={()=>setModal(false)} width={440}>
        <Input label="Name" value={form.name} onChange={e=>setForm(f=>({...f,name:e.target.value}))} placeholder="Category name" />
        <Textarea label="Description (optional)" value={form.description} onChange={e=>setForm(f=>({...f,description:e.target.value}))} style={{ height:80 }} />
        <div style={{ display:'flex', justifyContent:'flex-end', gap:8 }}>
          <Btn variant="secondary" onClick={()=>setModal(false)}>Cancel</Btn>
          <Btn onClick={save}>Save</Btn>
        </div>
      </Modal>
    </div>
  );
}

// ─────────────────────────────────────────────────────────
// Content Sections View
// ─────────────────────────────────────────────────────────
function ContentView() {
  const toast = useToast();
  const [sections, setSections] = useState([]);
  const [modal, setModal]       = useState(false);
  const [editing, setEditing]   = useState(null);
  const [form, setForm]         = useState({ section_key:'', title:'', body:'', sort_order:0, is_active:1 });

  const fetch_ = () => api('data.php?resource=content_sections').then(d=>setSections(d.data));
  useEffect(() => { fetch_(); }, []);

  const save = async () => {
    try {
      if (editing) {
        await apiJson(`data.php?resource=content_sections&id=${editing}`, 'PUT', form);
        toast('Section updated');
      } else {
        await apiJson('data.php?resource=content_sections', 'POST', form);
        toast('Section created');
      }
      setModal(false); fetch_();
    } catch (e) { toast(e.message,'error'); }
  };

  const del = async id => {
    try { await api(`data.php?resource=content_sections&id=${id}`, { method:'DELETE' }); toast('Deleted'); fetch_(); }
    catch (e) { toast(e.message,'error'); }
  };

  return (
    <div>
      <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center', marginBottom:20 }}>
        <h2>Content Sections</h2>
        <Btn onClick={() => { setEditing(null); setForm({section_key:'',title:'',body:'',sort_order:0,is_active:1}); setModal(true); }}>+ New Section</Btn>
      </div>
      <p style={{ color:'var(--text-muted)', fontSize:13, marginBottom:16 }}>These sections appear dynamically on the homepage. Use them to manage the "Why Use" and "How It Works" blocks.</p>

      {sections.map(s => (
        <div key={s.id} style={{ ...css.card, marginBottom:16, padding:0, overflow:'hidden' }}>
          <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center', padding:'14px 18px', borderBottom:'1px solid var(--border)', background:'#f8fafc' }}>
            <div>
              <span style={{ fontWeight:600 }}>{s.title}</span>
              <span style={{ marginLeft:10, fontSize:11, fontFamily:'monospace', color:'var(--text-muted)' }}>{s.section_key}</span>
            </div>
            <div style={{ display:'flex', gap:8, alignItems:'center' }}>
              <Badge color={s.is_active?'green':'gray'}>{s.is_active?'Active':'Hidden'}</Badge>
              <Btn size="sm" variant="secondary" onClick={()=>{ setEditing(s.id); setForm({section_key:s.section_key,title:s.title,body:s.body,sort_order:s.sort_order,is_active:s.is_active}); setModal(true); }}>Edit</Btn>
              <DeleteBtn onConfirm={()=>del(s.id)} />
            </div>
          </div>
          <div style={{ padding:'12px 18px', fontSize:13, color:'var(--text-muted)' }} dangerouslySetInnerHTML={{ __html: s.body?.substring(0,200)+'…' }} />
        </div>
      ))}

      <Modal open={modal} title={editing?'Edit Section':'New Section'} onClose={()=>setModal(false)} width={700}>
        <Input label="Section Key (unique identifier)" value={form.section_key} onChange={e=>setForm(f=>({...f,section_key:e.target.value}))} placeholder="homepage_why_use" disabled={!!editing} />
        <Input label="Title" value={form.title} onChange={e=>setForm(f=>({...f,title:e.target.value}))} placeholder="Why Use a Fake Address Generator?" />
        <div style={{ marginBottom:16 }}>
          <label style={css.label}>Content (HTML)</label>
          <div style={{ border:'1px solid var(--border)', borderRadius:6, overflow:'hidden' }}>
            <QuillEditor value={form.body} onChange={v=>setForm(f=>({...f,body:v}))} height={240} />
          </div>
        </div>
        <div style={{ display:'flex', gap:16, marginBottom:16 }}>
          <div style={{ flex:1 }}>
            <label style={css.label}>Sort Order</label>
            <input type="number" value={form.sort_order} onChange={e=>setForm(f=>({...f,sort_order:parseInt(e.target.value)||0}))} style={{ ...css.input }} />
          </div>
          <div style={{ display:'flex', alignItems:'flex-end', paddingBottom:4 }}>
            <label style={{ display:'flex', alignItems:'center', gap:8, cursor:'pointer' }}>
              <input type="checkbox" checked={!!form.is_active} onChange={e=>setForm(f=>({...f,is_active:e.target.checked?1:0}))} style={{ width:16,height:16 }} />
              <span style={{ fontSize:13 }}>Active (visible on homepage)</span>
            </label>
          </div>
        </div>
        <div style={{ display:'flex', justifyContent:'flex-end', gap:8 }}>
          <Btn variant="secondary" onClick={()=>setModal(false)}>Cancel</Btn>
          <Btn onClick={save}>Save Section</Btn>
        </div>
      </Modal>
    </div>
  );
}

// ─────────────────────────────────────────────────────────
// Footer Links View
// ─────────────────────────────────────────────────────────
function FooterLinksView() {
  const toast = useToast();
  const [links, setLinks]   = useState([]);
  const [modal, setModal]   = useState(false);
  const [editing, setEditing] = useState(null);
  const [form, setForm]     = useState({ label:'', url:'', row_number:1, sort_order:0, is_active:1 });

  const fetch_ = () => api('data.php?resource=footer_links').then(d=>setLinks(d.data));
  useEffect(() => { fetch_(); }, []);

  const save = async () => {
    try {
      if (editing) { await apiJson(`data.php?resource=footer_links&id=${editing}`, 'PUT', form); toast('Updated'); }
      else { await apiJson('data.php?resource=footer_links', 'POST', form); toast('Created'); }
      setModal(false); fetch_();
    } catch(e) { toast(e.message,'error'); }
  };

  const del = async id => {
    try { await api(`data.php?resource=footer_links&id=${id}`, {method:'DELETE'}); toast('Deleted'); fetch_(); }
    catch(e) { toast(e.message,'error'); }
  };

  // Group by row
  const grouped = links.reduce((acc,l) => { const r=l.row_number; acc[r]??=[]; acc[r].push(l); return acc; }, {});

  return (
    <div>
      <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center', marginBottom:20 }}>
        <h2>Footer Links</h2>
        <Btn onClick={() => { setEditing(null); setForm({label:'',url:'',row_number:1,sort_order:0,is_active:1}); setModal(true); }}>+ Add Link</Btn>
      </div>
      <p style={{ color:'var(--text-muted)', fontSize:13, marginBottom:16 }}>
        Links are organized into rows. The funnel layout shows more links in row 1, fewer in row 2, etc. for a narrowing effect.
      </p>

      {Object.entries(grouped).map(([row, rowLinks]) => (
        <div key={row} style={{ marginBottom:20 }}>
          <div style={{ fontSize:12, fontWeight:600, color:'var(--text-muted)', textTransform:'uppercase', letterSpacing:'.06em', marginBottom:8 }}>Row {row} — {rowLinks.length} link{rowLinks.length!==1?'s':''}</div>
          <div style={{ ...css.card, overflow:'hidden' }}>
            {rowLinks.map((l,i) => (
              <div key={l.id} style={{ display:'flex', alignItems:'center', gap:12, padding:'10px 16px', borderBottom: i<rowLinks.length-1 ? '1px solid var(--border)' : 'none' }}>
                <Badge color={l.is_active?'green':'gray'}>{l.is_active?'On':'Off'}</Badge>
                <span style={{ fontWeight:500, flex:1 }}>{l.label}</span>
                <span style={{ fontFamily:'monospace', fontSize:12, color:'var(--text-muted)', maxWidth:200, overflow:'hidden', textOverflow:'ellipsis' }}>{l.url}</span>
                <div style={{ display:'flex', gap:6 }}>
                  <Btn size="sm" variant="secondary" onClick={()=>{ setEditing(l.id); setForm({label:l.label,url:l.url,row_number:l.row_number,sort_order:l.sort_order,is_active:l.is_active}); setModal(true); }}>Edit</Btn>
                  <DeleteBtn onConfirm={()=>del(l.id)} />
                </div>
              </div>
            ))}
          </div>
        </div>
      ))}

      <Modal open={modal} title={editing?'Edit Link':'Add Footer Link'} onClose={()=>setModal(false)} width={480}>
        <Input label="Label" value={form.label} onChange={e=>setForm(f=>({...f,label:e.target.value}))} placeholder="US Address Generator" />
        <Input label="URL" value={form.url} onChange={e=>setForm(f=>({...f,url:e.target.value}))} placeholder="/us-fake-address or https://…" />
        <div style={{ display:'flex', gap:16 }}>
          <div style={{ flex:1 }}>
            <label style={css.label}>Funnel Row</label>
            <input type="number" min={1} max={10} value={form.row_number} onChange={e=>setForm(f=>({...f,row_number:parseInt(e.target.value)||1}))} style={css.input} />
            <p style={{ fontSize:11, color:'var(--text-muted)', marginTop:4 }}>Row 1 = top (most links), higher rows = fewer links</p>
          </div>
          <div style={{ flex:1 }}>
            <label style={css.label}>Sort Order</label>
            <input type="number" value={form.sort_order} onChange={e=>setForm(f=>({...f,sort_order:parseInt(e.target.value)||0}))} style={css.input} />
          </div>
        </div>
        <label style={{ display:'flex', alignItems:'center', gap:8, cursor:'pointer', margin:'12px 0' }}>
          <input type="checkbox" checked={!!form.is_active} onChange={e=>setForm(f=>({...f,is_active:e.target.checked?1:0}))} style={{ width:16,height:16 }} />
          <span style={{ fontSize:13 }}>Active (visible in footer)</span>
        </label>
        <div style={{ display:'flex', justifyContent:'flex-end', gap:8 }}>
          <Btn variant="secondary" onClick={()=>setModal(false)}>Cancel</Btn>
          <Btn onClick={save}>Save Link</Btn>
        </div>
      </Modal>
    </div>
  );
}

// ─────────────────────────────────────────────────────────
// Settings View
// ─────────────────────────────────────────────────────────
function SettingsView() {
  const toast = useToast();
  const [settings, setSettings] = useState([]);
  const [form, setForm]         = useState({});
  const [saving, setSaving]     = useState(false);
  const [pwForm, setPwForm]     = useState({ current_password:'', new_password:'', confirm_password:'' });

  useEffect(() => {
    api('data.php?resource=settings').then(d => {
      setSettings(d.data);
      const map = {};
      d.data.forEach(s => { map[s.setting_key] = s.setting_value || ''; });
      setForm(map);
    });
  }, []);

  const save = async () => {
    setSaving(true);
    try {
      await apiJson('data.php?resource=settings', 'POST', { settings: form });
      toast('Settings saved');
    } catch(e) { toast(e.message,'error'); } finally { setSaving(false); }
  };

  const changePassword = async () => {
    if (pwForm.new_password !== pwForm.confirm_password) { toast('Passwords do not match','error'); return; }
    if (pwForm.new_password.length < 8) { toast('Password must be at least 8 characters','error'); return; }
    try {
      await apiJson('auth.php?action=change_password', 'POST', pwForm);
      toast('Password changed successfully');
      setPwForm({current_password:'',new_password:'',confirm_password:''});
    } catch(e) { toast(e.message,'error'); }
  };

  const renderField = (key, value) => {
    const s = settings.find(x=>x.setting_key===key);
    if (!s) return null;
    if (s.setting_type === 'html') {
      return (
        <div key={key} style={{ marginBottom:20 }}>
          <label style={css.label}>{s.label}</label>
          <textarea value={form[key]||''} onChange={e=>setForm(f=>({...f,[key]:e.target.value}))} style={{ ...css.input, minHeight:120, fontFamily:'JetBrains Mono, monospace', fontSize:12 }} placeholder={`<!-- ${s.label} -->`} />
          <p style={{ fontSize:11, color:'var(--text-muted)', marginTop:4 }}>Paste analytics snippets, tag managers, or any custom HTML/JS</p>
        </div>
      );
    }
    return (
      <div key={key} style={{ marginBottom:20 }}>
        <label style={css.label}>{s.label}</label>
        <input value={form[key]||''} onChange={e=>setForm(f=>({...f,[key]:e.target.value}))} style={css.input} placeholder={s.label} />
      </div>
    );
  };

  return (
    <div>
      <h2 style={{ marginBottom:24 }}>Settings</h2>
      <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr', gap:20, alignItems:'start' }}>

        {/* Site Settings */}
        <div style={css.card}>
          <div style={{ padding:'14px 20px', borderBottom:'1px solid var(--border)', fontWeight:600 }}>🌐 Site Settings</div>
          <div style={{ padding:20 }}>
            {['site_name','site_tagline','contact_email','ga_tracking_id'].map(k => renderField(k, form[k]))}
            <Btn onClick={save} disabled={saving}>{saving?<><Spinner/> Saving…</>:'Save Settings'}</Btn>
          </div>
        </div>

        {/* Scripts */}
        <div style={css.card}>
          <div style={{ padding:'14px 20px', borderBottom:'1px solid var(--border)', fontWeight:600 }}>💻 Header / Footer Scripts</div>
          <div style={{ padding:20 }}>
            {['header_scripts','footer_scripts'].map(k => renderField(k, form[k]))}
            <Btn onClick={save} disabled={saving}>{saving?<><Spinner/> Saving…</>:'Save Scripts'}</Btn>
          </div>
        </div>

        {/* Change Password */}
        <div style={css.card}>
          <div style={{ padding:'14px 20px', borderBottom:'1px solid var(--border)', fontWeight:600 }}>🔐 Change Password</div>
          <div style={{ padding:20 }}>
            <Input label="Current Password" type="password" value={pwForm.current_password} onChange={e=>setPwForm(f=>({...f,current_password:e.target.value}))} />
            <Input label="New Password" type="password" value={pwForm.new_password} onChange={e=>setPwForm(f=>({...f,new_password:e.target.value}))} />
            <Input label="Confirm New Password" type="password" value={pwForm.confirm_password} onChange={e=>setPwForm(f=>({...f,confirm_password:e.target.value}))} />
            <Btn onClick={changePassword}>Update Password</Btn>
          </div>
        </div>

      </div>
    </div>
  );
}

// ─────────────────────────────────────────────────────────
// Main App Shell
// ─────────────────────────────────────────────────────────
function App() {
  const [user, setUser]       = useState(null);
  const [checking, setChecking] = useState(true);
  const [view, setView]       = useState('dashboard');

  useEffect(() => {
    api('auth.php?action=me').then(d => setUser(d.user)).catch(() => {}).finally(() => setChecking(false));
  }, []);

  const logout = async () => {
    await apiJson('auth.php?action=logout', 'POST', {});
    setUser(null);
  };

  if (checking) return <div style={{ display:'flex', alignItems:'center', justifyContent:'center', height:'100vh', color:'var(--text-muted)', fontSize:14 }}>Loading…</div>;
  if (!user) return <LoginScreen onLogin={setUser} />;

  const views = { dashboard:<DashboardView/>, posts:<PostsView/>, categories:<CategoriesView/>, content:<ContentView/>, footer_links:<FooterLinksView/>, settings:<SettingsView/> };

  return (
    <AuthCtx.Provider value={{ user, logout }}>
      <ToastProvider>
        <div style={{ display:'flex', minHeight:'100vh' }}>
          <Sidebar active={view} onNav={setView} />
          <main style={{ marginLeft:'var(--sidebar-w)', flex:1, padding:32, minHeight:'100vh', overflowX:'hidden' }}>
            <div style={{ maxWidth:1100, margin:'0 auto' }}>
              {views[view] || <DashboardView />}
            </div>
          </main>
        </div>
      </ToastProvider>
    </AuthCtx.Provider>
  );
}

ReactDOM.createRoot(document.getElementById('root')).render(<App />);
