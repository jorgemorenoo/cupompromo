# Cupompromo - Plugin WordPress Profissional

Um portal de cupons de desconto robusto, escalável e com painel administrativo intuitivo para WordPress.

## 📋 Descrição

O Cupompromo é um plugin WordPress profissional que cria um portal de afiliados onde usuários encontram cupons de desconto para diversas lojas online. O plugin oferece uma solução completa para gerenciamento de cupons, integração com redes de afiliados e analytics detalhados.

## ✨ Funcionalidades Principais

### 🏪 Gerenciamento de Lojas
- Cadastro e gerenciamento de lojas parceiras
- Upload de logos e informações detalhadas
- Configuração de URLs de afiliado
- Sistema de comissões personalizado
- Lojas em destaque

### 🎫 Sistema de Cupons
- Criação de cupons com códigos ou ofertas diretas
- Validação automática de cupons
- Controle de datas de expiração
- Limite de uso por cupom
- Categorização de cupons

### 📊 Analytics Avançado
- Tracking de cliques e conversões
- Relatórios detalhados por loja/cupom
- Métricas de performance
- Heatmaps de cupons populares
- ROI por loja/categoria

### 🎨 Frontend Moderno
- Design responsivo mobile-first
- Componentes React para interatividade
- Shortcodes para integração fácil
- Blocos Gutenberg personalizados
- Busca inteligente com AJAX

### ⚙️ Painel Administrativo
- Dashboard intuitivo com métricas
- Gerenciamento completo de lojas e cupons
- Relatórios exportáveis (CSV/PDF)
- Configurações avançadas
- Sistema de notificações

## 🚀 Instalação

### Requisitos
- WordPress 5.0 ou superior
- PHP 8.1 ou superior
- MySQL 5.7 ou superior

### Passos de Instalação

1. **Download do Plugin**
   ```bash
   git clone https://github.com/seu-usuario/cupompromo.git
   ```

2. **Upload para WordPress**
   - Faça upload da pasta `cupompromo` para `/wp-content/plugins/`
   - Ou use o instalador de plugins do WordPress

3. **Ativação**
   - Acesse o painel administrativo
   - Vá em Plugins > Plugins Instalados
   - Ative o plugin "Cupompromo"

4. **Configuração Inicial**
   - Acesse Cupompromo > Configurações
   - Configure as opções básicas
   - Adicione suas primeiras lojas e cupons

## 📖 Uso

### Shortcodes Disponíveis

```php
// Formulário de busca de cupons
[cupompromo_search]

// Grid de lojas em destaque
[cupompromo_stores_grid]

// Lista de cupons populares
[cupompromo_popular_coupons]

// Cupons por categoria
[cupompromo_coupons_by_category category="eletronicos"]
```

### Blocos Gutenberg

- **Cupompromo Store Grid**: Exibe grid de lojas
- **Cupompromo Coupon List**: Lista cupons com filtros
- **Cupompromo Search Bar**: Barra de busca inteligente
- **Cupompromo Featured Carousel**: Carrossel de destaques

### API REST

```php
// Buscar cupons
GET /wp-json/cupompromo/v1/coupons

// Buscar lojas
GET /wp-json/cupompromo/v1/stores

// Validar cupom
POST /wp-json/cupompromo/v1/validate-coupon
```

## 🏗️ Arquitetura

### Estrutura de Arquivos
```
cupompromo/
├── cupompromo.php                 # Arquivo principal
├── includes/
│   ├── class-cupompromo.php       # Classe principal
│   ├── class-post-types.php       # CPTs e taxonomias
│   ├── class-admin.php            # Interface administrativa
│   ├── class-frontend.php         # Funcionalidades frontend
│   ├── class-api.php              # Endpoints REST API
│   ├── class-shortcodes.php       # Shortcodes
│   ├── class-gutenberg.php        # Blocos Gutenberg
│   └── class-analytics.php        # Analytics e tracking
├── admin/
│   ├── views/                     # Templates admin
│   ├── css/                       # Estilos admin
│   └── js/                        # Scripts admin
├── public/
│   ├── css/                       # Estilos frontend
│   ├── js/                        # Scripts frontend
│   └── images/                    # Assets
├── blocks/                        # Blocos Gutenberg
├── languages/                     # Traduções
└── assets/                        # Arquivos fonte
```

### Banco de Dados
- `cupompromo_stores`: Lojas parceiras
- `cupompromo_coupons`: Cupons de desconto
- `cupompromo_categories`: Categorias
- `cupompromo_analytics`: Dados de analytics

## 🎨 Design System

### Paleta de Cores
```css
:root {
    --cupompromo-primary: #622599;     /* Roxo principal */
    --cupompromo-secondary: #8BC53F;   /* Verde para sucesso */
    --cupompromo-accent: #FF6B35;      /* Laranja para CTAs */
    --cupompromo-neutral-100: #F8F9FA; /* Background claro */
    --cupompromo-neutral-800: #2D3748; /* Texto escuro */
    --cupompromo-error: #E53E3E;       /* Vermelho para erros */
}
```

### Componentes
- Cards responsivos para lojas e cupons
- Modais para detalhes de cupons
- Formulários com validação em tempo real
- Loading states e feedback visual
- Animações suaves

## 🔧 Configuração

### Configurações Básicas
- Símbolo da moeda
- Comissão padrão
- Integração com redes de afiliados
- Configurações de email

### Configurações Avançadas
- Cache e performance
- SEO e meta tags
- Analytics e tracking
- Backup e exportação

## 📊 Analytics e Relatórios

### Métricas Disponíveis
- Total de lojas e cupons
- Cliques e conversões
- Performance por loja
- Cupons mais populares
- ROI por categoria

### Exportação
- Relatórios em CSV
- Relatórios em PDF
- Dados para análise externa

## 🔒 Segurança

- Validação rigorosa de entradas
- Escape de todas as saídas
- Prepared statements para queries
- Rate limiting em APIs
- Nonces para formulários
- Verificação de permissões

## 🚀 Performance

- Cache estratégico multicamadas
- Otimização de assets
- Lazy loading de imagens
- Minificação de CSS/JS
- CDN ready

## 🤝 Contribuição

### Como Contribuir

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

### Padrões de Código

- PHP 8.1+ com tipagem estrita
- WordPress Coding Standards
- PSR-12 para estrutura
- Comentários PHPDoc
- Testes unitários

## 📝 Changelog

### 1.0.0
- Versão inicial do plugin
- Sistema completo de lojas e cupons
- Painel administrativo intuitivo
- Analytics e relatórios
- Frontend responsivo
- API REST completa

## 📄 Licença

Este projeto está licenciado sob a Licença GPL v2 ou posterior - veja o arquivo [LICENSE](LICENSE) para detalhes.

## 👨‍💻 Autor

**Seu Nome** - [seu-email@exemplo.com](mailto:seu-email@exemplo.com)

- Website: [https://seusite.com](https://seusite.com)
- GitHub: [@seu-usuario](https://github.com/seu-usuario)

## 🐛 Suporte

- **Issues**: [GitHub Issues](https://github.com/seu-usuario/cupompromo/issues)
- **Documentação**: [Wiki do Projeto](https://github.com/seu-usuario/cupompromo/wiki)
- **Email**: [suporte@exemplo.com](mailto:suporte@exemplo.com)

## 🙏 Agradecimentos

- WordPress Community
- Contribuidores do projeto
- Testadores e feedback da comunidade

---

**Cupompromo** - Transformando cupons em conversões! 🎫✨ 