@echo off
setlocal EnableExtensions DisableDelayedExpansion
chcp 65001 >nul

REM ============================================================
REM CONFIGURACAO
REM ============================================================

set "ROOT=%~dp0"
set "OUTPUT=%~dp0consolidado.txt"
set "API_JSON=%~dp0api-docs.json"
set "README_MD=%~dp0README.md"

echo.
echo ============================================================
echo      CONSOLIDADOR SCALLE ERP (AUTO README + API JSON)
echo ============================================================
echo.
echo Pasta raiz:
echo %ROOT%
echo.

REM ============================================================
REM PASSO 1: GERAR O README.MD ATUALIZADO DO PROJETO
REM ============================================================

echo [1/3] Gerando README.md atualizado do Scalle ERP...
(
echo # 🚀 Scalle ERP - Reboot Modular
echo.
echo Sistema ERP construído com arquitetura de **Monolito Modular** desacoplado via APIs RESTful.
echo.
echo ## 🛠️ Tecnologias e Stack
echo - **Backend:** PHP 8.4 / 8.5 ^(Laravel Framework^)
echo - **Banco de Dados:** MySQL / MariaDB
echo - **Autenticação:** Laravel Sanctum ^(Tokens Bearer/JWT^)
echo - **Documentação:** OpenAPI / Scramble ^(/docs/api^)
echo.
echo ## 🧩 Módulos do ERP
echo.
echo ### 1. Módulo Pessoas ^(`app/Modules/Pessoas`^)
echo - Cadastro e gerenciamento unificado de **Clientes** e **Fornecedores**.
echo - **Rota Base:** `POST /api/v1/pessoas`
echo - **DTO:** Preservação de contrato de entrada via `PessoaDTO`.
echo - **Tabela dedicada:** `pes_pessoas`.
echo.
echo ## 🚦 Como Rodar Localmente
echo ```bash
echo # Subir o servidor de dev
echo php artisan serve
echo.
echo # Acessar a documentação interativa
echo [http://127.0.0.1:8000/docs/api](http://127.0.0.1:8000/docs/api)
echo ```
) > "%README_MD%"

REM ============================================================
REM PASSO 2: GERAR O JSON DA API VIA LARAVEL AUTOMATICAMENTE
REM ============================================================

echo [2/3] Exportando especificação OpenAPI (api-docs.json)...
php artisan scramble:export > "%API_JSON%" 2>nul

if not exist "%API_JSON%" (
    echo [Aviso] fallback ativado para geracao do JSON...
    php -r "require '%ROOT%vendor/autoload.php'; $app = require_once '%ROOT%bootstrap/app.php'; $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class); $kernel->bootstrap(); $doc = ['openapi' => '3.0.0', 'info' => ['title' => 'Scalle ERP API', 'version' => '1.0.0'], 'paths' => ['/api/v1/pessoas' => ['post' => ['summary' => 'Criar Pessoa']]]]; file_put_contents('%API_JSON%', json_encode($doc, JSON_PRETTY_PRINT));"
)

echo.
echo [3/3] Consolidando arquivos do projeto...
echo.

REM ============================================================
REM REMOVE ARQUIVO CONSOLIDADO ANTERIOR
REM ============================================================

if exist "%OUTPUT%" (
    del /f /q "%OUTPUT%" >nul 2>&1
)

REM ============================================================
REM EXECUCAO POWERSHELL VIA ARCHIVO TEMPORARIO / BLINDA ESTRUTURA
REM ============================================================

powershell -NoProfile -ExecutionPolicy Bypass -Command "$root = [System.IO.Path]::GetFullPath('%ROOT%'); $output = [System.IO.Path]::GetFullPath('%OUTPUT%'); $extList = @('.txt','.md','.markdown','.html','.htm','.css','.scss','.sass','.less','.js','.jsx','.ts','.tsx','.json','.xml','.csv','.php','.phtml','.sql','.py','.java','.kt','.kts','.c','.cpp','.h','.hpp','.cs','.vb','.vba','.bas','.cls','.frm','.sh','.bat','.cmd','.yml','.yaml','.ini','.conf','.config','.env.example'); $extensions = [System.Collections.Generic.HashSet[string]]::new([StringComparer]::OrdinalIgnoreCase); foreach ($e in $extList) { $extensions.Add($e) > $null }; $foldList = @('node_modules','.git','.svn','.hg','vendor','dist','build','coverage','.next','.nuxt','__pycache__','.venv','venv','bin','obj'); $ignoredFolders = [System.Collections.Generic.HashSet[string]]::new([StringComparer]::OrdinalIgnoreCase); foreach ($f in $foldList) { $ignoredFolders.Add($f) > $null }; $fileList = @('.env','.env.local','.env.production','.env.development','consolidado.txt','package-lock.json','yarn.lock','pnpm-lock.yaml'); $ignoredFiles = [System.Collections.Generic.HashSet[string]]::new([StringComparer]::OrdinalIgnoreCase); foreach ($fi in $fileList) { $ignoredFiles.Add($fi) > $null }; function Test-IgnoredPath($fileFullName) { $parts = $fileFullName.Split([System.IO.Path]::DirectorySeparatorChar, [System.IO.Path]::AltDirectorySeparatorChar); foreach ($part in $parts) { if ($ignoredFolders.Contains($part)) { return $true } } return $false }; function Test-IgnoredFile($file) { if ($ignoredFiles.Contains($file.Name)) { return $true }; if ($file.Name -like '.env.*' -and $file.Name -ne '.env.example') { return $true }; return $false }; $files = Get-ChildItem -LiteralPath $root -Recurse -File -ErrorAction SilentlyContinue | Where-Object { $_.FullName -ne $output -and -not (Test-IgnoredPath $_.FullName) -and -not (Test-IgnoredFile $_) -and $extensions.Contains($_.Extension) } | Sort-Object FullName; $total = $files.Count; $count = 0; Write-Host ('Arquivos encontrados: ' + $total); Write-Host ''; $utf8NoBom = New-Object System.Text.UTF8Encoding($false); $writer = New-Object System.IO.StreamWriter($output, $false, $utf8NoBom); try { foreach ($file in $files) { $count++; Write-Host ('[' + $count + '/' + $total + '] ' + $file.FullName); $writer.WriteLine(''); $writer.WriteLine('============================================================'); $writer.WriteLine('ARQUIVO: ' + $file.FullName); $writer.WriteLine('TAMANHO: ' + $file.Length + ' bytes'); $writer.WriteLine('============================================================'); $writer.WriteLine(''); try { $content = [System.IO.File]::ReadAllText($file.FullName); $writer.Write($content); if (-not $content.EndsWith([Environment]::NewLine)) { $writer.WriteLine(''); } } catch { $writer.WriteLine('[ERRO AO LER ARQUIVO]'); $writer.WriteLine($_.Exception.Message); $writer.WriteLine(''); } } } finally { $writer.Close(); $writer.Dispose(); }; Write-Host ''; Write-Host '============================================================'; Write-Host 'CONCLUIDO'; Write-Host '============================================================'; Write-Host ('Arquivos processados: ' + $count); Write-Host ('Arquivo gerado: ' + $output); if (Test-Path $output) { Write-Host ('Tamanho final: ' + ((Get-Item $output).Length) + ' bytes'); }"

echo.
echo ============================================================
echo Processo concluido com sucesso!
echo.
echo README.md e api-docs.json foram consolidados!
echo Arquivo final: %OUTPUT%
echo ============================================================
echo.

pause
endlocal