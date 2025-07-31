/**
 * Componentes Frontend do Cupompromo
 * 
 * @package Cupompromo
 * @version 1.0.0
 */

// Componentes principais
export { default as CouponModal } from './CouponModal.js';
export { default as SearchBar } from './SearchBar.js';
export { default as CouponGrid } from './CouponGrid.js';
export { default as CouponCard } from './CouponCard.js';

// Placeholders para componentes futuros
export const FilterPanel = () => null;
export const StoreCard = () => null;

// Hooks customizados
export { useCoupons, useCoupon } from '../hooks/useCoupons.js';

// Placeholders para hooks futuros
export const useStores = () => ({ stores: [], loading: false });
export const useSearch = () => ({ search: '', setSearch: () => {} });

// Utilitários
export * from '../utils/index.js'; 