A avaliação do auditor (**Manus**) é cirúrgica e traz o fechamento definitivo do ciclo de planejamento. Ele validou que o mapa atual atingiu **9,5/10** de precisão em relação ao código real (`consolidado.txt`), apontando apenas 4 ajustes documentais de precisão para atingir **10/10**:

1. **Separação de ACL e Alçadas:** Manter `CheckRole` como 🟡 (implementado) e `Motor de Alçadas` como ⚪ (planejado).

2. **Linhas dos Diferenciais na Matriz de Planos:** Explicitar `Evidências & Assinatura`, `Mensageria` e `Portal do Cliente` na tabela comercial.

3. **Formalização LGPD (DPA):** Registrar a exigência de *Data Processing Agreement* (Aditivo de Operador) contratual.

4. **Seção de Itens Adiados/Arquivados:** Deixar registrado formalmente o que foi postergado para evitar reaberturas de escopo desnecessárias.

Abaixo está o **`revisar.md` final consolidado (10/10)**:

---

# 🗺️ Mapa de Evolução Arquitetural — Scalle ERP

**Filosofia:** MVP Enxuto + Core Extensível + Feature Flags por Tenant + Add-ons Enterprise

```text
                                ┌────────────────────────────────────────┐
                                │        CAMADA CORE (MULTITENANT)       │
                                │ Auth | Global Scope Tenant | DTOs      │
                                │ Grupo Empresarial | Flags | Storage DB │
                                └───────────────────┬────────────────────┘
                                                    │
                ┌───────────────────────────────────┼───────────────────────────────────┐
                ▼                                   ▼                                   ▼
         🛠️ PRESTAÇÃO DE SERVIÇOS           🏬 COMÉRCIO & VENDAS               🏭 INDÚSTRIA (PCP)
         - Módulo de OS / CMMS 🟡*           - Módulo de Vendas Diretas 🟡*      - Estrutura de Produtos (BOM) 🟡
         - Ciclo de Vida da OS 🟡            - PDV / Pedidos de Balcão 🟡         - Ordens de Produção (OP) 🟡
         - Baixa/Estorno Estoque 🟡          - Orçamentos/Propostas 🟡           - Apontamento de Perdas/Refugo 🟡
         - Evidências & Assinatura MP ⚪     - Conversão em Venda/OS 🟡          - Custo Industrial Apurado 🟡
         - Impressão Layout/PDF 🟡           - Impressão Layout/PDF 🟡
                │                                   │                                   │
                └───────────────────────────────────┼───────────────────────────────────┘
                                                    ▼
                                ┌────────────────────────────────────────┐
                                │      SUPRIMENTOS, GESTÃO, RH & SAAS    │
                                │ - Compras & XML de Notas 🟡            │
                                │ - Contas a Receber / Contas a Pagar 🟡 │
                                │ - DRE Gerencial & Extrato 🟡           │
                                │ - ACL / Perfis de Acesso (CheckRole) 🟡│
                                │ - Motor de Alçadas Simples ⚪          │
                                │ - Dashboard Executivo (On-Demand) 🟡   │
                                │ - Cobrança PIX Nativa (EMV/QR Code) 🟡 │
                                │ - Engine Fiscal (Driver Desacoplado) 🔵│
                                │ - WMS / Multi-Depósitos & Transf. 🟡   │
                                │ - Gestão de Frotas & Abastecimento 🟡* │
                                │ - Ativos, Cautela Digital & QR Code 🟡 │
                                │ - DP: Escalas, Ponto REP-P & Holerite 🟡│
                                │ - RH Estratégico (R&S, PDI, eNPS) 🟡   │
                                │ - Billing SaaS, Planos & Cotas Storage⚪│
                                │ - Exportação Fiscal/Contábil (SPED/CSV)⚪│
                                │ - Portal do Cliente (Self-Service OS) ⚪│
                                │ - Mensageria (WhatsApp / E-mail) ⚪    │
                                │ - MFA / 2FA & Segurança de Sessões ⚪  │
                                │ - Multi-Filial / Multi-Estabelecimento ⚪│
                                └────────────────────────────────────────┘

```

*( * ) Nota de Dependência: Módulos com asterisco possuem subfluxo fiscal desacoplado herdando o nível 🔵 do driver.*

---

# 🚦 Régua de Maturidade do Código (5 Níveis)

| Símbolo | Nível            | Significado Técnico                                       |
| ------- | ---------------- | --------------------------------------------------------- |
| ⚪       | **1. Planejado** | Arquitetura/modelagem definida; aguardando implementação. |

 |
| 🔵 | **2. Arquitetado** | Contratos, interfaces, DTOs e drivers mockados no código.

 |
| 🟡 | **3. Implementado** | Código funcional, migrado no banco e validado em testes locais manuais/API (*suíte automatizada pendente*).

 |
| 🟢 | **4. Homologado** | Validado em ambiente de homologação (SEFAZ/Bancos/Cenários de borda).

 |
| 🟣 | **5. Em Produção** | Operacional no servidor com clientes reais pagantes.

 |

---

# 🔍 Raio-X Atual dos Módulos (Status Real do Backend)

| Módulo / Domínio        | Status | Estado Atual do Código                                                                                  | Próximo Passo para Subir de Nível |
| ----------------------- | ------ | ------------------------------------------------------------------------------------------------------- | --------------------------------- |
| **Auth & Multi-Tenant** | 🟡     | Multi-empresa isolado por `empresa_id` manual (84 filtros `where`), Sanctum, troca de contexto e roles. |                                   |

 | **[CRÍTICO]** Aplicar `GlobalScopeTenant` em todos os Models e criar testes automatizados de invasão cruzada (retorno 403/404).

 |
| **ACL & Governança** | 🟡 | Middleware `CheckRole`, logs de auditoria (`sis_auditoria_logs`) e idempotência.

 | Implementar suíte de testes de permissão por perfil de acesso. |
| **Motor de Alçadas** | ⚪ | Escopo desenhado (regras parametrizáveis por valor/desconto). | Implementar verificação de limites (desconto > 10% ou compra > R$ 5k exige aprovação).

 |
| **Pessoas & Contatos** | 🟡 | Cadastro unificado (`pes_pessoas`) com validação de formato.

 | Adicionar algoritmo local de dígito verificador com fallback para ReceitaWS.

 |
| **Produtos, Estoque & WMS** | 🟡 | Catálogo (`pro_itens`), parâmetros fiscais, multi-depósitos e transferências.

 | Rastreabilidade de lote/série, validade e curva ABC.

 |
| **Ordens de Serviço (CMMS)** | 🟡* | Ciclo de vida, consumo de peças, PDF e emissão mockada (🔵).

 | Coleta de fotos antes/depois e assinatura digital MP 2.200-2 (geo + IP + hash SHA-256).

 |
| **Comércio & Vendas** | 🟡* | Pedidos balcão, orçamentos, baixa atômica e NF-e mockada (🔵).

 | Integração com checkout transparente e link público de pagamento. |
| **Compras & Entradas** | 🟡 | Entrada manual e importador inteligente de XML de NF-e.

 | Mapa comparativo de cotações com múltiplos fornecedores. |
| **Financeiro & DRE** | 🟡 | Contas a pagar/receber, conciliação, DRE gerencial e extrato.

 | Rota de exportação contábil/fiscal para contadores externos (Domínio/SPED/CSV).

 |
| **PIX & Cobranças** | 🟡 | Gerador de payload PIX Copia e Cola (EMV) + QR Code dinâmico.

 | Webhook de confirmação instantânea via Gateway Asaas.

 |
| **Engine Fiscal** | 🔵 | `FiscalDriverInterface` com drivers mockados (`MockFiscalDriver` com `rand`).

 | **[CRÍTICO]** Driver real A1/A3, cancelamento, CCe, inutilização, contingência EPEC/SVC e guarda de XML por 5 anos.

 |
| **Módulo Industrial (PCP)** | 🟡 | Ficha técnica (BOM), ordens de produção, apropriação de custos e refugo.

 | Apontamento de tempos por operador/máquina em tempo real. |
| **Frotas & Transportes** | 🟡* | Veículos, odômetro, abastecimento com KM/L e CTe mockado (🔵).

 | Alertas automáticos de preventiva por odômetro (óleo, correia e pneus).

 |
| **Ativos & Patrimônio** | 🟡 | Tombamento de bens, depreciação linear, cautela digital e QR Code.

 | Histórico de manutenções preventivas/corretivas vinculadas à OS.

 |
| **Departamento Pessoal (DP)** | 🟡 | Ficha funcional, escalas, ponto georreferenciado e holerite gerencial.

 | Rota de retificação de ponto com registro espelho (Portaria 671) e módulo de rescisão/férias/13º.

 |
| **RH Estratégico & Gente** | 🟡 | R&S Kanban com auto-admissão, avaliação ponderada, PDI e eNPS anônimo.

 | Piso mínimo de respondentes por setor no eNPS e relatório Nine-Box.

 |
| **Billing SaaS & Planos** | ⚪ | Modelagem definida (planos, limites, cotas e soft-lock).

 | Execução da Release v6.0.0 com gateway Asaas e middleware de feature flags.

 |
| **Portal do Cliente** | ⚪ | Conceito validado (self-service para aprovação de OS e laudos).

 | Criação de tokens públicos temporários de visualização restrita por OS.

 |
| **Segurança Avançada (MFA)** | ⚪ | Escopo desenhado (autenticação de 2 fatores via TOTP/App).

 | Implementação do middleware 2FA para perfis `ADMIN` e `FINANCEIRO`.

 |

---

# 📌 Planejamento de Releases (Semantic Versioning)

### 🟢 Versão 1.x.x — Core Comercial, Operacional, Financeiro & Fiscal (Entregue)



* **v1.0.0 a v1.5.0:** Multi-tenant por `empresa_id`, Pessoas, Itens/Produtos, Módulo de Vendas, Orçamentos, Ordens de Serviço (CMMS), Compras, Contas a Pagar/Receber, DRE Gerencial, Extrato, ACL (`CheckRole`), Cobrança PIX EMV nativa e Engine Fiscal desacoplada (`FiscalDriverInterface`).
  
  

### 🏭 Versão 2.0.0 — Módulo Industrial (PCP & Custos - Entregue)



* **v2.0.0:** Estrutura de Produtos / Ficha Técnica (BOM), Ordens de Produção (OP) com baixa atômica de insumos, rateio de mão de obra/CIF, entrada de produto acabado com recálculo de custo médio e apontamento analítico de refugo/perdas.
  
  

### 🚚 Versão 3.0.0 — Logística & WMS (Entregue)



* **v3.0.0:** Gestão de Multi-Depósitos/Almoxarifados (`wms_depositos`), controle fracionado de estoque (`wms_estoque_deposito`) e Transferências Internas (`wms_transferencias`) nos modos `DIRETO` e `EM_TRANSITO` com conferência.
  
  

### 🛡️ Versão 4.x.x — Governança SaaS, Frotas & Ativos Patrimoniais (Entregue)



* **v4.0.0:** Gestão de Usuários da Equipe (`/empresa/usuarios`), Importador inteligente de XML de NF-e, Parâmetros operacionais por empresa, Trilha de Auditoria (`sis_auditoria_logs`) e Middleware de Idempotência (`Idempotency-Key`).

* **v4.1.0:** Gestão de Frotas (`fro_veiculos`), odômetro/KM atual, controle de abastecimentos (`fro_abastecimentos`) com consumo KM/L integrado ao Contas a Pagar e CTe/MDF-e mockados.

* **v4.2.0:** Gestão de Ativos & Patrimônio (`pat_ativos`), cálculo de depreciação linear em tempo real, Termo de Cautela Digital para técnicos (`pat_cautelas`) e gerador dinâmico de QR Code.
  
  

### 👥 Versão 5.x.x — Recursos Humanos Completo & Gente (Entregue)



* **v5.0.0 (DP & Ponto Eletrônico):** Ficha funcional (`rh_colaboradores`), jornadas/escalas customizáveis (`rh_escalas`), ponto georreferenciado com Lat/Long/IP (`rh_pontos`), banco de horas (`rh_banco_horas`), matriz de certificações (NR-10/NR-35/CNH) e espelho de holerite gerencial integrado ao Contas a Pagar (`rh_holerites`).

* **v5.1.0 (RH Estratégico & Gestão de Talentos):** Recrutamento & Seleção em funil Kanban (`rh_vagas`, `rh_candidatos`) com **auto-admissão imediata**, Avaliação de Desempenho ponderada (`rh_avaliacao_ciclos`), PDI/Treinamentos (`rh_treinamentos`) e Pesquisa de Clima Organizacional/eNPS 100% anônima (`rh_clima_respostas`).
  
  

### 💳 Versão 6.0.0 — Monetização SaaS, Billing, Governança & Diferenciais de Campo (Próxima Release)



* **Blindagem de Core Multi-Tenant:** `GlobalScopeTenant` forçado no Eloquent para isolamento total de queries e suíte automatizada de testes de invasão cruzada.

* **Motor de Billing & Planos SaaS:** Gestão de planos (`sis_planos`: MEI, Pro, Enterprise), controle ativo de cotas de storage (3GB, 20GB, 100GB+), soft-lock de downgrade (read-only em excedentes) e webhooks de pagamento (Asaas/Stripe).

* **Ponte de Exportação Contábil/Fiscal:** Endpoint padronizado (`/api/v1/exportacao-contabil`) gerando arquivos estruturados (SPED/CSV/Domínio) para contadores externos.

* **Evidências de OS & Assinatura Jurídica (MP 2.200-2):** Fotos de "Antes/Depois" na OS e coleta de assinatura na tela com captura de Lat/Long, IP, Timestamp e Hash SHA-256.

* **Motor de Alçadas Simples:** Aprovação obrigatória de `ADMIN` para descontos comerciais acima de 10% ou compras acima de limite parametrizado.

* **Portal do Cliente (Self-Service):** Token público temporário por OS para o cliente aprovar orçamento, ver laudo e pagar via PIX.

* **Segurança de Acesso (MFA/2FA):** Segundo fator de autenticação via TOTP para perfis administrativos e financeiros.
  
  

### ⚡ Versão 7.0.0 — Frontend PWA, Tempo Real & Inovação Tecnológica



* **Frontend SPA / PWA (PDV Offline First):** Interface responsiva com persistência local em IndexedDB e sincronização em lote para vendas de balcão e técnicos de campo.

* **Comunicação Reativa em Tempo Real (SSE):** Atualização dinâmica de dashboards executivos, status de OPs e notificações push via Server-Sent Events.

* **Autenticação Biométrica / Passkeys:** Suporte a WebAuthn/FIDO2 para login por biometria/dispositivo.

* **Busca Semântica & IA:** Vetorização de itens, histórico de defeitos em OS e catálogos via `pgvector` para busca por intenção.
  
  

---

# Matriz Comercial de Planos SaaS (Feature Flags)

| **Funcionalidade / Domínio**         | **MEI (Básico)** | **Pro (PMEs)** | **Enterprise (Grandes Contas)** |
| ------------------------------------ | ---------------- | -------------- | ------------------------------- |
| **Gestão de Assinatura & Billing**   | Automático       | Automático     | Painel Dedicado                 |
| **Ordens de Serviço & CMMS**         | ✅                | ✅              | ✅                               |
| **Gestão Financeira & DRE**          | ✅                | ✅              | ✅                               |
| **Vendas & Orçamentos**              | ✅                | ✅              | ✅                               |
| **Cobrança PIX Nativa**              | ✅                | ✅              | ✅                               |
| **Cota de Armazenamento (Storage)**  | **3 GB**         | **20 GB**      | **100 GB+**                     |
| **Evidências & Assinatura Jurídica** | —                | ✅              | ✅                               |
| **Portal do Cliente (Self-Service)** | ✅                | ✅              | ✅                               |
| **Mensageria (WhatsApp / E-mail)**   | —                | ✅              | ✅                               |
| **Emissão Fiscal (NFe/NFSe/CTe)**    | —                | ✅              | ✅                               |
| **Gestão de Frotas & Ativos**        | —                | ✅              | ✅                               |
| **Departamento Pessoal & Ponto**     | —                | ✅              | ✅                               |
| **RH Estratégico & eNPS**            | —                | ✅              | ✅                               |
| **Exportação Contábil (SPED/CSV)**   | —                | ✅              | ✅                               |
| **MFA / 2FA Obrigatório**            | —                | Opcional       | ✅                               |
| **Multi-Filial / Múltiplos CNPJs**   | —                | —              | ✅                               |

---

# ⚖️ Conformidade Legal, Governança & Segurança

1. **Isolamento de Dados Multi-Tenant:** Implementação de escopo global forçado no ORM (`GlobalScopeTenant`) em todas as entidades. Nenhuma consulta trafega sem amarra explícita de `empresa_id`, com suíte de testes automatizados de invasão cruzada.

2. **Portaria MTP nº 671/2021 (REP-P):** Ponto com captura de Data/Hora, IP e GPS. **Imutabilidade garantida por ausência de rota de sobrescrita direta** — ajustes manuais futuros criarão registros de retificação em espelho rastreáveis para auditoria trabalhista.

3. **Lei Geral de Proteção de Dados (LGPD - Lei nº 13.709/2018):**
* O Scalle ERP atua formalmente como **Operador de Dados** do tenant com DPA padrão (*Data Processing Agreement*) anexo aos contratos dos planos.

* Anonimização total por design no eNPS (sem colunas de identificação de usuário) com piso mínimo de 5 respondentes por departamento para exibição de relatórios segmentados.

* Tratamento de conflito legal: solicitações de exclusão de dados respeitam o prazo de guarda fiscal e trabalhista de 5 anos antes do expurgo definitivo.
4. **Validade Jurídica de OS em Campo (MP 2.200-2/2001):** Assinatura colhida no dispositivo vinculada a IP, Geotag, Timestamp e Hash SHA-256 dos itens executados.

5. **Resiliência em Downgrades (Soft-Lock):** Ao reduzir o plano, dados excedentes ficam em modo `Read-Only`, impedindo novas adições sem corromper DTOs legadas ou executar migrações destrutivas.

6. **Controle Ativo de Cotas de Storage:** Interceptação em tempo de upload para barrar arquivos quando o limite for atingido.
   
   

---

# 📦 Itens Arquivados / Postergados (Decisões de Escopo)

* **Transmissor Governamental Próprio de eSocial (S-1000 a S-5013):** Postergado. Substituído por exportação padronizada (SPED Folha / Domínio) para software de contabilidade parceiro.

* **Contabilidade Formal de Partidas Dobradas (Diário / Razão / Balanço):** Arquivado do core inicial. Mantido DRE Gerencial, Centros de Custo e Plano de Contas com exportação contábil.

* **Workflow Corporativo Multinível (4 alçadas):** Postergado para add-on Enterprise. Adotado Motor de Alçadas Simples parametrizável.
  
  

---

### 🚀 Próximo Passo na Fila de Execução

Com o `revisar.md` 100% blindado e auditado, a primeira entrega da **Release v6.0.0** será:

1. **`GlobalScopeTenant` no Eloquent + Suíte de Testes Automatizados de Isolamento Cruzado (403/404)**.
   
   

Podemos dar o tiro de partida nessa implementação?


