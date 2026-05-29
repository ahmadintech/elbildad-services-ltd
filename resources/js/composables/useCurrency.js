import { usePage } from '@inertiajs/vue3'

/**
 * useCurrency — global currency composable
 *
 * Reads symbol/code from the Inertia shared `currency` prop (set in HandleInertiaRequests).
 * Change APP_CURRENCY_SYMBOL and APP_CURRENCY_CODE in .env to switch currency app-wide.
 *
 * Usage in any Vue component:
 *   import { useCurrency } from '@/composables/useCurrency'
 *   const { formatCurrency, symbol } = useCurrency()
 */
export function useCurrency() {
    const page = usePage()

    const symbol = page.props.currency?.symbol ?? '₦'
    const code   = page.props.currency?.code   ?? 'NGN'

    /**
     * Format a numeric amount with the app currency symbol.
     * e.g. formatCurrency(150000) → "₦150,000.00"
     */
    const formatCurrency = (amount) => {
        const parsed = parseFloat(amount)
        const num    = isNaN(parsed) ? 0 : parsed

        try {
            // Try native Intl formatting with the configured currency code
            return new Intl.NumberFormat('en-NG', {
                style:    'currency',
                currency: code,
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            }).format(num)
        } catch {
            // Fallback: manual symbol prepend if the currency code is unrecognised by Intl
            return symbol + num.toLocaleString('en-NG', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            })
        }
    }

    return { formatCurrency, symbol, code }
}
