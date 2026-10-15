# Projeto Biblioteca

Sistema de Gestão de Biblioteca desenvolvido em **Laravel**, com autenticação segura **Laravel Jetstream (2FA)**, interface moderna baseada em **DaisyUI / Tailwind CSS**, integração com a **Google Books API**, controlo de requisições e exportação para Excel.

## Funcionalidades Principais
- **Autenticação Avançada:** Sistema completo com suporte a Autenticação de 2 Fatores (2FA).
- **Controlo de Acessos (RBAC):** Dois papéis de permissões estritos: **Administradores** (gestão de obras, receção de livros, importação externa e criação de novos administradores) e **Cidadãos** (requisição de exemplares disponíveis e consulta do seu histórico). Novos registos públicos assumem automaticamente o papel de Cidadão.
- **Estante Interativa ("Armário de Livros"):** Visualização de lombadas clássicas com texturas encadernadas dinâmicas, crachás de disponibilidade em tempo real (*Disponível* / *Requisitado*), pesquisa global e filtros avançados.
- **Modal "Livro Aberto Real":** Detalhe da obra com capa, sinopse, dados do autor e editora, botão reativo de requisição e histórico cronológico expansível de empréstimos daquele exemplar.
- **Módulo de Requisições e Empréstimos:** Numeração sequencial única, snapshot fotográfico do cidadão, data de início e prazo de devolução (+5 dias), com garantia de concorrência (`retry(3, ...)`) e limite estrito de 3 livros ativos em simultâneo por cidadão.
- **Ciclo de Devolução e Cancelamento:** Confirmação de receção exclusiva por Administradores com registo da data real e cálculo automático de dias decorridos. Mecanismo de cancelamento de requisições ativas por cidadãos e administradores através de modal temático.
- **Painel de Indicadores (KPIs):** Três métricas no topo do menu de requisições (*Requisições Ativas*, *Movimento nos últimos 30 dias* e *Livros entregues Hoje*), segregadas com escopo pessoal para Cidadãos e visão global para Administradores.
- **Sistema de Emails e Lembretes (Scheduler):** Notificação instantânea com capa do livro incorporada no ato da requisição (para cidadão e administradores) e comando agendado de alerta cordial na véspera da entrega (D-1).
- **Integração com a Google Books API:**
  - Pesquisa no catálogo mundial com qualificadores específicos (*Geral*, *Título*, *Autor*, *ISBN* e *Editora*);
  - Paginação externa real da Google Books (`startIndex`);
  - Deteção inteligente de obras já existentes no acervo local (*"✓ Já no Acervo"*);
  - Modal de pré-visualização completa com sinopse integral sem cortes e capa ampliada antes de importar;
  - Desduplicação canónica de autores e editoras para evitar duplicações com pontuações ou espaçamentos diferentes (ex.: *J. R. R. Tolkien* vs *J.R.R. Tolkien*);
  - Tratamento comercial fidedigno com suporte a *"Preço sob consulta"*;
  - Sincronização não-destrutiva de metadados via interface (`🔄 Sync`) e via comando Artisan em lote;
  - Utilização direta de capas em CDN de alta velocidade.
- **Módulos CRUD Completos:** Gestão isolada de Editoras, Autores e Livros, com suporte a pesquisa, ordenação e paginação (24 itens por página).
- **Exportação Excel:** Capacidade de exportar os dados da base de dados de Livros diretamente para ficheiro `.xlsx`, incluindo a nova coluna identificadora de `'Origem'` (*Acervo Local* vs *Google Books*).
- **Segurança & Cifragem:** Todos os dados sensíveis e textuais (nomes, ISBNs, bibliografias, preços, fotos e logótipos) encontram-se 100% cifrados na base de dados através de Eloquent Casts (`encrypted`). Proteção de atributos sensíveis contra vulnerabilidades de *mass-assignment*.


## Arquitetura de Segurança, Cifragem e APP_KEY

- **Cifragem de Dados Sensíveis:** Todos os dados do catálogo, incluindo nomes de livros, autores, editoras, ISBNs, bibliografias, preços, fotografias e logótipos, são armazenados de forma cifrada na base de dados MySQL. Esta implementação é realizada através dos *Eloquent Casts* do Laravel, utilizando o cast `encrypted` nos campos definidos.
- **Proteção da `APP_KEY`:** A `APP_KEY`, armazenada no ficheiro `.env`, constitui a chave criptográfica utilizada pelo Laravel para a cifragem e decifragem dos dados. A sua alteração ou regeneração após a existência de dados cifrados invalida a capacidade de os decifrar, podendo resultar no erro `The MAC is invalid`. Por este motivo, o ficheiro `.env` encontra-se excluído do controlo de versões através do `.gitignore`, evitando a exposição da chave criptográfica.
- **Processamento de Pesquisa e Ordenação em Memória:** Como os dados são armazenados na base de dados sob a forma de *ciphertext*, o MySQL não consegue aplicar diretamente operações como `LIKE` ou `ORDER BY` sobre os valores originais. Para ultrapassar esta limitação, a aplicação recupera e decifra os registos necessários, realizando posteriormente as operações de pesquisa, filtragem e ordenação em memória através das Coleções do Laravel. A paginação dos resultados é assegurada através da classe `LengthAwarePaginator`.
- **Deteção de Duplicados e Limitação Conhecida:** A desduplicação automática e indexada no MySQL é realizada com base na coluna indexada `isbn_hash` (Blind Index via HMAC-SHA256). Como decisão de arquitetura, obras desprovidas de ISBN formal no catálogo da Google Books podem ser importadas manualmente sem bloqueio por título, prevenindo falsos positivos entre autores distintos com obras homónimas (ex.: títulos idênticos como *"Poesias"* ou *"Sonetos"* de escritores diferentes).


## Requisitos do Sistema
- PHP 8.3 ou superior
- Composer
- Node.js e NPM
- Base de dados MySQL (Laragon / XAMPP)

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
5. Criar a base de dados `biblioteca` no MySQL.
6. Executar as migrações e o povoamento inicial.
   *(Nota: O comando `migrate:fresh --seed` é destrutivo e elimina todos os dados existentes, sendo adequado apenas para instalação limpa e desenvolvimento).*
   ```bash
   php artisan migrate:fresh --seed
   ```
7. Compilar os assets frontend:
   ```bash
   npm run build
   ```
8. Iniciar o servidor local:
   ```bash
   php artisan serve
   ```

## Configuração do Administrador
As credenciais da conta de administrador devem ser configuradas localmente no ficheiro `.env` através das variáveis:
* `ADMIN_NAME`
* `ADMIN_EMAIL`
* `ADMIN_PASSWORD`

Novos Administradores apenas podem ser criados a partir do painel de administração (`/admin/utilizadores/criar`) por utilizadores autenticados com o papel de Administrador.


## Comandos de Automação e Sincronização (Artisan)
- **Lembrete de Devolução (D-1):** Envia um email cordial aos cidadãos com requisições a terminar no dia seguinte:
  ```bash
  php artisan requisicoes:enviar-lembretes
  ```

- **Sincronização em Lote com a Google Books:** Enriquece e preenche campos em falta no acervo local sem substituir dados existentes:
  ```bash
  php artisan livros:sincronizar-google --limit=10
  ```


## Testes Automatizados
O projeto conta com uma suite completa de testes de integração e testes unitários com cobertura de 100% de aprovação (44 testes e 133 asserções), incluindo simulações de API externa com `Http::fake()`:
```bash
php artisan test
```


## Projeto desenvolvido para
Este projeto foi desenvolvido por **Bruno Pinto** para a **INOVCORP**, no âmbito do estágio curricular.