"use client";

import { useEffect, useState, useCallback } from "react";
import Header from "@/components/Header";
import ThemeSwitch from "@/components/ThemeSwitch";
import UTMTable from "@/components/UTMTable";
import { getDashboardData } from "@/lib/api";
import type { DashboardData } from "@/types/utm";
import { useAuth } from "@/lib/auth-context";
import { useRouter } from "next/navigation";
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  ArcElement,
  Filler
} from 'chart.js';
import { Doughnut, Line } from 'react-chartjs-2';

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  ArcElement,
  Filler
);

// Paleta categorica do grafico, liderada pelo ouro da marca.
//
// Sao SEIS e nao mais: num donut qualquer fatia encosta em qualquer
// outra, entao a checagem que vale e a de todos os pares, nao a de
// vizinhos. Dentro das familias de cor da marca, seis e o maximo que
// sobrevive a essa checagem nos dois modos. Com oito, dois dourados
// ficavam a ΔE 1.9 um do outro: ninguem distingue, nem com visao de
// cor normal. Validado com scripts/validate_palette.js da skill
// dataviz (--pairs all, modos claro e escuro).
//
// O aviso remanescente de CVD (ΔE 6.5, faixa de piso) so e aceitavel
// com codificacao secundaria. Ela existe e nao pode ser removida:
// legenda com rotulo de texto em cada fatia e vao de 2px na cor da
// superficie separando as fatias.
const COLORS = [
  '#B38D00', '#009F99', '#975AC0', '#686800', '#5590F3', '#006E9A'
];
const COR_OUTROS = '#95A4B4'; // cinza neutro: "Outros" nao e uma identidade
const MAX_FATIAS = COLORS.length;

// Acima de 8 series a cor deixa de identificar: ninguem casa 15 tons de
// legenda com 15 fatias. O excedente vira uma fatia "Outros" so.
function agruparFontes(linhas: { source_name: string; total_clicks: number | string }[]) {
  const ordenado = [...linhas].sort((a, b) => Number(b.total_clicks) - Number(a.total_clicks));
  const topo = ordenado.slice(0, MAX_FATIAS);
  const resto = ordenado.slice(MAX_FATIAS);
  if (resto.length === 0) return { rotulos: topo.map(d => d.source_name), valores: topo.map(d => Number(d.total_clicks)), temOutros: false };
  const somaResto = resto.reduce((acc, d) => acc + Number(d.total_clicks), 0);
  return {
    rotulos: [...topo.map(d => d.source_name), `Outros (${resto.length})`],
    valores: [...topo.map(d => Number(d.total_clicks)), somaResto],
    temOutros: true,
  };
}

export default function AnalyticsPage() {
  const { session, loading: authLoading } = useAuth();
  const router = useRouter();
  const [data, setData] = useState<DashboardData | null>(null);
  const [period, setPeriod] = useState(30);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (authLoading) return;
    if (!session || !session.logged_in) {
      router.push("/");
    }
  }, [session, authLoading, router]);

  const loadData = useCallback(async () => {
    setLoading(true);
    try {
      const res = await getDashboardData(period);
      setData(res);
    } catch (e) {
      console.error(e);
    } finally {
      setLoading(false);
    }
  }, [period]);

  useEffect(() => {
    if (session && session.logged_in) {
      loadData();
    }
  }, [session, loadData]);

  if (!session || !session.logged_in) return null;

  return (
    <>
      <ThemeSwitch />
      <Header />

      <div className="container mt-4 mb-5">
        <div className="ds-page-head">
          <span className="ds-page-head__icon" aria-hidden="true"><i className="bi bi-graph-up-arrow"></i></span>
          <div className="ds-page-head__text">
            <h1>Analytics Dashboard</h1>
            <p>Métricas de performance das UTMs</p>
          </div>
          <div className="ds-page-head__aside">
            <div className="btn-group period-filter" role="group" aria-label="Período">
              {[7, 15, 30, 90, 365].map(p => (
                <button
                  key={p}
                  className={`btn btn-sm btn-outline-secondary ${period === p ? 'active' : ''}`}
                  onClick={() => setPeriod(p)}
                >
                  {p}d
                </button>
              ))}
            </div>
          </div>
        </div>

        {/* KPI Cards */}
        <div className="row g-3 mb-4">
          {[
            { label: "Total de UTMs", value: data?.kpis?.total_utms, icon: "bi-link-45deg" },
            { label: "Total de cliques", value: data?.kpis?.total_clicks, icon: "bi-cursor-fill" },
            { label: "Média por UTM", value: Math.round(data?.kpis?.avg_clicks || 0), icon: "bi-bar-chart-fill" },
            { label: "UTMs ativas", value: data?.kpis?.active_utms, icon: "bi-check-circle-fill" }
          ].map((kpi, idx) => (
            <div key={idx} className="col-6 col-md-3">
              <div className="ag-card ds-kpi">
                <div className="ds-kpi__label">{kpi.label}</div>
                <div className="ds-kpi__value">{loading ? "…" : (kpi.value || 0).toLocaleString('pt-BR')}</div>
                <i className={`bi ${kpi.icon} ds-kpi__icon`} aria-hidden="true"></i>
              </div>
            </div>
          ))}
        </div>

        {/* Gráficos */}
        <div className="row g-3 mb-4">
          <div className="col-md-5">
            <div className="ag-card p-3 h-100">
              <h5 className="mb-3"><i className="bi bi-pie-chart-fill ds-icon me-2"></i>Cliques por Fonte</h5>
              <div style={{ position: "relative", height: "300px", width: "100%" }}>
                {!loading && data?.clicks_by_source && (
                  <Doughnut
                    data={{
                      labels: agruparFontes(data.clicks_by_source).rotulos,
                      datasets: [{
                        data: agruparFontes(data.clicks_by_source).valores,
                        backgroundColor: (() => {
                          const g = agruparFontes(data.clicks_by_source);
                          const cores = COLORS.slice(0, Math.min(MAX_FATIAS, g.valores.length));
                          return g.temOutros ? [...cores, COR_OUTROS] : cores;
                        })(),
                        // vao na cor da superficie: e a separacao fisica
                        // que a faixa de piso de CVD exige
                        borderColor: getComputedStyle(document.body)
                          .getPropertyValue('--color-bg-card').trim() || '#031726',
                        borderWidth: 2
                      }]
                    }}
                    options={{ maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }}
                  />
                )}
              </div>
            </div>
          </div>
          <div className="col-md-7">
            <div className="ag-card p-3 h-100">
              <h5 className="mb-3"><i className="bi bi-activity ds-icon me-2"></i>Tendência de Cliques</h5>
              <div style={{ position: "relative", height: "300px", width: "100%" }}>
                {!loading && data?.clicks_trend && (
                  <Line
                    data={{
                      labels: data.clicks_trend.map(d => {
                        const dt = new Date(d.click_date + 'T00:00:00');
                        return dt.toLocaleDateString('pt-BR', { day: '2-digit', month: 'short' });
                      }),
                      datasets: [{
                        label: 'Cliques',
                        data: data.clicks_trend.map(d => Number(d.click_count)),
                        borderColor: '#FFDA71',
                        backgroundColor: 'rgba(255, 218, 113, 0.14)',
                        borderWidth: 2,
                        pointRadius: 0,
                        pointHoverRadius: 5,
                        fill: true,
                        tension: 0.4
                      }]
                    }}
                    options={{ maintainAspectRatio: false, plugins: { legend: { display: false } } }}
                  />
                )}
              </div>
            </div>
          </div>
        </div>

        {/* Tabela de Top UTMs e Por Usuário */}
        <div className="row g-3 mb-5">
          <div className="col-md-8">
            <div className="ag-card p-3 h-100">
              <h5 className="mb-3"><i className="bi bi-trophy-fill ds-icon me-2"></i>Top 10 UTMs</h5>
              <div className="table-responsive">
                <table className="table table-sm table-hover">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Short Code</th>
                      <th>Descrição</th>
                      <th>Criador</th>
                      <th className="text-end">Cliques</th>
                    </tr>
                  </thead>
                  <tbody>
                    {loading ? <tr><td colSpan={5} className="text-center py-3">Carregando...</td></tr> : 
                     data?.top_utms?.map((u, i) => (
                      <tr key={i}>
                        <td><span className={`badge ${i<3 ? 'bg-warning text-dark' : 'bg-secondary'}`}>{i+1}</span></td>
                        <td><code>{u.shortened_url}</code></td>
                        <td>{u.comment || '-'}</td>
                        <td>{u.username}</td>
                        <td className="text-end fw-bold">{Number(u.clicks).toLocaleString('pt-BR')}</td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <div className="col-md-4">
            <div className="ag-card p-3 h-100">
              <h5 className="mb-3"><i className="bi bi-people-fill ds-icon me-2"></i>Por Usuário</h5>
              <div className="table-responsive">
                <table className="table table-sm table-hover">
                  <thead>
                    <tr>
                      <th>Usuário</th>
                      <th className="text-center">UTMs</th>
                      <th className="text-end">Cliques</th>
                    </tr>
                  </thead>
                  <tbody>
                    {loading ? <tr><td colSpan={3} className="text-center py-3">Carregando...</td></tr> : 
                     data?.by_user?.map((u, i) => (
                      <tr key={i}>
                        <td>{u.username}</td>
                        <td className="text-center">{u.total}</td>
                        <td className="text-end fw-bold">{Number(u.clicks).toLocaleString('pt-BR')}</td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        {/* Minhas UTMs */}
        <div className="mt-5">
          <div className="ds-page-head">
            <span className="ds-page-head__icon" aria-hidden="true"><i className="bi bi-list-ul"></i></span>
            <div className="ds-page-head__text"><h2>Todas as UTMs</h2></div>
          </div>
          <div className="ag-card p-3">
            <UTMTable />
          </div>
        </div>
      </div>
    </>
  );
}
