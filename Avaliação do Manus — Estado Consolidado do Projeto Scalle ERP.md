# Avaliação do Manus — Estado Consolidado do Projeto Scalle ERP

## 1. O que examinei

Desta vez não avaliei apenas os documentos de planejamento: o `consolidado.txt` contém o **código real do projeto** — módulos Laravel organizados em `app/Modules/` (Auth, Compras, Empresa, Financeiro, Fiscal, Frotas, Orcamentos, OrdensServico, PCP, Pessoas, Produtos, RH, Vendas, WMS, Ativos), as 20 migrações de banco, rotas da API, o Observer de auditoria, o OpenAPI (`api.json`/`api-docs.json`) e as suítes de teste. Cruzei cada afirmação do `revisar.md` atualizado contra o código. A conclusão principal é positiva: **o mapa e o código estão alinhados, e a honestidade da régua de maturidade foi comprovada no repositório** — mas encontrei também algumas divergências relevantes entre o que o mapa diz e o que o código mostra.

## 2. Confirmações — o que o código comprova

**A Engine Fiscal é genuinamente 🔵 (arquitetada/mockada), exatamente como o mapa agora declara.** O código confirma: `FiscalDriverInterface` com 4 métodos (`emitirNFeVenda`, `emitirNFSeOS`, `emitirCTe`, `emitirMDFe`), `FiscalService` injetando o driver via interface, e o `AppServiceProvider` vinculando a interface ao `MockFiscalDriver` — que gera números, chaves de acesso e protocolos **aleatórios** (rand). Não existe nenhum driver real no repositório. A classificação 🔵 é, portanto, fiel e correta. Atenção: isso significa que **qualquer NFe/NFSe "emitida" hoje por um cliente real é um documento fiscal inexistente perante a SEFAZ** — números e chaves fabricados. O sistema não pode processar nota fiscal de verdade até o driver real existir.

**O ponto REP-P é mais forte do que o mapa sugere.** O `PontoService` usa `firstOrCreate` com chave `empresa_id + colaborador_id + data_referencia` dentro de transação DB, e as batidas subsequentes preenchem apenas campos vazios (`saida_1`, `entrada_2`, `saida_2`). Não existe nenhum endpoint de edição ou exclusão de batidas no `RHController` (apenas `POST /ponto/bater`). Na prática, o registro é **imutável por ausência de rota de escrita corretiva** — o que atende bem à Portaria 671/2021. O ponto de ajuste/justificativa precisa ser criado como **rota nova de retificação com registro espelho** (como o mapa já prevê), mantendo essa imutabilidade por design.

**O eNPS é genuinamente anônimo por design.** A tabela `rh_clima_respostas` não tem `colaborador_id` nem `user_id` — apenas `empresa_id`, `pesquisa_id`, `departamento`, `nota_enps` e `comentario_anonimo`. O `ClimaService` calcula eNPS agregando tudo sem vínculo individual. A tese "privacy by design" do mapa é verificável no código. Falta apenas a proteção que eu apontei antes: piso mínimo de respondentes para relatórios segmentados por departamento (com poucos respondentes, departamento + data identifica a pessoa).

**A blindagem manual por `empresa_id` é consistente, mas ainda é manual.** Contei **84 ocorrências de `where('empresa_id', ...)` no código** e 380 referências a `empresa_id` — o padrão de filtro manual é aplicado de forma disciplinada em todos os services (Venda, OS, Fiscal, Compras, Ponto, Clima). Isso confirma o nível 🟡 dos módulos. E confirma, ao mesmo tempo, o risco do **item crítico do roadmap**: nenhum filtro é automático; um endpoint novo esquecido vaza dados. O `GlobalScopeTenant` do Eloquent (planejado na v6) é exatamente a correção certa, e o código atual prova que ele é necessário.

**A arquitetura modular está bem executada.** Módulos com Controller/Model/Service/DTO/Rotas separados, `CheckRole` como middleware, `IdempotencyMiddleware`, `AuditObserver` conectado aos 5 models do core (Item, OS, Venda, LancamentoFinanceiro, Compra), numerações comerciais sequenciais via services dedicados (`OsNumeroService`, `VendaNumeroService`, `OpNumeroService`), migrações idempotentes (`if (!Schema::hasTable(...))`), PIX EMV com chave dinâmica por empresa, importador de XML de NF-e e WMS com transferências DIRETO/EM_TRANSITO. Para um MVP de ERP multi-tenant, o código é limpo, coerente e acima da média.

## 3. Divergências encontradas — o que o código revela que o mapa não mostra

### 3.1 A suite de testes é inexistente

O `tests/` tem apenas `ExampleTest.php` (os dois testes padrão do Laravel, um deles ainda com `RefreshDatabase` comentado). **Todo módulo marcado como 🟡 ("validado em testes locais/API") não possui nenhum teste automatizado no repositório.** A régua de 5 níveis define 🟡 como "código funcional, migrado no banco e validado em testes locais/API" — o validado, aqui, é humano (Postman/manual), não automatizado. Sugiro uma recalibragem honesta: ou criar a suíte mínima (recomendo forte), ou marcar os módulos como 🟡 com a ressalva "sem testes automatizados". O roadmap da v6 acerta ao planejar a "suíte de testes de invasão cruzada" — ela é o primeiro teste que o projeto deve ter.

### 3.2 Não há um único teste de isolamento de tenant — o risco crítico está aberto

O `revisar.md` lista "suíte de testes automatizados de invasão cruzada" como próximo passo crítico do Auth — correto. O código confirma que hoje **nada** impede automaticamente um usuário autenticado com `empresa_id = 2` de ler dados do `empresa_id = 1`, além da disciplina dos services. O `GlobalScopeTenant` planejado precisa ser implementado logo no início da v6, antes do billing (porque o billing multiplica o número de tenants e de usuários por tenant).

### 3.3 O driver fiscal não tem sequer ambiente de homologação

A migração `fis_documentos_fiscais` tem `status` (RASCUNHO, PROCESSANDO, AUTORIZADO, REJEITADO, CANCELADO), `mensagem_sefaz`, `chave_acesso` e `url_xml` — o schema está preparado para eventos reais. Mas não há: ambiente de homologação SEFAZ (AMSP-SP/SEFAZ-RJ), geração XML com xsd, DANFE com QR Code (NT 2015.002), assinatura A1, contingência, cancelamento, CCe ou inutilização. O `status` CANCELADO e REJEITADO existem na tabela, mas **nenhum endpoint da API implementa cancelamento ou CCe**. O "próximo passo [CRÍTICO]" do mapa captura bem o gap; apenas reforço que o esforço do driver real é da ordem de semanas, não dias — contingência EPEC + assinatura XML + SEFAZ é um trabalho de integração pesado por si só.

### 3.4 O DP real ainda não cobre férias, afastamentos e rescisão

O módulo de DP cobre ficha funcional, escala, ponto, banco de horas, certificação e holerite — e o código confirma exatamente isso. Como consequência prática: o holerite gerado hoje **não calcula 13º, férias + 1/3, INSS, IRRF, FGTS nem encargos**; a "provisão no Contas a Pagar" é o valor bruto da folha, não o custo real com encargos. Para a venda inicial isso é suficiente (o cliente usa o sistema como gestão + espelho para o contador), mas o `revisar.md` deve manter a nota de que o holerite é "interno/gerencial" — nunca tratar como folha oficial para pagamento até a v7, quando rescisão/encargos entrarem.

### 3.5 Observações menores de consistência

| Achado no código | Impacto |
| --- | --- |
| `MockFiscalDriver` gera número de nota com `rand()` | Em produção real, isso geraria numeração duplicada/conflitante na SEFAZ — reforça a urgência do driver real |
| `rh_colaboradores` sem campos de benefícios/VT/VR | Coerente com o "DP gerencial"; registrar explicitamente no roadmap como pendência de DP completo |
| Routes globais sem prefixo de tenant além do middleware de role | Sem problema enquanto houver filtro manual em todos os services; o GlobalScope resolve |
| `bootstrap/cache` e `storage/framework/views` incluídos no consolidado | Artefatos gerados — não versionar; o `.gitignore` já os cobre, apenas atenção ao histórico |

## 4. Veredito e próximo passo recomendado

O projeto está em um estado **sólido para um MVP multi-tenant de ERP**: a arquitetura modular é real (não é só marketing no documento), a régua de maturidade do `revisar.md` agora corresponde ao código, e as três decisões mais importantes da v6 — `GlobalScopeTenant` + testes de invasão cruzada, driver fiscal real com ciclo de vida completo, e billing/feature flags — estão corretamente classificadas como críticas.

A sequência de implementação que eu manteria, agora verificada contra o código real:

1. **`GlobalScopeTenant` + suíte de testes de isolamento** (o risco crítico nº 1, ainda sem nenhuma mitigação no código);
2. **Suite mínima de testes automatizados** para os modules 🟡 (começando por Ponto — imutabilidade — e Fiscal — idempotência de emissão);
3. **Driver fiscal real** (A1, XML assinado, DANFE, SEFAZ homologação → produção, contingência EPEC, cancelamento, CCe, inutilização, guarda de XML por 5 anos);
4. **Billing + feature flags + cotas (3GB/20GB/100GB) + soft-lock** — o schema `sis_planos` ainda não existe no banco, então é criação limpa;
5. **Motor de alçadas simples + MFA TOTP para ADMIN/FINANCEIRO**;
6. **Exportação contábil SPED/CSV** (o endpoint `/api/v1/exportacao-contabil` é criação nova, sem dependências);
7. **Evidências de OS + assinatura jurídica (MP 2.200-2)** e **Portal do Cliente com token temporário** — os dois diferenciais comerciais;
8. **DP completo (férias, rescisão, encargos)** — para o holerite deixar de ser "gerencial" e virar folha válida.

Uma última recomendação de governança: mantenha o `revisar.md` como documento vivo no repositório (ele já está dentro de `scalle-erp/`, o que é bom) e trate cada entrada da tabela "Próximo Passo" como **tarefa rastreável** — cada item migrando de nível deve ter o commit e o teste que provam a migração. É isso que transforma o mapa de um retrato bonito em um instrumento de auditoria de arquitetura.
