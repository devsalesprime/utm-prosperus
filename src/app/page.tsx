"use client";

import { useState } from "react";
import Header from "@/components/Header";
import ThemeSwitch from "@/components/ThemeSwitch";
import UTMGenerator from "@/components/UTMGenerator";
import UTMTable from "@/components/UTMTable";
import LoginForm from "@/components/LoginForm";
import { useAuth } from "@/lib/auth-context";

export default function HomePage() {
  const { session } = useAuth();
  const [showLoginModal, setShowLoginModal] = useState(false);
  const [loginMode, setLoginMode] = useState<"login" | "register">("login");
  const [refreshKey, setRefreshKey] = useState(0);

  const openModal = (mode: "login" | "register") => {
    setLoginMode(mode);
    setShowLoginModal(true);
  };

  return (
    <>
      <ThemeSwitch />
      <Header onShowLoginModal={openModal} />

      {/* Modal de Login e Cadastro (mesmo estilo do index.php) */}
      {showLoginModal && (
        <>
          <div className="modal-backdrop fade show"></div>
          <div className="modal fade show" style={{ display: "block" }} tabIndex={-1}>
            <div className="modal-dialog modal-dialog-centered modal-lg">
              <div className="modal-content">
                <div className="modal-header">
                  <h5 className="modal-title">Autenticação do usuário</h5>
                  <button type="button" className="btn-close" onClick={() => setShowLoginModal(false)}></button>
                </div>
                <div className="modal-body">
                  <LoginForm initialMode={loginMode} onClose={() => setShowLoginModal(false)} />
                </div>
              </div>
            </div>
          </div>
        </>
      )}

      {/* Formulário UTM Generator (estilo original com disabled se não logado) */}
      <UTMGenerator onSuccess={() => setRefreshKey(k => k + 1)} />

      {/* Sobre UTMs (Accordion) */}
      <div className="container mt-4 mb-5">
        <p>
          <a data-bs-toggle="collapse" href="#sobreUTM" className="theme-link" role="button" aria-expanded="false" aria-controls="sobreUTM">
            <i className="bi bi-caret-right-fill"></i> Sobre links de UTM
          </a>
        </p>
        <div className="collapse mt-3" id="sobreUTM">
          <div className="card card-body">
            <h5 className="mb-3"><i className="bi bi-gear me-2"></i>Como Funciona Este Sistema</h5>
            <div className="alert alert-info">
              <strong>Fluxo de Criação:</strong>
              <ol className="mb-0 mt-2">
                <li><strong>URL do Site:</strong> Insira a URL de destino</li>
                <li><strong>Canal:</strong> Selecione o canal de marketing</li>
                <li><strong>Origem/Fonte:</strong> Escolha o tipo de tráfego</li>
                <li><strong>UTM Source & Medium:</strong> Selecione a plataforma e o formato do conteúdo</li>
                <li><strong>Nome Personalizado:</strong> (Opcional) Nome curto para a URL encurtada</li>
                <li><strong>Comentário:</strong> Descrição interna</li>
              </ol>
            </div>
          </div>
        </div>
      </div>

      {/* Histórico de URLs */}
      {session?.logged_in && (
        <div className="container mt-5 mb-5">
          <div className="ag-card p-3">
            <UTMTable key={refreshKey} />
          </div>
        </div>
      )}
    </>
  );
}
