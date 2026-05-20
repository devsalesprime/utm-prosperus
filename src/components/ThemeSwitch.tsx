"use client";

import { useEffect, useState } from "react";

export default function ThemeSwitch() {
  const [theme, setTheme] = useState<"light" | "dark">("light");

  useEffect(() => {
    const applyTheme = (isDark: boolean) => {
      const body = document.body;
      
      // Classes do body e Bootstrap
      body.classList.toggle("dark-theme", isDark);
      body.classList.toggle("dark-active", isDark);
      body.classList.toggle("bg-dark", isDark);
      body.classList.toggle("text-light", isDark);
      
      // HTML theme do Bootstrap 5.3+
      document.documentElement.setAttribute("data-bs-theme", isDark ? "dark" : "light");

      // Atualizar classes de elementos específicos
      const updateElements = () => {
        document.querySelectorAll("table").forEach(el => el.classList.toggle("table-dark", isDark));
        document.querySelectorAll("table tbody tr").forEach(el => el.classList.toggle("text-light", isDark));
        document.querySelectorAll(".theme-input").forEach(el => {
          el.classList.toggle("dark-input", isDark);
          el.classList.toggle("dark-placeholder", isDark);
          el.classList.toggle("light-input", !isDark);
          el.classList.toggle("light-placeholder", !isDark);
        });
        document.querySelectorAll(".theme-link").forEach(el => {
          el.classList.toggle("dark-link", isDark);
          el.classList.toggle("light-link", !isDark);
        });
        document.querySelectorAll(".modal-content").forEach(el => {
          el.classList.toggle("bg-dark", isDark);
          el.classList.toggle("text-light", isDark);
        });
      };

      updateElements();

      // Mutation observer para aplicar o tema em elementos gerados dinamicamente pelo React
      const observer = new MutationObserver(() => updateElements());
      observer.observe(document.body, { childList: true, subtree: true });

      return () => observer.disconnect();
    };

    const savedTheme = localStorage.getItem("theme");
    const isDark = savedTheme === "dark";
    setTheme(isDark ? "dark" : "light");
    
    const cleanup = applyTheme(isDark);
    
    // Disparar evento
    window.dispatchEvent(new Event("theme-changed"));

    return cleanup;
  }, [theme]); // Re-executar quando o estado theme mudar

  const toggleTheme = (newTheme: "light" | "dark") => {
    setTheme(newTheme);
    localStorage.setItem("theme", newTheme);
  };

  return (
    <div className="p-3 fixed-top mt-5" style={{ width: "10%", zIndex: 1000 }}>
      <div className={`theme-switch-container ${theme === "light" ? "light-active" : "dark-active"}`}>
        <div className="theme-switch-bg"></div>
        <div 
          className={`theme-switch-option light-option ${theme === "light" ? "active" : ""}`}
          onClick={() => toggleTheme("light")}
        >
          <i className="bi bi-sun"></i>
          <span>Light</span>
        </div>
        <div 
          className={`theme-switch-option dark-option ${theme === "dark" ? "active" : ""}`}
          onClick={() => toggleTheme("dark")}
        >
          <i className="bi bi-moon-stars-fill"></i>
          <span>Dark</span>
        </div>
      </div>
    </div>
  );
}
