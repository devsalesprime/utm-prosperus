"use client";

import { useEffect, useState, useCallback } from "react";
import Header from "@/components/Header";
import ThemeSwitch from "@/components/ThemeSwitch";
import { getAdminUsers, approveUser, toggleAdmin } from "@/lib/api";
import type { AdminUser } from "@/types/utm";
import { useAuth } from "@/lib/auth-context";
import { useRouter } from "next/navigation";

export default function AdminPage() {
  const { session, loading: authLoading } = useAuth();
  const router = useRouter();
  const [users, setUsers] = useState<AdminUser[]>([]);
  const [loading, setLoading] = useState(true);

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
    }
  }, [session, loadUsers]);

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
                        <span className="badge bg-primary">Administrador</span>
                      ) : (
                        <span className="badge bg-secondary">Usuário</span>
                      )}
                    </td>
                    <td className="text-end">
                      <div className="btn-group">
                        {!user.is_approved && (
                          <button 
                            className="btn btn-sm btn-success" 
                            onClick={() => handleApprove(user.id)}
                            title="Aprovar Usuário"
                          >
                            <i className="bi bi-check-lg"></i>
                          </button>
                        )}
                        <button 
                          className={`btn btn-sm ${user.is_admin ? 'btn-outline-danger' : 'btn-outline-primary'}`}
                          onClick={() => handleToggleAdmin(user.id, user.is_admin)}
                          title={user.is_admin ? "Remover Admin" : "Tornar Admin"}
                        >
                          <i className={`bi ${user.is_admin ? 'bi-arrow-down' : 'bi-arrow-up'}`}></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </>
  );
}
