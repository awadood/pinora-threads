<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Order Confirmation</title>
</head>

<body style="margin:0;padding:0;background:#f7f7f7;font-family:Arial,sans-serif;color:#111;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
            <td align="center" style="padding:32px 16px;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="640"
                    style="max-width:640px;background:#ffffff;border-radius:12px;overflow:hidden;">
                    <tr>
                        <td style="padding:28px 32px;border-bottom:1px solid #eee;">
                            <h2 style="margin:0 0 6px;font-size:22px;">Thank you for your order</h2>
                            <p style="margin:0;color:#555;font-size:14px;">Order #{{ $order->number }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 32px;">
                            <p style="margin:0 0 12px;font-size:14px;color:#333;">
                                We’ve received your order and will email you updates as it moves through fulfillment.
                            </p>
                            <p style="margin:0 0 8px;font-size:14px;">
                                Total: <strong>{{ number_format((float) $order->total, 2) }} {{ $order->currency_code }}</strong>
                            </p>
                            <p style="margin:0 0 18px;font-size:14px;">
                                Shipping method: <strong>{{ $order->shipping_method ?? 'TBD' }}</strong>
                            </p>

                            @if ($claimUrl && $order->claim_status !== \App\Models\Order::CLAIM_STATUS_CLAIMED)
                                @if ($order->claim_status === \App\Models\Order::CLAIM_STATUS_PENDING)
                                    <p style="margin:0 0 8px;font-size:14px;color:#333;">
                                        We found an existing account with this email. Use this secure link to attach this order to your
                                        account history.
                                    </p>
                                @else
                                    <p style="margin:0 0 8px;font-size:14px;color:#333;">
                                        Create your account to track orders, keep all guest orders in one place and reorder faster.
                                    </p>
                                @endif
                                <p style="margin:0;">
                                    <a href="{{ $claimUrl }}"
                                        style="display:inline-block;padding:10px 16px;background:#111;color:#fff;text-decoration:none;border-radius:6px;font-size:14px;">
                                        {{ $order->claim_status === \App\Models\Order::CLAIM_STATUS_PENDING ? 'Claim this order' : 'Create account' }}
                                    </a>
                                </p>
                                @if ($trackingUrl)
                                    <p style="margin:10px 0 0;font-size:13px;color:#555;line-height:1.6;">
                                        Prefer to continue as guest? You can still
                                        <a href="{{ $trackingUrl }}" style="color:#111;text-decoration:underline;">track your order
                                            here</a>.
                                    </p>
                                @endif
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 32px;border-top:1px solid #eee;color:#777;font-size:12px;">
                            If you did not place this order, you can ignore this email.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
