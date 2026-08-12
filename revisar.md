Visão macro de produto de um **ERP SaaS Escalável e Multissetorial**! 🔥

Com o fechamento da **v1.2.0**, consolidamos a camada comercial (Orçamentos com Conversão) e a camada de Parametrização/Segurança (ACL e Cadastros Auxiliares) no **Scalle ERP**.

Abaixo está o **Raio-X de Revisão do Projeto**, refletindo os módulos ativos no código e no contrato da API (`document.json`), além do roadmap alinhado por versão.
🗺️ Mapa de Evolução Arquitetural do Scalle ERP
-----------------------------------------------

                            ┌────────────────────────────────────────┐  
                            │        CAMADA CORE (MULTITENANT)       │  
                            │ Auth | Pessoas | Itens | DTOs | ACL DB │  
                            └───────────────────┬────────────────────┘  
                                                │  
            ┌───────────────────────────────────┼───────────────────────────────────┐  
            ▼                                   ▼                                   ▼  
     🛠️ PRESTAÇÃO DE SERVIÇOS           🏬 COMÉRCIO & VENDAS               🏭 INDÚSTRIA (PCP)  
     - Módulo de OS / CMMS ✅           - Módulo de Vendas Diretas ✅      - Estrutura de Produtos (BOM) 🔮  
     - Ciclo de Vida da OS ✅           - PDV / Pedidos de Balcão ✅        - Ordens de Produção (OP) 🔮  
     - Baixa/Estorno Estoque ✅          - Orçamentos/Propostas ✅          - Apontamento de Perdas 🔮  
     - Garantia & Laudos ✅             - Conversão em Venda/OS ✅         - Custo Industrial Apurado 🔮  
            │                                   │                                   │  
            └───────────────────────────────────┼───────────────────────────────────┘  
                                                ▼  
                            ┌────────────────────────────────────────┐  
                            │      SUPRIMENTOS, GESTÃO & FINANCEIRO  │  
                            │ - Compras & Entradas de Notas ✅       │  
                            │ - Contas a Receber / Contas a Pagar ✅ │  
                            │ - DRE Consolidado & Fluxo de Caixa ✅   │  
                            │ - ACL / Perfis de Acesso (CheckRole) ✅│  
                            │ - Categorias & Unidades (Auxiliares) ✅│  
                            │ - Fiscal (NFe / NFSe / CTM) 🔮         │  
                            │ - WMS / Logística / Frotas 🔮           │  
                            └────────────────────────────────────────┘

🔍 Revisão Atual do Projeto (Status Real do Código)
---------------------------------------------------

| **Módulo**              | **Status**  | **O que faz hoje?**                                                                                                                 |
| ----------------------- | ----------- | ----------------------------------------------------------------------------------------------------------------------------------- |
| **Auth & Tenant**       | ✅ Concluído | Multi-empresa isolado (`empresa_id`), token Sanctum e retorno do perfil (`role`).                                                   |
| **ACL & Segurança**     | ✅ Concluído | Middleware `CheckRole` bloqueando perfis não autorizados com `403 Forbidden` (`ADMIN`, `FINANCEIRO`, `TECNICO`, `ATENDENTE`).       |
| **Pessoas**             | ✅ Concluído | Tabela unificada para Clientes e Fornecedores (`pes_pessoas`).                                                                      |
| **Produtos & Serviços** | ✅ Concluído | Itens (`pro_itens`), Categorias (`pro_categorias`) e Unidades de Medida (`pro_unidades`). DTOs blindados mantendo saldo de estoque. |
| **Ordens de Serviço**   | ✅ Concluído | Numeração comercial (`OS-2026-000001`), ciclo de vida, baixa atômica de estoque na conclusão e lançamento no Contas a Receber.      |
| **Compras & Entradas**  | ✅ Concluído | Registro de notas, incremento atômico de estoque, atualização do preço de custo e lançamento no Contas a Pagar.                     |
| **Vendas Diretas**      | ✅ Concluído | Pedidos de balcão (`VEN-2026-000001`), baixa atômica de peças e geração de Contas a Receber.                                        |
| **Orçamentos**          | ✅ Concluído | Cotações (`ORC-2026-000001`), controle de validade e conversão em 1 clique para Venda ou OS.                                        |
| **Financeiro & DRE**    | ✅ Concluído | Contas a Receber/Pagar, liquidação/baixa com meio de pagamento, Plano de Contas (`fin_categorias`) e DRE do período (`Y-m-d`).      |

📌 Planejamento de Releases (Semantic Versioning)
-------------------------------------------------

### 🟢 Versão 1.x.x — Core Comercial, Operacional & Segurança (Concluídas v1.0 a v1.2)

* **v1.0.0 (Concluída):** Multi-tenant, Pessoas, Produtos/Serviços, OS/CMMS, Compras e Financeiro/DRE.

* **v1.1.0 (Concluída):** Módulo de Vendas Diretas + ACL / Perfis de Acesso (`CheckRole`).

* **v1.2.0 (Concluída):** Gestão de Orçamentos com conversão + Cadastros Auxiliares (Categorias e Unidades de Medida).

* **v1.3.0 (Próxima Release):** Módulo Fiscal (NFe / NFS-e) + Cobranças Automáticas (PIX / Boleto Asaas/Mercado Pago) + Dashboard de Indicadores.

### 🏭 Versão 2.0.0 — Módulo Industrial (PCP & Custo Industrial)

* **Ficha Técnica / Árvore do Produto (BOM):** Composição de insumos por produto final.

* **Ordens de Produção (OP):** Baixa atômica de insumos/matéria-prima e entrada do produto acabado no estoque.

* **Apontamento de Horas e CIF:** Custo Indireto de Fabricação e horas de máquina.

### 🚚 Versão 3.0.0 — Logística, WMS & Multi-Depósitos

* **Multi-Depósitos:** Estoque fracionado por galpão, loja física e van do técnico.

* **Gestão de Frotas:** Manutenções preventivas dos veículos (utilizando o módulo CMMS).

* **Emissão de CTe e MDF-e:** Documentos fiscais de transporte.
