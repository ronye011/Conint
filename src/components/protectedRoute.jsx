// src/components/ProtectedRoute.jsx
import { useEffect, useState } from "react";
import { Navigate } from "react-router-dom";
import LoadingScreen from "../pages/LoadingScreen"; // ajuste o caminho se necessário

const ProtectedRoute = ({ children }) => {
  const [authChecked, setAuthChecked] = useState(false);
  const [isAuthenticated, setIsAuthenticated] = useState(false);

  useEffect(() => {
    fetch("http://localhost/Conint/src/core/routers/sessionCheck.php", {
      credentials: "include", // Importante: envia cookies com a requisição!
    })
      .then((res) => {
        if (res.status === 200) {
          return res.json();
        }
        throw new Error("Não autenticado");
      })
      .then((data) => {
        if (data.authenticated) {
          setIsAuthenticated(true);
        }
      })
      .catch(() => {
        setIsAuthenticated(false);
      })
      .finally(() => {
        setAuthChecked(true);
      });
  }, []);

  if (!authChecked) {
    return <LoadingScreen />;
  }

  if (!isAuthenticated) {
    return <Navigate to="/" />;
  }

  return children;
};

export default ProtectedRoute;
