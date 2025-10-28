<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Canonical lookup rows
    | - Codes are your natural keys. Keep them stable.
    | - sort_order helps with UI ordering.
    | - active is true by default; set false to hide without deleting.
    |--------------------------------------------------------------------------
    */

    'tables' => [

        // Order lifecycle
        'order_statuses' => [
            ['code' => 'pending',   'name' => 'Pending',   'sort_order' => 10],
            ['code' => 'paid',      'name' => 'Paid',      'sort_order' => 20],
            ['code' => 'picking',   'name' => 'Picking',   'sort_order' => 30],
            ['code' => 'shipped',   'name' => 'Shipped',   'sort_order' => 40],
            ['code' => 'delivered', 'name' => 'Delivered', 'sort_order' => 50],
            ['code' => 'closed',    'name' => 'Closed',    'sort_order' => 60],
            ['code' => 'cancelled', 'name' => 'Cancelled', 'sort_order' => 70],
            ['code' => 'refunded',  'name' => 'Refunded',  'sort_order' => 80],
        ],

        // Shipment lifecycle (manual & courier)
        'shipment_statuses' => [
            ['code' => 'pending',          'name' => 'Pending',          'sort_order' => 10],
            ['code' => 'out_for_delivery', 'name' => 'Out for Delivery', 'sort_order' => 20],
            ['code' => 'in_transit',       'name' => 'In Transit',       'sort_order' => 30],
            ['code' => 'delivered',        'name' => 'Delivered',        'sort_order' => 40],
            ['code' => 'returned',         'name' => 'Returned',         'sort_order' => 50],
            ['code' => 'cancelled',        'name' => 'Cancelled',        'sort_order' => 60],
            ['code' => 'failed',           'name' => 'Failed',           'sort_order' => 70],
        ],

        // Payment lifecycle
        'payment_statuses' => [
            ['code' => 'pending',   'name' => 'Pending',   'sort_order' => 10],
            ['code' => 'succeeded', 'name' => 'Succeeded', 'sort_order' => 20],
            ['code' => 'failed',    'name' => 'Failed',    'sort_order' => 30],
            ['code' => 'cancelled', 'name' => 'Cancelled', 'sort_order' => 40],
        ],

        // Invoice lifecycle
        'invoice_statuses' => [
            ['code' => 'issued', 'name' => 'Issued', 'sort_order' => 10],
            ['code' => 'voided', 'name' => 'Voided', 'sort_order' => 20],
            ['code' => 'paid',   'name' => 'Paid',   'sort_order' => 30],
        ],

        // Refund lifecycle
        'refund_statuses' => [
            ['code' => 'requested', 'name' => 'Requested', 'sort_order' => 10],
            ['code' => 'approved',  'name' => 'Approved',  'sort_order' => 20],
            ['code' => 'processed', 'name' => 'Processed', 'sort_order' => 30],
            ['code' => 'failed',    'name' => 'Failed',    'sort_order' => 40],
            ['code' => 'cancelled', 'name' => 'Cancelled', 'sort_order' => 50],
        ],

        // Pyament methods (expand as gateways will be added)
        'payment_methods' => [
            ['code' => 'stripe',    'name' => 'Stripe',            'sort_order' => 10],
            ['code' => 'paypal',    'name' => 'PayPal',            'sort_order' => 20],
            ['code' => 'payfast',   'name' => 'PayFast',           'sort_order' => 30],
            ['code' => 'cod',       'name' => 'Cash on Delivery',  'sort_order' => 40],
            ['code' => 'easypaisa', 'name' => 'Easypaisa',         'sort_order' => 50],
            ['code' => 'jazzcash',  'name' => 'JazzCash',          'sort_order' => 60],
        ],

        // Shipment methods
        'shipment_methods' => [
            ['code' => 'pickup',  'name' => 'Pickup',             'sort_order' => 10],
            ['code' => 'self',    'name' => 'Own Courier',        'sort_order' => 20],
            ['code' => 'courier', 'name' => '3rd-Party Courier',  'sort_order' => 30],
        ],

        // Inventory movement types
        'stock_movement_types' => [
            ['code' => 'purchase',    'name' => 'Purchase',     'sort_order' => 10],
            ['code' => 'sale',        'name' => 'Sale',         'sort_order' => 20],
            ['code' => 'refund',      'name' => 'Refund',       'sort_order' => 30],
            ['code' => 'adjustment',  'name' => 'Adjustment',   'sort_order' => 40],
            ['code' => 'transfer_in', 'name' => 'Transfer In',  'sort_order' => 50],
            ['code' => 'transfer_out', 'name' => 'Transfer Out', 'sort_order' => 60],
        ],
    ],

];
