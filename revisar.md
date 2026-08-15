Visão macro de produto de um **ERP SaaS Escalável e Multissetorial**! 🔥



Abaixo está o **Raio-X de Revisão do Projeto**, refletindo os módulos ativos no código e no contrato da API (`document.json`), além do roadmap alinhado por versão.

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
                                │      SUPRIMENTOS, GESTÃO & FINANCEIRO  │
                                │ - Compras & Entradas de Notas ✅       │
                                │ - Contas a Receber / Contas a Pagar ✅ │
                                │ - DRE Consolidado & Extrato ✅         │
                                │ - ACL / Perfis de Acesso (CheckRole) ✅│
                                │ - Categorias & Unidades (Auxiliares) ✅│
                                │ - Dashboard Executivo Real-time ✅     │
                                │ - Cobrança PIX Nativa (EMV/QR Code) ✅ │
                                │ - Engine Fiscal Desacoplada (NFe/NFSe)✅│
                                │ - Gestão Multi-Tenant & Switch Context✅│
                                │ - WMS / Logística / Frotas 🔮           │
                                └────────────────────────────────────────┘

🔍 Revisão Atual do Projeto (Status Real do Código)
===================================================

| **Módulo**              | **Status**  | **O que faz hoje?**                                                                                                                                                                                                                                        |
| ----------------------- | ----------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Auth & Tenant           | ✅ Concluído | Multi-empresa isolado (`empresa_id`), token Sanctum, retorno do perfil (`role`), cadastro de empresas e troca dinâmica de contexto (`/trocar-contexto`).<br><br><br>                                                                                       |
| ACL & Segurança         | ✅ Concluído | Middleware `CheckRole` bloqueando perfis não autorizados com `403 Forbidden` (`ADMIN`, `FINANCEIRO`, `TECNICO`, `ATENDENTE`).<br><br><br>                                                                                                                  |
| Pessoas                 | ✅ Concluído | Tabela unificada para Clientes e Fornecedores (`pes_pessoas`).<br><br><br>                                                                                                                                                                                 |
| Produtos & Serviços     | ✅ Concluído | Itens (`pro_itens`), Categorias (`pro_categorias`) e Unidades (`pro_unidades`). Parâmetros fiscais (NCM, CEST, CFOP, Origem).<br><br><br>                                                                                                                  |
| Ordens de Serviço       | ✅ Concluído | Numeração comercial (`OS-2026-000001`), baixa/estorno de estoque, Contas a Receber e Impressão/PDF.<br><br><br>                                                                                                                                            |
| Compras & Entradas      | ✅ Concluído | Registro de notas, incremento atômico de estoque, atualização do preço de custo e lançamento no Contas a Pagar.<br><br><br>                                                                                                                                |
| Vendas Diretas          | ✅ Concluído | Pedidos de balcão (`VEN-2026-000001`), baixa atômica de peças e geração de Contas a Receber.<br><br><br>                                                                                                                                                   |
| Orçamentos              | ✅ Concluído | Cotações (`ORC-2026-000001`), conversão em 1 clique para Venda ou OS e Impressão/PDF.<br><br><br>                                                                                                                                                          |
| Financeiro & DRE        | ✅ Concluído | Contas a Receber/Pagar, liquidação/baixa, Plano de Contas, DRE e Extrato Financeiro Detalhado por período.<br><br><br>                                                                                                                                     |
| Dashboard & PIX         | ✅ Concluído | Indicadores operacionais/comerciais/financeiros e gerador de PIX Copia e Cola (EMV) + QR Code.<br><br><br>                                                                                                                                                 |
| Empresa Emitente        | ✅ Concluído | Cadastro de Inscrição Estadual, CRT e Endereço Fiscal completo com Código IBGE (`sis_empresas`).<br><br><br>                                                                                                                                               |
| Engine Fiscal           | ✅ Concluído | Driver desacoplado (`FiscalDriverInterface`), emissão de NFe (Vendas) e NFS-e (OS) com chave de 44 dígitos, protocolo, XML e PDF.<br><br><br>                                                                                                              |
| Módulo Industrial (PCP) | ✅ Concluído | Ficha Técnica/BOM (`pcp_fichas_tecnicas`), Ordens de Produção (`OP-2026-000001`), consumo de insumos, rateio de mão de obra/CIF, entrada atômica do acabado com recálculo de custo e apontamento de perdas/refugos (`pcp_apontamentos_perda`).<br><br><br> |

📌 Planejamento de Releases (Semantic Versioning)
=================================================





### 🟢 Versão 1.x.x — Core Comercial, Operacional, Financeiro & Fiscal (Concluídas v1.0 a v1.5)





* **v1.0.0 (Concluída):** Multi-tenant, Pessoas, Produtos/Serviços, OS/CMMS, Compras e Financeiro/DRE[cite: 4].
  
  

* **v1.1.0 (Concluída):** Módulo de Vendas Diretas + ACL / Perfis de Acesso (`CheckRole`)[cite: 4].
  
  

* **v1.2.0 (Concluída):** Gestão de Orçamentos com conversão + Cadastros Auxiliares (Categorias e Unidades de Medida)[cite: 4].
  
  

* **v1.3.0 (Concluída):** Engine de Impressão de Orçamento/OS, Dashboard de Indicadores e Cobrança PIX Nativa (Payload EMV + QR Code)[cite: 4].
  
  

* **v1.4.0 (Concluída):** Estrutura Fiscal nos Produtos (NCM/CFOP), Parâmetros do Emitente (IBGE/CRT) e Extrato Financeiro por Período[cite: 4].
  
  

* **v1.5.0 (Concluída):** Engine de Transmissão Fiscal Desacoplada (`FiscalDriverInterface`) com suporte a NFe e NFS-e + Gestão e Troca Dinâmica de Múltiplos Tenants.
  
  
  
  
  
  

### 🏭 Versão 2.0.0 — Módulo Industrial (PCP & Custo Industrial - Concluída)

[cite: 4]





* **v2.0.0 (Concluída):**

* **Ficha Técnica / Árvore do Produto (BOM):** Composição de insumos físicos e custos indiretos/mão de obra por produto acabado.
  
  

* **Ordens de Produção (OP):** Controle de ciclo de vida (`PLANEJADA`, `EM_PRODUCAO`, `CONCLUIDA`, `CANCELADA`), baixa atômica de insumos, entrada do produto acabado no estoque e recálculo automático do preço de custo unitário final.
  
  

* **Apontamento de Perdas & Motivos de Refugo:** Cadastro de motivos e registro analítico de perdas no chão de fábrica.
  
  
  
  
  
  

### 🚚 Versão 3.0.0 — Logística, WMS & Multi-Depósitos (Próxima Release)

[cite: 4]





* **Multi-Depósitos / Almoxarifados:** Estoque fracionado por galpão, loja física e van de técnicos[cite: 4].
  
  

* **Transferência Interna de Mercadorias:** Movimentação entre estoques/locais com conferência e tracking.
  
  

* **Gestão de Frotas:** Controle de abastecimento e manutenções preventivas dos veículos de serviço (integrado ao módulo de OS/CMMS)[cite: 4].
  
  

* **Emissão de CTe e MDF-e:** Documentos fiscais eletrônicos de transporte de cargas[cite: 4].
