#!/usr/bin/env bash

# Script para configurar testes do WordPress no ambiente Docker
# Este script usa as credenciais do container MySQL do Docker

echo "🔧 Configurando ambiente de testes para Cupompromo..."

# Verificar se estamos no ambiente Docker
if [ ! -f /.dockerenv ] && [ ! -f /run/.containerenv ]; then
    echo "⚠️  Este script deve ser executado dentro do container WordPress"
    echo "Execute: docker exec -it cupomzeiros-wordpress-1 bash"
    exit 1
fi

# Configurações do banco de dados Docker
DB_NAME="cupompromo_tests"
DB_USER="root"
DB_PASS="root"
DB_HOST="cupomzeiros-mysql-1:3306"

# Versão do WordPress para testes
WP_VERSION="latest"

# Diretórios de teste
WP_TESTS_DIR="/tmp/wordpress-tests-lib"
WP_CORE_DIR="/tmp/wordpress"

echo "📦 Instalando bibliotecas de teste do WordPress..."

# Executar o script de instalação
./bin/install-wp-tests.sh "$DB_NAME" "$DB_USER" "$DB_PASS" "$DB_HOST" "$WP_VERSION"

echo "✅ Ambiente de testes configurado com sucesso!"
echo ""
echo "📋 Para executar os testes:"
echo "   composer run test:wordpress"
echo ""
echo "📋 Para executar testes simples:"
echo "   composer run test"
echo ""
echo "📋 Para verificar qualidade do código:"
echo "   composer run phpcs"
echo ""
echo "📋 Para executar todas as verificações:"
echo "   composer run check" 