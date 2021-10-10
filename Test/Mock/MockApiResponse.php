<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace CM\Payments\Test\Mock;

class MockApiResponse
{
    public function getOrderDetaulWithoutPayment()
    {
        return [
            "order_reference" => "100000001",
            "description" => "Order 12345",
            "amount" => 50,
            "currency" => "USD",
            "email" => "johan.devries@docdatapayments.com",
            "language" => "be",
            "country" => "NL",
            "profile" => "test",
            "timestamp" => "2021-07-01T11:59:49Z",
            "expires_on" => "2021-08-05T11:59:49Z",
        ];
    }

    public function getOrderDetail()
    {
        return [
            "order_reference" => "100000001",
            "description" => "Order 12345",
            "amount" => 50,
            "currency" => "USD",
            "email" => "johan.devries@docdatapayments.com",
            "language" => "be",
            "country" => "NL",
            "profile" => "test",
            "timestamp" => "2021-07-01T11:59:49Z",
            "expires_on" => "2021-08-05T11:59:49Z",
            "considered_safe" => [
                "level" => "SAFE",
                "timestamp" => "2021-07-01T12:02:00Z"
            ],
            "payments" => [
                [
                    "id" => "pid4911203603t",
                    "method" => "IDEAL",
                    "authorization" => [
                        "amount" => 42,
                        "currency" => "EUR",
                        "confidence" => "ACQUIRER_APPROVED",
                        "state" => "AUTHORIZED"
                    ],
                    "captures" => [
                        [
                            "amount" => 42,
                            "currency" => "EUR",
                            "state" => "CAPTURED",
                            "date_last_modified" => "2021-07-01T12:02:00Z"
                        ]
                    ]
                ]
            ]
        ];
    }

    public function getOrderDetailCanceledPayment()
    {
        return [
            "order_reference" => "100000001",
            "description" => "Order 12345",
            "amount" => 50,
            "currency" => "USD",
            "email" => "johan.devries@docdatapayments.com",
            "language" => "be",
            "country" => "NL",
            "profile" => "test",
            "timestamp" => "2021-07-01T11:59:49Z",
            "expires_on" => "2021-08-05T11:59:49Z",
            "payments" => [
                [
                    "id" => "pid4911203603t",
                    "method" => "IDEAL",
                    "authorization" => [
                        "amount" => 42,
                        "currency" => "EUR",
                        "state" => "CANCELED"
                    ]
                ]
            ]
        ];
    }

    public function getOrderDetailRedirectedForAuthenticationPayment()
    {
        return [
            "order_reference" => "100000001",
            "description" => "Order 12345",
            "amount" => 50,
            "currency" => "USD",
            "email" => "johan.devries@docdatapayments.com",
            "language" => "be",
            "country" => "NL",
            "profile" => "test",
            "timestamp" => "2021-07-01T11:59:49Z",
            "expires_on" => "2021-08-05T11:59:49Z",
            "payments" => [
                [
                    "id" => "pid4911203603t",
                    "method" => "IDEAL",
                    "authorization" => [
                        "amount" => 42,
                        "currency" => "EUR",
                        "state" => "REDIRECTED_FOR_AUTHENTICATION"
                    ]
                ]
            ]
        ];
    }

    public function getOrderDetailMultiplePayments()
    {
        return [
            "order_reference" => "100000001",
            "description" => "Order 12345",
            "amount" => 50,
            "currency" => "USD",
            "email" => "johan.devries@docdatapayments.com",
            "language" => "be",
            "country" => "NL",
            "profile" => "test",
            "timestamp" => "2021-07-01T11:59:49Z",
            "expires_on" => "2021-08-05T11:59:49Z",
            "considered_safe" => [
                "level" => "SAFE",
                "timestamp" => "2021-07-01T12:02:00Z"
            ],
            "payments" => [
                [
                    "id" => "pid4911203603t",
                    "method" => "IDEAL",
                    "authorization" => [
                        "amount" => 42,
                        "currency" => "EUR",
                        "confidence" => "ACQUIRER_APPROVED",
                        "state" => "AUTHORIZED"
                    ],
                    "captures" => [
                        [
                            "amount" => 42,
                            "currency" => "EUR",
                            "state" => "CAPTURED",
                            "date_last_modified" => "2021-07-01T12:02:00Z"
                        ]
                    ]
                ],
                [
                    "id" => "pid4911203603t",
                    "method" => "MASTERCARD",
                    "authorization" => [
                        "amount" => 42,
                        "currency" => "EUR",
                        "reason" => "01 - Card authentication failed",
                        "state" => "CANCELED"
                    ]
                ]
            ]
        ];
    }

    public function getOrderDetailConsideredFast()
    {
        return [
            "order_reference" => "2021-07-01T11:59:47.920Z",
            "description" => "Order 12345",
            "amount" => 50,
            "currency" => "USD",
            "email" => "johan.devries@docdatapayments.com",
            "language" => "be",
            "country" => "NL",
            "profile" => "test",
            "timestamp" => "2021-07-01T11:59:49Z",
            "expires_on" => "2021-08-05T11:59:49Z",
            "considered_safe" => [
                "level" => "FAST",
                "timestamp" => "2021-07-01T12:02:00Z"
            ],
            "payments" => [
                [
                    "id" => "pid4911203603t",
                    "method" => "IDEAL",
                    "authorization" => [
                        "amount" => 42,
                        "currency" => "EUR",
                        "confidence" => "ACQUIRER_APPROVED",
                        "state" => "AUTHORIZED"
                    ],
                    "captures" => [
                        [
                            "amount" => 42,
                            "currency" => "EUR",
                            "state" => "CAPTURED",
                            "date_last_modified" => "2021-07-01T12:02:00Z"
                        ]
                    ]
                ]
            ]
        ];
    }
}
