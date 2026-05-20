import type { Metadata } from "next";
import { AuthProvider } from "@/lib/auth-context";
import "./globals.css";

export const metadata: Metadata = {
  title: "Gerador de UTM",
  description: "Gerador de UTMs da equipe Prosperus Club",
  icons: {
    icon: "/favicon.ico",
  },
};

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="pt-BR">
      <head>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
        <link rel="stylesheet" href="/css/base.css" />
        <link rel="stylesheet" href="/css/componentes.css" />
        <link rel="stylesheet" href="/css/utm-btn.css" />
        <link rel="stylesheet" href="/css/theme-antigravity.css" />
        <link rel="stylesheet" href="/css/tema.css" />
        <link rel="stylesheet" href="/css/responsivo.css" />
      </head>
      <body className="theme-sales-prime">
        <AuthProvider>{children}</AuthProvider>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
      </body>
    </html>
  );
}
