import { redirect } from "next/navigation";

export default function ShortCodeRedirect({ params }: { params: { code: string } }) {
  // Ignora rotas internas
  if (["admin", "analytics", "dashboard", "login"].includes(params.code)) {
    return null;
  }
  
  // Redireciona para o script PHP legado que registra o clique e faz o redirecionamento final
  redirect(`/redirect.php?code=${params.code}`);
}
