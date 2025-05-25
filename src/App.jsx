
    import React from 'react';
    import DataComparatorPage from '@/pages/DataComparatorPage';
    import { Toaster } from '@/components/ui/toaster';
    import { TooltipProvider } from '@/components/ui/tooltip';

    function App() {
      return (
        <TooltipProvider>
          <div className="min-h-screen bg-gradient-to-br from-slate-900 to-slate-800 text-slate-50 flex flex-col items-center justify-center p-4">
            <DataComparatorPage />
            <Toaster />
          </div>
        </TooltipProvider>
      );
    }

    export default App;
  