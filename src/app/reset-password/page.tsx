"use client";

import React, { useState, useEffect, Suspense } from "react";
import { useRouter, useSearchParams } from "next/navigation";
import { resetPassword } from "@/lib/api";

function ResetPasswordForm() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const token = searchParams.get("token");

  const [password, setPassword] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [success, setSuccess] = useState("");
  const [isDark, setIsDark] = useState(false);

  useEffect(() => {
    const isDarkMode = document.documentElement.getAttribute("data-bs-theme") === "dark";
    setIsDark(isDarkMode);

    const observer = new MutationObserver(() => {
      setIsDark(document.documentElement.getAttribute("data-bs-theme") === "dark");
    });
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ["data-bs-theme"] });
    return () => observer.disconnect();
  }, []);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!token) {
      setError("Token de redefinição não encontrado ou inválido.");
      return;
    }
    if (password !== confirmPassword) {
      setError("As senhas não coincidem.");
      return;
    }
    if (password.length < 8) {
      setError("A senha deve ter pelo menos 8 caracteres.");
      return;
    }

    setLoading(true);
    setError("");
    setSuccess("");

    const res = await resetPassword(token, password);
    if (res.success) {
      setSuccess("Senha redefinida com sucesso! Redirecionando para o login...");
      setTimeout(() => {
        router.push("/");
      }, 3000);
    } else {
      setError(res.message || "Ocorreu um erro ao redefinir sua senha.");
    }
    setLoading(false);
  };

  return (
    <div className="d-flex justify-content-center align-items-center vh-100 p-3">
      <div className="card shadow-lg w-100" style={{ maxWidth: "450px", border: "none", borderRadius: "12px" }}>
        <div className="card-body p-5">
          <div className="text-center mb-4">
            <img 
              src={isDark ? "/images/logo-tema-claro.svg" : "/images/logo-tema-escuro.svg"} 
              alt="Prosperus Club" 
              className="img-fluid mb-3" 
              style={{ maxWidth: 200 }}
            />
            <h3 className="fw-bold">Nova Senha</h3>
            <p className="text-muted">Crie uma nova senha de acesso.</p>
          </div>

          {error && <div className="alert alert-danger">{error}</div>}
          {success && <div className="alert alert-success">{success}</div>}

          {!success && (
            <form onSubmit={handleSubmit}>
              <div className="mb-3">
                <label className="form-label fw-bold">Nova Senha</label>
                <input 
                  type="password" 
                  className="form-control form-control-lg" 
                  value={password}
                  onChange={e => setPassword(e.target.value)}
                  placeholder="No mínimo 8 caracteres"
                  required 
                />
              </div>
              <div className="mb-4">
                <label className="form-label fw-bold">Confirmar Senha</label>
                <input 
                  type="password" 
                  className="form-control form-control-lg" 
                  value={confirmPassword}
                  onChange={e => setConfirmPassword(e.target.value)}
                  placeholder="Digite novamente"
                  required 
                />
              </div>
              <button 
                type="submit" 
                className="btn btn-lg w-100 fw-bold" 
                style={{ background: "linear-gradient(135deg, #FFDA71, #CA9A43)", color: "#031A2B", border: "none" }}
                disabled={loading || !token}
              >
                {loading ? "Salvando..." : "Redefinir Credenciais"}
              </button>
            </form>
          )}

          <div className="text-center mt-4">
            <a href="/" className="text-decoration-none fw-bold" style={{ color: "var(--sp-text-primary)" }}>
              Voltar ao Login
            </a>
          </div>
        </div>
      </div>
    </div>
  );
}

export default function ResetPasswordPage() {
  return (
    <Suspense fallback={<div className="vh-100 d-flex justify-content-center align-items-center"><div className="spinner-border text-warning" role="status"></div></div>}>
      <ResetPasswordForm />
    </Suspense>
  );
}
