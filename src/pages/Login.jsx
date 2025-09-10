import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { motion } from "framer-motion";
import { ArrowRight, LogIn } from "lucide-react";

function Login() {
  const [email, setEmail] = useState("");
  const [senha, setSenha] = useState("");
  const navigate = useNavigate();

  const handleLogin = async (e) => {
    e.preventDefault();

    const data = {
      email : email,
      senha : senha,
    }
    const loginResp = await fetch("http://localhost/Conint/src/core/routers/routerInterface.php?route=Login", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(data),
      credentials: 'include',
    });
    const loginResult = await loginResp.json();

    if (loginResult.status === 'success') {
      navigate("/DataComparatorPage");
    } else {
      alert("Credenciais inválidas!");
    }
  };

  return (
    <div
      className="flex items-center justify-center min-h-screen px-4"
      style={{ backgroundColor: "#13182dff" }}
    >
      <motion.div
        initial={{ opacity: 0, y: -30 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.6, ease: "easeOut" }}
        className="w-full max-w-md"
      >
        <div className="bg-slate-900 border border-slate-700 shadow-2xl rounded-xl p-8">
          {/* Título */}
          <h1 className="text-3xl font-bold tracking-tight text-center bg-clip-text text-transparent bg-gradient-to-r from-sky-400 via-cyan-300 to-teal-400 mb-8">
            Conciliador Inteligente
          </h1>

          <h2 className="text-xl font-semibold text-center text-slate-200 mb-6 flex items-center justify-center gap-2">
            <LogIn className="h-5 w-5 text-cyan-400" /> Login
          </h2>

          {/* Formulário */}
          <form onSubmit={handleLogin} className="space-y-5">
            <div>
              <input
                type="email"
                placeholder="E-mail"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                required
                className="w-full px-4 py-3 border border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-400 bg-slate-800 text-slate-200 placeholder-slate-400 transition"
              />
            </div>

            <div>
              <input
                type="password"
                placeholder="Senha"
                value={senha}
                onChange={(e) => setSenha(e.target.value)}
                required
                className="w-full px-4 py-3 border border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-400 bg-slate-800 text-slate-200 placeholder-slate-400 transition"
              />
            </div>

            <motion.button
              type="submit"
              whileTap={{ scale: 0.97 }}
              className="w-full py-3 rounded-lg font-semibold text-white bg-gradient-to-r from-sky-500 to-cyan-600 hover:from-sky-600 hover:to-cyan-700 focus:outline-none focus:ring-2 focus:ring-cyan-400 flex items-center justify-center gap-2 transition"
            >
              Entrar <ArrowRight className="h-4 w-4" />
            </motion.button>
          </form>
        </div>
      </motion.div>
    </div>
  );
}

export default Login;
