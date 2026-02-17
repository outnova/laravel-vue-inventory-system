export const formatCurrency = (value) => {
    if(value === null || value === undefined) return '0.00'

    return parseFloat(value).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    })
}