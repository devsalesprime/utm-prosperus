"use client";

import { useEffect, useState, useCallback } from "react";
import Header from "@/components/Header";
import ThemeSwitch from "@/components/ThemeSwitch";
import { getProfile, updateProfileName, changePassword } from "@/lib/api";
import type { ProfileData } from "@/types/utm";
import { useAuth } from "@/lib/auth-context";
import { useRouter } from "next/navigation";
import Link from "next/link";

export default function PerfilPage() {
  const { session, loading: authLoading, refresh } = useAuth();
  const router = useRouter();

  const [data, setData] = useState<ProfileData | null>(null);
  const [loading, setLoading] = useState(true);

  // dados da conta
  const [nome, setNome] = useState("");
  const [nomeMsg, setNomeMsg] = useState<{ tipo: "ok" | "erro"; texto: string } | null>(null);
  const [salvandoNome, setSalvandoNome] = useState(false);

  // senha
  const [senhaAtual, setSenhaAtual] = useState("");
  const [senhaNova, setSenhaNova] = useState("");
  const [senhaConf, setSenhaConf] = useState("");
  const [senhaMsg, setSenhaMsg] = useState<{ tipo: "ok" | "erro"; texto: string } | null>(null);
  const [salvandoSenha, setSalvandoSenha] = useState(false);
  const [mostrar, setMostrar] = useState(false);

  useEffect(() => {
    if (authLoading) return;
    if (!session || !session.logged_in) router.push("/");
  }, [session, authLoading, router]);

  const carregar = useCallback(async () => {
    setLoading(true);
    try {
      const d = await getProfile();
      if (d && d.user) { setData(d); setNome(d.user.name); }
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => { if (session?.logged_in) carregar(); }, [session, carregar]);

  const salvarNome = async (e: React.FormEvent) => {
    e.preventDefault();
    setNomeMsg(null); setSalvandoNome(true);
    try {
      const r = await updateProfileName(nome.trim());
      if (r.success) { setNomeMsg({ tipo: "ok", texto: "Nome atualizado." }); await refresh(); carregar(); }
      else setNomeMsg({ tipo: "erro", texto: r.error || "Não foi possível salvar o nome." });
    } catch { setNomeMsg({ tipo: "erro", texto: "Falha de rede. Tente de novo." }); }
    finally { setSalvandoNome(false); }
  };

  const forcaSenha = (s: string) => {
    let n = 0;
    if (s.length >= 8) n++;
    if (/[A-Z]/.test(s) && /[a-z]/.test(s)) n++;
    if (/\d/.test(s)) n++;
    if (/[^A-Za-z0-9]/.test(s)) n++;
    return n; // 0 a 4
  };
  const forca = forcaSenha(senhaNova);
  const rotuloForca = ["", "Fraca", "Razoável", "Boa", "Forte"][forca];

  const salvarSenha = async (e: React.FormEvent) => {
    e.preventDefault();
    setSenhaMsg(null);
    if (senhaNova.length < 8) { setSenhaMsg({ tipo: "erro", texto: "A nova senha deve ter pelo menos 8 caracteres." }); return; }
    if (senhaNova !== senhaConf) { setSenhaMsg({ tipo: "erro", texto: "A confirmação não coincide com a nova senha." }); return; }
    setSalvandoSenha(true);
    try {
      const r = await changePassword(senhaAtual, senhaNova, senhaConf);
      if (r.success) { setSenhaMsg({ tipo: "ok", texto: "Senha alterada." }); setSenhaAtual(""); setSenhaNova(""); setSenhaConf(""); }
      else setSenhaMsg({ tipo: "erro", texto: r.error || "Não foi possível alterar a senha." });
    } catch { setSenhaMsg({ tipo: "erro", texto: "Falha de rede. Tente de novo." }); }
    finally { setSalvandoSenha(false); }
  };

  if (!session || !session.logged_in) return null;

  const u = data?.user; const at = data?.activity;
  const fmt = (iso?: string | null) => iso ? new Date(iso.replace(" ", "T")).toLocaleDateString("pt-BR") : "—";

  return (
    <>
      <ThemeSwitch />
      <Header />
      <div className="container mt-4 mb-5">
        <div className="ds-page-head">
          <span className="ds-page-head__icon" aria-hidden="true"><i className="bi bi-person-circle"></i></span>
          <div className="ds-page-head__text">
            <h1>Meu perfil</h1>
            <p>{u?.email ?? "…"}</p>
          </div>
          <div className="ds-page-head__aside">
            {u && <span className={`badge ${u.is_admin ? "ds-badge-admin" : "ds-badge-neutro"}`}>{u.is_admin ? "Administrador" : "Usuário"}</span>}
          </div>
        </div>

        <div className="row g-3">
          <div className="col-lg-7 d-grid gap-3">
            {/* Dados da conta */}
            <form className="ag-card p-3" onSubmit={salvarNome}>
              <h5 className="mb-3"><i className="bi bi-person ds-icon me-2"></i>Dados da conta</h5>
              <div className="mb-3">
                <label className="form-label ds-legend" htmlFor="perfil-nome">Nome</label>
                <input id="perfil-nome" className="form-control" value={nome} onChange={e => setNome(e.target.value)} minLength={3} maxLength={80} required disabled={loading} />
                <div className="form-text">É o nome que aparece como autor das suas UTMs. Renomear atualiza as UTMs antigas também.</div>
              </div>
              <div className="mb-3">
                <label className="form-label ds-legend" htmlFor="perfil-email">E-mail</label>
                <input id="perfil-email" className="form-control" value={u?.email ?? ""} readOnly disabled />
                <div className="form-text">O e-mail é o seu login e não muda por aqui. Peça a um administrador se precisar trocar.</div>
              </div>
              <div className="d-flex align-items-center gap-3 flex-wrap">
                <button type="submit" className="ag-btn-accent btn-sm px-4" disabled={salvandoNome || loading || !nome.trim() || nome.trim() === u?.name}>
                  {salvandoNome ? "Salvando…" : "Salvar nome"}
                </button>
                {nomeMsg && <span className={`small ${nomeMsg.tipo === "ok" ? "text-success" : "text-danger"}`} role="status">{nomeMsg.texto}</span>}
                <span className="small ms-auto" style={{ color: "var(--color-text-muted)" }}>Conta criada em {fmt(u?.created_at)}</span>
              </div>
            </form>

            {/* Alterar senha */}
            <form className="ag-card p-3" onSubmit={salvarSenha} autoComplete="off">
              <h5 className="mb-3"><i className="bi bi-shield-lock ds-icon me-2"></i>Alterar senha</h5>
              <div className="row g-3">
                <div className="col-12">
                  <label className="form-label ds-legend" htmlFor="senha-atual">Senha atual</label>
                  <input id="senha-atual" type={mostrar ? "text" : "password"} className="form-control" value={senhaAtual} onChange={e => setSenhaAtual(e.target.value)} autoComplete="current-password" required />
                </div>
                <div className="col-md-6">
                  <label className="form-label ds-legend" htmlFor="senha-nova">Nova senha</label>
                  <input id="senha-nova" type={mostrar ? "text" : "password"} className="form-control" value={senhaNova} onChange={e => setSenhaNova(e.target.value)} autoComplete="new-password" minLength={8} required aria-describedby="senha-ajuda" />
                </div>
                <div className="col-md-6">
                  <label className="form-label ds-legend" htmlFor="senha-conf">Confirmar nova senha</label>
                  <input id="senha-conf" type={mostrar ? "text" : "password"} className={`form-control ${senhaConf && senhaConf !== senhaNova ? "is-invalid" : ""}`} value={senhaConf} onChange={e => setSenhaConf(e.target.value)} autoComplete="new-password" required />
                </div>
              </div>
              <div id="senha-ajuda" className="form-text mt-2">
                Pelo menos 8 caracteres. Misturar maiúsculas, números e símbolos ajuda.
                {senhaNova && <span className="ms-2">Força: <strong>{rotuloForca}</strong></span>}
              </div>
              {senhaNova && (
                <div className="ds-forca mt-2" aria-hidden="true">
                  {[1, 2, 3, 4].map(i => <span key={i} className={`ds-forca__seg ${i <= forca ? "on" : ""}`}></span>)}
                </div>
              )}
              <div className="d-flex align-items-center gap-3 flex-wrap mt-3">
                <button type="submit" className="ag-btn-accent btn-sm px-4" disabled={salvandoSenha || !senhaAtual || !senhaNova || !senhaConf}>
                  {salvandoSenha ? "Alterando…" : "Alterar senha"}
                </button>
                <label className="small d-flex align-items-center gap-2 m-0" style={{ cursor: "pointer" }}>
                  <input type="checkbox" className="form-check-input m-0" checked={mostrar} onChange={e => setMostrar(e.target.checked)} /> Mostrar senhas
                </label>
                {senhaMsg && <span className={`small ${senhaMsg.tipo === "ok" ? "text-success" : "text-danger"}`} role="status">{senhaMsg.texto}</span>}
              </div>
            </form>
          </div>

          {/* Minha atividade */}
          <div className="col-lg-5">
            <div className="ag-card p-3 h-100">
              <h5 className="mb-3"><i className="bi bi-activity ds-icon me-2"></i>Minha atividade</h5>
              <div className="row g-2">
                <div className="col-6"><div className="ds-kpi ds-kpi--flat"><div className="ds-kpi__label">UTMs criadas</div><div className="ds-kpi__value">{loading ? "…" : (at?.total_utms ?? 0).toLocaleString("pt-BR")}</div></div></div>
                <div className="col-6"><div className="ds-kpi ds-kpi--flat"><div className="ds-kpi__label">Cliques recebidos</div><div className="ds-kpi__value">{loading ? "…" : (at?.total_clicks ?? 0).toLocaleString("pt-BR")}</div></div></div>
                <div className="col-6"><div className="ds-kpi ds-kpi--flat"><div className="ds-kpi__label">Ativas</div><div className="ds-kpi__value">{loading ? "…" : (at?.ativas ?? 0).toLocaleString("pt-BR")}</div></div></div>
                <div className="col-6"><div className="ds-kpi ds-kpi--flat"><div className="ds-kpi__label">Última UTM</div><div className="ds-kpi__value ds-kpi__value--sm">{loading ? "…" : fmt(at?.ultima_utm)}</div></div></div>
              </div>
              <p className="small mt-3 mb-2" style={{ color: "var(--color-text-muted)" }}>
                A atividade é contada pelo nome de autor nas UTMs.
              </p>
              <Link href="/" className="btn btn-sm ds-nav"><i className="bi bi-list-ul me-1" aria-hidden="true"></i>Ver minhas UTMs</Link>
            </div>
          </div>
        </div>
      </div>
    </>
  );
}
