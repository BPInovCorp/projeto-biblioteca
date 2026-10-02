# Projeto Biblioteca

Sistema de Gestão de Biblioteca desenvolvido em **Laravel**, com autenticação segura **Laravel Jetstream (2FA)**, interface moderna baseada em **DaisyUI / Tailwind CSS** e gestão completa de acervo literário com exportação para Excel.

## Funcionalidades Principais
- **Autenticação Avançada:** Sistema completo com suporte a Autenticação de 2 Fatores (2FA).
- **Controlo de Acessos (RBAC):** Diferenciação estrita entre Administradores (gestão de catálogo) e Leitores (perfil pessoal e estante).
- **Estante Interativa ("Armário de Livros"):** Visualização de lombadas clássicas com texturas encadernadas variadas, pesquisa global e filtragem avançada por ordenação (A-Z, ISBN) e exclusão de autores/editoras.
- **Modal "Livro Aberto Real":** Visualização detalhada estilo livro clássico com capa, sinopse, fotografia do autor e logótipo da editora.
- **Módulos CRUD Completos:** Gestão isolada de Editoras, Autores e Livros, com suporte a pesquisa, ordenação e paginação (24 itens por página).
- **Exportação Excel:** Capacidade de exportar os dados da base de dados de Livros diretamente para ficheiro `.xlsx`.
- **Segurança & Cifragem:** Todos os dados sensíveis e textuais (nomes, ISBNs, bibliografias, preços, fotos e logótipos) encontram-se 100% cifrados na base de dados através de Eloquent Casts (`encrypted`).


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


## Projeto desenvolvido para
Este projeto foi desenvolvido por **Bruno Pinto** para a **INOVCORP**, no âmbito do estágio curricular.