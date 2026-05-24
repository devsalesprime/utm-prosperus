"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { useAuth } from "@/lib/auth-context";

interface Props {
  onShowLoginModal?: (mode: "login" | "register") => void;
}

export default function Header({ onShowLoginModal }: Props) {
  const { session, logout } = useAuth();
  const [isDark, setIsDark] = useState(false);

  useEffect(() => {
    const checkTheme = () => {
      setIsDark(document.body.classList.contains("dark-theme"));
    };
    checkTheme();
    window.addEventListener("theme-changed", checkTheme);
    return () => window.removeEventListener("theme-changed", checkTheme);
  }, []);

  return (
    <div className="container mt-2">
      {/* Botões de Login / User Info no topo */}
      {!session ? (
        <div className="d-flex justify-content-end mb-3">
          <button 
            className="ag-btn-accent me-2" 
            onClick={() => onShowLoginModal?.("login")}
          >
            <i className="bi bi-box-arrow-in-right me-2"></i>Entrar
          </button>
          <button 
            className="btn btn-outline-secondary" 
            onClick={() => onShowLoginModal?.("register")}
          >
            <i className="bi bi-person-plus me-2"></i>Cadastro
          </button>
        </div>
      ) : (
        <div className="z-3 float-md-none float-sm-end text-end mx-2 mb-3">
          <span className="me-3">Usuário: {session.username}</span>
          {session.is_admin && (
            <Link href="/admin" className="btn btn-success btn-sm me-2">
              <i className="bi bi-gear"></i> Painel Admin
            </Link>
          )}
          <Link href="/analytics" className="btn btn-primary btn-sm me-2">
            <i className="bi bi-graph-up me-1"></i>Analytics
          </Link>
          <button onClick={() => logout()} className="btn btn-danger btn-sm">Sair</button>
        </div>
      )}

      <div className="row">
        {/* Usando o logo prosperus club pois o sales prime foi descontinuado */}
        <Link href="/" className="text-decoration-none text-reset">
          <img 
            src={isDark ? "/images/logo-tema-claro.svg" : "/images/logo-tema-escuro.svg"} 
            alt="Prosperus Club" 
            className="img-fluid d-block mx-auto" 
            style={{ maxWidth: 200 }}
          />
          <h1 className="text-center mt-3">Gerador de UTM</h1>
        </Link>
      </div>
    </div>
  );
}
