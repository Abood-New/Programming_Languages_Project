<?php

namespace App\Enums;

enum OrderStatus
{
    case PENDING = 'pending';
    case DELIVERED = 'delivered';
    case CANCELED = 'canceled';
    case RETURNED = 'returned';
}
