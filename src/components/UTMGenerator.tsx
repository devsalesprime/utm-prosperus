"use client";

import React, { useState, useEffect } from "react";
import { createUTM } from "@/lib/api";
import { getTeamMembers } from "@/lib/api";
import { useAuth } from "@/lib/auth-context";

interface Props {
  onSuccess?: (code: string) => void;
}

const mediumMap: Record<string, string[]> = {
  ig: ['INSTAGRAM_FEED', 'INSTAGRAM_STORIES', 'INSTAGRAM_REELS', 'INSTAGRAM_BIO', 'INSTAGRAM_DIRECT'],
  yt: ['YOUTUBE_DESCRICAO', 'YOUTUBE_CARD', 'YOUTUBE_BIO', 'YOUTUBE_COMUNIDADE', 'YOUTUBE_VIDEO'],
  in: ['LINKEDIN_POST', 'LINKEDIN_ARTIGO', 'LINKEDIN_BIO', 'LINKEDIN_INMAIL'],
  tktk: ['TIKTOK_BIO', 'TIKTOK_REDE_ORIGEM'],
  thrd: ['THREADS_BIO'],
  spot: ['SPOTIFY_VIDEO', 'SPOTIFY_DESCRICAO'],
  wpp: ['WHATSAPP_MENSAGEM'],
  appl: ['APPLE_DESCRICAO'],
  amz: ['AMAZON_DESCRICAO'],
  dzr: ['DEEZER_DESCRICAO'],
  email: ['EMAIL_MARKETING', 'EMAIL_TRANSACIONAL', 'EMAIL_NEWSLETTER'],
  site: ['SITE_BANNER', 'SITE_POPUP', 'SITE_FOOTER', 'SITE_MENU']
};
const sourceAliases: Record<string, string> = { linkd: 'in', wtt: 'wpp' };

function normalizeForUtm(text: string) {
  if (!text) return '';
  text = text.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
  text = text.toUpperCase().trim();
  text = text.replace(/[^A-Z0-9\s\-_]/g, '');
  text = text.replace(/\s+/g, '_');
  text = text.replace(/__+/g, '_');
  text = text.replace(/^_+|_+$/g, '');
  return text;
}

export default function UTMGenerator({ onSuccess }: Props) {
  const { session } = useAuth();
  const isDisabled = !session;
  
  const [teamMembers, setTeamMembers] = useState<any[]>([]);
  useEffect(() => {
    getTeamMembers().then(setTeamMembers);
  }, []);

  const closers = teamMembers.filter(m => m.type === 'Closer');
  const sdrs = teamMembers.filter(m => m.type === 'SDR');
  const socialSellers = teamMembers.filter(m => m.type === 'SocialSeller');
  const csMembers = teamMembers.filter(m => m.type === 'CS');

  const [url, setUrl] = useState("");
  const [campaign, setCampaign] = useState("Sales-Prime");
  const [contentSelect, setContentSelect] = useState("");
  const [closerName, setCloserName] = useState("");
  const [sdrName, setSdrName] = useState("");
  const [ssName, setSsName] = useState("");
  const [ssContent, setSsContent] = useState("");
  const [csName, setCsName] = useState("");
  const [moType, setMoType] = useState("LIVRO");
  const [moName, setMoName] = useState("");
  const [source, setSource] = useState("ig");
  const [medium, setMedium] = useState("");
  const [mediumCustom, setMediumCustom] = useState("");
  const [term, setTerm] = useState("");
  const [customName, setCustomName] = useState("");
  const [comment, setComment] = useState("");

  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [result, setResult] = useState<any>(null);

  // Compute final content
  let computedContent = contentSelect;
  if (contentSelect === "COMM") computedContent = closerName ? `${normalizeForUtm(closerName)}_CLOSER` : "";
  else if (contentSelect === "SDR") computedContent = sdrName ? `${normalizeForUtm(sdrName)}_SDR` : "";
  else if (contentSelect === "SSELL") computedContent = ssName ? `${normalizeForUtm(ssName)}_SSELL` : "";
  else if (contentSelect === "CS") computedContent = csName ? `${normalizeForUtm(csName)}_CS` : "";
  else if (contentSelect === "MO") {
    if (moType === "PALESTRA") computedContent = "MO_PALESTRA";
    else if (moType === "EVENTO") computedContent = "MO_EVENTO";
    else computedContent = moName ? `MO_${moType}_${normalizeForUtm(moName)}` : "";
  }

  // Determine visibility of source/medium
  const hideSourceMedium = contentSelect === "WEBINAR" || (contentSelect === "MO" && (moType === "PALESTRA" || moType === "EVENTO"));
  const sourceKey = sourceAliases[source] || source;
  const mediumOptions = mediumMap[sourceKey] || [];

  // Update effect on SS content change
  useEffect(() => {
    if (contentSelect === "SSELL" && ssContent) {
      if (ssContent.includes("_IG")) setSource("ig");
      else if (ssContent.includes("_IN")) setSource("in");
    }
  }, [ssContent, contentSelect]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (isDisabled) return;
    setLoading(true);
    setError("");

    const m = medium === "OUTRO" ? mediumCustom : medium;

    const res = await createUTM({
      url,
      utm_campaign: campaign,
      utm_source: hideSourceMedium ? "" : source,
      utm_medium: hideSourceMedium ? "" : m,
      utm_content: computedContent,
      utm_term: term,
      custom_name: customName,
      comment
    });

    setLoading(false);
    if (res.success && res.data) {
      setResult(res.data);
      onSuccess?.(res.data.short_code);
    } else {
      setError(res.message || "Erro ao criar UTM");
    }
  };

  // Render variables
  const getTermPlaceholder = () => {
    const mapping: Record<string, string> = {
      ig: 'Instagram: Data',
      yt: 'Youtube: Título ou Data',
      in: 'Linkedin: Título',
      spot: 'PodCast: Título ou Data',
    };
    return mapping[sourceKey] || "Ex: [VIDEO][NOME], [ESTATICO][NOME]";
  };

  const previewUrl = url 
    ? `${url}?utm_campaign=${campaign}&utm_source=${hideSourceMedium ? "" : source}&utm_medium=${hideSourceMedium ? "" : (medium === "OUTRO" ? mediumCustom : medium)}&utm_content=${computedContent}&utm_term=${term}`
    : "Preencha os campos para visualizar a URL...";

  return (
    <div className="container mt-2">
      {result && (
        <div className="alert alert-success d-flex justify-content-between align-items-center">
          <div>
            <strong>UTM Gerada!</strong><br />
            <a href={result.short_url} target="_blank">{result.short_url}</a>
          </div>
          <button className="btn btn-sm btn-outline-success" onClick={() => navigator.clipboard.writeText(result.short_url)}>
            Copiar
          </button>
        </div>
      )}
      {error && <div className="alert alert-danger">{error}</div>}

      <form onSubmit={handleSubmit} className="mt-4">
        {/* URL */}
        <div className="input-group mb-3">
          <span className="input-group-text p5"><i className="bi bi-link me-1"></i> URL do site:</span>
          <input type="url" className="form-control theme-input" required disabled={isDisabled} value={url} onChange={e => setUrl(e.target.value)} />
        </div>

        {/* Campaign */}
        <div className="input-group mb-3">
          <div className="btn-group w-100" role="radiogroup">
            <span className="input-group-text p5 rounded-end-0"><i className="bi bi-person me-1"></i> Canais:</span>
            {["Sales-Prime", "Dani-Martins", "Prosperus", "Lumiere", "Prime", "PodVender", "Joel-Jota", "PodCast"].map(c => {
              const idMap: Record<string, string> = {
                "Sales-Prime": "sales", "Dani-Martins": "dani", "Prosperus": "prosperus",
                "Lumiere": "lumiere", "Prime": "prime", "PodVender": "podvender",
                "Joel-Jota": "joel", "PodCast": "podcast"
              };
              const cssId = `profile_${idMap[c]}`;
              return (
                <React.Fragment key={c}>
                  <input type="radio" className="btn-check" id={cssId} name="campaign" value={c} checked={campaign === c} onChange={() => setCampaign(c)} disabled={isDisabled} />
                  <label className="btn btn-outline-secondary" htmlFor={cssId}>{c}</label>
                </React.Fragment>
              );
            })}
          </div>
        </div>

        {/* Content Select */}
        <div className="mb-3">
          <label className="form-label p5 d-block estrutura rounded-end-0"><i className="bi bi-diagram-3 me-1"></i> Origem / Fonte:</label>
          <div className="row row-cols-2 row-cols-md-3 row-cols-lg-6 g-2">
            {[
              {v: "TP", l: "Mídia Paga (TP)"}, {v: "TO", l: "Mídia Orgânica (TO)"}, {v: "SEM", l: "Pesquisa Paga (SEM)"},
              {v: "COMM", l: "Comercial"}, {v: "SDR", l: "SDR"}, {v: "SSELL", l: "Social Selling"},
              {v: "CS", l: "Suporte"}, {v: "TJJ", l: "Time Joel Jota"}, {v: "MO", l: "Mídia Offline"},
              {v: "TV", l: "Mídia Televisiva"}, {v: "APP", l: "APP Mobile"}, {v: "WEBINAR", l: "WEBINAR"}
            ].map(c => (
              <div className="col" key={c.v}>
                <input type="radio" className="btn-check" id={`content_${c.v.toLowerCase()}`} value={c.v} checked={contentSelect === c.v} onChange={() => setContentSelect(c.v)} disabled={isDisabled} />
                <label className="btn btn-outline-secondary w-100" htmlFor={`content_${c.v.toLowerCase()}`}>{c.l}</label>
              </div>
            ))}
          </div>
        </div>

        {/* Dynamic Containers */}
        {contentSelect === "COMM" && (
          <div className="mb-3">
            <label className="form-label">Nome do Closer</label>
            <select className="form-select" value={closerName} onChange={e => setCloserName(e.target.value)} disabled={isDisabled}>
              <option value="">Selecione um Closer</option>
              {closers.map(m => <option key={m.id} value={m.name}>{m.name}</option>)}
            </select>
          </div>
        )}
        {contentSelect === "SDR" && (
          <div className="mb-3">
            <label className="form-label">Nome do SDR</label>
            <select className="form-select" value={sdrName} onChange={e => setSdrName(e.target.value)} disabled={isDisabled}>
              <option value="">Selecione um SDR</option>
              {sdrs.map(m => <option key={m.id} value={m.name}>{m.name}</option>)}
            </select>
          </div>
        )}
        {contentSelect === "SSELL" && (
          <div className="mb-3">
            <label className="form-label">Nome do Social Selling</label>
            <select className="form-select mb-2" value={ssName} onChange={e => setSsName(e.target.value)} disabled={isDisabled}>
              <option value="">Selecione um Social Seller</option>
              {socialSellers.map(m => <option key={m.id} value={m.name}>{m.name}</option>)}
            </select>
            {ssName && (
              <div className="btn-group w-100 mt-2">
                {["ATIVO_IG", "PASSIVO_IG", "ATIVO_IN", "PASSIVO_IN"].map(t => {
                  const cssId = `ss_${t.toLowerCase().split('_').reverse().join('_')}`;
                  return (
                    <React.Fragment key={t}>
                      <input type="radio" className="btn-check" id={cssId} value={t} checked={ssContent === t} onChange={() => setSsContent(t)} />
                      <label className="btn btn-outline-secondary" htmlFor={cssId}>{t}</label>
                    </React.Fragment>
                  );
                })}
              </div>
            )}
          </div>
        )}
        {contentSelect === "CS" && (
          <div className="mb-3">
            <label className="form-label">Nome do CS/Suporte</label>
            <select className="form-select" value={csName} onChange={e => setCsName(e.target.value)} disabled={isDisabled}>
              <option value="">Selecione um CS</option>
              {csMembers.map(m => <option key={m.id} value={m.name}>{m.name}</option>)}
            </select>
          </div>
        )}
        {contentSelect === "MO" && (
          <div className="mb-3">
            <label className="form-label">Tipo de Material</label>
            <select className="form-select mb-2" value={moType} onChange={e => setMoType(e.target.value)} disabled={isDisabled}>
              <option value="LIVRO">Livro</option><option value="PDF">PDF</option>
              <option value="PALESTRA">Palestra</option><option value="EVENTO">Evento</option>
            </select>
            {moType !== "PALESTRA" && moType !== "EVENTO" && (
              <>
                <label className="form-label">Nome do Arquivo</label>
                <input type="text" className="form-control" value={moName} onChange={e => setMoName(e.target.value)} disabled={isDisabled} />
              </>
            )}
          </div>
        )}

        {/* Source */}
        {!hideSourceMedium && (
          <div className="input-group mb-3">
            <div className="btn-group w-100" role="radiogroup">
              <span className="input-group-text p5 rounded-end-0"><i className="bi bi-menu-up me-1"></i> Source:</span>
              {["ig", "yt", "in", "tktk", "thrd", "spot", "wpp", "appl", "amz", "dzr", "email", "site"].map(s => (
                <React.Fragment key={s}>
                  <input type="radio" className="btn-check" id={`source_${s}`} value={s} checked={source === s} onChange={() => setSource(s)} disabled={isDisabled || Boolean(contentSelect === "SSELL" && ssContent && !ssContent.includes(s.toUpperCase().substring(0,2)))} />
                  <label className="btn btn-outline-secondary" htmlFor={`source_${s}`}><i className={`bi bi-${s === 'wpp' ? 'whatsapp' : s === 'in' ? 'linkedin' : s === 'ig' ? 'instagram' : s === 'yt' ? 'youtube' : s === 'tktk' ? 'tiktok' : s === 'thrd' ? 'threads' : s === 'spot' ? 'spotify' : s === 'appl' ? 'apple' : s === 'amz' ? 'amazon' : s === 'email' ? 'envelope' : s === 'dzr' ? 'music-note-beamed' : 'globe'}`}></i></label>
                </React.Fragment>
              ))}
            </div>
          </div>
        )}

        {/* Medium */}
        {!hideSourceMedium && (
          <div className="input-group mb-3">
            <span className="input-group-text p5"><i className="bi bi-link-45deg me-1"></i> Medium:</span>
            <select className="form-select" value={medium} onChange={e => setMedium(e.target.value)} required disabled={isDisabled}>
              <option value="" disabled>Selecione...</option>
              {mediumOptions.map(o => <option key={o} value={o}>{o}</option>)}
              <option value="OUTRO">Outro (personalizado)</option>
            </select>
            {medium === "OUTRO" && (
              <input type="text" className="form-control" placeholder="Digite o Medium personalizado" value={mediumCustom} onChange={e => setMediumCustom(e.target.value)} disabled={isDisabled} />
            )}
          </div>
        )}

        {/* Term */}
        <div className="input-group mb-3">
          <span className="input-group-text p5"><i className="bi bi-key me-1"></i> Term:</span>
          <input type="text" className="form-control theme-input" placeholder={getTermPlaceholder()} value={term} onChange={e => setTerm(e.target.value)} required disabled={isDisabled} />
        </div>

        {/* Custom Name */}
        <div className="input-group mb-3">
          <span className="input-group-text p5"><i className="bi bi-pencil me-1"></i> Nome Personalizado:</span>
          <input type="text" className="form-control theme-input" placeholder="Opcional" value={customName} onChange={e => setCustomName(e.target.value)} disabled={isDisabled} />
        </div>

        {/* Comment */}
        <div className="input-group mb-3">
          <span className="input-group-text p5"><i className="bi bi-chat-left-text me-1"></i> Comentário:</span>
          <input type="text" className="form-control theme-input" required placeholder="Observação sobre esta UTM" value={comment} onChange={e => setComment(e.target.value)} disabled={isDisabled} />
        </div>

        {/* Preview */}
        <div className="ag-glass mb-4 p-1">
          <div className="card-body p-3">
            <div className="d-flex align-items-center gap-2">
              <i className="bi bi-eye text-primary"></i>
              <small className="text-muted fw-bold">Preview:</small>
            </div>
            <div className="mt-1" style={{ wordBreak: "break-all" }}>
              <span className="text-muted small">{previewUrl}</span>
            </div>
          </div>
        </div>

        <div className="d-flex gap-3 mb-4 mt-4">
          <button type="submit" className="ag-btn-accent flex-grow-1" disabled={isDisabled || loading}>
            <i className="bi bi-magic me-2"></i> {loading ? "Gerando..." : "Gerar UTM"}
          </button>
        </div>
      </form>
    </div>
  );
}
