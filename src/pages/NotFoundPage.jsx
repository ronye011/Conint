// src/pages/NotFoundPage.jsx
import { Link } from "react-router-dom";

const NotFoundPage = () => {
  return (
    <div className="min-h-screen bg-slate-900 text-white flex flex-col items-center justify-center p-6">
      <h1 className="text-6xl font-bold text-blue-500 mb-4">404</h1>
      <h2 className="text-2xl font-semibold mb-2">Página não encontrada</h2>
      <p className="mb-6 text-slate-300 text-center max-w-md">
        A página que você está tentando acessar não existe ou foi movida.
      </p>
      <Link
        to="/"
        className="px-6 py-2 bg-blue-600 hover:bg-blue-700 transition rounded-md text-white font-medium"
      >
        Voltar para o login
      </Link>
    </div>
  );
};

export default NotFoundPage;
