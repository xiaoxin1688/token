export function buildPaidSuccessText(paidAt = '') {
    if (!paidAt) {
        return '支付成功，订单已完成。';
    }

    return `支付成功，订单已完成。支付成功时间：${paidAt}`;
}
