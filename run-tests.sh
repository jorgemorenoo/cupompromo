#!/bin/bash

# Script para executar testes do Cupompromo
# Uso: ./run-tests.sh [opções]

set -e

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Função para mostrar mensagens
print_message() {
    echo -e "${BLUE}[Cupompromo Tests]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

# Verifica se o Composer está instalado
check_composer() {
    if ! command -v composer &> /dev/null; then
        print_error "Composer não está instalado. Instale o Composer primeiro."
        exit 1
    fi
}

# Verifica se as dependências estão instaladas
check_dependencies() {
    if [ ! -d "vendor" ]; then
        print_warning "Dependências não encontradas. Instalando..."
        composer install
    fi
}

# Executa linting
run_lint() {
    print_message "Executando linting..."
    if php -l cupompromo.php && php -l includes/*.php; then
        print_success "Linting passou!"
    else
        print_error "Linting falhou!"
        return 1
    fi
}

# Executa PHP CodeSniffer
run_phpcs() {
    print_message "Executando PHP CodeSniffer..."
    if ./vendor/bin/phpcs --standard=WordPress includes/; then
        print_success "PHP CodeSniffer passou!"
    else
        print_warning "PHP CodeSniffer encontrou problemas. Execute 'composer run phpcbf' para corrigir automaticamente."
        return 1
    fi
}

# Executa testes
run_tests() {
    print_message "Executando testes..."
    
    # Verifica se o WordPress Test Suite está configurado
    if [ ! -d "/tmp/wordpress-tests-lib" ]; then
        print_warning "WordPress Test Suite não encontrado. Configurando..."
        setup_wordpress_tests
    fi
    
    if ./vendor/bin/phpunit; then
        print_success "Todos os testes passaram!"
    else
        print_error "Alguns testes falharam!"
        return 1
    fi
}

# Configura WordPress Test Suite
setup_wordpress_tests() {
    print_message "Configurando WordPress Test Suite..."
    
    # Cria diretórios temporários
    mkdir -p /tmp/wordpress-tests-lib
    mkdir -p /tmp/wordpress
    
    # Baixa WordPress
    if [ ! -d "/tmp/wordpress/wp-admin" ]; then
        print_message "Baixando WordPress..."
        wget -q https://wordpress.org/latest.zip -O /tmp/wordpress.zip
        unzip -q /tmp/wordpress.zip -d /tmp/
        mv /tmp/wordpress/* /tmp/wordpress/
        rm -rf /tmp/wordpress/wordpress
        rm /tmp/wordpress.zip
    fi
    
    # Baixa WordPress Test Suite
    if [ ! -f "/tmp/wordpress-tests-lib/includes/functions.php" ]; then
        print_message "Baixando WordPress Test Suite..."
        svn co --quiet https://develop.svn.wordpress.org/trunk/tests/phpunit/includes/ /tmp/wordpress-tests-lib/includes/
        svn co --quiet https://develop.svn.wordpress.org/trunk/tests/phpunit/data/ /tmp/wordpress-tests-lib/data/
    fi
    
    print_success "WordPress Test Suite configurado!"
}

# Executa cobertura de código
run_coverage() {
    print_message "Executando cobertura de código..."
    if ./vendor/bin/phpunit --coverage-html coverage; then
        print_success "Cobertura gerada em coverage/index.html"
    else
        print_error "Falha ao gerar cobertura!"
        return 1
    fi
}

# Limpa arquivos temporários
cleanup() {
    print_message "Limpando arquivos temporários..."
    rm -rf .phpunit.cache
    rm -rf coverage
    print_success "Limpeza concluída!"
}

# Mostra ajuda
show_help() {
    echo "Uso: $0 [opção]"
    echo ""
    echo "Opções:"
    echo "  test        Executa todos os testes"
    echo "  lint        Executa apenas linting"
    echo "  phpcs       Executa apenas PHP CodeSniffer"
    echo "  coverage    Executa testes com cobertura"
    echo "  setup       Configura WordPress Test Suite"
    echo "  clean       Limpa arquivos temporários"
    echo "  all         Executa lint, phpcs e testes"
    echo "  help        Mostra esta ajuda"
    echo ""
    echo "Exemplos:"
    echo "  $0 test"
    echo "  $0 all"
    echo "  $0 coverage"
}

# Função principal
main() {
    local command=${1:-"all"}
    
    print_message "Iniciando testes do Cupompromo..."
    
    # Verifica dependências
    check_composer
    check_dependencies
    
    case $command in
        "test")
            run_tests
            ;;
        "lint")
            run_lint
            ;;
        "phpcs")
            run_phpcs
            ;;
        "coverage")
            run_coverage
            ;;
        "setup")
            setup_wordpress_tests
            ;;
        "clean")
            cleanup
            ;;
        "all")
            run_lint && run_phpcs && run_tests
            ;;
        "help"|"-h"|"--help")
            show_help
            ;;
        *)
            print_error "Comando desconhecido: $command"
            show_help
            exit 1
            ;;
    esac
    
    print_success "Processo concluído!"
}

# Executa função principal
main "$@" 