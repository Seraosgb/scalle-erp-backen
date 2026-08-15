# Veredito do Manus — Nova versão do `revisar.md` (Gemini)

## 1. Avaliação geral

A nova versão é **significativamente melhor** que a original e incorpora corretamente as três grandes correções que todas as análises convergiram: a fórmula "MVP enxuto + Core extensível + Feature Flags + Add-ons Enterprise", a **régua de maturidade de 5 níveis** (que resolve o problema de marcar "mockado" como "concluído") e a tabela "Próximo Passo para Subir de Nível". A classificação honesta da Engine Fiscal como 🔵 (arquitetado/mockado) é o item mais importante — era exatamente o ponto que inflava artificialmente a maturidade do projeto.

A matriz comercial de planos está bem desenhada, e o enquadramento da assinatura em campo na MP 2.200-2/2001 (ICP-Brasil/marcas de tempo) com SHA-256 + geo + IP é a fundamentação jurídica correta. O soft-lock de downgrade e as cotas de storage fecham a fundação SaaS.

**Nota: 8/10. O documento está pronto para virar documento de arquitetura vivo — mas ainda há 5 lacunas específicas que eu não deixaria passar.**

## 2. As 5 lacunas que ainda restam

### Lacuna 1 — A régua de 5 níveis foi adotada, mas não aplicada a tudo (e há inconsistências)

O diagrama ASCII marca quase tudo como 🟡 (Implementado), e a tabela de raio-X também — mas aqui há uma **tensão interna**: no documento anterior, "Engine Fiscal" aparecia como ✅ "emissão de NFe/NFSe/CTe/MDF-e concluída", e agora ela virou 🔵 "drivers mockados". Se os drivers estão mockados, então os módulos que dependem deles (OS emitindo NFS-e, Vendas emitindo NFe, CTe/MDF-e de transporte) **não podem estar todos 🟡 "código funcional"**. Ou o fluxo de emissão já funciona end-to-end (e então a fiscal não é mockada), ou é mockado (e então OS/Vendas/Compras carregam uma dependência 🟡→🔵 que precisa ser declarada). Recomendo adicionar uma nota explícita de **dependência entre níveis**: "módulo X está 🟡 *exceto* o subfluxo de emissão fiscal, que herda o nível do driver".

Também faltou a coluna de nível em alguns itens órfãos do diagrama original: CTe/MDF-e, mensageria, backup/LGPD, SSE/PWA/Passkeys/IA da v7 desapareceram do documento sem registro do nível em que ficaram (descontinuados? herdados para v6/v7?).

### Lacuna 2 — O risco crítico de isolamento de tenant não virou item de backlog

Meu diagnóstico anterior apontava o vazamento cruzado entre tenants (especialmente dados de RH/salários) como **um dos 3 riscos críticos** do sistema, com mitigação específica: escopo global de `empresa_id` em *todas* as queries via Eloquent global scope, e suíte de testes de isolamento por rota. A nova versão mantém o `empresa_id` na arquitetura, mas **nenhuma linha o trata como risco a mitigar** — não há item no raio-X, não há "próximo passo", não há menção nos diferenciais. Feature flags e cotas de storage protegem o modelo de negócio; nada no documento prova que o tenant A não lê o tenant B. Sugiro:

> **Auth & Multi-Tenant | 🟡 → Próximo passo:** global scope de `empresa_id` em todos os models + teste automatizado de isolamento por rota (tentativa de leitura de tenant cruzado retorna 403/404).

### Lacuna 3 — Ciclo de vida fiscal segue ausente (o segundo risco crítico)

A linha da Engine Fiscal agora diz "Implementação de driver real com suporte a A1, CCe, cancelamento e rejeições SEFAZ" — ótimo. Mas faltam **contingência offline (EPEC/SVC), inutilização de numeração, manifestação do destinatário e conservação de XML por 5 anos** em repositório dedicado. Sem contingência, o primeiro evento de instabilidade da SEFAZ para a operação fiscal de todos os tenants simultaneamente. Recomendo explicitar esses 4 itens no "próximo passo" do driver fiscal, com prioridade CRÍTICA.

### Lacuna 4 — Imutabilidade do ponto REP-P ficou implícita, não explícita

O documento agora cita "captura imutável" no item de conformidade legal — mas o meu ponto era sobre **ajustes e correções**: a portaria 671/2021 não proíbe corrigir, proíbe **sobrescrever** a marca original. É preciso afirmar na linha do DP: "toda justificativa/ajuste de batida gera registro espelho; a batida original é fisicamente imutável". Sem isso, a afirmação de conformidade REP-P continua vulnerável na primeira auditoria trabalhista.

### Lacuna 5 — LGPD ficou só no eNPS

A LGPD aparece uma única vez, no parágrafo do eNPS. Mas o Scalle armazena CPF, salários, holerites, certificações, dados de menores (dependentes, no futuro) e endereços de centenas de colaboradores por tenant. Falta registrar: **contrato operador-controlador** (o Scalle é operador de dados do tenant), política de retenção documentada — inclusive o conflito explícito entre direito ao esquecimento e a retenção fiscal de 5 anos — e notificação de incidente. O backup da v6 continua sem RPO/RTO definidos nem restore testado.

## 3. Ajustes menores de coerência

| Item no documento | Observação |
| --- | --- |
| Storage 1GB (MEI) vs 15GB (Pro) | Gap de 15x é agressivo; se a v6 incluir fotos de OS + assinatura + QR, 1GB enche rápido e o "upsell natural" vira frustração de cliente no primeiro mês. Sugiro 3–5GB no MEI. |
| "Dashboard Executivo Real-time" 🟡 | "Real-time" via polling não é real-time; como SSE está planejado na v7, reclassificar como 🟡 (pós-atualização manual) ou deixar o "real-time" para o momento do SSE. |
| Validação de CNPJ na Receita WS | Boa ideia, mas é API externa com limite de requisições (captcha/limite); incluir fallback de validação de dígito verificador local para não criar dependencia quebrável. |
| Matriz de planos sem linha "Billing/Assinatura" | O próprio billing é a feature que gerencia os flags — incluir linha "Billing & Feature Flags: Enterprise ✅ / Pro (auto) / MEI (auto)" para fechar a tabela. |
| Nine-Box no RH Estratégico | Útil, mas é analítico; o risco não coberto no RH é outro — férias, afastamentos e rescisão (o "DP incompleto" que o ChatGPT apontou). Priorizar rescisão/13º antes do Nine-Box. |

## 4. Conclusão e recomendação prática

O Gemini executou bem a consolidação: a régua de maturidade honesta, a filosofia de arquitetura e a fundação v6 estão corretas e alinhadas com o que eu recomendei. O documento está **aprovado como base** — com a condição de incorporar as 5 lacunas acima antes de iniciar a codificação da v6. A ordem que manteria intacta:

1. **Isolamento de tenant** (global scope + testes) — risco crítico, custo baixo, e é pré-requisito de *tudo* que vem depois, inclusive do billing;
2. **Motor de alçadas simples** — já listado, manter na v6;
3. **Driver fiscal real** com ciclo de vida completo (cancelamento, CCe, inutilização, contingência, A1 com cofre) — os 4 itens ausentes na lacuna 3;
4. **Billing + feature flags + cotas + soft-lock** — a espinha dorsal comercial;
5. **MFA para ADMIN/FINANCEIRO** — já no raio-X, manter;
6. **Exportação contábil SPED/CSV** — fecha a venda com o contador do cliente;
7. **Portal do Cliente** — o diferencial mais barato da lista.

Com isso, a v6 sai não apenas "planejada", mas **auditável**: cada módulo sabe qual é seu nível real, o que o separa do nível seguinte e qual risco regulatório ele carrega enquanto estiver abaixo de 🟣.
