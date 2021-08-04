<?php
namespace LSB\UtilityBundle\Interfaces\Base;

interface BasePackageInterface
{
    const PACKAGE_TYPE_FROM_LOCAL_STOCK = 10;
    const PACKAGE_TYPE_FROM_REMOTE_STOCK = 20;
    const PACKAGE_TYPE_NEXT_SHIPPING = 30;
    const PACKAGE_TYPE_FROM_SUPPLIER = 40;
    const PACKAGE_TYPE_BACKORDER = 50;

    const BACKORDER_PACKAGE_ITEM_SHIPPING_DAYS = 999;
    const LOCAL_PACKAGE_MAX_SHIPPING_DAYS = 2;
    const PACKAGE_MAX_PERIOD = 1;
    const FIRST_POSITION = 1;
}