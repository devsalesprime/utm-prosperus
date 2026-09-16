// Tipos TypeScript compartilhados em todo o projeto

export interface User {
  id: number;
  name: string;
  email: string;
  is_admin: boolean;
  is_approved: boolean;
}

export interface AuthSession {
  user_id: number;
  username: string;
  is_admin: boolean;
  logged_in: boolean;
}

export interface UTM {
  id: number;
  original_url: string;
  long_url: string;
  shortened_url: string;
  username: string;
  comment: string | null;
  clicks: number;
  is_enabled: boolean;
  domain: string;
  generation_date: string;
  short_url?: string;
}

export interface CreateUTMPayload {
  url: string;
  utm_campaign: string;
  utm_source: string;
  utm_medium: string;
  utm_content: string;
  utm_term: string;
  custom_name?: string;
  comment?: string;
}

export interface CreateUTMResponse {
  success: boolean;
  message: string;
  data?: {
    id: number;
    short_code: string;
    short_url: string;
    long_url: string;
    original_url: string;
  };
}

export interface DashboardKPIs {
  total_utms: number;
  total_clicks: number;
  avg_clicks: number;
  active_utms: number;
}

export interface ClicksBySource {
  source_name: string;
  total_clicks: number;
  utm_count: number;
}

export interface ClickTrend {
  click_date: string;
  click_count: number;
}

export interface TopUTM {
  shortened_url: string;
  clicks: number;
  comment: string | null;
  username: string;
  generation_date: string;
  campaign_raw: string;
}

export interface DashboardData {
  kpis: DashboardKPIs;
  clicks_by_source: ClicksBySource[];
  clicks_trend: ClickTrend[];
  top_utms: TopUTM[];
  by_user: { username: string; total: number; clicks: number }[];
  period: number;
}

export interface DayClickUTM {
  id: number;
  shortened_url: string;
  comment: string | null;
  username: string | null;
  clicks_day: number;
  campaign: string;
  source: string;
}

export interface DayClicks {
  date: string;
  total: number;
  utms: DayClickUTM[];
}

export interface ProfileUser {
  id: number;
  name: string;
  email: string;
  is_admin: boolean;
  is_approved: boolean;
  created_at: string;
}
export interface ProfileActivity {
  total_utms: number;
  total_clicks: number;
  ativas: number;
  ultima_utm: string | null;
}
export interface ProfileData {
  success?: boolean;
  error?: string;
  user: ProfileUser;
  activity: ProfileActivity;
}

export interface AdminUser {
  id: number;
  name: string;
  email: string;
  is_admin: boolean;
  is_approved: boolean;
  created_at: string;
}
