
    import React, { useState, useEffect, useRef } from 'react';
    import { motion, AnimatePresence, color } from 'framer-motion';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Legend } from '@/components/ui/legend';
    import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
    import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
    import { Checkbox } from '@/components/ui/checkbox';
    import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
    import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
    import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
    import { useToast } from '@/components/ui/use-toast';
    import { UploadCloud, FileText, Banknote, Settings, HelpCircle, Download, Terminal, Loader2, ArrowRight, ArrowLeft } from 'lucide-react';

    const StepIndicator = ({ currentStep, totalSteps }) => (
      <div className="flex justify-center space-x-2 mb-8">
        {Array.from({ length: totalSteps }).map((_, index) => (
          <motion.div
            key={index}
            className={`w-3 h-3 rounded-full ${index + 1 === currentStep ? 'bg-primary' : 'bg-secondary'}`}
            animate={{ scale: index + 1 === currentStep ? 1.2 : 1 }}
            transition={{ type: 'spring', stiffness: 300 }}
          />
        ))}
      </div>
    );

    const HelpTooltip = ({ content }) => (
      <Tooltip>
        <TooltipTrigger asChild>
          <HelpCircle className="h-4 w-4 text-muted-foreground ml-2 cursor-help" />
        </TooltipTrigger>
        <TooltipContent>
          <p className="max-w-xs">{content}</p>
        </TooltipContent>
      </Tooltip>
    );
    
    const DataComparatorPage = () => {
      const [step, setStep] = useState(1);
      const { toast } = useToast();
    
      // Step 1 State
      const [fileFormat, setFileFormat] = useState('csv');
      const [csvSeparator, setCsvSeparator] = useState('0');
      const [referenceColumn, setReferenceColumn] = useState('');
      const [spreadsheetFile, setSpreadsheetFile] = useState(null);
      const spreadsheetFileRef = useRef(null);
      // API Integration State
      const [urlAPI, seturlAPI] = useState('');
      const [api, setapi] = useState('ixcprovedor');
      const [dataInicial, setDataInicial] = useState("");
      const [dataFinal, setDataFinal] = useState("");
    
      // Step 2 State
      const [bank, setBank] = useState('sicoob');
      const [token, settoken] = useState('');
      const [validPix, setvalidPix] = useState('Todos');
      const [comparisonNumber, setComparisonNumber] = useState('seu_numero');
      const [removeBar, setRemoveBar] = useState(false);
      const [removeCheckDigit, setRemoveCheckDigit] = useState(false);
      const [comparisonBase, setComparisonBase] = useState('0');
      const [bankStatementFile, setBankStatementFile] = useState(null);
      const bankStatementFileRef = useRef(null);
    
      // Comparison State
      const [isComparing, setIsComparing] = useState(false);
      const [comparisonLog, setComparisonLog] = useState([]);
      const [downloadReady, setDownloadReady] = useState(false);
      const [comparisonResult, setComparisonResult] = useState(null);
    
      const handleNextStep = () => {
        if (fileFormat !== 'Recebimentos IXC' && fileFormat !== 'API (Em desenvolvimento)' && !referenceColumn.trim()) {
          toast({ title: "Erro de Validação", description: "O nome da coluna de referência é obrigatório.", variant: "destructive" });
          return;
        }
        if (fileFormat === 'API (Em desenvolvimento)' && (!urlAPI.trim() || !token.trim() || !dataInicial.trim() || !dataFinal.trim())) {
          toast({ title: "Erro de Validação", description: "Por favor, preencha os campos de integração.", variant: "destructive" });
          return;
        }
        if (!spreadsheetFile && fileFormat !== 'API (Em desenvolvimento)') {
          toast({ title: "Erro de Validação", description: "Por favor, faça o upload do arquivo.", variant: "destructive" });
          return;
        }
        setStep(2);
      };
    
      const handlePreviousStep = () => {
        setStep(1);
      };

      const getFileAcceptType = (format) => {
        if (format === 'csv') return '.csv';
        if (format === 'xlsx') return '.xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        if (format === 'pdf') return '.pdf';
        return '';
      };

      const getBankFileAcceptType = (selectedBank) => {
        // This is a placeholder. Specific banks might have specific statement formats.
        // For now, let's assume OFX, TXT, or PDF are common.
        if (selectedBank === 'modobank') return '.txt,.csv'; // Example
        return '.txt,.pdf,.csv,.xls';
      };

      const writeInTerminal = (message) => {
        setComparisonLog(prev => [...prev, { type: 'info', message }]);
      };
    
      const handleCompare = async () => {
        if (!bankStatementFile || !spreadsheetFile) {
          toast({ title: "Erro de Validação", description: "Por favor, selecione ambos os arquivos.", variant: "destructive" });
          return;
        }

        setIsComparing(true);
        setDownloadReady(false);
        setComparisonLog([{ type: 'system', message: 'Iniciando comparação...' }]);

        try {
          // Envio da planilha
          const planilhaForm = new FormData();
          planilhaForm.append("file", spreadsheetFile);
          planilhaForm.append("nameColumnFile", referenceColumn);
          if (fileFormat === "csv") planilhaForm.append("csvSeparate", csvSeparator);

          writeInTerminal("Enviando dados do arquivo...");
          const planilhaResp = await fetch(`http://localhost/Conint/src/core/routers/routerInterface.php?route=${encodeURIComponent(fileFormat)}`, {
            method: "POST",
            body: planilhaForm,
            credentials: 'include',
          });

          const planilhaData = await planilhaResp.json();
          if (!planilhaData.success) throw new Error(planilhaData.message || "Erro no envio da planilha.");

          writeInTerminal("Dados do arquivo enviados com sucesso.");
          writeInTerminal("Enviando dados do extrato bancário...");

          // Envio do extrato
          const extratoForm = new FormData();
          extratoForm.append("fileBank", bankStatementFile);
          extratoForm.append("pix_valid", validPix === "Sim" ? "0" : validPix === "Não" ? "1" : "2");
          extratoForm.append("number_valid", comparisonNumber === "seu_numero" ? "0" : comparisonNumber === "nosso_numero" ? "1" : "2");
          extratoForm.append("compare", comparisonBase);
          extratoForm.append("remove_barra", removeBar ? "0" : "1");
          extratoForm.append("remove_verify_digit", removeCheckDigit ? "0" : "1");
          extratoForm.append("dataCSV", planilhaData.data);

          const extratoResp = await fetch(`http://localhost/Conint/src/core/routers/routerInterface.php?route=${bank}`, {
            method: "POST",
            body: extratoForm,
            credentials: 'include',
          });

          const extratoData = await extratoResp.json();
          if (!extratoData.success) throw new Error(extratoData.message || "Erro no envio do extrato.");

          writeInTerminal("Comparação realizada com sucesso!");
          setComparisonResult(extratoData.data);
          localStorage.setItem("comparisonResult", JSON.stringify(extratoData.data));
          toast({ title: "Sucesso", description: "Comparação concluída. Você pode baixar o resultado." });
          setDownloadReady(true);

        } catch (error) {
          toast({ title: "Erro", description: error.message || "Falha geral no processamento.", variant: "destructive" });
          writeInTerminal("❌ " + error.message);
        }
      };
    
      const handleDownload = () => {
        if (!comparisonResult || !Array.isArray(comparisonResult) || comparisonResult.length === 0) {
          toast({ title: "Erro", description: "Nenhum dado para exportar.", variant: "destructive" });
          return;
        }

        let csv = '';

        // Verifica se é array simples ou de objetos
        if (typeof comparisonResult[0] !== 'object' || comparisonResult[0] === null) {
          csv = 'Valor\n' + comparisonResult.map(item => `"${String(item).replace(/"/g, '""')}"`).join('\n');
        } else {
          const validObj = comparisonResult.find(d => typeof d === 'object' && d !== null && Object.keys(d).length > 0);
          if (!validObj) {
            toast({ title: "Erro", description: "Nenhum objeto válido para exportar.", variant: "destructive" });
            return;
          }

          const header = Object.keys(validObj);
          csv += header.join(',') + '\n';

          comparisonResult.forEach(item => {
            const row = header.map(key => {
              const value = item[key] ?? '';
              return `"${String(value).replace(/"/g, '""')}"`;
            }).join(',');
            csv += row + '\n';
          });
        }

        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement("a");
        link.setAttribute("href", url);
        link.setAttribute("download", "resultado.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);

        toast({ title: "Download Iniciado", description: "Arquivo CSV sendo baixado." });
      };

      const renderStepContent = () => {
        const pageTransition = {
          initial: { opacity: 0, x: step === 1 ? -100 : 100 },
          animate: { opacity: 1, x: 0 },
          exit: { opacity: 0, x: step === 1 ? 100 : -100 },
          transition: { type: 'spring', stiffness: 260, damping: 20 }
        };

        if (step === 1) {
          return (
            <motion.div {...pageTransition} key="step1">
              <CardHeader>
                <div className="flex items-center">
                  <FileText className="h-8 w-8 mr-3 text-primary" />
                  <CardTitle>Etapa 1: Configurar Arquivo</CardTitle>
                </div>
                <CardDescription>Escolha o formato e detalhes de seu arquivo de origem.</CardDescription>
              </CardHeader>
              <CardContent className="space-y-6">
                <div className="space-y-2">
                  <Legend htmlFor="fileFormat" className="flex items-center">
                    Formato da Arquivo
                    <HelpTooltip content="Selecione o tipo de arquivo: CSV, XLSX (Excel) ou PDF." />
                  </Legend>
                  <RadioGroup id="fileFormat" value={fileFormat} onValueChange={setFileFormat} className="flex space-x-4">
                    {['csv', 'xlsx', 'Recebimentos IXC', 'API (Em desenvolvimento)'].map(format => (
                      <div key={format} className="flex items-center space-x-2">
                        <RadioGroupItem value={format} id={`format-${format}`} />
                        <Label htmlFor={`format-${format}`}>{format.toUpperCase()}</Label>
                      </div>
                    ))}
                  </RadioGroup>
                </div>
    
                {fileFormat === 'csv' && (
                  <motion.div 
                    initial={{ opacity: 0, height: 0 }} 
                    animate={{ opacity: 1, height: 'auto' }} 
                    exit={{ opacity: 0, height: 0 }}
                    className="space-y-2 overflow-hidden"
                  >
                    <Legend htmlFor="csvSeparator" className="flex items-center">
                      Separador do CSV
                      <HelpTooltip content="Defina o caractere usado para separar os valores no seu arquivo CSV (geralmente vírgula ou ponto e vírgula)." />
                    </Legend>
                    <RadioGroup id="csvSeparator" value={csvSeparator} onValueChange={setCsvSeparator} className="flex space-x-4">
                      {[{label: 'Vírgula (,)', value: '0'}, {label: 'Ponto e Vírgula (;)', value: '1'}].map(sep => (
                        <div key={sep.value} className="flex items-center space-x-2">
                          <RadioGroupItem value={sep.value} id={`sep-${sep.value}`} />
                          <Label htmlFor={`sep-${sep.value}`}>{sep.label}</Label>
                        </div>
                      ))}
                    </RadioGroup>
                  </motion.div>
                )}
    
                {(fileFormat === 'xlsx' || fileFormat === 'csv') && (
                  <div className="space-y-2">
                    <Label htmlFor="referenceColumn" className="flex items-center">
                      Nome da Coluna de Referência
                      <HelpTooltip content="Informe o nome exato da coluna na sua planilha que será usada como chave para a comparação. A primeira linha da planilha deve conter os nomes das colunas." />
                    </Label>
                    <Input 
                      id="referenceColumn" 
                      value={referenceColumn} 
                      onChange={(e) => setReferenceColumn(e.target.value)} 
                      placeholder="Ex: ID_TRANSACAO" 
                      required 
                    />
                  </div>
                )}

                {fileFormat === 'API (Em desenvolvimento)' && (
                  <div className="space-y-4">
                    <div className="space-y-2">
                      <Label htmlFor="api" className="flex items-center">
                        Integração
                        <HelpTooltip content="Selecione o sistema que será utilizado." />
                      </Label>
                      <Select value={api} onValueChange={setapi}>
                        <SelectTrigger id="api">
                          <SelectValue placeholder="Selecione a API" />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem value="ixcprovedor">IXC Provedor</SelectItem>
                        </SelectContent>
                      </Select>
                    </div>

                    <div className="space-y-2">
                      <Label htmlFor="urlAPI" className="flex items-center">
                        URL da API
                        <HelpTooltip content="Informe a url das requisição do sistema para o sistema integrador." />
                      </Label>
                      <Input 
                        id="urlAPI" 
                        value={urlAPI} 
                        onChange={(e) => seturlAPI(e.target.value)} 
                        placeholder="Ex: https://api.seusistema.com.br/router" 
                        required 
                      />
                    </div>

                    <div className="space-y-2">
                      <Label htmlFor="token" className="flex items-center">
                        Token de Acesso
                        <HelpTooltip content="Informe o token para acesso ao sistema integrador." />
                      </Label>
                      <Input 
                        id="token" 
                        value={token} 
                        onChange={(e) => settoken(e.target.value)} 
                        placeholder="Ex: 2343:v243v5vn5b4235v3n45bv235nb4v35b35v325vb43nmn2bn54v2354" 
                        required 
                      />
                    </div>

                    <div className="flex gap-4">
                      <div className="flex-1 space-y-2">
                        <Label htmlFor="dataInicial" className="flex items-center">
                          Data Inicial
                          <HelpTooltip content="Informe a data inicial para filtragem ou requisição." />
                        </Label>
                        <Input
                          id="dataInicial"
                          type="date"
                          value={dataInicial}
                          onChange={(e) => setDataInicial(e.target.value)}
                          required
                        />
                      </div>

                      <div className="flex-1 space-y-2">
                        <Label htmlFor="dataFinal" className="flex items-center">
                          Data Final
                          <HelpTooltip content="Informe a data final para filtragem ou requisição." />
                        </Label>
                        <Input
                          id="dataFinal"
                          type="date"
                          value={dataFinal}
                          onChange={(e) => setDataFinal(e.target.value)}
                          required
                        />
                      </div>
                    </div>
                  </div>
                )}
    
                {fileFormat != 'API (Em desenvolvimento)' && (
                  <div className="space-y-2">
                    <Label htmlFor="spreadsheetFile" className="flex items-center">
                      Upload do Arquivo
                      <HelpTooltip content={`Selecione o arquivo da planilha no formato ${fileFormat.toUpperCase()} escolhido.`} />
                    </Label>
                    <div className="flex items-center space-x-2">
                      <Button type="button" variant="outline" onClick={() => spreadsheetFileRef.current?.click()} className="flex-shrink-0">
                        <UploadCloud className="mr-2 h-4 w-4" /> Escolher Arquivo
                      </Button>
                      <Input 
                        id="spreadsheetFile" 
                        type="file" 
                        ref={spreadsheetFileRef}
                        accept={getFileAcceptType(fileFormat)}
                        onChange={(e) => setSpreadsheetFile(e.target.files[0])} 
                        className="hidden"
                      />
                      {spreadsheetFile && <span className="text-sm text-muted-foreground truncate max-w-[200px]">{spreadsheetFile.name}</span>}
                    </div>
                  </div>
                )}
              </CardContent>
              <CardFooter className="justify-end">
                <Button onClick={handleNextStep} className="bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 transition-all duration-300 ease-in-out">
                  Próximo <ArrowRight className="ml-2 h-4 w-4" />
                </Button>
              </CardFooter>
            </motion.div>
          );
        }
    
        if (step === 2) {
          return (
            <motion.div {...pageTransition} key="step2">
              <CardHeader>
                <div className="flex items-center">
                  <Banknote className="h-8 w-8 mr-3 text-primary" />
                  <CardTitle>Etapa 2: Configurar Extrato Bancário</CardTitle>
                </div>
                <CardDescription>Defina os parâmetros do seu extrato bancário para comparação.</CardDescription>
              </CardHeader>
              <CardContent className="space-y-6">
                <div className="space-y-2">
                  <Label htmlFor="bank" className="flex items-center">
                    Banco de Origem
                    <HelpTooltip content="Selecione o banco do extrato que será utilizado." />
                  </Label>
                  <Select value={bank} onValueChange={setBank}>
                    <SelectTrigger id="bank">
                      <SelectValue placeholder="Selecione o banco" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="sicoob">Sicoob</SelectItem>
                      <SelectItem value="santander">Santander</SelectItem>
                      <SelectItem value="sicredi">Sicredi</SelectItem>
                      <SelectItem value="bancodobrasil">Banco do Brasil</SelectItem>
                      <SelectItem value="modobank">ModoBank</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
    
                {bank === 'modobank' ? (
                  <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }}>
                    <Alert variant="info" className="bg-blue-900/30 border-blue-700">
                      <Settings className="h-4 w-4 !text-blue-400" />
                      <AlertTitle className="text-blue-300">Informação Importante - ModoBank</AlertTitle>
                      <AlertDescription className="text-blue-400">
                        Para o ModoBank, a comparação será feita utilizando o TXID do PIX. As opções de validação de PIX e número de comparação não se aplicam.
                      </AlertDescription>
                    </Alert>
                  </motion.div>
                ) : (
                  <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} className="space-y-4">
                    <div className="space-y-2">
                      <Label htmlFor="validPix" className="flex items-center">
                        Validar dados de PIX
                        <HelpTooltip content="Marque esta opção se desejar validar especificamente transações PIX." />
                      </Label>
                      <RadioGroup value={validPix} onValueChange={setvalidPix} className="flex flex-col sm:flex-row sm:space-x-4 space-y-2 sm:space-y-0">
                        {['Sim', 'Não', 'Todos'].map(numOpt => (
                          <div key={numOpt} className="flex items-center space-x-2">
                            <RadioGroupItem value={numOpt} id={`validPix-${numOpt}`}  />
                            <Label htmlFor={`validPix-${numOpt}`}>{numOpt.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</Label>
                          </div>
                        ))}
                      </RadioGroup>
                    </div>
                    
                    <div className="space-y-2">
                      <Label className="flex items-center">
                        Número para Comparação
                        <HelpTooltip content="Escolha qual número do extrato será usado para cruzar com a planilha: 'Seu número' (identificador do pagador/recebedor), 'Nosso número' (identificador do boleto/documento no banco) ou 'TXID' (identificador da transação PIX, se aplicável)." />
                      </Label>
                      <RadioGroup value={comparisonNumber} onValueChange={setComparisonNumber} className="flex flex-col sm:flex-row sm:space-x-4 space-y-2 sm:space-y-0">
                        {['seu_numero', 'nosso_numero', 'txid'].map(numOpt => (
                          <div key={numOpt} className="flex items-center space-x-2">
                            <RadioGroupItem value={numOpt} id={`compNum-${numOpt}`} disabled={numOpt === 'txid' && (validPix === "Não" || validPix === "Todos" || bank === "sicoob" || bank === "bancodobrasil" || bank === "santander")  /* Example: TXID only if PIX validation is on */} />
                            <Label htmlFor={`compNum-${numOpt}`}>{numOpt.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</Label>
                          </div>
                        ))}
                      </RadioGroup>
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                      {bank !== 'sicoob' && (
                        <div className="flex items-center space-x-2">
                          <Checkbox id="removeBar" checked={removeBar} onCheckedChange={setRemoveBar} />
                          <Label htmlFor="removeBar" className="flex items-center">
                            Remover barra ("/") após número
                            <HelpTooltip content="Alguns bancos incluem uma barra e informações adicionais após o número principal. Marque para remover." />
                          </Label>
                        </div>
                      )}
                      {comparisonNumber == 'nosso_numero' && (
                        <div className="flex items-center space-x-2">
                          <Checkbox id="removeCheckDigit" checked={removeCheckDigit} onCheckedChange={setRemoveCheckDigit} />
                          <Label htmlFor="removeCheckDigit" className="flex items-center">
                            Remover dígito verificador
                            <HelpTooltip content="Remove o último dígito do número, que geralmente é um dígito verificador." />
                          </Label>
                        </div>
                      )}
                    </div>
                  </motion.div>
                )}
    
                <div className="space-y-2">
                  <Label className="flex items-center">
                    Base da Comparação
                    <HelpTooltip content="Defina qual arquivo será a referência principal para a comparação: o extrato bancário ou a planilha." />
                  </Label>
                  <RadioGroup value={comparisonBase} onValueChange={setComparisonBase} className="flex space-x-4">
                    {[{label: 'Falta na planilha', value: '0'}, {label: 'Falta no banco', value: '1'}].map(base => (
                      <div key={base.value} className="flex items-center space-x-2">
                        <RadioGroupItem value={base.value} id={`base-${base.value}`} />
                        <Label htmlFor={`base-${base.value}`}>{base.label}</Label>
                      </div>
                    ))}
                  </RadioGroup>
                </div>
    
                <div className="space-y-2">
                  <Label htmlFor="bankStatementFile" className="flex items-center">
                    Upload do Extrato Bancário
                    <HelpTooltip content={`Selecione o arquivo do extrato bancário. Formatos comuns são OFX, TXT, CSV ou PDF, dependendo do banco.`} />
                  </Label>
                  <div className="flex items-center space-x-2">
                    <Button type="button" variant="outline" onClick={() => bankStatementFileRef.current?.click()} className="flex-shrink-0">
                      <UploadCloud className="mr-2 h-4 w-4" /> Escolher Arquivo
                    </Button>
                    <Input 
                      id="bankStatementFile" 
                      type="file" 
                      ref={bankStatementFileRef}
                      accept={getBankFileAcceptType(bank)}
                      onChange={(e) => setBankStatementFile(e.target.files[0])} 
                      className="hidden"
                    />
                    {bankStatementFile && <span className="text-sm text-muted-foreground truncate max-w-[200px]">{bankStatementFile.name}</span>}
                  </div>
                </div>
              </CardContent>
              <CardFooter className="justify-between">
                <Button variant="outline" onClick={handlePreviousStep}>
                  <ArrowLeft className="mr-2 h-4 w-4" /> Anterior
                </Button>
                <Button onClick={handleCompare} disabled={isComparing || !bankStatementFile} className="bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 transition-all duration-300 ease-in-out">
                  {isComparing ? <Loader2 className="mr-2 h-4 w-4 animate-spin" /> : <Settings className="mr-2 h-4 w-4" />}
                  Comparar
                </Button>
              </CardFooter>
            </motion.div>
          );
        }
        return null;
      };

      return (
        <div className="w-full max-w-2xl">
          <motion.div 
            initial={{ y: -20, opacity: 0 }}
            animate={{ y: 0, opacity: 1 }}
            transition={{ delay: 0.2, type: 'spring' }}
            className="text-center mb-8"
          >
            <h1 className="text-4xl font-bold tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-sky-400 via-cyan-300 to-teal-400">
              Conciliador Inteligente
            </h1>
            <p className="text-lg text-slate-400 mt-2">Compare seus dados financeiros com facilidade e precisão.</p>
          </motion.div>

          <StepIndicator currentStep={step} totalSteps={2} />
          
          <Card className="w-full shadow-2xl overflow-hidden">
            <AnimatePresence mode="wait">
              {renderStepContent()}
            </AnimatePresence>
          </Card>

          {downloadReady && comparisonResult && (
            <motion.div 
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              className="mt-8 text-center"
            >
              <Button onClick={handleDownload} size="lg" className="bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 transition-all duration-300 ease-in-out">
                <Download className="mr-2 h-5 w-5" /> Baixar Resultado CSV
              </Button>
            </motion.div>
          )}

          <Dialog open={isComparing} onOpenChange={ (open) => {if(!open) setIsComparing(false) } }>
            <DialogContent className="sm:max-w-md md:max-w-lg lg:max-w-2xl !bg-slate-900 border-slate-700 text-slate-200">
              <DialogHeader>
                <DialogTitle className="flex items-center text-slate-100">
                  <Terminal className="mr-2 h-5 w-5 text-cyan-400" />
                  Processando Comparação
                </DialogTitle>
                <DialogDescription className="text-slate-400">
                  Aguarde enquanto os arquivos são processados e os dados comparados.
                </DialogDescription>
              </DialogHeader>
              <div className="mt-4 max-h-80 overflow-y-auto p-4 bg-black rounded-md font-mono text-sm space-y-1 scrollbar-thin scrollbar-thumb-slate-700 scrollbar-track-slate-800">
                {comparisonLog.map((log, index) => (
                  <motion.p 
                    key={index} 
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ delay: index * 0.1 }}
                    className={`
                      ${log.type === 'error' ? 'text-red-400' : ''}
                      ${log.type === 'success' ? 'text-green-400' : ''}
                      ${log.type === 'info' ? 'text-blue-400' : ''}
                      ${log.type === 'system' ? 'text-yellow-400' : ''}
                    `}
                  >
                    <span className="mr-2">{log.type === 'error' ? '❌' : log.type === 'success' ? '✅' : log.type === 'info' ? 'ℹ️' : '⚙️'}</span>
                    {log.message}
                  </motion.p>
                ))}
                {!downloadReady && <Loader2 className="h-4 w-4 animate-spin text-slate-400 inline-block" />}
              </div>
              {downloadReady && (
                <div className="mt-6 flex justify-end space-x-2">
                   <Button variant="outline" onClick={() => setIsComparing(false)}>Fechar</Button>
                   <Button onClick={handleDownload} className="bg-gradient-to-r from-purple-500 to-pink-500">
                     <Download className="mr-2 h-4 w-4" /> Baixar Resultado
                   </Button>
                </div>
              )}
            </DialogContent>
          </Dialog>
        </div>
      );
    };
    
    export default DataComparatorPage;
  