import { BrowserRouter, Routes, Route } from "react-router-dom";
import Login from "./pages/Login";
import DataComparatorPage from "./pages/DataComparatorPage";
import ProtectedRoute from "./components/protectedRoute";
import { Toaster } from "@/components/ui/toaster";
import { TooltipProvider } from "@/components/ui/tooltip";
import NotFoundPage from "./pages/NotFoundPage";

function App() {
  return (
    <BrowserRouter>
      <Routes>
        {/* Rota pública */}
        <Route path="/" element={<Login />} />

        {/* Rota protegida */}
        <Route
          path="/DataComparatorPage"
          element={
            <ProtectedRoute>
              <TooltipProvider>
                <div className="min-h-screen bg-gradient-to-br from-slate-900 to-slate-800 text-slate-50 flex flex-col items-center justify-center p-4">
                  <DataComparatorPage />
                  <Toaster />
                </div>
              </TooltipProvider>
            </ProtectedRoute>
          }
        />

        {/* Rota fallback (404) */}
        <Route path="*" element={<NotFoundPage />} />
      </Routes>
    </BrowserRouter>
  );
}

export default App;
