import test from 'node:test';
import assert from 'node:assert/strict';

import { buildPaidSuccessText } from '../../resources/js/package-payment-ui.js';

test('buildPaidSuccessText includes paid_at in visible success copy', () => {
    assert.equal(
        buildPaidSuccessText('2026-04-20 14:35:00'),
        '支付成功，订单已完成。支付成功时间：2026-04-20 14:35:00',
    );
});
