É essa visão macro de produto que separa um sisteminha de gaveta de um **ERP Saas Escalável e Multissetorial**\! 🔥  
Se a meta do **Scalle ERP** é atender desde a assistência técnica e o comércio de balcão até indústrias e distribuidoras, precisamos desenhar o mapa de módulos do sistema em **3 Camadas de Maturidade**.  
Abaixo está o **Raio-X de Revisão do Projeto**, mostrando o que já temos blindado, o que falta para fechar o Comércio/Serviços e como o sistema vai expandir para Indústria e Logística no futuro sem quebrar a arquitetura modular:

## 🗺️ Mapa de Evolução Arquitetural do Scalle ERP

                            ┌────────────────────────────────────────┐  
                            │        CAMADA CORE (MULTITENANT)       │  
                            │   Auth | Pessoas | Itens | DTOs | DB   │  
                            └───────────────────┬────────────────────┘  
                                                │  
            ┌───────────────────────────────────┼───────────────────────────────────┐  
            ▼                                   ▼                                   ▼  
     🛠️ PRESTAÇÃO DE SERVIÇOS           🏬 COMÉRCIO & VENDAS               🏭 INDÚSTRIA (PCP)  
     - Módulo de OS / CMMS ✅           - Módulo de Vendas Diretas ✅      - Estrutura de Produtos (BOM) 🔮  
     - Ciclo de Vida da OS ✅           - PDV / Pedidos de Balcão ✅        - Ordens de Produção (OP) 🔮  
     - Baixa/Estorno Estoque ✅          - Orçamentos/Propostas ⏳          - Apontamento de Perdas 🔮  
     - Garantia & Laudos ✅             - Comissões de Vendedores 🔮       - Custo Industrial Apurado 🔮  
            │                                   │                                   │  
            └───────────────────────────────────┼───────────────────────────────────┘  
                                                ▼  
                            ┌────────────────────────────────────────┐  
                            │      SUPRIMENTOS, GESTÃO & FINANCEIRO  │  
                            │ - Compras & Entradas de Notas ✅       │  
                            │ - Contas a Receber / Contas a Pagar ✅ │  
                            │ - DRE Consolidado & Fluxo de Caixa ✅   │  
                            │ - ACL / Perfis de Acesso (CheckRole) ⏳│  
                            │ - Fiscal (NFe / NFSe / CTM) 🔮         │  
                            │ - WMS / Logística / Frotas 🔮           │  
                            └────────────────────────────────────────┘

🔍 Revisão Atual do Projeto (O que já temos ativo)
--------------------------------------------------

| **Módulo**              | **Status**  | **O que faz hoje?**                                                                                     |
| ----------------------- | ----------- | ------------------------------------------------------------------------------------------------------- |
| **Auth & Tenant**       | ✅ Concluído | Multi-empresa isolado (`empresa_id`) com autenticação Sanctum.                                          |
| **Pessoas**             | ✅ Concluído | Tabela unificada para Clientes e Fornecedores.                                                          |
| **Produtos & Serviços** | ✅ Concluído | Unificado com DTOs, precificação de custo/venda, DTO de update preservando saldo e controle de estoque. |
| **Ordens de Serviço**   | ✅ Concluído | Numeração comercial, subtotais separados, ciclo de vida e baixa atômica de estoque na conclusão.        |
| **Compras & Entradas**  | ✅ Concluído | Entrada de fornecedor, incremento de estoque, atualização de preço de custo e gera Contas a Pagar.      |
| **Financeiro & DRE**    | ✅ Concluído | Receitas, Despesas, Liquidação com meio de pagamento e DRE do período (`Y-m-d`).                        |
| **Vendas Diretas**      | ✅ Concluído | Pedidos de balcão (`VEN-2026-000001`), baixa atômica no estoque e geração de Contas a Receber.          |

📌 Planejamento de Releases (Semantic Versioning)
-------------------------------------------------

### 🟢 Versão 1.x.x — Core Comercial, Operacional & Segurança (Atual)

* **v1.0.0 (Concluída):** Multi-tenant, Pessoas, Produtos/Serviços, OS/CMMS, Compras e Financeiro/DRE.

* **v1.1.0 (Fase Atual):** Módulo de Vendas Diretas + ACL / Perfis de Acesso (`ADMIN`, `FINANCEIRO`, `TECNICO`, `ATENDENTE`).

* **v1.2.0 (Próxima):** Gestão de Orçamentos/Propostas + Cadastros Auxiliares (Categorias e Unidades de Medida).

* **v1.3.0:** Emissão Fiscal (NFe / NFS-e) + Gateway de Cobrança PIX/Boleto.

### 🏭 Versão 2.0.0 — Módulo Industrial (PCP & Custo Industrial)

* **Ficha Técnica / Árvore do Produto (BOM):** Composição de insumos por produto final.

* **Ordens de Produção (OP):** Baixa atômica de insumos/matéria-prima e entrada do produto acabado no estoque.

* **Apontamento de Horas e CIF:** Custo Indireto de Fabricação e horas de máquina.

### 🚚 Versão 3.0.0 — Logística, WMS & Multi-Depósitos

* **Multi-Depósitos:** Estoque fracionado por galpão, loja física e van do técnico.

* **Gestão de Frotas:** Manutenções preventivas dos veículos (utilizando o módulo CMMS).

* **Emissão de CTe e MDF-e:** Documentos fiscais de transporte.

🎯 Próxima Ação Imediata
------------------------

Efetivar a aplicação do **Middleware `CheckRole`** para fechar o ciclo de **Segurança e Perfis de Acesso (v1.1.0)** nas rotas ativas.
