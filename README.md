# pfSense Captive Portal: Autocadastro Moderno com LGPD

Este projeto é uma solução de **Autocadastro em Etapa Única** para o Captive Portal do pfSense. Ele permite que os usuários se registrem e obtenham acesso à internet instantaneamente, sem a necessidade de pré-aprovação manual. O sistema cria ou atualiza as credenciais automaticamente no banco de dados do FreeRADIUS (MySQL/MariaDB) e armazena os dados coletados (leads) para fins de marketing e conformidade legal.

## 🚀 Novas Funcionalidades (v2026)

* **Interface Moderna:** Redesenhada do zero com **Bootstrap 4.5.3**, oferecendo um visual limpo e totalmente responsivo.
* **Foco em Leads:** Otimizado para coletar **Nome Completo** e **WhatsApp**.
* **Conformidade LGPD:** Inclui Modal de Termos de Uso estruturado conforme a Lei Geral de Proteção de Dados e o Marco Civil da Internet.
* **Validação Inteligente:** Integração com **jQuery Validate** e **jQuery Mask** para garantir dados padronizados, incluindo máscaras de telefone dinâmicas.
* **Handshake Automático:** Fluxo contínuo que realiza o registro no banco e a autenticação no firewall de forma transparente para o usuário.
* **Segurança:** Uso de **Prepared Statements (MySQLi)** para prevenir SQL Injection.
* **Totalmente Local:** Referências de arquivos adaptadas ao padrão `captiveportal-` do pfSense, garantindo carregamento rápido sem dependência de CDNs externas.

## 🛠️ Requisitos

* **pfSense** com pacote **FreeRADIUS3** instalado.
* Servidor **MySQL/MariaDB** (externo ou local) configurado no FreeRADIUS.
* Tabela `reg_users` criada para armazenamento de leads.

## 📋 Como Instalar

1. **Configuração do Banco:** Certifique-se de que o FreeRADIUS está lendo do seu banco MySQL e que a tabela `radcheck` existe.
2. **Ajuste de Configuração:** Edite o arquivo `captiveportal-config.php` com as credenciais do seu banco de dados e as informações da sua marca/empresa.
3. **Upload de Arquivos:** No pfSense, vá em `Services > Captive Portal > File Manager` e faça o upload de todos os arquivos do projeto.
* **Nota Importante:** Todos os arquivos (PHP, JS, CSS, Imagens) **devem** ter o prefixo `captiveportal-` no nome para serem servidos corretamente pelo pfSense antes da autenticação.


4. **Ativação:** Em `Services > Captive Portal > [Sua Zona]`, aponte a página de login para o arquivo `captiveportal-index.php`.

## 📂 Estrutura de Arquivos

| Arquivo | Descrição |
| --- | --- |
| **Lógica e Configuração** |  |
| `captiveportal-index.php` | Arquivo principal contendo a Interface e Lógica de Registro. |
| `captiveportal-config.php` | Configurações de banco, marcas e variáveis globais. |
| `captiveportal-termsofuse.php` | Modal de Termos de Uso e Política de Privacidade (LGPD). |
| **Ativos Visuais** |  |
| `captiveportal-background.jpg` | Imagem de fundo da tela de login. |
| `captiveportal-favicon.ico` | Ícone de atalho do navegador. |
| **CSS e Scripts (JS)** |  |
| `captiveportal-bootstrap.min.css` | Framework CSS (Bootstrap 4.5.3). |
| `captiveportal-bootstrap.bundle.min.js` | JS do Bootstrap (inclui Popper.js para Modais). |
| `captiveportal-jquery-3.5.1.min.js` | Biblioteca base jQuery. |
| `captiveportal-jquery.validate.min.js` | Plugin para validação de formulário. |
| `captiveportal-jquery.mask.min.js` | Plugin para máscaras dinâmicas de WhatsApp. |
| **Banco de Dados (Pasta /sql)** |  |
| `sql/reg_users.sql` | Script para criação da tabela de leads. |
| `sql/schema.sql` | Estrutura base para FreeRADIUS SQL (inclui `radcheck`, `radacct`, etc.). |
| `sql/admin.sql` | MySQL comandos para criar o usuário RADIUS e definir privilégios. |
| `sql/nas.sql` | Estrutura da tabela para gerenciamento de clientes RADIUS (NAS). |
| `sql/radippool.sql` | Estrutura da tabela para gerenciamento de pools de IP. |
| `sql/cui.sql` | Estrutura da tabela para Chargeable User Identity. |
| `sql/wimax.sql` | Estrutura da tabela para WiMAX. |

## 🗄️ Banco de Dados (SQL)

#### Sequência de Comandos para Importação
Substitua root pelo seu usuário administrativo e informe a senha quando solicitado:

```sh
mysql -u root -p radius < sql/reg_users.sql;
mysql -u root -p radius < sql/schema.sql;
mysql -u root -p radius < sql/admin.sql;
mysql -u root -p radius < sql/nas.sql;
mysql -u root -p radius < sql/radippool.sql;
mysql -u root -p radius < sql/cui.sql;
mysql -u root -p radius < sql/wimax.sql;
```

## 🔗 Referências Originais

* **Repositório Base:** [deajan/pfSense-cp-auth-onestep](https://github.com/deajan/pfSense-cp-auth-onestep)
* **Tutorial de Instalação (Base):** [Assista no YouTube](https://www.youtube.com/watch?v=N7LmJrzi9Wg)
