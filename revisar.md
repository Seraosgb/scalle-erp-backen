Visão macro de produto de um **ERP SaaS Escalável e Multissetorial**! 🔥

Abaixo está o **Raio-X de Revisão do Projeto** atualizado com as entregas da **Release v4.0.0 (Governança & Equipe)** e da **Release v4.1.0 (Gestão de Frotas & Transporte)**:
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
                                │ - Gestão de Usuários da Equipe ✅      │
                                │ - Parâmetros Operacionais / Empresa ✅ │
                                │ - Importador de XML de Compras ✅      │
                                │ - Auditoria & Logs de Ações ✅         │
                                │ - Padrões de Resiliência (Idempotência)✅│
                                │ - Gestão de Frotas & Abastecimento ✅  │
                                │ - Emissão CTe / MDF-e Desacoplada ✅   │
                                │ - Gestão de Ativos & Patrimônio 🔮     │
                                │ - RH & DP Completo (7 Painéis) 🔮      │
                                │ - Gestão de Assinaturas & Billing SaaS🔮│
                                │ - Notificações (WhatsApp / E-mail) 🔮  │
                                │ - Backup & Exportação LGPD 🔮          │
                                │ - Frontend PWA & PDV Offline First 🔮  │
                                │ - Tempo Real via SSE & Passkeys 🔮     │
                                │ - Busca Semântica / IA (pgvector) 🔮   │
                                └────────────────────────────────────────┘

🔍 Revisão Atual do Projeto (Status Real do Código)
===================================================

| **Módulo**              | **Status**            | **O que faz hoje?**                                                                                                                                                                                                                                      |
| ----------------------- | --------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Auth & Tenant           | ✅ Concluído           | Multi-empresa isolado (`empresa_id`), token Sanctum, retorno do perfil (`role`), cadastro de empresas e troca dinâmica de contexto (`/trocar-contexto`).                                                                                                 |
| ACL & Segurança         | ✅ Concluído           | Middleware `CheckRole` bloqueando perfis não autorizados com `403 Forbidden` (`ADMIN`, `FINANCEIRO`, `TECNICO`, `ATENDENTE`).                                                                                                                            |
| Pessoas                 | ✅ Concluído           | Tabela unificada para Clientes e Fornecedores (`pes_pessoas`).                                                                                                                                                                                           |
| Produtos & Serviços     | ✅ Concluído           | Itens (`pro_itens`), Categorias (`pro_categorias`) e Unidades (`pro_unidades`). Parâmetros fiscais (NCM, CEST, CFOP, Origem).                                                                                                                            |
| Ordens de Serviço       | ✅ Concluído           | Numeração comercial (`OS-2026-000001`), baixa/estorno de estoque, Contas a Receber, vínculo opcional com veículos da frota (`veiculo_id`) e Impressão/PDF.                                                                                               |
| Compras & Entradas      | ✅ Concluído           | Registro de notas, incremento atômico de estoque, atualização do preço de custo e lançamento no Contas a Pagar.                                                                                                                                          |
| Vendas Diretas          | ✅ Concluído           | Pedidos de balcão (`VEN-2026-000001`), baixa atômica de peças e geração de Contas a Receber.                                                                                                                                                             |
| Orçamentos              | ✅ Concluído           | Cotações (`ORC-2026-000001`), conversão em 1 clique para Venda ou OS e Impressão/PDF.                                                                                                                                                                    |
| Financeiro & DRE        | ✅ Concluído           | Contas a Receber/Pagar, liquidação/baixa, Plano de Contas, DRE e Extrato Financeiro Detalhado por período.                                                                                                                                               |
| Dashboard & PIX         | ✅ Concluído           | Indicadores operacionais/comerciais/financeiros e gerador de PIX Copia e Cola (EMV) + QR Code com busca dinâmica de chave cadastrada por empresa.                                                                                                        |
| Empresa Emitente        | ✅ Concluído           | Cadastro de Inscrição Estadual, CRT e Endereço Fiscal completo com Código IBGE (`sis_empresas`).                                                                                                                                                         |
| Engine Fiscal           | ✅ Concluído           | Driver desacoplado (`FiscalDriverInterface`), emissão de NFe (Vendas), NFS-e (OS), CTe e MDF-e de transporte.                                                                                                                                            |
| Módulo Industrial (PCP) | ✅ Concluído           | Ficha Técnica/BOM (`pcp_fichas_tecnicas`), Ordens de Produção (`OP-2026-000001`), consumo de insumos, rateio de mão de obra/CIF, entrada atômica do acabado com recálculo de custo e apontamento de perdas/refugos (`pcp_apontamentos_perda`).           |
| Logística & WMS         | ✅ Concluído           | Gestão de Depósitos/Almoxarifados (`wms_depositos`), controle de estoque fracionado por local/viatura técnica (`wms_estoque_deposito`), e Transferências Internas (`TRF-2026-000001`) nos modos DIRETO e EM_TRANSITO (`wms_transferencias`).             |
| Governança & Equipe     | ✅ Concluído           | Gestão de Usuários da Equipe (`/empresa/usuarios`), Parâmetros por Empresa (`sis_empresa_parametros`), Importador de XML de NF-e (`/compras/importar-xml`), Trilha de Auditoria (`sis_auditoria_logs`) e Middleware de Idempotência (`Idempotency-Key`). |
| Gestão de Frotas        | ✅ Concluído           | Cadastro de veículos (`fro_veiculos`), odômetro/KM atual, controle analítico de abastecimentos (`fro_abastecimentos`) com cálculo de consumo e geração automática de despesa no Contas a Pagar.                                                          |
| Gestão de Ativos        | 🔮 Planejado          | Tombamento de bens, número de série, depreciação, termos de cautela digital e QR Code para equipamentos/ferramentas.                                                                                                                                     |
| RH & Gente Completo     | 🔮 Planejado          | DP, Folha/Holerite interno, Ponto georreferenciado, Férias, Recrutamento & Seleção (Kanban), Avaliação de Desempenho (360°/OKRs), PDI/Treinamentos e Clima/eNPS.                                                                                         |
| Billing & SaaS Core     | 🔮 Planejado          | Planos (`sis_planos`), Bloqueio automático de inadimplentes, Webhook de Gateway (Asaas/Stripe), Notificações transacionais e Backup/Exportação LGPD.                                                                                                     |
| Inovação & Arquitetura  | 🔮 Planejado[cite: 1] | Frontend PWA & PDV Offline First, Autenticação por Passkeys (WebAuthn), Tempo Real via SSE e Busca Semântica (`pgvector`)[cite: 1].                                                                                                                      |

📌 Planejamento de Releases (Semantic Versioning)
=================================================

### 🟢 Versão 1.x.x — Core Comercial, Operacional, Financeiro & Fiscal (Concluídas v1.0 a v1.5)

[cite: 1]

* **v1.0.0 (Concluída):** Multi-tenant, Pessoas, Produtos/Serviços, OS/CMMS, Compras e Financeiro/DRE[cite: 1].

* **v1.1.0 (Concluída):** Módulo de Vendas Diretas + ACL / Perfis de Acesso (`CheckRole`)[cite: 1].

* **v1.2.0 (Concluída):** Gestão de Orçamentos com conversão + Cadastros Auxiliares (Categorias e Unidades de Medida)[cite: 1].

* **v1.3.0 (Concluída):** Engine de Impressão de Orçamento/OS, Dashboard de Indicadores e Cobrança PIX Nativa (Payload EMV + QR Code)[cite: 1].

* **v1.4.0 (Concluída):** Estrutura Fiscal nos Produtos (NCM/CFOP), Parâmetros do Emitente (IBGE/CRT) e Extrato Financeiro por Período[cite: 1].

* **v1.5.0 (Concluída):** Engine de Transmissão Fiscal Desacoplada (`FiscalDriverInterface`) com suporte a NFe e NFS-e + Gestão e Troca Dinâmica de Múltiplos Tenants[cite: 1].

### 🏭 Versão 2.0.0 — Módulo Industrial (PCP & Custo Industrial - Concluída)

[cite: 1]

* **v2.0.0 (Concluída):**
  
  * **Ficha Técnica / Árvore do Produto (BOM):** Composição de insumos físicos e custos indiretos/mão de obra por produto acabado[cite: 1].
  
  * **Ordens de Produção (OP):** Controle de ciclo de vida (`PLANEJADA`, `EM_PRODUCAO`, `CONCLUIDA`, `CANCELADA`), baixa atômica de insumos, entrada do produto acabado no estoque e recálculo automático do preço de custo unitário final[cite: 1].
  
  * **Apontamento de Perdas & Motivos de Refugo:** Cadastro de motivos e registro analítico de perdas no chão de fábrica[cite: 1].

### 🚚 Versão 3.0.0 — Logística & WMS (Concluída)

[cite: 1]

* **v3.0.0 (Concluída):**
  
  * **Multi-Depósitos / Almoxarifados:** Estoque fracionado por galpão, loja física e viaturas técnicas volantes (`wms_depositos` e `wms_estoque_deposito`)[cite: 1].
  
  * **Transferência Interna de Mercadorias:** Movimentação entre locais de estoque em modo instantâneo (`DIRETO`) ou com conferência de recebimento (`EM_TRANSITO`)[cite: 1].

### 🛡️ Versão 4.x.x — Governança SaaS, Automações & Frotas (Concluídas v4.0 e v4.1)

[cite: 1]

* **v4.0.0 (Concluída):**
  
  * **Gestão de Usuários da Equipe:** CRUD completo para o `ADMIN` convidar e gerenciar operadores (`TECNICO`, `FINANCEIRO`, `ATENDENTE`) vinculados à sua empresa[cite: 1].
  
  * **Importador de XML de Compra:** Leitura automática de NF-e (.xml), autocadastro de fornecedor, reconciliação de produtos e lançamento financeiro[cite: 1].
  
  * **Parâmetros Operacionais por Tenant:** Chave PIX personalizada por empresa (`sis_empresa_parametros`), dias de vencimento padrão e termos de garantia[cite: 1].
  
  * **Auditoria & Logs de Atividade (Activity Log):** Trilha de auditoria forense para identificar quem criou, alterou ou excluiu registros críticos no sistema (`sis_auditoria_logs`)[cite: 1].
  
  * **Idempotência de Requisições:** Middleware de `Idempotency-Key` para blindar pagamentos, liquidações e encerramentos de OS contra cliques duplos[cite: 1].

* **v4.1.0 (Concluída - Gestão de Frotas & Transporte):**
  
  * **Gestão de Veículos:** Cadastro de veículos da frota (`fro_veiculos`), odômetro/KM atual, motorista responsável e status[cite: 1].
  
  * **Controle de Abastecimentos:** Registro analítico (`fro_abastecimentos`), consumo médio e geração automática de despesa no Contas a Pagar[cite: 1].
  
  * **Emissão CTe e MDF-e:** Métodos mockados desacoplados no `FiscalDriverInterface`[cite: 1].

* **v4.2.0 (Gestão de Ativos & Patrimônio - Próxima Release):**
  
  * **Tombamento e Inventário Patrimonial:** Rastreabilidade de máquinas, ferramentas e equipamentos em comodato[cite: 1].
  
  * **Termo de Cautela & QR Code:** Assinatura digital de custódia e histórico de manutenção por escaneamento de etiqueta[cite: 1].

### 👥 Versão 5.0.0 — RH Estratégico & Departamento Pessoal Completo (7 Painéis)

[cite: 1]

* **DP & Folha Interna:** Ficha do colaborador, histórico funcional, jornada/escala, ponto georreferenciado, férias e espelho de holerite com proventos/descontos[cite: 1].

* **Gente & Talentos:** Recrutamento & Seleção (Kanban de vagas), Avaliação de Desempenho 360°, OKRs/Metas, PDI/Treinamentos com matriz de certificações e Pesquisa de Clima/eNPS[cite: 1].

### 💳 Versão 6.0.0 — Monetização SaaS, Mensageria & Segurança de Dados

[cite: 1]

* **Motor de Assinatura & Planos (Billing):** Definição de planos (`sis_planos`), controle de limites por tenant, webhooks de gateway (Asaas/Stripe) e bloqueio automático de inadimplentes[cite: 1].

* **Mensageria Transacional:** Disparo automático de links de OS, vendas e chave PIX via WhatsApp (Evolution API / Z-API) e E-mail transacional (SMTP/Resend)[cite: 1].

* **Backups & Exportação LGPD:** Rotina agendada de dump do banco e exportação de dados em ZIP/Excel por tenant para conformidade jurídica[cite: 1].

### ⚡ Versão 7.0.0 — Frontend PWA, Tempo Real & Inovação Tecnológica

[cite: 1]

* **Frontend SPA / PWA (PDV Offline First):** Interface responsiva completa com persistência local em IndexedDB e sincronização em lote para vendas de balcão[cite: 1].

* **Comunicação Reativa em Tempo Real (SSE):** Atualização dinâmica de dashboards, status de OPs e notificações push internas via Server-Sent Events sem polling[cite: 1].

* **Autenticação Biométrica / Passkeys:** Suporte a WebAuthn/FIDO2 para login rápido e seguro por biometria/dispositivo[cite: 1].

* **Busca Semântica & IA:** Vetorização de itens, histórico de defeitos em OS e catálogos via `pgvector` para busca por intenção[cite: 1].

### 🚀 Próximo Passo na Fila de Execução:

Com as versões **v4.0.0** e **v4.1.0** devidamente homologadas e no ar, o próximo alvo do roadmap é a **v4.2.0 (Gestão de Ativos & Patrimônio — Tombamento de Ferramentas/Máquinas, QR Code e Termo de Cautela)**[cite: 1]!
