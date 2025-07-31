/**
 * Arquivo de entrada para todos os blocos Gutenberg do Cupompromo
 * 
 * @package Cupompromo
 * @version 1.0.0
 */

// Importa todos os blocos
import './stores-grid';
import './search-bar';
import './coupons-list';
import './featured-carousel';

// Registra a categoria personalizada
import { registerBlockCollection } from '@wordpress/blocks';

registerBlockCollection('cupompromo', {
    title: 'Cupompromo',
    icon: 'tickets-alt'
}); 