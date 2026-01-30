/**
 * Currency utility functions
 * Base currency: USD
 * Display currency: TRY (converted from USD using exchange rate)
 */

let exchangeRate = null;
let exchangeRateDate = null;

/**
 * Fetch latest USD to TRY exchange rate
 */
export async function fetchExchangeRate() {
    try {
        const apiClient = (await import('../api/client.js')).default;
        const response = await apiClient.get('/core/exchange-rates/latest/USD');
        if (response.data.success) {
            exchangeRate = parseFloat(response.data.data.rate);
            exchangeRateDate = response.data.data.rate_date;
            return exchangeRate;
        }
    } catch (error) {
        console.error('Error fetching exchange rate:', error);
    }
    return null;
}

/**
 * Get cached exchange rate or fetch if not available
 */
export async function getExchangeRate() {
    if (exchangeRate) {
        return exchangeRate;
    }
    return await fetchExchangeRate();
}

/**
 * Convert USD to TRY
 * @param {number} usdAmount Amount in USD
 * @returns {number|null} Amount in TRY or null if rate not available
 */
export async function usdToTry(usdAmount) {
    const rate = await getExchangeRate();
    if (!rate) {
        return null;
    }
    return usdAmount * rate;
}

/**
 * Convert TRY to USD
 * @param {number} tryAmount Amount in TRY
 * @returns {number|null} Amount in USD or null if rate not available
 */
export async function tryToUsd(tryAmount) {
    const rate = await getExchangeRate();
    if (!rate) {
        return null;
    }
    return tryAmount / rate;
}

/**
 * Format currency in USD
 * @param {number} amount Amount in USD
 * @param {object} options Formatting options
 * @returns {string} Formatted currency string
 */
export function formatUsd(amount, options = {}) {
    const {
        showSymbol = true,
        decimals = 2,
        locale = 'en-US'
    } = options;

    const formatted = new Intl.NumberFormat(locale, {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    }).format(amount);

    return formatted;
}

/**
 * Format currency in TRY
 * @param {number} amount Amount in TRY
 * @param {object} options Formatting options
 * @returns {string} Formatted currency string
 */
export function formatTry(amount, options = {}) {
    const {
        showSymbol = true,
        decimals = 2,
        locale = 'tr-TR'
    } = options;

    const formatted = new Intl.NumberFormat(locale, {
        style: 'currency',
        currency: 'TRY',
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    }).format(amount);

    return formatted;
}

/**
 * Format USD amount with TRY equivalent
 * @param {number} usdAmount Amount in USD
 * @param {object} options Formatting options
 * @returns {string} Formatted string with both currencies
 */
export async function formatWithTry(usdAmount, options = {}) {
    const {
        showBoth = true,
        primary = 'usd',
        decimals = 2
    } = options;

    const tryAmount = await usdToTry(usdAmount);
    
    if (!showBoth) {
        return formatUsd(usdAmount, { decimals });
    }

    if (primary === 'usd') {
        return `${formatUsd(usdAmount, { decimals })} (${tryAmount ? formatTry(tryAmount, { decimals }) : 'Kur yok'})`;
    } else {
        return `${tryAmount ? formatTry(tryAmount, { decimals }) : 'Kur yok'} (${formatUsd(usdAmount, { decimals })})`;
    }
}

/**
 * Format currency (default: USD with TRY equivalent)
 * @param {number} amount Amount in USD
 * @param {object} options Formatting options
 * @returns {string} Formatted currency string
 */
export async function formatCurrency(amount, options = {}) {
    const {
        showTry = true,
        currency = 'USD'
    } = options;

    if (currency === 'USD') {
        if (showTry) {
            return await formatWithTry(amount, options);
        }
        return formatUsd(amount, options);
    }

    return formatTry(amount, options);
}
