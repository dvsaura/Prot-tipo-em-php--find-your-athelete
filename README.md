 FIND YOUR ATHLETE

 Descrição do Projeto

O **FIND YOUR ATHLETE** é uma plataforma web desenvolvida em **PHP**, utilizando a arquitetura **MVC (Model-View-Controller)**, com o objetivo de aproximar atletas e avaliadores esportivos.

A plataforma permite que **atletas** divulguem seu perfil esportivo, habilidades e experiências, enquanto **avaliadores** (clubes, técnicos e olheiros) podem buscar talentos e entrar em contato com potenciais candidatos.

Inspirado em redes sociais e plataformas de recrutamento esportivo, o sistema busca facilitar a descoberta de novos talentos e fortalecer a conexão entre atletas e oportunidades no esporte.



Tecnologias Utilizadas

- PHP 8.2+
- MySQL
- HTML5
- CSS3
- JavaScript
- Apache (XAMPP)

---

Pré-requisitos

Para executar o projeto localmente, é necessário possuir:

- PHP 8.2 ou superior
- MySQL
- Apache (XAMPP)
- Git 


Instalação

1. Clone o repositório

bash
git clone https://github.com/SEU-USUARIO/find-your-athlete.git

2. Copie o projeto

Coloque a pasta do projeto dentro do diretório do servidor local (por exemplo, `htdocs` no XAMPP).

3. Inicie os serviços

Abra o XAMPP e inicie:
- Apache
- MySQL

4. Configure o banco de dados
- Crie um banco de dados no MySQL.
- Importe o arquivo `.sql` disponível no projeto.

5. Configure a conexão

Edite o arquivo:
text
config/database.php e informe as credenciais do seu banco de dados.

6. Execute o projeto

Acesse no navegador:

```text
http://localhost:8080
```

---

 Como utilizar

1. Inicie o Apache e o MySQL.
2. Configure o banco de dados.
3. Acesse:

```text
http://localhost:8080
```

4. Faça login ou cadastre-se como atleta ou avaliador.
5. Utilize as funcionalidades disponíveis conforme o perfil do usuário.

---

 Arquitetura

O projeto segue o padrão **MVC (Model-View-Controller)**, organizando a aplicação em:

- **Model:** acesso e manipulação dos dados.
- **View:** interface do usuário.
- **Controller:** lógica de negócio e controle das requisições.

 Objetivo

O FIND YOUR ATHLETE tem como propósito facilitar a conexão entre atletas e avaliadores esportivos, proporcionando um ambiente digital para divulgação de talentos e identificação de oportunidades no esporte.
