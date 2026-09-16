"use client";

import { useEffect, useState, useCallback } from "react";
import { createPortal } from "react-dom";
import Header from "@/components/Header";
import ThemeSwitch from "@/components/ThemeSwitch";
import { getAdminUsers, approveUser, toggleAdmin, deleteAdminUser, getMasterStatus, setMasterPassword } from "@/lib/api";
import type { AdminUser, MasterStatus } from "@/types/utm";
import { useAuth } from "@/lib/auth-context";
import { useRouter } from "next/navigation";

export default function AdminPage() {
  const { session, loading: authLoading } = useAuth();
  const router = useRouter();
  const [users, setUsers] = useState<AdminUser[]>([]);
  const [loading, setLoading] = useState(true);

  // exclusao de usuario: irreversivel, exige senha master e confirmacao
  const [deleteUser, setDeleteUser] = useState<AdminUser | null>(null);
  const [deletePassword, setDeletePassword] = useState("");
  const [deleteError, setDeleteError] = useState("");
  const [deleteLoading, setDeleteLoading] = useState(false);
  const [mounted, setMounted] = useState(false);
  useEffect(() => { setMounted(true); }, []);
  const meuId = Number((session as { user_id?: number } | null)?.user_id);

  // senha master: protege excluir UTM e excluir usuario
  const [master, setMaster] = useState<MasterStatus | null>(null);
  const [masterAdminPw, setMasterAdminPw] = useState("");
  const [masterNew, setMasterNew] = useState("");
  const [masterConf, setMasterConf] = useState("");
  const [masterMsg, setMasterMsg] = useState<{ tipo: "ok" | "erro"; texto: string } | null>(null);
  const [masterSaving, setMasterSaving] = useState(false);
  const [showMaster, setShowMaster] = useState(false);
  const carregarMaster = useCallback(async () => {
    try { const s = await getMasterStatus(); if (s && s.origem) setMaster(s); } catch { /* status e informativo */ }
  }, []);
  const salvarMaster = async (e: React.FormEvent) => {
    e.preventDefault();
    setMasterMsg(null);
    if (masterNew.length < 8) { setMasterMsg({ tipo: "erro", texto: "A nova senha master deve ter pelo menos 8 caracteres." }); return; }
    if (masterNew !== masterConf) { setMasterMsg({ tipo: "erro", texto: "A confirmação não coincide com a nova senha master." }); return; }
    setMasterSaving(true);
    try {
      const r = await setMasterPassword(masterAdminPw, masterNew, masterConf);
      if (r.success) { setMaster(r); setMasterMsg({ tipo: "ok", texto: "Senha master definida. Ela já vale para as próximas exclusões." }); setMasterAdminPw(""); setMasterNew(""); setMasterConf(""); }
      else setMasterMsg({ tipo: "erro", texto: r.error || "Não foi possível definir a senha master." });
    } catch { setMasterMsg({ tipo: "erro", texto: "Falha de rede. Tente de novo." }); }
    finally { setMasterSaving(false); }
  };

  useEffect(() => {
    if (authLoading) return;
    if (!session || !session.logged_in || !session.is_admin) {
      router.push("/");
    }
  }, [session, authLoading, router]);

  const loadUsers = useCallback(async () => {
    setLoading(true);
    try {
      const data = await getAdminUsers();
      setUsers(data);
    } catch (e) {
      console.error(e);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    if (session && session.is_admin) {
      loadUsers();
      carregarMaster();
    }
  }, [session, loadUsers, carregarMaster]);

  const handleApprove = async (id: number) => {
    const res = await approveUser(id);
    if (res.success) loadUsers();
    else alert("Erro ao aprovar usuário");
  };

  const handleToggleAdmin = async (id: number, currentStatus: boolean) => {
    const res = await toggleAdmin(id, !currentStatus);
    if (res.success) loadUsers();
    else alert("Erro ao alterar permissão");
  };

  const fecharExclusao = () => { setDeleteUser(null); setDeletePassword(""); setDeleteError(""); };
  const handleDeleteUser = async () => {
    if (!deleteUser) return;
    if (!deletePassword) { setDeleteError("Digite a senha master para confirmar."); return; }
    setDeleteLoading(true); setDeleteError("");
    try {
      const res = await deleteAdminUser(deleteUser.id, deletePassword);
      if (res.success) { fecharExclusao(); loadUsers(); }
      else setDeleteError(res.error || "Não foi possível excluir o usuário.");
    } catch {
      setDeleteError("Falha de rede ao excluir. Tente de novo.");
    } finally {
      setDeleteLoading(false);
    }
  };

  if (!session || !session.is_admin) return null;

  return (
    <>
      <ThemeSwitch />
      <Header />

      <div className="container mt-4 mb-5">
        <div className="dashboard-header mb-4">
          <h1><i className="bi bi-shield-lock me-2"></i>Painel Administrativo</h1>
          <p className="text-muted">Gerenciamento de Usuários, Permissões e Domínios</p>
        </div>

        <ul className="nav nav-tabs mb-4">
          <li className="nav-item">
            <button className="nav-link active"><i className="bi bi-people me-2"></i>Usuários</button>
          </li>
          <li className="nav-item">
            <button className="nav-link disabled"><i className="bi bi-globe me-2"></i>Domínios (Em Breve)</button>
          </li>
          <li className="nav-item">
            <button className="nav-link disabled"><i className="bi bi-key me-2"></i>Permissões (Em Breve)</button>
          </li>
        </ul>

        <div className="ag-card p-3">
          <div className="table-responsive">
            <table className="table table-hover align-middle">
              <thead>
                <tr>
                  <th>Nome</th>
                  <th>E-mail</th>
                  <th>Data de Cadastro</th>
                  <th>Status</th>
                  <th>Nível</th>
                  <th className="text-end">Ações</th>
                </tr>
              </thead>
              <tbody>
                {loading ? (
                  <tr><td colSpan={6} className="text-center py-4">Carregando usuários...</td></tr>
                ) : users.length === 0 ? (
                  <tr><td colSpan={6} className="text-center py-4">Nenhum usuário encontrado.</td></tr>
                ) : users.map(user => (
                  <tr key={user.id}>
                    <td><strong>{user.name}</strong></td>
                    <td>{user.email}</td>
                    <td>{new Date(user.created_at).toLocaleDateString('pt-BR')}</td>
                    <td>
                      {user.is_approved ? (
                        <span className="badge bg-success">Aprovado</span>
                      ) : (
                        <span className="badge bg-warning text-dark">Pendente</span>
                      )}
                    </td>
                    <td>
                      {user.is_admin ? (
                        <span className="badge ds-badge-admin">Administrador</span>
                      ) : (
                        <span className="badge ds-badge-neutro">Usuário</span>
                      )}
                    </td>
                    <td className="text-end">
                      <div className="btn-group">
                        {!user.is_approved && (
                          <button 
                            className="btn btn-sm btn-success" 
                            onClick={() => handleApprove(user.id)}
                            title="Aprovar usuário"
                            aria-label={`Aprovar ${user.name}`}
                          >
                            <i className="bi bi-check-lg" aria-hidden="true"></i>
                          </button>
                        )}
                        <button 
                          className={`btn btn-sm ${user.is_admin ? 'btn-outline-danger' : 'btn-outline-primary'}`}
                          onClick={() => handleToggleAdmin(user.id, user.is_admin)}
                          title={user.is_admin ? "Remover admin" : "Tornar admin"}
                          aria-label={user.is_admin ? `Remover privilegio de admin de ${user.name}` : `Tornar ${user.name} admin`}
                        >
                          <i className={`bi ${user.is_admin ? 'bi-arrow-down' : 'bi-arrow-up'}`} aria-hidden="true"></i>
                        </button>
                        <button
                          type="button"
                          className="btn btn-sm ds-nav ds-nav-sair"
                          onClick={() => { setDeleteUser(user); setDeletePassword(""); setDeleteError(""); }}
                          disabled={user.id === meuId}
                          title={user.id === meuId ? "Você não pode excluir o próprio usuário" : "Excluir usuário"}
                          aria-label={`Excluir ${user.name}`}
                        >
                          <i className="bi bi-trash" aria-hidden="true"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
        {/* Senha master */}
        <form className="ag-card p-3 mt-3" onSubmit={salvarMaster} autoComplete="off">
          <h5 className="mb-2"><i className="bi bi-shield-lock ds-icon me-2"></i>Senha master</h5>
          <p className="small mb-2" style={{ color: "var(--color-text-muted)" }}>
            Protege as ações irreversíveis: excluir UTM e excluir usuário. Ela não pode ser lida de volta, só substituída.
          </p>
          <p className="small mb-3" role="status">
            {master === null && "Verificando…"}
            {master?.origem === "banco" && (
              <>Definida pelo sistema em <strong>{master.atualizado_em ? new Date(master.atualizado_em.replace(" ", "T")).toLocaleString("pt-BR", { dateStyle: "short", timeStyle: "short" }) : "—"}</strong>{master.atualizado_por ? <> por <strong>{master.atualizado_por}</strong></> : null}.</>
            )}
            {master?.origem === "env" && (
              <>Ainda usa o valor inicial do arquivo de configuração do servidor. Defina uma nova aqui para passar a controlá-la pelo painel.</>
            )}
            {master?.origem === "nenhuma" && (
              <span className="text-danger">Nenhuma senha master configurada: as exclusões estão bloqueadas até você definir uma.</span>
            )}
          </p>
          <div className="row g-3">
            <div className="col-md-4">
              <label className="form-label ds-legend" htmlFor="master-admin">Sua senha de administrador</label>
              <div className="ds-senha">
                <input id="master-admin" type={showMaster ? "text" : "password"} className="form-control" value={masterAdminPw} onChange={e => setMasterAdminPw(e.target.value)} autoComplete="current-password" required />
                <button type="button" className="ds-senha__olho" onClick={() => setShowMaster(v => !v)} aria-label={showMaster ? "Ocultar senhas" : "Mostrar senhas"} aria-pressed={showMaster}><i className={`bi ${showMaster ? "bi-eye-slash" : "bi-eye"}`} aria-hidden="true"></i></button>
              </div>
              <div className="form-text">Você não precisa saber a master atual, só provar que é você.</div>
            </div>
            <div className="col-md-4">
              <label className="form-label ds-legend" htmlFor="master-nova">Nova senha master</label>
              <input id="master-nova" type={showMaster ? "text" : "password"} className="form-control" value={masterNew} onChange={e => setMasterNew(e.target.value)} autoComplete="new-password" minLength={8} required />
              <div className="form-text">Pelo menos 8 caracteres.</div>
            </div>
            <div className="col-md-4">
              <label className="form-label ds-legend" htmlFor="master-conf">Confirmar nova senha master</label>
              <input id="master-conf" type={showMaster ? "text" : "password"} className={`form-control ${masterConf && masterConf !== masterNew ? "is-invalid" : ""}`} value={masterConf} onChange={e => setMasterConf(e.target.value)} autoComplete="new-password" required />
            </div>
          </div>
          <div className="d-flex align-items-center gap-3 flex-wrap mt-3">
            <button type="submit" className="ag-btn-accent btn-sm px-4" disabled={masterSaving || !masterAdminPw || !masterNew || !masterConf}>
              {masterSaving ? "Definindo…" : "Definir senha master"}
            </button>
            {masterMsg && <span className={`small ${masterMsg.tipo === "ok" ? "text-success" : "text-danger"}`} role="status">{masterMsg.texto}</span>}
          </div>
        </form>

      </div>

      {mounted && deleteUser && createPortal(
        <div className="ag-modal-overlay" onClick={fecharExclusao}>
          <div className="ag-modal" role="dialog" aria-modal="true" aria-labelledby="titulo-excluir-usuario" onClick={e => e.stopPropagation()}>
            <div className="ag-modal-header">
              <h3 id="titulo-excluir-usuario">Excluir usuário</h3>
              <button type="button" className="btn btn-icon btn-ghost" onClick={fecharExclusao} aria-label="Fechar">✕</button>
            </div>
            <p style={{ marginBottom: 8 }}>
              Excluir <strong>{deleteUser.name}</strong> ({deleteUser.email}) apaga o acesso desta pessoa. As UTMs que ela criou continuam no sistema.
            </p>
            <p style={{ marginBottom: 20 }}>Esta ação é irreversível. Digite a senha master para confirmar.</p>
            {deleteError && <div className="alert alert-danger" role="alert" style={{ marginBottom: 16 }}>{deleteError}</div>}
            <div className="form-group" style={{ marginBottom: 20 }}>
              <label className="form-label" htmlFor="senha-master-usuario">Senha master</label>
              <input type="text" name="dummy_email" style={{ display: "none" }} />
              <input
                id="senha-master-usuario"
                type="password"
                className="form-control"
                placeholder="••••••••"
                value={deletePassword}
                onChange={e => setDeletePassword(e.target.value)}
                onKeyDown={e => { if (e.key === "Enter") handleDeleteUser(); }}
                autoComplete="new-password"
                autoFocus
              />
            </div>
            <div style={{ display: "flex", gap: 12, justifyContent: "flex-end" }}>
              <button type="button" className="btn btn-ghost" onClick={fecharExclusao}>Cancelar</button>
              <button type="button" className="btn btn-danger" onClick={handleDeleteUser} disabled={deleteLoading}>
                {deleteLoading ? "Excluindo…" : "Excluir usuário"}
              </button>
            </div>
          </div>
        </div>,
        document.body
      )}
    </>
  );
}
