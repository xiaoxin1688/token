import './bootstrap';
import { buildPaidSuccessText } from './package-payment-ui.js';

const state = {
    billingCycle: 'month',
    currentOrderNo: null,
};

function initPackagePage() {
    const modal = document.getElementById('payment-modal');

    if (!modal || !window.packagePageConfig) {
        return;
    }

    const toggleButtons = Array.from(document.querySelectorAll('.billing-toggle'));
    const priceNodes = Array.from(document.querySelectorAll('.package-price'));
    const cycleLabels = Array.from(document.querySelectorAll('.package-cycle-label'));
    const buyButtons = Array.from(document.querySelectorAll('.buy-button'));
    const closeButtons = [
        document.getElementById('payment-modal-close'),
        document.getElementById('payment-close-secondary'),
    ].filter(Boolean);
    const refreshButton = document.getElementById('payment-refresh-status');

    const packageNameNode = document.getElementById('payment-package-name');
    const packageValueNode = document.getElementById('payment-package-value');
    const cycleValueNode = document.getElementById('payment-cycle-value');
    const amountValueNode = document.getElementById('payment-amount-value');
    const orderNoNode = document.getElementById('payment-order-no');
    const summaryNode = document.getElementById('payment-order-summary');
    const statusTextNode = document.getElementById('payment-status-text');
    const successTextNode = document.getElementById('payment-success-text');
    const qrcodeNode = document.getElementById('payment-qrcode');

    function setBillingCycle(nextCycle) {
        state.billingCycle = nextCycle;

        toggleButtons.forEach((button) => {
            const active = button.dataset.cycle === nextCycle;

            button.classList.toggle('bg-cyan-300', active);
            button.classList.toggle('text-slate-950', active);
            button.classList.toggle('text-slate-400', !active);
        });

        priceNodes.forEach((node) => {
            const nextValue = node.dataset[nextCycle] ?? '0.00';
            node.textContent = nextValue;
        });

        cycleLabels.forEach((node) => {
            node.textContent = nextCycle === 'year' ? '/年' : '/月';
        });
    }

    function openModal() {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    }

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    }

    function resetPaymentState() {
        state.currentOrderNo = null;
        orderNoNode.textContent = '-';
        summaryNode.textContent = '请使用微信扫码完成支付。';
        statusTextNode.textContent = '二维码已生成，请使用微信扫描支付。';
        successTextNode.textContent = buildPaidSuccessText();
        successTextNode.classList.add('hidden');
        statusTextNode.classList.remove('hidden');
        if (qrcodeNode) {
            qrcodeNode.innerHTML = '';
        }
    }

    function renderPaymentQrCode(codeUrl) {
        if (!qrcodeNode || !codeUrl) {
            return false;
        }

        qrcodeNode.innerHTML = '';

        if (window.jQuery?.fn?.qrcode) {
            window.jQuery(qrcodeNode).qrcode({
                render: 'canvas',
                text: codeUrl,
                size: 208,
                fill: '#050914',
                background: '#ffffff',
            });

            return true;
        }

        if (window.QRCode?.toCanvas) {
            window.QRCode.toCanvas(codeUrl, {
                width: 208,
                margin: 1,
                color: {
                    dark: '#050914',
                    light: '#ffffff',
                },
            }, (error, canvas) => {
                if (error) {
                    statusTextNode.textContent = '二维码生成失败，请稍后重试。';
                    return;
                }

                qrcodeNode.innerHTML = '';
                qrcodeNode.appendChild(canvas);
            });

            return true;
        }

        return false;
    }

    async function createOrder(button) {
        const packageId = button.dataset.packageId;
        const packageName = button.dataset.packageName;
        const amount = state.billingCycle === 'year' ? button.dataset.yearPrice : button.dataset.monthPrice;

        packageNameNode.textContent = packageName;
        packageValueNode.textContent = packageName;
        cycleValueNode.textContent = state.billingCycle === 'year' ? '年度付费' : '月度付费';
        amountValueNode.textContent = `¥${amount}`;
        resetPaymentState();
        openModal();

        try {
            const response = await fetch(window.packagePageConfig.createOrderUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '',
                },
                body: JSON.stringify({
                    package_id: Number(packageId),
                    billing_cycle: state.billingCycle,
                }),
            });

            const payload = await response.json();

            if (!response.ok) {
                throw new Error(payload?.message ?? '订单创建失败');
            }

            const order = payload.data ?? {};
            state.currentOrderNo = order.order_no;
            orderNoNode.textContent = order.order_no ?? '-';
            summaryNode.textContent = `请使用微信扫码支付 ${order.package_name ?? packageName}`;

            if (!renderPaymentQrCode(order.code_url)) {
                statusTextNode.textContent = '二维码库未加载，请刷新页面重试。';
            }
        } catch (error) {
            statusTextNode.textContent = error instanceof Error ? error.message : '订单创建失败，请稍后重试。';
        }
    }

    async function refreshOrderStatus() {
        if (!state.currentOrderNo) {
            statusTextNode.textContent = '当前没有可查询的订单。';
            return;
        }

        try {
            const statusUrl = window.packagePageConfig.orderStatusUrlTemplate.replace('__ORDER_NO__', encodeURIComponent(state.currentOrderNo));
            const response = await fetch(statusUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const payload = await response.json();

            if (!response.ok) {
                throw new Error(payload?.message ?? '支付状态查询失败');
            }

            const order = payload.data ?? {};

            if (Number(order.pay_status) === 1) {
                successTextNode.textContent = buildPaidSuccessText(order.paid_at);
                successTextNode.classList.remove('hidden');
                statusTextNode.classList.add('hidden');
                summaryNode.textContent = '支付成功，系统已确认订单状态。';
            } else {
                statusTextNode.textContent = '尚未收到支付成功通知，请完成扫码后再次刷新。';
            }
        } catch (error) {
            statusTextNode.textContent = error instanceof Error ? error.message : '支付状态查询失败，请稍后重试。';
        }
    }

    toggleButtons.forEach((button) => {
        button.addEventListener('click', () => setBillingCycle(button.dataset.cycle ?? 'month'));
    });

    buyButtons.forEach((button) => {
        button.addEventListener('click', () => {
            createOrder(button);
        });
    });

    closeButtons.forEach((button) => {
        button.addEventListener('click', closeModal);
    });

    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });

    refreshButton?.addEventListener('click', refreshOrderStatus);

    setBillingCycle('month');
}

window.addEventListener('DOMContentLoaded', () => {
    initPackagePage();
});
