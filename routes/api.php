<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImpressaoController;
use App\Http\Controllers\EmpresaConfigController;
use App\Modules\Fiscal\Controllers\FiscalController;
use App\Modules\PCP\Controllers\PcpController;
use App\Modules\WMS\Controllers\WmsController;
use App\Modules\Empresa\Controllers\UsuarioEquipeController;
use App\Modules\Empresa\Controllers\ParametroEmpresaController;
use App\Modules\Compras\Controllers\CompraController;
use App\Modules\Frotas\Controllers\FrotaController;
use App\Modules\Ativos\Controllers\AtivoController;
use App\Modules\RH\Controllers\RHController;
use App\Modules\RH\Controllers\RHEstrategicoController;

// 🔓 Rotas PÚBLICAS de Autenticação
Route::prefix('v1/auth')->group(base_path('app/Modules/Auth/Routes/api.php'));

// --------------------------------------------------------------------------
// 🔒 ROTAS PROTEGIDAS (Sanctum + ACL Role + Feature Flags por Plano)
// --------------------------------------------------------------------------

// 👥 Pessoas: Todos os perfis (Plano MEI, PRO, ENTERPRISE)
Route::middleware(['auth:sanctum', 'role:ADMIN,FINANCEIRO,TECNICO,ATENDENTE'])
    ->prefix('v1/pessoas')
    ->group(base_path('app/Modules/Pessoas/Routes/api.php'));

// 📦 Produtos e Serviços: Todos os perfis (Plano MEI, PRO, ENTERPRISE)
Route::middleware(['auth:sanctum', 'role:ADMIN,FINANCEIRO,TECNICO,ATENDENTE'])
    ->prefix('v1/produtos')
    ->group(base_path('app/Modules/Produtos/Routes/api.php'));

// 🛠️ Ordens de Serviço & CMMS: (Plano MEI, PRO, ENTERPRISE)
Route::middleware(['auth:sanctum', 'role:ADMIN,TECNICO,ATENDENTE', 'feature:os'])
    ->prefix('v1/ordens-servico')
    ->group(base_path('app/Modules/OrdensServico/Routes/api.php'));

// 🛒 Compras & Importador XML: (Plano MEI, PRO, ENTERPRISE)
Route::middleware(['auth:sanctum', 'role:ADMIN,FINANCEIRO'])
    ->prefix('v1/compras')
    ->group(function () {
        Route::post('/importar-xml', [CompraController::class, 'importarXml']);
        Route::prefix('/')->group(base_path('app/Modules/Compras/Routes/api.php'));
    });

// 🏬 Vendas Diretas: (Plano MEI, PRO, ENTERPRISE)
Route::middleware(['auth:sanctum', 'role:ADMIN,FINANCEIRO,ATENDENTE', 'feature:vendas'])
    ->prefix('v1/vendas')
    ->group(base_path('app/Modules/Vendas/Routes/api.php'));

// 📋 Orçamentos: (Plano MEI, PRO, ENTERPRISE)
Route::middleware(['auth:sanctum', 'role:ADMIN,FINANCEIRO,TECNICO,ATENDENTE'])
    ->prefix('v1/orcamentos')
    ->group(base_path('app/Modules/Orcamentos/Routes/api.php'));

// 💰 Financeiro, DRE & Dashboard: (Plano MEI, PRO, ENTERPRISE)
Route::middleware(['auth:sanctum', 'role:ADMIN,FINANCEIRO', 'feature:financeiro'])
    ->prefix('v1/financeiro')
    ->group(function () {
        Route::get('/dashboard/resumo', [\App\Modules\Financeiro\Controllers\DashboardController::class, 'resumo']);
        Route::prefix('lancamentos')->group(base_path('app/Modules/Financeiro/Routes/api.php'));
    });

// 🖨️ Impressão de Orçamento e OS: (Autenticado)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('v1/orcamentos/{id}/imprimir', [ImpressaoController::class, 'orcamento']);
    Route::get('v1/ordens-servico/{id}/imprimir', [ImpressaoController::class, 'ordemServico']);
});

// 🏢 Governança, Empresas & Contexto Multi-Tenant (Somente ADMIN)
Route::middleware(['auth:sanctum', 'role:ADMIN'])->prefix('v1/empresa')->group(function () {
    Route::get('/', [EmpresaConfigController::class, 'index']);
    Route::post('/', [EmpresaConfigController::class, 'store']);
    Route::get('/configuracao-fiscal', [EmpresaConfigController::class, 'show']);
    Route::put('/configuracao-fiscal', [EmpresaConfigController::class, 'update']);
    Route::post('/trocar-contexto/{empresaId}', [EmpresaConfigController::class, 'trocarContexto']);

    // Gestão de Usuários da Equipe
    Route::get('/usuarios', [UsuarioEquipeController::class, 'index']);
    Route::post('/usuarios', [UsuarioEquipeController::class, 'store']);
    Route::put('/usuarios/{id}', [UsuarioEquipeController::class, 'update']);
    Route::delete('/usuarios/{id}', [UsuarioEquipeController::class, 'destroy']);

    // Parâmetros Operacionais
    Route::get('/parametros', [ParametroEmpresaController::class, 'show']);
    Route::put('/parametros', [ParametroEmpresaController::class, 'update']);
});

// 📄 Engine Fiscal: (A partir do Plano PRO)
Route::middleware(['auth:sanctum', 'role:ADMIN,FINANCEIRO', 'feature:fiscal'])->prefix('v1/fiscal')->group(function () {
    Route::post('/vendas/{vendaId}/emitir-nfe', [FiscalController::class, 'emitirNFe']);
    Route::post('/ordens-servico/{osId}/emitir-nfse', [FiscalController::class, 'emitirNFSe']);
});

// 🚚 Módulo de Frotas & Transporte: (A partir do Plano PRO)
Route::middleware(['auth:sanctum', 'role:ADMIN,TECNICO,FINANCEIRO', 'feature:frotas'])->prefix('v1/frotas')->group(function () {
    Route::get('/veiculos', [FrotaController::class, 'listarVeiculos']);
    Route::post('/veiculos', [FrotaController::class, 'criarVeiculo']);

    Route::get('/abastecimentos', [FrotaController::class, 'listarAbastecimentos']);
    Route::post('/abastecimentos', [FrotaController::class, 'registrarAbastecimento']);

    Route::post('/emitir-cte', [FrotaController::class, 'emitirCTe']);
});

// 🏷️ Módulo de Gestão de Ativos & Patrimônio: (A partir do Plano PRO)
Route::middleware(['auth:sanctum', 'role:ADMIN,TECNICO,FINANCEIRO', 'feature:ativos'])->prefix('v1/ativos')->group(function () {
    Route::get('/', [AtivoController::class, 'index']);
    Route::post('/', [AtivoController::class, 'store']);
    Route::get('/{id}/qrcode', [AtivoController::class, 'gerarQrCode']);

    // Termo de Cautela & Devolução
    Route::post('/cautelas', [AtivoController::class, 'emitirCautela']);
    Route::patch('/cautelas/{id}/devolver', [AtivoController::class, 'devolverCautela']);
});

// 👥 Módulo de DP, Ponto & Folha: (A partir do Plano PRO)
Route::middleware(['auth:sanctum', 'role:ADMIN,FINANCEIRO,TECNICO', 'feature:dp'])->prefix('v1/rh')->group(function () {
    // Escalas
    Route::get('/escalas', [RHController::class, 'listarEscalas']);
    Route::post('/escalas', [RHController::class, 'criarEscala']);

    // Colaboradores & Certificações
    Route::get('/colaboradores', [RHController::class, 'listarColaboradores']);
    Route::post('/colaboradores', [RHController::class, 'criarColaborador']);
    Route::post('/certificacoes', [RHController::class, 'registrarCertificacao']);

    // Ponto Eletrônico
    Route::post('/ponto/bater', [RHController::class, 'baterPonto']);

    // Folha de Pagamento & Holerite
    Route::post('/folha/gerar-holerite', [RHController::class, 'gerarHolerite']);
});

// 🎯 Módulo de RH Estratégico, R&S & Clima: (A partir do Plano PRO)
Route::middleware(['auth:sanctum', 'role:ADMIN,FINANCEIRO,TECNICO', 'feature:rh_estrategico'])->prefix('v1/rh-estrategico')->group(function () {
    // Recrutamento & Seleção (Kanban)
    Route::get('/vagas', [RHEstrategicoController::class, 'listarVagas']);
    Route::post('/vagas', [RHEstrategicoController::class, 'criarVaga']);
    Route::post('/candidatos', [RHEstrategicoController::class, 'cadastrarCandidato']);
    Route::patch('/candidatos/{id}/kanban', [RHEstrategicoController::class, 'moverEtapaKanban']);

    // Avaliação de Desempenho
    Route::post('/avaliacoes/ciclos', [RHEstrategicoController::class, 'criarCicloAvaliacao']);
    Route::post('/avaliacoes/responder', [RHEstrategicoController::class, 'responderAvaliacao']);

    // PDI & Treinamentos
    Route::post('/treinamentos', [RHEstrategicoController::class, 'registrarTreinamento']);

    // Pesquisa de Clima & eNPS Anônimo
    Route::post('/clima/responder', [RHEstrategicoController::class, 'responderClima']);
    Route::get('/clima/pesquisas/{id}/enps', [RHEstrategicoController::class, 'consultarEnps']);

    // Gestão de Pesquisas de Clima
    Route::get('/clima/pesquisas', [RHEstrategicoController::class, 'listarPesquisasClima']);
    Route::post('/clima/pesquisas', [RHEstrategicoController::class, 'criarPesquisaClima']);
});

// 🏭 Módulo Industrial (PCP): (Exclusivo Plano ENTERPRISE)
Route::middleware(['auth:sanctum', 'role:ADMIN,TECNICO', 'feature:pcp'])->prefix('v1/pcp')->group(function () {
    // Ficha Técnica (BOM)
    Route::get('/produtos/{produtoId}/ficha-tecnica', [PcpController::class, 'obterFichaTecnica']);
    Route::post('/produtos/{produtoId}/ficha-tecnica', [PcpController::class, 'salvarFichaTecnica']);

    // Ordens de Produção
    Route::get('/ordens-producao', [PcpController::class, 'listarOPs']);
    Route::post('/ordens-producao', [PcpController::class, 'criarOP']);
    Route::patch('/ordens-producao/{id}/status', [PcpController::class, 'atualizarStatus']);
    Route::post('/ordens-producao/{id}/perdas', [PcpController::class, 'registrarPerda']);

    // Motivos de Perda/Refugo
    Route::get('/motivos-perda', [PcpController::class, 'listarMotivosPerda']);
    Route::post('/motivos-perda', [PcpController::class, 'criarMotivoPerda']);
});

// 📦 Módulo Logística, WMS & Multi-Depósitos: (Exclusivo Plano ENTERPRISE)
Route::middleware(['auth:sanctum', 'role:ADMIN,TECNICO', 'feature:wms'])->prefix('v1/wms')->group(function () {
    // Gestão de Depósitos & Saldo Local
    Route::get('/depositos', [WmsController::class, 'listarDepositos']);
    Route::post('/depositos', [WmsController::class, 'criarDeposito']);
    Route::get('/depositos/{depositoId}/estoque', [WmsController::class, 'consultarEstoquePorDeposito']);

    // Transferências Internas
    Route::get('/transferencias', [WmsController::class, 'listarTransferencias']);
    Route::post('/transferencias', [WmsController::class, 'criarTransferencia']);
    Route::patch('/transferencias/{id}/concluir', [WmsController::class, 'concluirTransferencia']);
});