# 🧪 Testes do Cupompromo

Este documento explica como configurar e executar os testes do plugin Cupompromo.

## 📋 Pré-requisitos

- PHP 8.1 ou superior
- Composer
- MySQL/MariaDB
- WordPress (para testes de integração)

## 🐳 Ambiente Docker (Recomendado)

### 1. Acessar o Container WordPress

```bash
docker exec -it cupomzeiros-wordpress-1 bash
```

### 2. Navegar para o Diretório do Plugin

```bash
cd /var/www/html/wp-content/plugins/cupompromo
```

### 3. Instalar Dependências

```bash
composer install
```

### 4. Configurar Ambiente de Testes

```bash
./bin/setup-tests-docker.sh
```

### 5. Executar Testes

```bash
# Testes simples (sem WordPress)
composer run test

# Testes completos (com WordPress)
composer run test:wordpress

# Verificar qualidade do código
composer run phpcs

# Executar todas as verificações
composer run check
```

## 🖥️ Ambiente Local

### 1. Configurar Banco de Dados

Crie um banco de dados para testes:

```sql
CREATE DATABASE cupompromo_tests;
```

### 2. Instalar Dependências

```bash
composer install
```

### 3. Configurar Ambiente de Testes

```bash
./bin/install-wp-tests.sh cupompromo_tests root password localhost latest
```

### 4. Executar Testes

```bash
composer run test:wordpress
```

## 📊 Tipos de Testes

### Testes Unitários
- Testam classes e métodos isoladamente
- Não dependem do WordPress
- Executados com: `composer run test`

### Testes de Integração
- Testam integração com WordPress
- Requerem banco de dados
- Executados com: `composer run test:wordpress`

### Testes de Qualidade
- Verificam padrões de código
- Executados com: `composer run phpcs`

## 🏗️ Estrutura de Testes

```
tests/
├── bootstrap.php          # Configuração inicial
├── test-basic.php         # Testes básicos
├── test-apis.php          # Testes das APIs
├── test-admin.php         # Testes do painel admin
└── reports/               # Relatórios de teste
    ├── coverage/          # Cobertura de código
    └── junit.xml          # Relatório JUnit
```

## 🔧 Configuração de Testes

### Variáveis de Ambiente

```bash
WP_TESTS_DIR=/tmp/wordpress-tests-lib
WP_CORE_DIR=/tmp/wordpress
CUPOMPROMO_PLUGIN_PATH=/path/to/plugin
CUPOMPROMO_VERSION=1.0.0
```

### Configuração do PHPUnit

O arquivo `phpunit.xml` define:
- Bootstrap do WordPress
- Diretórios de teste
- Cobertura de código
- Relatórios

## 📈 Relatórios

### Cobertura de Código

```bash
composer run test:coverage
```

Os relatórios são gerados em:
- `tests/reports/coverage/` (HTML)
- `tests/reports/coverage.txt` (Texto)

### Relatório JUnit

```bash
composer run test:wordpress
```

Relatório gerado em: `tests/reports/junit.xml`

## 🐛 Debugging

### Verbose Output

```bash
composer run test:wordpress -- --verbose
```

### Teste Específico

```bash
composer run test:wordpress -- --filter test_plugin_loaded
```

### Debug com Xdebug

```bash
php -dxdebug.mode=debug vendor/bin/phpunit -c phpunit.xml
```

## 🔄 CI/CD

### GitHub Actions

O projeto inclui workflow para:
- Executar testes automaticamente
- Verificar qualidade do código
- Gerar relatórios de cobertura

### Comandos para CI

```bash
# Instalar dependências
composer install --no-dev --optimize-autoloader

# Executar testes
composer run test

# Verificar qualidade
composer run phpcs
```

## 📝 Escrevendo Testes

### Estrutura Básica

```php
<?php
class Cupompromo_My_Test extends WP_UnitTestCase {
    
    public function setUp(): void {
        parent::setUp();
        // Configuração inicial
    }
    
    public function tearDown(): void {
        // Limpeza
        parent::tearDown();
    }
    
    public function test_my_function() {
        // Seu teste aqui
        $this->assertTrue(true);
    }
}
```

### Helpers Disponíveis

- `cupompromo_create_test_data()` - Cria dados de teste
- `cupompromo_cleanup_test_data()` - Limpa dados de teste
- `cupompromo_mock_awin_data()` - Dados mock da API Awin

### Boas Práticas

1. **Isolamento**: Cada teste deve ser independente
2. **Limpeza**: Sempre limpe dados criados
3. **Nomes descritivos**: Use nomes que expliquem o que está sendo testado
4. **Assertions claras**: Use assertions específicas
5. **Mocks**: Use mocks para dependências externas

## 🚨 Troubleshooting

### Erro: "WordPress Test Suite não encontrado"

```bash
# Execute o script de configuração
./bin/setup-tests-docker.sh
```

### Erro: "Database connection failed"

Verifique as credenciais do banco:
- Usuário: `root`
- Senha: `root`
- Host: `cupomzeiros-mysql-1:3306`

### Erro: "Class not found"

```bash
# Reinstale o autoloader
composer dump-autoload
```

### Erro: "Permission denied"

```bash
# Torne os scripts executáveis
chmod +x bin/*.sh
```

## 📚 Recursos Adicionais

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [WordPress Testing Handbook](https://make.wordpress.org/core/handbook/testing/)
- [PHP CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer)

## 🤝 Contribuindo

1. Escreva testes para novas funcionalidades
2. Mantenha cobertura de código acima de 80%
3. Execute todos os testes antes de fazer commit
4. Verifique qualidade do código com `composer run phpcs` 