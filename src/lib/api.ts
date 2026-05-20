// Funções de chamada para a API PHP Backend
import type { AuthSession, CreateUTMPayload, CreateUTMResponse, DashboardData, UTM, AdminUser } from "@/types/utm";

const API_BASE = "/api";

// ── Auth ─────────────────────────────────────────────────────────────────────

export async function login(email: string, password: string): Promise<{ success: boolean; message: string }> {
  const res = await fetch(`${API_BASE}/auth.php`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    credentials: "include",
    body: JSON.stringify({ action: "login", email, password }),
  });
  return res.json();
}

export async function register(name: string, email: string, password: string): Promise<{ success: boolean; message: string }> {
  const res = await fetch(`${API_BASE}/auth.php`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    credentials: "include",
    body: JSON.stringify({ action: "register", name, email, password }),
  });
  return res.json();
}

export async function logout(): Promise<void> {
  await fetch(`${API_BASE}/auth.php`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    credentials: "include",
    body: JSON.stringify({ action: "logout" }),
  });
}

export async function getSession(): Promise<AuthSession | null> {
  try {
    const res = await fetch(`${API_BASE}/auth.php?action=session`, {
      credentials: "include",
      cache: "no-store",
    });
    if (!res.ok) return null;
    const data = await res.json();
    return data.logged_in ? data : null;
  } catch {
    return null;
  }
}

// ── UTMs ─────────────────────────────────────────────────────────────────────

export async function createUTM(payload: CreateUTMPayload): Promise<CreateUTMResponse> {
  const res = await fetch(`${API_BASE}/create_utm.php`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    credentials: "include",
    body: JSON.stringify(payload),
  });
  return res.json();
}

export async function getUTMs(page = 1, search = ""): Promise<{ data: UTM[]; total: number; pages: number }> {
  const params = new URLSearchParams({ page: String(page), search });
  const res = await fetch(`${API_BASE}/list_utms.php?${params}`, {
    credentials: "include",
    cache: "no-store",
  });
  return res.json();
}

export async function toggleUTM(id: number, enabled: boolean): Promise<{ success: boolean }> {
  const res = await fetch(`${API_BASE}/toggle_utm.php`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    credentials: "include",
    body: JSON.stringify({ id, is_enabled: enabled ? 1 : 0 }),
  });
  return res.json();
}

export async function deleteUTM(id: number, password: string): Promise<{ success: boolean; message: string }> {
  const res = await fetch(`${API_BASE}/delete_utm.php`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    credentials: "include",
    body: JSON.stringify({ id, password }),
  });
  return res.json();
}

// ── Dashboard ─────────────────────────────────────────────────────────────────

export async function getDashboardData(period = 30): Promise<DashboardData> {
  const res = await fetch(`${API_BASE}/dashboard_data.php?period=${period}`, {
    credentials: "include",
    cache: "no-store",
  });
  return res.json();
}

// ── Admin ─────────────────────────────────────────────────────────────────────

export async function getAdminUsers(): Promise<AdminUser[]> {
  const res = await fetch(`${API_BASE}/admin_users.php?action=list`, {
    credentials: "include",
    cache: "no-store",
  });
  const data = await res.json();
  return data.data ?? [];
}

export async function approveUser(userId: number): Promise<{ success: boolean }> {
  const res = await fetch(`${API_BASE}/admin_users.php`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    credentials: "include",
    body: JSON.stringify({ action: "approve", user_id: userId }),
  });
  return res.json();
}

export async function toggleAdmin(userId: number, isAdmin: boolean): Promise<{ success: boolean }> {
  const res = await fetch(`${API_BASE}/admin_users.php`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    credentials: "include",
    body: JSON.stringify({ action: "toggle_admin", user_id: userId, is_admin: isAdmin ? 1 : 0 }),
  });
  return res.json();
}
// Append to the end of api.ts
export async function getTeamMembers() {
  try {
    const res = await fetch(`${API_BASE}/team.php`, { credentials: "omit" });
    if (!res.ok) return [];
    const json = await res.json();
    return json.success ? json.data : [];
  } catch {
    return [];
  }
}
