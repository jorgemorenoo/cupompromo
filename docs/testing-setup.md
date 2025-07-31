# Configuração de Testes - Cupompromo

## Visão Geral

O projeto Cupompromo está configurado com PHPUnit para testes automatizados. Esta documentação explica como configurar e executar os testes.

## Estrutura de Testes

```
tests/
├── bootstrap.php              # Bootstrap para testes WordPress
├── bootstrap-simple.php       # Bootstrap para testes simples
├── test-basic.php            # Testes básicos do WordPress
├── test-simple.php           # Testes simples sem WordPress
├── test-store-card.php       # Testes da classe Store_Card
└── test-very-simple.php      # Teste muito simples
```

## Configuração

### 1. Dependências

O projeto usa Composer para gerenciar dependências de teste:

```bash
# Instalar dependências
composer install

# Verificar se PHPUnit está funcionando
./vendor/bin/phpunit --version
```

### 2. Arquivos de Configuração

#### phpunit.xml (Principal)
- Configuração para testes WordPress completos
- Requer banco de dados MySQL
- Bootstrap: `tests/bootstrap.php`

#### phpunit-simple.xml (Simples)
- Configuração para testes básicos
- Não requer banco de dados
- Bootstrap: `tests/bootstrap-simple.php`

## Executando Testes

### Testes Simples (Recomendado para desenvolvimento)

```bash
# Executar testes simples
./vendor/bin/phpunit -c phpunit-simple.xml

# Executar teste específico
./vendor/bin/phpunit -c phpunit-simple.xml tests/test-simple.php

# Executar com cobertura
./vendor/bin/phpunit -c phpunit-simple.xml --coverage-html coverage
```

### Testes WordPress Completos

```bash
# Configurar WordPress Test Suite (primeira vez)
./run-tests.sh setup

# Executar testes WordPress
./run-tests.sh test

# Executar todos os testes
./run-tests.sh all
```

### Script de Conveniência

```bash
# Ver ajuda
./run-tests.sh help

# Executar linting
./run-tests.sh lint

# Executar PHP CodeSniffer
./run-tests.sh phpcs

# Executar testes com cobertura
./run-tests.sh coverage

# Limpar arquivos temporários
./run-tests.sh clean
```

## Tipos de Teste

### 1. Testes Simples
- Não requerem WordPress
- Testam funcionalidades básicas
- Rápidos de executar
- Ideais para desenvolvimento

### 2. Testes WordPress
- Requerem WordPress Test Suite
- Testam integração com WordPress
- Requerem banco de dados
- Mais completos

## Configuração do Banco de Dados

Para testes WordPress completos, você precisa:

1. **MySQL/MariaDB instalado**
2. **Banco de dados de teste criado**
3. **Usuário com permissões**

### Configuração Manual

```bash
# Criar banco de dados
mysql -u root -p -e "CREATE DATABASE wordpress_test;"

# Criar usuário (opcional)
mysql -u root -p -e "CREATE USER 'wp_test'@'localhost' IDENTIFIED BY 'password';"
mysql -u root -p -e "GRANT ALL PRIVILEGES ON wordpress_test.* TO 'wp_test'@'localhost';"
```

### Atualizar Configuração

Edite `/tmp/wordpress-tests-lib/wp-tests-config.php`:

```php
define( 'DB_NAME', 'wordpress_test' );
define( 'DB_USER', 'root' ); // ou 'wp_test'
define( 'DB_PASSWORD', '' ); // ou 'password'
define( 'DB_HOST', 'localhost' );
```

## Troubleshooting

### Erro 127: PHPUnit não encontrado

```bash
# Verificar se Composer está instalado
which composer

# Instalar dependências
composer install

# Verificar se PHPUnit está disponível
./vendor/bin/phpunit --version
```

### Erro de Conexão com Banco de Dados

```bash
# Verificar se MySQL está rodando
sudo systemctl status mysql

# Verificar configuração
cat /tmp/wordpress-tests-lib/wp-tests-config.php

# Testar conexão
mysql -u root -p -e "SHOW DATABASES;"
```

### Testes WordPress Falhando

```bash
# Reconfigurar WordPress Test Suite
./run-tests.sh setup

# Verificar se arquivos existem
ls -la /tmp/wordpress-tests-lib/
ls -la /tmp/wordpress/

# Limpar cache
./run-tests.sh clean
```

### Testes Simples Não Executando

```bash
# Verificar sintaxe
php -l tests/test-simple.php

# Executar sem configuração
./vendor/bin/phpunit --no-configuration tests/test-simple.php

# Verificar bootstrap
php -l tests/bootstrap-simple.php
```

## Comandos Úteis

### Desenvolvimento

```bash
# Executar apenas linting
./run-tests.sh lint

# Executar apenas PHPCS
./run-tests.sh phpcs

# Executar testes simples
./vendor/bin/phpunit -c phpunit-simple.xml

# Executar com verbose
./vendor/bin/phpunit -c phpunit-simple.xml --verbose
```

### CI/CD

```bash
# Executar todos os checks
./run-tests.sh all

# Executar com cobertura
./run-tests.sh coverage

# Verificar cobertura
open coverage/index.html
```

### Debug

```bash
# Executar com debug
./vendor/bin/phpunit -c phpunit-simple.xml --debug

# Listar testes disponíveis
./vendor/bin/phpunit -c phpunit-simple.xml --list-tests

# Executar teste específico
./vendor/bin/phpunit -c phpunit-simple.xml --filter TestClassName
```

## Estrutura de Testes

### Testes de Classe

```php
class Cupompromo_Class_Test extends PHPUnit\Framework\TestCase {
    
    public function setUp(): void {
        // Setup antes de cada teste
    }
    
    public function tearDown(): void {
        // Cleanup após cada teste
    }
    
    public function test_functionality() {
        // Teste específico
        $this->assertTrue(true);
    }
}
```

### Testes WordPress

```php
class Cupompromo_WordPress_Test extends WP_UnitTestCase {
    
    public function setUp(): void {
        parent::setUp();
        // Setup WordPress
    }
    
    public function test_wordpress_integration() {
        // Teste com WordPress
        $this->assertTrue(function_exists('wp_insert_post'));
    }
}
```

## Boas Práticas

1. **Testes Simples Primeiro**: Use testes simples para desenvolvimento rápido
2. **Testes WordPress para Integração**: Use testes WordPress para funcionalidades que dependem do WordPress
3. **Cobertura de Código**: Mantenha cobertura alta (mínimo 80%)
4. **Testes Isolados**: Cada teste deve ser independente
5. **Cleanup**: Sempre limpe dados de teste

## Próximos Passos

1. **Configurar CI/CD**: GitHub Actions ou similar
2. **Adicionar Mais Testes**: Para todas as classes principais
3. **Testes de Integração**: Para API Awin
4. **Testes de Frontend**: Para componentes React
5. **Testes de Performance**: Para funcionalidades críticas

---

**Status**: ✅ **Configuração Completa**

O ambiente de testes está configurado e funcionando. Use `./run-tests.sh help` para ver todas as opções disponíveis. 