"use client";

import { useState, useEffect, useCallback } from "react";
import { createPortal } from "react-dom";
import { getUTMs, toggleUTM, deleteUTM } from "@/lib/api";
import type { UTM } from "@/types/utm";
import styles from "./UTMTable.module.css";
import { useAuth } from "@/lib/auth-context";
import { QRCodeCanvas } from "qrcode.react";

const BASE_URL = process.env.NEXT_PUBLIC_APP_URL ?? "";

export default function UTMTable() {
  const [utms, setUtms] = useState<UTM[]>([]);
  const [total, setTotal] = useState(0);
  const [pages, setPages] = useState(1);
  const [page, setPage] = useState(1);
  const [search, setSearch] = useState("");
  const [loading, setLoading] = useState(true);
  const [deleteId, setDeleteId] = useState<number | null>(null);
  const [deletePassword, setDeletePassword] = useState("");
  const [deleteLoading, setDeleteLoading] = useState(false);
  const [deleteError, setDeleteError] = useState("");
  const [copiedId, setCopiedId] = useState<number | null>(null);
  const [qrModalUrl, setQrModalUrl] = useState<string | null>(null);
  const [logoMode, setLogoMode] = useState<"nenhuma" | "prosperus">("nenhuma");
  const { session } = useAuth();
  const [mounted, setMounted] = useState(false);

  useEffect(() => setMounted(true), []);

  const load = useCallback(async () => {
    setLoading(true);
    try {
      const res = await getUTMs(page, search);
      setUtms(res.data ?? []);
      setTotal(res.total ?? 0);
      setPages(res.pages ?? 1);
    } finally {
      setLoading(false);
    }
  }, [page, search]);

  useEffect(() => { load(); }, [load]);

  const handleToggle = async (utm: UTM) => {
    await toggleUTM(utm.id, !utm.is_enabled);
    load();
  };

  const handleDelete = async () => {
    if (!deleteId) return;
    setDeleteLoading(true);
    setDeleteError("");
    const res = await deleteUTM(deleteId, deletePassword);
    setDeleteLoading(false);
    if (res.success) {
      setDeleteId(null);
      setDeletePassword("");
      load();
    } else {
      setDeleteError(res.message || "Senha incorreta");
    }
  };

  const copyLink = async (utm: UTM) => {
    const url = `https://prosperusclub.com.br/${utm.shortened_url}`;
    await navigator.clipboard.writeText(url);
    setCopiedId(utm.id);
    setTimeout(() => setCopiedId(null), 2000);
  };

  const formatDate = (d: string) =>
    new Date(d).toLocaleDateString("pt-BR", { day: "2-digit", month: "2-digit", year: "2-digit" });

  return (
    <div>
      {/* Table header */}
      <div className={styles.tableHeader}>
        <div>
          <h3 style={{ marginBottom: 2 }}>Links gerados</h3>
          <p style={{ fontSize: "0.8125rem" }}>
            {total > 0 ? `${total} UTMs encontradas` : "Nenhuma UTM ainda"}
          </p>
        </div>
        <div className={styles.tableActions}>
          <div className="search-wrapper">
            <span className="search-icon">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                <circle cx="11" cy="11" r="8" /><line x1="21" y1="21" x2="16.65" y2="16.65" />
              </svg>
            </span>
            <input
              type="text"
              name="search_utm_off"
              className="form-control search-input"
              placeholder="Buscar UTM..."
              value={search}
              onChange={e => { setSearch(e.target.value); setPage(1); }}
              style={{ width: 220, padding: "9px 9px 9px 38px" }}
              autoComplete="off"
            />
          </div>
          <button className="btn btn-ghost btn-sm" onClick={load}>
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
              <polyline points="1 4 1 10 7 10" /><path d="M3.51 15a9 9 0 1 0 .49-3.51" />
            </svg>
          </button>
        </div>
      </div>

      <div className="table-wrapper">
        <table className="table">
          <thead>
            <tr style={{ fontSize: "0.75rem", color: "var(--text-muted)" }}>
              <th style={{ width: "60px" }}>QR Code</th>
              <th style={{ width: "35%" }}>Link original com UTM</th>
              <th>Link encurtado</th>
              <th>Cliques</th>
              <th>Status</th>
              <th>Excluir</th>
              <th>Data</th>
              <th>Comentário</th>
              <th>Usuário</th>
            </tr>
          </thead>
          <tbody>
            {loading ? (
              Array.from({ length: 5 }).map((_, i) => (
                <tr key={i}>
                  {Array.from({ length: 9 }).map((_, j) => (
                    <td key={j}><div className="skeleton" style={{ height: 16, width: "80%" }} /></td>
                  ))}
                </tr>
              ))
            ) : utms.length === 0 ? (
              <tr>
                <td colSpan={9}>
                  <div className="empty-state">
                    <div className="empty-state-icon">⚡</div>
                    <p>Nenhuma UTM encontrada</p>
                  </div>
                </td>
              </tr>
            ) : utms.map(utm => (
              <tr key={utm.id}>
                {/* QR Code */}
                <td>
                  <div 
                    style={{ cursor: "pointer", background: "white", padding: 4, display: "inline-block", borderRadius: 4 }} 
                    onClick={() => setQrModalUrl(`https://prosperusclub.com.br/${utm.shortened_url}`)}
                    title="Ampliar QR Code"
                  >
                    <QRCodeCanvas value={`https://prosperusclub.com.br/${utm.shortened_url}`} size={48} level="L" />
                  </div>
                </td>
                
                {/* Link Original */}
                <td>
                  <span
                    style={{
                      display: "block", wordBreak: "break-all",
                      fontSize: "0.8125rem", color: "var(--theme-accent)"
                    }}
                  >
                    {utm.long_url}
                  </span>
                  <button
                    className="btn btn-ghost btn-sm p-0 mt-1"
                    onClick={() => {
                      navigator.clipboard.writeText(utm.long_url);
                      setCopiedId(utm.id + 10000);
                      setTimeout(() => setCopiedId(null), 2000);
                    }}
                    title="Copiar Link Original"
                  >
                    {copiedId === (utm.id + 10000) ? <i className="bi bi-check2 text-success"></i> : <i className="bi bi-clipboard text-muted"></i>}
                  </button>
                </td>
                
                {/* Link Encurtado */}
                <td>
                  <span style={{ fontSize: "0.8125rem", color: "var(--theme-accent)", fontWeight: 500 }}>
                    https://prosperusclub.com.br/{utm.shortened_url}
                  </span>
                  <br />
                  <button
                    className="btn btn-ghost btn-sm p-0 mt-1"
                    onClick={() => copyLink(utm)}
                    title="Copiar Link Encurtado"
                  >
                    {copiedId === utm.id ? <i className="bi bi-check2 text-success"></i> : <i className="bi bi-clipboard text-muted"></i>}
                  </button>
                </td>
                
                {/* Clicks */}
                <td>
                  <span className={styles.clicksBadge}>{utm.clicks ?? 0}</span>
                </td>
                
                {/* Status */}
                <td>
                  <div className="form-check form-switch d-inline-block">
                    <input 
                      className="form-check-input" 
                      type="checkbox" 
                      role="switch" 
                      checked={utm.is_enabled} 
                      onChange={() => handleToggle(utm)} 
                      style={{ cursor: "pointer", borderColor: utm.is_enabled ? "var(--color-success)" : "", backgroundColor: utm.is_enabled ? "var(--color-success)" : "" }}
                    />
                  </div>
                </td>
                
                {/* Excluir */}
                <td>
                  {session?.is_admin ? (
                    <button
                      className="btn btn-icon btn-ghost btn-sm text-danger"
                      onClick={() => { setDeleteId(utm.id); setDeleteError(""); }}
                      title="Excluir"
                    >
                      <i className="bi bi-trash"></i>
                    </button>
                  ) : <span className="text-muted">-</span>}
                </td>
                
                {/* Data */}
                <td>
                  <span style={{ fontSize: "0.8125rem", color: "var(--text-muted)" }}>
                    {formatDate(utm.generation_date)}
                    <br />
                    {new Date(utm.generation_date).toLocaleTimeString("pt-BR", { hour: "2-digit", minute: "2-digit" })}
                  </span>
                </td>
                
                {/* Comentário */}
                <td>
                  <span style={{ fontSize: "0.8125rem", color: "var(--text-muted)", display: "block", maxWidth: 150, wordWrap: "break-word" }}>
                    {utm.comment || "-"}
                  </span>
                </td>
                
                {/* Usuário */}
                <td>
                  <span style={{ fontSize: "0.8125rem" }}>
                    {utm.username ? utm.username.split(" ").slice(0, 2).join(" ") : "-"}
                  </span>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      {/* Abaixo de 992px a tabela de nove colunas nao cabe. Cada UTM vira
          um card: link curto em destaque, comentario como titulo humano,
          URL longa truncada, e as acoes no rodape. O QR sai do card e
          vai para o modal que ja existe, para nao pintar 20 canvases. */}
      <div className="ds-cards" aria-label="Lista de UTMs">
        {loading ? (
          Array.from({ length: 3 }).map((_, i) => (
            <div key={i} className="ds-utm-card"><div className="skeleton" style={{ height: 18, width: "60%" }} /><div className="skeleton mt-2" style={{ height: 14, width: "90%" }} /></div>
          ))
        ) : utms.length === 0 ? (
          <div className="empty-state"><p>Nenhuma UTM encontrada</p></div>
        ) : utms.map(utm => (
          <article key={`c${utm.id}`} className="ds-utm-card">
            <header className="ds-utm-card__top">
              <a className="ds-utm-card__short" href={`https://prosperusclub.com.br/${utm.shortened_url}`} target="_blank" rel="noreferrer">
                prosperusclub.com.br/{utm.shortened_url}
              </a>
              <button type="button" className="btn btn-sm ds-nav" onClick={() => copyLink(utm)} aria-label="Copiar link encurtado">
                <i className={`bi ${copiedId === utm.id ? "bi-check2" : "bi-clipboard"}`} aria-hidden="true"></i>
              </button>
            </header>
            <p className="ds-utm-card__comment">{utm.comment || "Sem comentário"}</p>
            <p className="ds-utm-card__long">{utm.long_url}</p>
            <dl className="ds-utm-card__meta">
              <div><dt>Cliques</dt><dd>{utm.clicks ?? 0}</dd></div>
              <div><dt>Data</dt><dd>{formatDate(utm.generation_date)}</dd></div>
              <div><dt>Por</dt><dd>{utm.username ? utm.username.split(" ").slice(0, 2).join(" ") : "-"}</dd></div>
            </dl>
            <footer className="ds-utm-card__actions">
              <label className="form-check form-switch m-0 d-flex align-items-center gap-2">
                <input className="form-check-input m-0" type="checkbox" role="switch" checked={utm.is_enabled} onChange={() => handleToggle(utm)} />
                <span className="small">{utm.is_enabled ? "Ativa" : "Desativada"}</span>
              </label>
              <div className="d-flex gap-2">
                <button type="button" className="btn btn-sm ds-nav" onClick={() => setQrModalUrl(`https://prosperusclub.com.br/${utm.shortened_url}`)}>
                  <i className="bi bi-qr-code me-1" aria-hidden="true"></i>QR
                </button>
                {session?.is_admin && (
                  <button type="button" className="btn btn-sm ds-nav ds-nav-sair" onClick={() => { setDeleteId(utm.id); setDeleteError(""); }} aria-label="Excluir UTM">
                    <i className="bi bi-trash" aria-hidden="true"></i>
                  </button>
                )}
              </div>
            </footer>
          </article>
        ))}
      </div>

      {/* Pagination */}
      {pages > 1 && (
        <div className={styles.pagination}>
          <button className="btn btn-ghost btn-sm" disabled={page === 1} onClick={() => setPage(p => p - 1)}>
            ← Anterior
          </button>
          <span style={{ fontSize: "0.8125rem", color: "var(--text-muted)" }}>
            Página {page} de {pages}
          </span>
          <button className="btn btn-ghost btn-sm" disabled={page >= pages} onClick={() => setPage(p => p + 1)}>
            Próxima →
          </button>
        </div>
      )}

      {/* Delete Modal */}
      {mounted && deleteId && createPortal(
        <div className="ag-modal-overlay" onClick={() => setDeleteId(null)}>
          <div className="ag-modal" onClick={e => e.stopPropagation()}>
            <div className="ag-modal-header">
              <h3>Confirmar exclusão</h3>
              <button className="btn btn-icon btn-ghost" onClick={() => setDeleteId(null)}>✕</button>
            </div>
            <p style={{ marginBottom: 20 }}>
              Esta ação é irreversível. Digite a senha master para confirmar.
            </p>
            {deleteError && (
               <div className="alert alert-danger" style={{ marginBottom: 16 }}>{deleteError}</div>
            )}
            <div className="form-group" style={{ marginBottom: 20 }}>
              <label className="form-label" htmlFor="delete-password">Senha master</label>
              {/* input invisivel e autocomplete dummy para barrar o navegador */}
              <input type="text" name="dummy_email" style={{display: 'none'}} />
              <input
                id="delete-password"
                type="password"
                className="form-control"
                placeholder="••••••••"
                value={deletePassword}
                onChange={e => setDeletePassword(e.target.value)}
                autoComplete="new-password"
                autoFocus
              />
            </div>
            <div style={{ display: "flex", gap: 12, justifyContent: "flex-end" }}>
              <button className="btn btn-ghost" onClick={() => setDeleteId(null)}>Cancelar</button>
              <button className="btn btn-danger" onClick={handleDelete} disabled={deleteLoading}>
                {deleteLoading ? <span className="spinner" /> : "Excluir"}
              </button>
            </div>
          </div>
        </div>,
        document.body
      )}

      {/* QR Code Modal */}
      {mounted && qrModalUrl && createPortal(
        <div className="ag-modal-overlay" onClick={() => setQrModalUrl(null)}>
          <div className="ag-modal text-center" onClick={e => e.stopPropagation()}>
            <div className="ag-modal-header">
              <h3>QR Code — {qrModalUrl.split('/').pop()}</h3>
              <button className="btn btn-icon btn-ghost" onClick={() => setQrModalUrl(null)}>✕</button>
            </div>
            <div className="p-4 bg-white d-inline-block rounded shadow-sm mb-3 mt-3">
              {logoMode === "prosperus" ? (
                <QRCodeCanvas 
                  value={qrModalUrl} 
                  size={200} 
                  level="H" 
                  includeMargin 
                  imageSettings={{
                    src: "/images/logo_prosperus_club.png",
                    height: 48,
                    width: 48,
                    excavate: true,
                  }}
                />
              ) : (
                <QRCodeCanvas 
                  value={qrModalUrl} 
                  size={200} 
                  level="H" 
                  includeMargin 
                />
              )}
            </div>
            <div className="form-group text-start mb-3">
              <label className="form-label" style={{ fontSize: "0.85rem", fontWeight: 600 }}>Logo no QR Code:</label>
              <select className="form-select theme-input" value={logoMode} onChange={e => setLogoMode(e.target.value as any)}>
                <option value="nenhuma">Nenhuma</option>
                <option value="prosperus">Prosperus Club</option>
              </select>
            </div>
            <div className="mt-2">
              <button className="ag-btn-accent w-100" onClick={() => {
                const canvas = document.querySelector('.ag-modal canvas') as HTMLCanvasElement;
                if(!canvas) return;
                const pngFile = canvas.toDataURL("image/png");
                const downloadLink = document.createElement("a");
                downloadLink.download = "qrcode.png";
                downloadLink.href = pngFile;
                downloadLink.click();
              }}>
                <i className="bi bi-download me-2"></i> BAIXAR QR CODE
              </button>
            </div>
          </div>
        </div>,
        document.body
      )}
    </div>
  );
}
