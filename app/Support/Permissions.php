<?php

namespace App\Support;

/**
 * Centralized permission slugs for Spatie\Permission.
 * Keep names short but clear; values match your route middleware strings.
 *
 * @author Abdul Wadood
 */
final class Permissions
{
    // Countries
    public const CTRY_CREATE = 'core.country.create';

    public const CTRY_UPDATE = 'core.country.update';

    public const CTRY_DESTROY = 'core.country.destroy';

    // States
    public const STATE_CREATE = 'core.state.create';

    public const STATE_UPDATE = 'core.state.update';

    public const STATE_DESTROY = 'core.state.destroy';

    // Currencies
    public const CURR_CREATE = 'core.currency.create';

    public const CURR_UPDATE = 'core.currency.update';

    public const CURR_DESTROY = 'core.currency.destroy';

    // Customer Groups
    public const CGRP_CREATE = 'core.customer.group.create';

    public const CGRP_UPDATE = 'core.customer.group.update';

    public const CGRP_DESTROY = 'core.customer.group.destroy';

    // Order Statuses
    public const ORST_CREATE = 'core.order.status.create';

    public const ORST_UPDATE = 'core.order.status.update';

    public const ORST_DESTROY = 'core.order.status.destroy';

    // Shipment Statuses
    public const SHST_CREATE = 'core.shipment.status.create';

    public const SHST_UPDATE = 'core.shipment.status.update';

    public const SHST_DESTROY = 'core.shipment.status.destroy';

    // Payment Statuses
    public const PYST_CREATE = 'core.payment.status.create';

    public const PYST_UPDATE = 'core.payment.status.update';

    public const PYST_DESTROY = 'core.payment.status.destroy';

    // Invoice Statuses
    public const IVST_CREATE = 'core.invoice.status.create';

    public const IVST_UPDATE = 'core.invoice.status.update';

    public const IVST_DESTROY = 'core.invoice.status.destroy';

    // Refund Statuses
    public const RFST_CREATE = 'core.refund.status.create';

    public const RFST_UPDATE = 'core.refund.status.update';

    public const RFST_DESTROY = 'core.refund.status.destroy';

    // Payment Methods
    public const PYMT_CREATE = 'core.payment.method.create';

    public const PYMT_UPDATE = 'core.payment.method.update';

    public const PYMT_DESTROY = 'core.payment.method.destroy';

    // Shipment Methods
    public const SHMT_CREATE = 'core.shipment.method.create';

    public const SHMT_UPDATE = 'core.shipment.method.update';

    public const SHMT_DESTROY = 'core.shipment.method.destroy';

    // Stock Movement Types
    public const SMT_CREATE = 'core.stock.movement.type.create';

    public const SMT_UPDATE = 'core.stock.movement.type.update';

    public const SMT_DESTROY = 'core.stock.movement.type.destroy';
}
