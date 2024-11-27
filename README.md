# Sistema de Gerenciamento de Comentários

## Descrição
Este sistema permite a gestão de usuários e comentários. Os usuários podem se cadastrar, autenticar e interagir com os comentários, com funcionalidades como edição, histórico e exclusão, além de um controle de acesso baseado em permissões.

## Requisitos

### Requisitos Obrigatórios
1. **Gerenciamento de Usuários:**
   - O sistema permite que os usuários se cadastrem e editem seu cadastro. 
   - A rota `/api/register` permite o cadastro de novos usuários e a rota `/api/me` permite que usuários autenticados editem suas informações.
   
2. **Autenticação via E-mail e Senha:**
   - O sistema permite a autenticação de usuários através de e-mail e senha.
   - A rota `/api/auth` realiza o login, retornando um token de autenticação que será utilizado nas requisições subsequentes.
   - A autenticação é feita via token em todas as requisições após o login (utilizando `auth:sanctum`).

3. **Gerenciamento de Comentários:**
   - Os comentários podem ser acessados por qualquer usuário (rota `/api/comments`).
   - Apenas usuários autenticados podem criar comentários, utilizando a rota `/api/comments` (POST).
   - O autor do comentário e a data e hora da postagem são retornados nas respostas.
   
4. **Controle de Acesso:**
   - Usuários autenticados podem criar, editar e excluir seus próprios comentários.
   - A exclusão de todos os comentários só é permitida ao administrador.

### Requisitos Desejáveis
1. **Edição de Comentários:**
   - O sistema permite que os usuários editem seus próprios comentários.
   - A data de criação e a data da última edição do comentário são exibidas.

2. **Histórico de Edições de Comentários:**
   - O sistema mantém um histórico de edições de comentários, permitindo que o usuário veja as versões anteriores de seu comentário.
   - A rota `/api/comments/{commentId}/history` exibe o histórico de edições de um comentário específico.

3. **Exclusão de Comentários:**
   - Os usuários podem excluir seus próprios comentários.
   - O administrador tem permissão para excluir todos os comentários através da rota `/api/comments` (DELETE).

4. **Criptografia de Senha:**
   - As senhas dos usuários são criptografadas ao serem armazenadas no banco de dados, utilizando o método `Hash::make` do Laravel.

5. **Testes Automatizados:**
   - O sistema inclui testes automatizados na camada de **serviço** utilizando PHPUnit para validar o comportamento das funcionalidades.
   - Os testes garantem que as funcionalidades de criação, edição, exclusão de comentários, bem como a autenticação de usuários, estejam funcionando corretamente.
   - Os testes são executados com o comando `php artisan test`.

## Endpoints da API

### Rota de Autenticação
- **POST** `/api/auth`: Autentica o usuário com e-mail e senha, retornando um token de autenticação.
- **POST** `/api/register`: Realiza o cadastro de um novo usuário.

### Rota de Comentários
- **GET** `/api/comments`: Retorna todos os comentários disponíveis.
- **GET** `/api/comments/{commentId}`: Retorna um comentário específico pelo seu ID.
- **POST** `/api/comments`: Permite que um usuário autenticado crie um novo comentário.
- **GET** `/api/comments/{commentId}/history`: Exibe o histórico de edições de um comentário.
- **PUT** `/api/comments/{commentId}`: Permite que um usuário autenticado edite seu próprio comentário.
- **DELETE** `/api/comments`: Permite que um administrador exclua todos os comentários.
- **DELETE** `/api/comments/{commentId}`: Permite que um usuário exclua seu próprio comentário.

### Rota de Usuário
- **GET** `/api/me`: Retorna as informações do usuário autenticado.
- **PUT** `/api/me`: Permite que o usuário autenticado edite suas informações.
- **PATCH** `/api/change-password`: Permite que o usuário altere sua senha.
- **POST** `/api/logout`: Realiza o logout do usuário autenticado.

## Tecnologias Utilizadas
- **Laravel 8**: Framework PHP para desenvolvimento da API.
- **Sanctum**: Para autenticação de tokens.
- **MySQL**: Banco de dados utilizado para armazenar os dados dos usuários e comentários.
- **PHPUnit**: Framework para testes automatizados.

## Como Rodar o Projeto

### Requisitos
- PHP 8.0
- Composer
- MySQL ou outro banco de dados configurado

### Passos para Execução

1. Clone o repositório:
```sh
git clone https://github.com/iFHA/Selecao-Back-End.git
```
2. Instale as dependências do projeto:
```sh
composer install
```
3. Renomeie o arquivo .env.example para .env e configure-o com suas credenciais do banco de dados:
```dosini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nome_do_banco
DB_USERNAME=usuario
DB_PASSWORD=senha
```
4. Gere a chave do projeto
```sh
php artisan key:generate
```
5. Execute as migrações para criar as tabelas necessárias:
```sh
php artisan migrate
```
6. Também pode ser feito um seed após executar as migrations, que irá gerar um usuário admin(email "admin@gmail.com" e senha "password") e alguns comentários gerados pela biblioteca faker
```sh
php artisan db:seed
```
7. Para rodar os testes automatizados com PHPUnit:
```sh
php artisan test
```
8. Inicie o servidor de desenvolvimento:
```sh
php artisan serve
```
Agora, basta acessar alguma das rotas listadas anteriormente, através do endereço informado pela saída do comando acima, como por exemplo: verbo HTTP GET, header "Accept: application/json" e endereço: http://127.0.0.1:8000/api/comments.

Foram disponibilidados na raiz desse projeto arquivos de "collection" e "environment" do postman, contendo informações mais detalhadas sobre as rotas, e que podem ser importados no postman para mais facilidade no momento de fazer as requisições.

## Considerações Finais
Este sistema foi desenvolvido com foco em segurança e desempenho, utilizando boas práticas como a criptografia de senhas e a autenticação via token. A implementação de testes automatizados garante que as funcionalidades sejam validadas de forma contínua. A estrutura modular do código facilita a manutenção e a expansão do sistema.

## Extra
## Como Rodar o Projeto Com Docker Compose

1. Clone o projeto
```sh
git clone https://github.com/iFHA/Selecao-Back-End.git
```
2. Acesse a pasta do projeto
```sh
cd Selecao-Back-End/
```
3. Crie o Arquivo .env
```sh
cp .env.example .env
```
4. Atualize as variáveis de ambiente no arquivo .env
```dosini
APP_NAME="Sistema de Comentários"
APP_URL=http://localhost:8989

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=root
```
5. Suba os containers do projeto
```sh
docker-compose up -d
```
6. Acesse o container
```sh
docker-compose exec app bash
```
7. Instale as dependências do projeto
```sh
composer install
```
8. Gere a chave do projeto Laravel
```sh
php artisan key:generate
```
9. Rode as migrações
```sh
php artisan migrate
```
10. Também pode ser feito um seed após executar as migrations, que irá gerar um usuário admin(email "admin@gmail.com" e senha "password") e alguns comentários gerados pela biblioteca faker
```sh
php artisan db:seed
```
11. Para rodar os testes automatizados com PHPUnit:
```sh
php artisan test
```
O servidor de desenvolvimento poderá ser acessado por meio do endereço http://127.0.0.1:8989
E a api por meio do endereço http://127.0.0.1:8989/api
