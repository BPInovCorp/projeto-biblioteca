# Projeto Biblioteca

Sistema de Gestão de Biblioteca desenvolvido em **Laravel**, com autenticação segura **Laravel Jetstream (2FA)**, interface moderna baseada em **DaisyUI / Tailwind CSS** e gestão completa de acervo literário com exportação para Excel.

## Funcionalidades Principais
- **Autenticação Avançada:** Sistema completo com suporte a Autenticação de 2 Fatores (2FA).
- **Controlo de Acessos (RBAC):** Dois papéis de permissões estritos: **Administradores** (gestão de obras, receção de livros e criação de novos administradores) e **Cidadãos** (requisição de exemplares disponíveis e consulta do seu histórico). Novos registos públicos assumem automaticamente o papel de Cidadão.
- **Estante Interativa ("Armário de Livros"):** Visualização de lombadas clássicas com texturas encadernadas dinâmicas, crachás de disponibilidade em tempo real (*Disponível* / *Requisitado*), pesquisa global e filtros avançados.
- **Modal "Livro Aberto Real":** Detalhe da obra com capa, sinopse, dados do autor e editora, botão reativo de requisição e histórico cronológico expansível de empréstimos daquele exemplar.
- **Módulo de Requisições e Empréstimos:** Numeração sequencial única, snapshot fotográfico do cidadão, data de início e prazo de devolução (+5 dias), com garantia de concorrência e limite estrito de 3 livros ativos em simultâneo por cidadão.
- **Ciclo de Devolução e Cancelamento:** Confirmação de receção exclusiva por Administradores com registo da data real e cálculo automático de dias decorridos. Mecanismo de cancelamento de requisições ativas por cidadãos e administradores através de modal temático.
- **Painel de Indicadores (KPIs):** Três métricas no topo do menu de requisições (*Requisições Ativas*, *Movimento nos últimos 30 dias* e *Livros entregues Hoje*), segregadas com escopo pessoal para Cidadãos e visão global para Administradores.
- **Sistema de Emails e Lembretes (Scheduler):** Notificação instantânea com capa do livro incorporada no ato da requisição (para cidadão e administradores) e comando agendado de alerta cordial na véspera da entrega (D-1).
- **Módulos CRUD Completos:** Gestão isolada de Editoras, Autores e Livros, com suporte a pesquisa, ordenação e paginação (24 itens por página).
- **Exportação Excel:** Capacidade de exportar os dados da base de dados de Livros diretamente para ficheiro `.xlsx`.
- **Segurança & Cifragem:** Todos os dados sensíveis e textuais (nomes, ISBNs, bibliografias, preços, fotos e logótipos) encontram-se 100% cifrados na base de dados através de Eloquent Casts (`encrypted`). Proteção de atributos sensíveis contra vulnerabilidades de *mass-assignment*.


## Arquitetura de Segurança, Cifragem e APP_KEY

- **Cifragem de Dados Sensíveis:** Todos os dados do catálogo, incluindo nomes de livros, autores, editoras, ISBNs, bibliografias, preços, fotografias e logótipos, são armazenados de forma cifrada na base de dados MySQL. Esta implementação é realizada através dos *Eloquent Casts* do Laravel, utilizando o cast `encrypted` nos campos definidos.
- **Proteção da `APP_KEY`:** A `APP_KEY`, armazenada no ficheiro `.env`, constitui a chave criptográfica utilizada pelo Laravel para a cifragem e decifragem dos dados. A sua alteração ou regeneração após a existência de dados cifrados invalida a capacidade de os decifrar, podendo resultar no erro `The MAC is invalid`. Por este motivo, o ficheiro `.env` encontra-se excluído do controlo de versões através do `.gitignore`, evitando a exposição da chave criptográfica.
- **Processamento de Pesquisa e Ordenação em Memória:** Como os dados são armazenados na base de dados sob a forma de *ciphertext*, o MySQL não consegue aplicar diretamente operações como `LIKE` ou `ORDER BY` sobre os valores originais. Para ultrapassar esta limitação, a aplicação recupera e decifra os registos necessários, realizando posteriormente as operações de pesquisa, filtragem e ordenação em memória através das Coleções do Laravel. A paginação dos resultados é assegurada através da classe `LengthAwarePaginator`.


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


## Sistema de Notificações por Email e Agendamento (Scheduler)
- **Email de Confirmação:** No ato da requisição, é disparado simultaneamente um email para o cidadão e para todos os administradores com os detalhes do empréstimo e a imagem da capa do livro embutida via MIME (`$message->embed()`).
- **Comando de Lembrete D-1:** Diariamente às 09:00, o comando agendado no scheduler pesquisa requisições ativas com devolução prevista para o dia seguinte e envia um lembrete cordial ao cidadão:
  ```bash
  php artisan requisicoes:enviar-lembretes
  ```


## Testes Automatizados
O projeto inclui uma suite completa de testes de integração (`tests/Feature/RequisicaoTest.php`) que valida a totalidade das regras de negócio, permissões, concorrência e comunicações:
```bash
php artisan test
```


## Projeto desenvolvido para
Este projeto foi desenvolvido por **Bruno Pinto** para a **INOVCORP**, no âmbito do estágio curricular.