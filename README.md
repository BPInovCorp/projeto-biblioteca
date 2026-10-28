# Projeto Biblioteca

Sistema de Gestão de Biblioteca e Comércio Literário desenvolvido em **Laravel 13**, com autenticação segura **Laravel Jetstream (2FA)**, interface moderna baseada em **DaisyUI / Tailwind CSS**, integração com a **Google Books API**, motor de recomendação inteligente, controlo de requisições, moderação de críticas literárias, exportação para Excel e **módulo completo de e-commerce com pagamentos Stripe Sandbox**.

## Funcionalidades Principais

### 1. Autenticação & Controlo de Acessos (RBAC)
- **Autenticação Segura com 2FA:** Suporte nativo a Autenticação de Dois Fatores (Two-Factor Authentication).
- **Segregação Estrita de Papéis:**
  - **Administradores:** Gestão de obras, receção de livros devolvidos, moderação de críticas, auditoria de encomendas comerciais, importação da Google Books e criação de novos administradores;
  - **Cidadãos:** Requisição de exemplares disponíveis, consulta do histórico pessoal, submissão de avaliações, subscrição de alertas de disponibilidade e aquisição de livros via carrinho de compras.
  - Novos registos públicos assumem automaticamente o papel de Cidadão. Novos Administradores só podem ser criados a partir do painel interno por outro Administrador autenticado.

### 2. Estante Interativa & Catálogo Público
- **Estante de Madeira ("Armário de Livros"):** Lombadas dinâmicas com texturas clássicas, pesquisa global, acordeão de filtros cruzados e crachás de disponibilidade em tempo real (*Disponível* / *Requisitado*).
- **Modal "Livro Aberto Real":** Visualização detalhada estilo livro clássico com capa, sinopse, dados do autor e editora, crachá de preço comercial, botão reativo de requisição e seletor de quantidade com adição direta ao carrinho de compras.
- **Navegação Híbrida (SPA) em Livros Relacionados:** As sugestões de leitura no modal abrem de forma fluida instantaneamente (via DOM ou endpoint JSON otimizado `/livros/{livro}/detalhes`) sem recarregamento de página.

### 3. Módulo de Requisições & Devoluções (S2)
- **Regras Estritas de Empréstimo:** Limite máximo de 3 livros ativos em simultâneo por cidadão e prazo automático de devolução (+5 dias).
- **Numeração Sequencial Atómica:** Identificadores únicos (`#0001`, `#0002`) protegidos contra condições de corrida concorrentes com mecanismo de repetição (`retry(3, ...)`).
- **Ciclo de Devolução:** Confirmação de receção exclusiva por Administradores com registo da data real de entrega e cálculo automático de dias decorridos.
- **Cancelamento Temático:** Cidadãos podem cancelar as suas requisições ativas e Administradores podem cancelar qualquer requisição, libertando a obra de imediato no acervo.
- **Painel de Indicadores (KPIs):** Três cartões de topo (*Requisições Ativas*, *Movimento nos últimos 30 dias* e *Livros entregues Hoje*), com escopo pessoal para Cidadãos e visão global para Administradores.
- **Notificações por Email Assíncronas (`ShouldQueue`):** Disparo assíncrono de confirmações com capa incorporada e comando agendado de lembrete cordial na véspera da entrega (D-1).

### 4. Integração com a Google Books API (S3)
- **Pesquisa Externa com Qualificadores:** Modos de consulta específicos (*Geral*, *Título*, *Autor*, *ISBN* e *Editora*).
- **Paginação Externa Real:** Navegação por blocos de resultados através do parâmetro `startIndex` da Google Books.
- **Deteção Inteligente de Acervo:** Identificação visual se a obra encontrada já existe na biblioteca local (*"✓ Já no Acervo"*).
- **Modal de Pré-visualização:** Consulta de sinopses completas sem limite de corte e capas em alta resolução antes da importação.
- **Desduplicação Canónica:** Algoritmo que normaliza chaves alfanuméricas de autores e editoras, evitando registos repetidos com pontuações ou espaços diferentes (ex.: *J. R. R. Tolkien* vs *J.R.R. Tolkien*).
- **Sincronização Não-Destrutiva:** Enriquecimento exclusivo de campos vazios sem sobrescrever dados locais já existentes.

### 5. Críticas Literárias & Moderação Administrativa (S4)
- **Submissão Pós-Entrega:** Cidadãos podem avaliar e comentar livros que tenham efetivamente requisitado e devolvido (classificação de 1 a 5 estrelas e crítica textual).
- **Moderação Obrigatória:** Todas as avaliações nascem com o estado `'suspenso'`, disparando um email assíncrono de alerta aos Administradores com link direto para moderação.
- **Painel de Moderação (`/admin/comentarios`):** Ecrã com abas por estado (*Suspensas*, *Ativas*, *Recusadas*, *Todas*), aprovação reversível e recusa com preenchimento obrigatório de justificação (5 a 500 caracteres).
- **Feedback por Email ao Cidadão:** Disparo automático notificando a aprovação ou recusa (com a justificação do Administrador incluída em caso de rejeição).
- **Publicação Condicional no Catálogo:** Apenas avaliações ativas são exibidas no modal de leitura na estante, garantindo total confidencialidade com renderização segura via nós do DOM (`textContent` anti-XSS).

### 6. Motor Inteligente de Livros Relacionados (S4)
- **Análise Semântica de Conteúdo:** Algoritmo de processamento textual que tokeniza títulos e sinopses, elimina *stop-words* da língua portuguesa e calcula o **Índice de Similaridade de Jaccard** ($\frac{|A \cap B|}{|A \cup B|}$).
- **Ponderação Híbrida & Autoexclusão:** Aplicação de bónus para obras com autores partilhados, desempate determinístico e garantia de que o livro consultado nunca surge nas suas próprias recomendações com cache de 6 horas.

### 7. Alertas de Disponibilidade / Lista de Espera (S4)
- **Subscrição em Obras Requisitadas:** Quando um livro se encontra indisponível, o Cidadão autenticado pode clicar em *"🔔 Notificar-me quando disponível"*.
- **Prevenção de Duplicados & Reativação:** Índice único composto `['user_id', 'livro_id']` na tabela `alertas`, com suporte a reativação inteligente de alertas previamente notificados caso o livro volte a ficar indisponível no futuro.
- **Disparo Imediato na Devolução ou Cancelamento:** No momento exato em que a obra volta a ficar disponível, o sistema deteta todos os leitores em lista de espera e dispara o email `LivroDisponivelMail` de forma assíncrona.

### 8. Módulo Comercial, Carrinho de Compras & Stripe Sandbox (S5)
- **Passo 1: Carrinho de Compras (`/carrinho`):**
  - Aglutinação em tempo real de exemplares adicionados via catálogo com crachá dinâmico de contagem na barra de navegação superior.
  - Tabela com capas, autores, controlos incrementais de quantidade (`+` / `-`), subtotais explícitos com fórmula (`Preço × Quantidade`) e rodapé com totais acumulados.
  - Modal DaisyUI customizado (tema *Autumn*) para esvaziamento em massa e remoção individual de artigos.
- **Passo 2: Morada de Entrega (`/checkout/morada`):**
  - Recolha e validação rigorosa de morada no servidor (nome, rua, código postal, cidade, país e contacto telefónico).
  - Criação transacional atómica da encomenda preliminar em estado `pendente`, com identificador único não-previsível (`ENC-AAAAMMDD-XXXXXX`) e **snapshot imutável de preços em cêntimos inteiros** (`preco_unitario_centimos`), garantindo integridade contra alterações posteriores no catálogo.
- **Passo 3: Pagamento Seguro com Stripe Sandbox:**
  - Redirecionamento dinâmico para o Stripe Checkout oficial em conformidade com as diretivas PCI-DSS e métodos dinâmicos de pagamento (*Dynamic Payment Methods*).
  - Suporte completo a cartões virtuais de teste (3D Secure, cartões válidos e recusas).
- **Webhook Idempotente & Fonte Única da Verdade (`/stripe/webhook`):**
  - Verificação criptográfica do cabeçalho `Stripe-Signature` e isenção de CSRF em `bootstrap/app.php`.
  - Confirmação estrita de `payment_status === 'paid'` nos eventos `checkout.session.completed` e `checkout.session.async_payment_succeeded`.
  - **Idempotência Persistente na Base de Dados:** Tabela `stripe_webhook_events` com restrição `UNIQUE(event_id)`, transação com bloqueio pessimista (`lockForUpdate`), registo do `stripe_payment_intent_id` e remoção cirúrgica de artigos do carrinho através de `removerItensComprados()`, preservando eventuais artigos adicionados em sessões concorrentes.
- **Ecrãs de Retorno (`/checkout/{id}/sucesso` e `/cancelado`):** Desacoplamento do redirecionamento HTTP da verdade do webhook, com apresentação do estado real (`pago` vs `a processar`) e opções para retomar o pagamento ou regressar ao carrinho.

### 9. Backoffice Administrativo de Encomendas (S5)
- **Painel de Controlo Integrado:** Novo módulo de Encomendas no `/dashboard` dos Administradores.
- **Listagem Segregada (`/admin/encomendas`):**
  - Abas de navegação rápida com contadores agregados diretamente na base de dados (*Todas*, *Pagas*, *Pendentes*).
  - Pesquisa avançada parametrizada (número da encomenda, nome do destinatário ou e-mail do cidadão) com preservação de filtros durante a paginação (`withQueryString()`).
- **Ficha de Auditoria Detalhada (`/admin/encomendas/{id}`):**
  - Consulta detalhada dos dados do cidadão, morada de entrega, identificadores de transação Stripe (`session_id`, `payment_intent_id`) e listagem de artigos com preços históricos imutáveis.

### 10. Automação de Lembrete de Carrinho Abandonado (S5)
- **Regra Estrita de 1 Hora de Inatividade:** Deteção de cidadãos com livros no carrinho cujo último `updated_at` ultrapasse 60 minutos sem alterações.
- **Salvaguardas Concorrenciais:** Exclusão de cidadãos que tenham concluído compras na última hora e controlo atómico por timestamp (`aviso_abandono_enviado_em`) que impede envios duplicados, rearmando o ciclo apenas caso o utilizador volte a interagir com o carrinho.
- **Mailable Assíncrono em Fila (`ShouldQueue`):** O comando Artisan `biblioteca:lembrete-carrinho-abandonado` despacha o `CarrinhoAbandonadoMail` para a fila, renderizando uma mensagem acolhedora em Português Europeu com a tabela dos livros esquecidos e botão de retoma direta.
- **Agendamento no Scheduler:** Execução horária automática configurada em `routes/console.php`.


## Arquitetura de Segurança, Cifragem e APP_KEY

- **Cifragem de Dados Sensíveis:** Todos os dados do catálogo, incluindo nomes de livros, autores, editoras, ISBNs, bibliografias, preços, fotografias e logótipos, são armazenados de forma cifrada na base de dados MySQL através do cast `encrypted`.
- **Proteção da `APP_KEY`:** A `APP_KEY`, armazenada no ficheiro `.env`, constitui a chave criptográfica utilizada pelo Laravel para a cifragem e decifragem dos dados. Por este motivo, o ficheiro `.env` encontra-se protegido pelo `.gitignore`.
- **Blind Index (`isbn_hash`):** Implementação de coluna determinística indexada no MySQL (HMAC-SHA256) em migração isolada, viabilizando pesquisas de duplicados instantâneas sem necessidade de carregar coleções em memória (`Livro::all()`).
- **Segurança Financeira em Cêntimos Inteiros:** Todos os valores monetários do carrinho, encomendas e linhas de compra são estritamente geridos em cêntimos inteiros (`total_centimos`, `preco_unitario_centimos`), erradicando falhas de precisão de vírgula flutuante e assegurando comunicação nativa com a API da Stripe.
- **Idempotência Criptográfica de Webhooks:** Proteção integral contra retransmissões acidentais da rede através de registos únicos de `event_id` processados dentro de transações de base de dados.
- **Proteção de Mass-Assignment:** O atributo sensível `role` encontra-se estritamente excluído do `$fillable` do modelo `User`, sendo atribuído unicamente no servidor através de `forceCreate()` / `forceFill()`.


## Requisitos do Sistema
- PHP 8.3 ou superior
- Composer
- Node.js e NPM
- Base de dados MySQL (Laragon / XAMPP)
- Stripe CLI (para teste e encaminhamento local de webhooks)

## Passos para Instalação
1. Clonar o repositório.
2. Copiar o ficheiro de configuração (PowerShell):
   ```powershell
   Copy-Item .env.example .env
   ```
3. Instalar as dependências PHP e JavaScript:
   ```bash
   composer install
   npm install
   ```
4. Gerar a chave da aplicação:
   ```bash
   php artisan key:generate
   ```
5. Configurar as credenciais da Stripe no ficheiro `.env`:
   ```env
   STRIPE_KEY=pk_test_...
   STRIPE_SECRET=sk_test_...
   STRIPE_WEBHOOK_SECRET=whsec_...
   ```
6. Criar a base de dados `biblioteca` no MySQL.
7. Executar as migrações e o povoamento inicial:
   ```bash
   php artisan migrate:fresh --seed
   ```
8. Compilar os assets frontend:
   ```bash
   npm run build
   ```
9. Iniciar o servidor local:
   ```bash
   php artisan serve
   ```

## Comandos de Fila, Automação, Stripe e Sincronização
- **Processamento de Fila de Emails (Queue):** Obrigatório para o envio assíncrono de notificações de requisição, moderação, alertas e lembretes de carrinho abandonado:
  ```bash
  php artisan queue:work
  ```
- **Escuta e Encaminhamento de Webhooks da Stripe (Local):** Redireciona eventos da Stripe Sandbox para o endpoint local:
  ```powershell
  stripe listen --all-snapshot --forward-to localhost:8000/stripe/webhook
  ```
- **Lembrete de Carrinho Abandonado (1 hora de inatividade):** Verifica e despacha emails a utilizadores com livros esquecidos no carrinho:
  ```bash
  php artisan biblioteca:lembrete-carrinho-abandonado
  ```
- **Lembrete de Devolução de Livros (D-1):** Envia um email cordial aos cidadãos com requisições a terminar no dia seguinte:
  ```bash
  php artisan requisicoes:enviar-lembretes
  ```
- **Sincronização em Lote com a Google Books:** Enriquece e preenche campos em falta no acervo local sem substituir dados existentes:
  ```bash
  php artisan livros:sincronizar-google --limit=10
  ```


## Testes Automatizados
O projeto conta com uma suite de testes de integração e testes unitários com **100% de aprovação (102 testes aprovados, 7 ignorados e 358 asserções com zero falhas em ~4.9s)**:
```bash
php artisan test
```


## Projeto desenvolvido para
Este projeto foi desenvolvido por **Bruno Pinto** para a **INOVCORP**, no âmbito do estágio curricular.