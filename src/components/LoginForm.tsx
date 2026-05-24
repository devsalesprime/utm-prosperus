"use client";

import React, { useState, useEffect } from "react";
import { useRouter } from "next/navigation";
import { login, register, forgotPassword } from "@/lib/api";
import { useAuth } from "@/lib/auth-context";

interface Props {
  initialMode?: "login" | "register";
  onClose?: () => void;
}

export default function LoginForm({ initialMode = "login", onClose }: Props) {
  const [isRightPanelActive, setIsRightPanelActive] = useState(initialMode === "register");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [success, setSuccess] = useState("");
  const [isForgotPassword, setIsForgotPassword] = useState(false);
  const { refresh } = useAuth();
  const router = useRouter();

  // Login
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");

  // Register
  const [name, setName] = useState("");
  const [regEmail, setRegEmail] = useState("");
  const [regPassword, setRegPassword] = useState("");

  useEffect(() => {
    setIsRightPanelActive(initialMode === "register");
  }, [initialMode]);

  const handleLogin = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError("");
    const res = await login(email, password);
    if (res.success) {
      await refresh();
      if (onClose) onClose();
      else router.push("/dashboard");
    } else {
      setError(res.message || "Credenciais inválidas");
      setLoading(false);
    }
  };

  const handleForgot = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError("");
    setSuccess("");
    const res = await forgotPassword(email);
    if (res.success) {
      setSuccess(res.message || "E-mail de recuperação enviado!");
      setIsForgotPassword(false);
    } else {
      setError(res.message || "Erro ao solicitar recuperação.");
    }
    setLoading(false);
  };

  const handleRegister = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError("");
    setSuccess("");
    const res = await register(name, regEmail, regPassword);
    if (res.success) {
      setSuccess("Cadastro enviado! Aguarde aprovação do administrador.");
      setIsRightPanelActive(false);
    } else {
      setError(res.message || "Erro ao cadastrar");
    }
    setLoading(false);
  };

  return (
    <div>
      {error && <div className="alert alert-danger mb-3">{error}</div>}
      {success && <div className="alert alert-success mb-3">{success}</div>}
      
      <div className={`container ${isRightPanelActive ? "right-panel-active" : ""}`} id="container">
        <div className="form-container sign-up-container">
          <form onSubmit={handleRegister}>
            <h1>Crie sua conta</h1>
            <input type="text" className="form-control mb-3" placeholder="Nome Completo" value={name} onChange={e => setName(e.target.value)} required />
            <input type="email" className="form-control mb-3" placeholder="Email" value={regEmail} onChange={e => setRegEmail(e.target.value)} required />
            <input type="password" className="form-control mb-3" placeholder="Senha" value={regPassword} onChange={e => setRegPassword(e.target.value)} required />
            <button type="submit" disabled={loading}>{loading ? "Enviando..." : "Cadastrar"}</button>
          </form>
        </div>
        <div className="form-container sign-in-container">
          {!isForgotPassword ? (
            <form onSubmit={handleLogin}>
              <h1>Logar</h1>
              <input type="email" className="form-control mb-3" placeholder="Email" value={email} onChange={e => setEmail(e.target.value)} required />
              <input type="password" className="form-control mb-3" placeholder="Senha" value={password} onChange={e => setPassword(e.target.value)} required />
              <a href="#" onClick={(e) => { e.preventDefault(); setIsForgotPassword(true); setError(""); setSuccess(""); }}>Perdeu sua senha?</a>
              <button type="submit" disabled={loading}>{loading ? "Entrando..." : "Logar"}</button>
            </form>
          ) : (
            <form onSubmit={handleForgot}>
              <h1>Recuperar Senha</h1>
              <p className="text-muted mb-3" style={{ fontSize: "14px", lineHeight: 1.4 }}>
                Digite seu e-mail cadastrado e enviaremos um link criptografado para redefinir suas credenciais.
              </p>
              <input type="email" className="form-control mb-3" placeholder="Email cadastrado" value={email} onChange={e => setEmail(e.target.value)} required />
              <button type="submit" disabled={loading}>{loading ? "Enviando..." : "Enviar Link"}</button>
              <a href="#" className="mt-3" onClick={(e) => { e.preventDefault(); setIsForgotPassword(false); setError(""); setSuccess(""); }}>Voltar ao Login</a>
            </form>
          )}
        </div>
        <div className="overlay-container">
          <div className="overlay">
            <div className="overlay-panel overlay-left">
              <h1>Seja Bem vindo!</h1>
              <p>Para continuar conectado conosco, faça login com suas informações pessoais</p>
              <button type="button" className="ghost" onClick={() => setIsRightPanelActive(false)}>Logar com meu usuário</button>
            </div>
            <div className="overlay-panel overlay-right">
              <h1>Olá, tudo bem?</h1>
              <p>Insira seus dados pessoais e crie sua UTM da <strong>Prosperus Club</strong>!</p>
              <button type="button" className="ghost" onClick={() => setIsRightPanelActive(true)}>Criar Usuário</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
