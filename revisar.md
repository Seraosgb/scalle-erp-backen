Visão macro de produto de um **ERP SaaS Escalável e Multissetorial**! 🔥

Abaixo está o **Raio-X de Revisão do Projeto**, refletindo os módulos ativos no código e no contrato da API (`api.json` / `document.json`), além do roadmap alinhado por versão e os modelos de custos/repasses.
🗺️ Mapa de Evolução Arquitetural do Scalle ERP
===============================================

                                ┌────────────────────────────────────────┐
                                │        CAMADA CORE (MULTITENANT)       │
                                │ Auth | Pessoas | Itens | DTOs | ACL DB │
                                └───────────────────┬────────────────────┘
                                                    │
                ┌───────────────────────────────────┼───────────────────────────────────┐
                ▼                                   ▼                                   ▼
         🛠️ PRESTAÇÃO DE SERVIÇOS           🏬 COMÉRCIO & VENDAS               🏭 INDÚSTRIA (PCP)
         - Módulo de OS / CMMS ✅           - Módulo de Vendas Diretas ✅      - Estrutura de Produtos (BOM) ✅
         - Ciclo de Vida da OS ✅           - PDV / Pedidos de Balcão ✅        - Ordens de Produção (OP) ✅
         - Baixa/Estorno Estoque ✅          - Orçamentos/Propostas ✅          - Apontamento de Perdas/Refugo ✅
         - Garantia & Laudos ✅             - Conversão em Venda/OS ✅         - Custo Industrial Apurado ✅
         - Impressão Layout/PDF ✅          - Impressão Layout/PDF ✅
                │                                   │                                   │
                └───────────────────────────────────┼───────────────────────────────────┘
                                                    ▼
                                ┌────────────────────────────────────────┐
                                │      SUPRIMENTOS, GESTÃO & WMS         │
                                │ - Compras & Entradas de Notas ✅       │
                                │ - Contas a Receber / Contas a Pagar ✅ │
                                │ - DRE Consolidado & Extrato ✅         │
                                │ - ACL / Perfis de Acesso (CheckRole) ✅│
                                │ - Categorias & Unidades (Auxiliares) ✅│
                                │ - Dashboard Executivo Real-time ✅     │
                                │ - Cobrança PIX Nativa (EMV/QR Code) ✅ │
                                │ - Engine Fiscal Desacoplada (NFe/NFSe)✅│
                                │ - Gestão Multi-Tenant & Switch Context✅│
                                │ - WMS / Multi-Depósitos & Transferência✅│
                                │ - Importador de XML de Compras 🔮      │
                                │ - Gestão de Usuários da Equipe 🔮      │
                                │ - Parâmetros Operacionais / Empresa 🔮 │
                                │ - Auditoria & Logs de Ações 🔮         │
                                │ - RH & DP Completo (7 Painéis) 🔮      │
                                │ - Gestão de Ativos & Patrimônio 🔮     │
                                │ - Gestão de Frotas & Transporte (CTe)🔮 │
                                │ - Gestão de Assinaturas & Billing SaaS🔮│
                                │ - Notificações (WhatsApp / E-mail) 🔮  │
                                │ - Backup & Exportação LGPD 🔮          │
                                │ - Padrões de Resiliência (Idempotência)🔮│
                                │ - Frontend PWA & PDV Offline First 🔮  │
                                │ - Tempo Real via SSE & Passkeys 🔮     │
                                │ - Busca Semântica / IA (pgvector) 🔮   │
                                └────────────────────────────────────────┘

🔍 Revisão Atual do Projeto (Status Real do Código)
===================================================

| **Módulo**              | **Status**   | **O que faz hoje?**                                                                                                                                                                                                                                        |
| ----------------------- | ------------ | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Auth & Tenant           | ✅ Concluído  | Multi-empresa isolado (`empresa_id`), token Sanctum, retorno do perfil (`role`), cadastro de empresas e troca dinâmica de contexto (`/trocar-contexto`).                                                                                                   |
| ACL & Segurança         | ✅ Concluído  | Middleware `CheckRole` bloqueando perfis não autorizados com `403 Forbidden` (`ADMIN`, `FINANCEIRO`, `TECNICO`, `ATENDENTE`).                                                                                                                              |
| Pessoas                 | ✅ Concluído  | Tabela unificada para Clientes e Fornecedores (`pes_pessoas`).                                                                                                                                                                                             |
| Produtos & Serviços     | ✅ Concluído  | Itens (`pro_itens`), Categorias (`pro_categorias`) e Unidades (`pro_unidades`). Parâmetros fiscais (NCM, CEST, CFOP, Origem).                                                                                                                              |
| Ordens de Serviço       | ✅ Concluído  | Numeração comercial (`OS-2026-000001`), baixa/estorno de estoque, Contas a Receber e Impressão/PDF.                                                                                                                                                        |
| Compras & Entradas      | ✅ Concluído  | Registro de notas, incremento atômico de estoque, atualização do preço de custo e lançamento no Contas a Pagar.                                                                                                                                            |
| Vendas Diretas          | ✅ Concluído  | Pedidos de balcão (`VEN-2026-000001`), baixa atômica de peças e geração de Contas a Receber.                                                                                                                                                               |
| Orçamentos              | ✅ Concluído  | Cotações (`ORC-2026-000001`), conversão em 1 clique para Venda ou OS e Impressão/PDF.                                                                                                                                                                      |
| Financeiro & DRE        | ✅ Concluído  | Contas a Receber/Pagar, liquidação/baixa, Plano de Contas, DRE e Extrato Financeiro Detalhado por período.                                                                                                                                                 |
| Dashboard & PIX         | ✅ Concluído  | Indicadores operacionais/comerciais/financeiros e gerador de PIX Copia e Cola (EMV) + QR Code.                                                                                                                                                             |
| Empresa Emitente        | ✅ Concluído  | Cadastro de Inscrição Estadual, CRT e Endereço Fiscal completo com Código IBGE (`sis_empresas`)[cite: 5, 6].                                                                                                                                               |
| Engine Fiscal           | ✅ Concluído  | Driver desacoplado (`FiscalDriverInterface`), emissão de NFe (Vendas) e NFS-e (OS) com chave de 44 dígitos, protocolo, XML e PDF[cite: 5, 6].                                                                                                              |
| Módulo Industrial (PCP) | ✅ Concluído  | Ficha Técnica/BOM (`pcp_fichas_tecnicas`), Ordens de Produção (`OP-2026-000001`), consumo de insumos, rateio de mão de obra/CIF, entrada atômica do acabado com recálculo de custo e apontamento de perdas/refugos (`pcp_apontamentos_perda`)[cite: 5, 6]. |
| Logística & WMS         | ✅ Concluído  | Gestão de Depósitos/Almoxarifados (`wms_depositos`), controle de estoque fracionado por local/viatura técnica (`wms_estoque_deposito`), e Transferências Internas (`TRF-2026-000001`) nos modos DIRETO e EM_TRANSITO (`wms_transferencias`)[cite: 5, 6].   |
| Governança & Equipe     | 🔮 Planejado | Gestão de Usuários da Equipe (`/empresa/usuarios`), Parâmetros por Empresa (`sis_empresa_parametros`), Importador de XML de NF-e (`/compras/importar-xml`) e Trilha de Auditoria (`sis_auditoria_logs`)[cite: 5, 6].                                       |
| RH & Gente Completo     | 🔮 Planejado | DP, Folha/Holerite interno, Ponto georreferenciado, Férias, Recrutamento & Seleção (Kanban), Avaliação de Desempenho (360°/OKRs), PDI/Treinamentos e Clima/eNPS.                                                                                           |
| Gestão de Ativos        | 🔮 Planejado | Tombamento de bens, número de série, depreciação, termos de cautela digital e QR Code para equipamentos/ferramentas.                                                                                                                                       |
| Frotas & Transporte     | 🔮 Planejado | Ciclo de vida dos veículos (KM, abastecimento, manutenções via CMMS/OS), controle de condutores e emissão de CTe/MDF-e.                                                                                                                                    |
| Billing & SaaS Core     | 🔮 Planejado | Planos (`sis_planos`), Bloqueio automático de inadimplentes, Webhook de Gateway (Asaas/Stripe), Notificações transacionais e Backup/Exportação LGPD.                                                                                                       |
| Inovação & Arquitetura  | 🔮 Planejado | Idempotency Keys (`Idempotency-Key`), Notificações em Tempo Real (SSE), Frontend PWA/PDV Offline First, Autenticação por Passkeys (WebAuthn) e Busca Semântica (`pgvector`).                                                                               |

📌 Planejamento de Releases (Semantic Versioning)
=================================================

### 🟢 Versão 1.x.x — Core Comercial, Operacional, Financeiro & Fiscal (Concluídas v1.0 a v1.5)

* **v1.0.0 (Concluída):** Multi-tenant, Pessoas, Produtos/Serviços, OS/CMMS, Compras e Financeiro/DRE.

* **v1.1.0 (Concluída):** Módulo de Vendas Diretas + ACL / Perfis de Acesso (`CheckRole`).

* **v1.2.0 (Concluída):** Gestão de Orçamentos com conversão + Cadastros Auxiliares (Categorias e Unidades de Medida).

* **v1.3.0 (Concluída):** Engine de Impressão de Orçamento/OS, Dashboard de Indicadores e Cobrança PIX Nativa (Payload EMV + QR Code).

* **v1.4.0 (Concluída):** Estrutura Fiscal nos Produtos (NCM/CFOP), Parâmetros do Emitente (IBGE/CRT) e Extrato Financeiro por Período.

* **v1.5.0 (Concluída):** Engine de Transmissão Fiscal Desacoplada (`FiscalDriverInterface`) com suporte a NFe e NFS-e + Gestão e Troca Dinâmica de Múltiplos Tenants[cite: 5, 6].

### 🏭 Versão 2.0.0 — Módulo Industrial (PCP & Custo Industrial - Concluída)

* **v2.0.0 (Concluída):**
  
  * **Ficha Técnica / Árvore do Produto (BOM):** Composição de insumos físicos e custos indiretos/mão de obra por produto acabado[cite: 5, 6].
  
  * **Ordens de Produção (OP):** Controle de ciclo de vida (`PLANEJADA`, `EM_PRODUCAO`, `CONCLUIDA`, `CANCELADA`), baixa atômica de insumos, entrada do produto acabado no estoque e recálculo automático do preço de custo unitário final[cite: 5, 6].
  
  * **Apontamento de Perdas & Motivos de Refugo:** Cadastro de motivos e registro analítico de perdas no chão de fábrica[cite: 5, 6].

### 🚚 Versão 3.0.0 — Logística, WMS, Ativos & Frotas

* **v3.0.0 (Concluída):**
  
  * **Multi-Depósitos / Almoxarifados:** Estoque fracionado por galpão, loja física e viaturas técnicas volantes (`wms_depositos` e `wms_estoque_deposito`)[cite: 5, 6].
  
  * **Transferência Interna de Mercadorias:** Movimentação entre locais de estoque em modo instantâneo (`DIRETO`) ou com conferência de recebimento (`EM_TRANSITO`)[cite: 5, 6].

* **v3.1.0 (Gestão de Frotas & Transporte):**
  
  * **Gestão de Frotas:** Manutenções preventivas e controle de viaturas vinculado ao módulo de OS/CMMS.
  
  * **Emissão de CTe e MDF-e:** Documentos fiscais eletrônicos de transporte de cargas.

* **v3.2.0 (Gestão de Ativos & Patrimônio):**
  
  * **Tombamento e Inventário Patrimonial:** Rastreabilidade de máquinas, ferramentas e equipamentos em comodato.
  
  * **Termo de Cautela & QR Code:** Assinatura digital de custódia e histórico de manutenção por escaneamento de etiqueta.

### 🛡️ Versão 4.0.0 — Governança SaaS, Equipe & Automações de Entrada (Próxima Release)

* **Gestão de Usuários da Equipe:** CRUD completo para o `ADMIN` convidar e gerenciar operadores (`TECNICO`, `FINANCEIRO`, `ATENDENTE`) vinculados à sua empresa[cite: 5, 6].

* **Importador de XML de Compra:** Leitura automática de NF-e (.xml), autocadastro de fornecedor, reconciliação de produtos e lançamento financeiro.

* **Parâmetros Operacionais por Tenant:** Chave PIX personalizada por empresa, dias de vencimento padrão e termos de garantia customizáveis.

* **Auditoria & Logs de Atividade (Activity Log):** Trilha de auditoria para identificar quem criou, alterou ou excluiu registros críticos no sistema.

* **Idempotência de Requisições:** Middleware de `Idempotency-Key` para blindar pagamentos, liquidações e encerramentos de OS contra cliques duplos.

### 👥 Versão 5.0.0 — RH Estratégico & Departamento Pessoal Completo (7 Painéis)

* **DP & Folha Interna:** Ficha do colaborador, histórico funcional, jornada/escala, ponto georreferenciado, férias e espelho de holerite com proventos/descontos.

* **Gente & Talentos:** Recrutamento & Seleção (Kanban de vagas), Avaliação de Desempenho 360°, OKRs/Metas, PDI/Treinamentos com matriz de certificações e Pesquisa de Clima/eNPS.

### 💳 Versão 6.0.0 — Monetização SaaS, Mensageria & Segurança de Dados

* **Motor de Assinatura & Planos (Billing):** Definição de planos (`sis_planos`), controle de limites por tenant, webhooks de gateway (Asaas/Stripe) e bloqueio automático de inadimplentes.

* **Mensageria Transacional:** Disparo automático de links de OS, vendas e chave PIX via WhatsApp (Evolution API / Z-API) e E-mail transacional (SMTP/Resend).

* **Backups & Exportação LGPD:** Rotina agendada de dump do banco e exportação de dados em ZIP/Excel por tenant para conformidade jurídica.

### ⚡ Versão 7.0.0 — Frontend PWA, Tempo Real & Inovação Tecnológica

* **Frontend SPA / PWA (PDV Offline First):** Interface responsiva completa com persistência local em IndexedDB e sincronização em lote para vendas de balcão[cite: 5].

* **Comunicação Reativa em Tempo Real (SSE):** Atualização dinâmica de dashboards, status de OPs e notificações push internas via Server-Sent Events sem polling[cite: 5].

* **Autenticação Biométrica / Passkeys:** Suporte a WebAuthn/FIDO2 para login rápido e seguro por biometria/dispositivo[cite: 5].

* **Busca Semântica & IA:** Vetorização de itens, histórico de defeitos em OS e catálogos via `pgvector` para busca por intenção[cite: 5].

💵 Matriz de Custos, Integrações e Repasse aos Tenants
======================================================

| **Módulo / Recurso**                          | **Tipo de Custo**                                    | **Quem Paga / Como Repassar**                                                                                           |
| --------------------------------------------- | ---------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------- |
| **PIX Nativo, WMS, PCP e DRE**[cite: 5, 6]    | R$ 0,00 (Código interno)[cite: 5, 6]                 | **Custo Zero.** Absorvido pela aplicação nativa[cite: 5, 6].                                                            |
| **Importador XML, Logs e RH/DP**[cite: 5]     | R$ 0,00 (Código interno)[cite: 5]                    | **Custo Zero.** Processamento interno em banco de dados[cite: 5].                                                       |
| **Certificado Digital (e-CNPJ A1)**           | Direto do Tenant                                     | **Tenant paga por fora** na autoridade certificadora de sua escolha.                                                    |
| **Emissão Fiscal (NFe/NFSe/CTe)**[cite: 5, 6] | Variável por consumo (se via gateway parceiro)       | **Embutido no plano** com franquia de notas (excedente faturado à parte)[cite: 5].                                      |
| **WhatsApp Transacional**[cite: 5]            | Mensalidade por instância conectada[cite: 5]         | **Add-on opcional** cobrado na mensalidade do cliente[cite: 5].                                                         |
| **Billing & Taxa Gateway**[cite: 5]           | Taxa por cobrança liquidada[cite: 5]                 | **Custo da Plataforma SaaS** absorvido na margem de assinatura[cite: 5].                                                |
| **Transmissão eSocial**                       | R$ 0,00 (Web Service direto) ou Taxa por colaborador | **Gratuito no Core** ou repasse por funcionário ativo caso use gateway fiscal terceirizado. |
